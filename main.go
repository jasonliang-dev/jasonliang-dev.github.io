package main

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"html/template"
	"net/http"
	"os"
	"os/exec"
	"path"
	"regexp"
	"runtime"
	"sort"
	"strings"
	"time"

	"github.com/fsnotify/fsnotify"
	"github.com/gomarkdown/markdown"
	"github.com/gomarkdown/markdown/html"
	"github.com/gomarkdown/markdown/parser"
	"github.com/gorilla/websocket"
)

type Post struct {
	Title    string
	Date     time.Time
	HTML     template.HTML
	Link     string
	document []byte
}

var (
	pages struct {
		Posts    []Post
		Index    []byte
		NotFound []byte
	}

	funcs = template.FuncMap{
		"IsStatic": func() bool {
			return static
		},
		"ReadJSONArray": func(file string) []interface{} {
			var res []interface{}

			contents := Must1(os.ReadFile(file))
			Must(json.Unmarshal(contents, &res))
			return res
		},
		"Posts": func() []Post {
			return pages.Posts
		},
	}

	static bool
)

func main() {
	usage := "expected one 'build' or 'serve' argument"
	if len(os.Args) != 2 {
		fmt.Println(usage)
	} else if os.Args[1] == "build" {
		Generate("dist")
	} else if os.Args[1] == "serve" {
		Serve()
	} else {
		fmt.Println(usage)
	}
}

func Generate(dest string) {
	static = true

	Write := func(file string, contents []byte) {
		filepath := path.Join(dest, file)
		if err := os.WriteFile(filepath, contents, 0644); err != nil {
			fmt.Println("failed to write: " + filepath)
			panic(err)
		}
	}

	Must(RenderAll())
	if err := System("sass src/style.scss:public/style.css").Run(); err != nil {
		fmt.Println(err)
	}

	Must(os.RemoveAll(dest))
	Must(os.MkdirAll(dest, 0755))
	Write("index.html", pages.Index)
	Write("404.html", pages.NotFound)

	for _, post := range pages.Posts {
		Write(post.Link, post.document)
	}

	if runtime.GOOS == "windows" {
		Must(System(`xcopy public dist\public /s /e /I`).Run())
		Must(System(`xcopy favicon.ico dist`).Run())
		Must(System(`xcopy CNAME dist`).Run())
	} else {
		Must(System(`cp -r public dist/public`).Run())
		Must(System(`cp favicon.ico dist`).Run())
		Must(System(`cp CNAME dist`).Run())
	}
}

func Serve() {
	static = false

	go func() {
		cmd := System("sass --watch src/style.scss:public/style.css")
		cmd.Stdout = os.Stdout
		if err := cmd.Run(); err != nil {
			fmt.Println(err)
		}
	}()

	http.Handle("/public/", http.StripPrefix("/public/", http.FileServer(http.Dir("public/"))))
	http.Handle("/src/", http.StripPrefix("/src/", http.FileServer(http.Dir("src/"))))

	http.HandleFunc("/favicon.ico", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusNoContent)
	})

	http.HandleFunc("/ws", HandleHotReload)
	http.HandleFunc("/", HandlePages)

	fmt.Println("serving: localhost:8181")
	Must(http.ListenAndServe("localhost:8181", nil))
}

func HandleHotReload(w http.ResponseWriter, r *http.Request) {
	upgrader := websocket.Upgrader{
		ReadBufferSize:  1024,
		WriteBufferSize: 1024,
	}
	conn, _ := upgrader.Upgrade(w, r, nil)

	watcher, err := fsnotify.NewWatcher()
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	defer watcher.Close()

	done := make(chan bool)
	go func() {
		for {
			select {
			case e, ok := <-watcher.Events:
				if ok && !e.Has(fsnotify.Chmod) {
					conn.WriteMessage(websocket.TextMessage, []byte("reload"))
				}
				done <- true
				return
			case <-watcher.Errors:
				done <- true
				return
			}
		}
	}()

	watch := []string{"public", "posts", "pages", "data"}
	for _, dir := range watch {
		if err := watcher.Add(dir); err != nil {
			http.Error(w, err.Error(), 500)
			return
		}
	}
	<-done
}

func HandlePages(w http.ResponseWriter, r *http.Request) {
	if err := RenderAll(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if r.URL.Path == "/" {
		w.Write(pages.Index)
		return
	}

	for _, page := range pages.Posts {
		if r.URL.Path == page.Link {
			w.Write(page.document)
			return
		}
	}

	w.WriteHeader(http.StatusNotFound)
	w.Write(pages.NotFound)
}

func RenderAll() error {
	entries, err := os.ReadDir("posts")
	if err != nil {
		return err
	}

	posts := make([]Post, 0, len(entries))
	for _, file := range entries {
		post, err := RenderMarkdown(file.Name())
		if err != nil {
			return err
		} else {
			posts = append(posts, post)
		}
	}

	sort.Slice(posts, func(i, j int) bool {
		return posts[j].Date.Before(posts[i].Date)
	})

	pages.Posts = posts

	pages.Index, err = RenderHTML("pages/index.html")
	if err != nil {
		return err
	}

	pages.NotFound, err = RenderHTML("pages/404.html")
	if err != nil {
		return err
	}

	return nil
}

func RenderHTML(file string) ([]byte, error) {
	var b bytes.Buffer
	t := template.New("_base.html").Funcs(funcs)
	t, err := t.ParseFiles("pages/_base.html", file)
	if err != nil {
		return nil, err
	}

	if err := t.Execute(&b, nil); err != nil {
		return nil, err
	} else {
		return b.Bytes(), nil
	}
}

func RenderMarkdown(file string) (Post, error) {
	contents, err := os.ReadFile("posts/" + file)
	if err != nil {
		return Post{}, err
	}

	re, err := regexp.Compile(`---\ntitle:\s*(.*)\ndate:\s*(.*)\n---\n`)
	if err != nil {
		return Post{}, err
	}

	match := re.FindStringSubmatch(string(contents))
	if len(match) != 3 {
		return Post{}, errors.New("expected title and date from frontmatter")
	}

	title := match[1]

	datetime, err := time.Parse("2006-01-02", match[2])
	if err != nil {
		return Post{}, err
	}

	md := contents[len(match[0]):]

	ext := parser.CommonExtensions | parser.AutoHeadingIDs | parser.NoEmptyLineBeforeBlock
	doc := parser.NewWithExtensions(ext).Parse(md)

	opts := html.RendererOptions{Flags: html.CommonFlags | html.HrefTargetBlank}
	renderer := html.NewRenderer(opts)

	html := markdown.Render(doc, renderer)

	post := Post{
		Title: title,
		Date:  datetime,
		HTML:  template.HTML(html),
		Link:  "/" + file[:len(file)-len(".md")] + ".html",
	}

	var b bytes.Buffer
	t := template.New("_base.html").Funcs(funcs)
	t, err = t.ParseFiles("pages/_base.html", "pages/_post.html")
	if err != nil {
		return Post{}, err
	}

	if err := t.Execute(&b, post); err != nil {
		return Post{}, err
	}

	post.document = b.Bytes()
	return post, nil
}

func System(cmd string) *exec.Cmd {
	split := strings.Fields(cmd)
	return exec.Command(split[0], split[1:]...)
}

func Must(err error) {
	if err != nil {
		panic(err)
	}
}

func Must1[T any](t T, err error) T {
	if err != nil {
		panic(err)
	}
	return t
}
