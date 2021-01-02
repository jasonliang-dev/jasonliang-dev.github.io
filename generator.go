package main

import (
	"bufio"
	"bytes"
	"fmt"
	"github.com/yuin/goldmark"
	"github.com/yuin/goldmark-meta"
	"github.com/yuin/goldmark/parser"
	"github.com/yuin/goldmark/renderer/html"
	"gopkg.in/yaml.v2"
	"html/template"
	"io"
	"os"
	"path"
	"path/filepath"
	"sort"
	"strings"
	"time"
)

func check(err error) {
	if err != nil {
		panic(err)
	}
}

func must_readf(filename string) []byte {
	bytes, err := os.ReadFile(filename)
	check(err)
	return bytes
}

func file_str(filename string) string {
	return string(must_readf(filename))
}

func file_newer(path string, fileTime time.Time) bool {
	file, err := os.Open(path)
	if err != nil {
		return false
	}
	defer file.Close()

	info, err := file.Stat()
	if err != nil {
		return false
	}

	return info.ModTime().After(fileTime)
}

func copy_file(source string, dest string) {
	in, err := os.Open(source)
	check(err)
	defer in.Close()

	out, err := os.Create(dest)
	check(err)
	defer out.Close()

	_, err = io.Copy(out, in)
	check(err)
}

func path_drop_first(path string) string {
	split := strings.Split(path, string(os.PathSeparator))
	return strings.Join(split[1:], string(os.PathSeparator))
}

func render(outfile string, tmpl *template.Template, data interface{}) {
	fo, err := os.Create(outfile)
	check(err)
	w := bufio.NewWriter(fo)
	check(tmpl.Execute(w, data))
	check(w.Flush())
	check(fo.Close())
}

type Post struct {
	Name    string
	Title   string
	Date    string
	Content template.HTML
}

func main() {
	md := goldmark.New(goldmark.WithExtensions(meta.Meta), goldmark.WithRendererOptions(html.WithUnsafe()))

	var site interface{}
	check(yaml.Unmarshal(must_readf("src/site.yaml"), &site))

	files, err := os.ReadDir("src")
	check(err)

	var posts []Post
	for _, file := range files {
		if file.IsDir() {
			continue
		}

		split := strings.Split(file.Name(), ".")
		if len(split) == 2 && split[1] == "md" {
			in := must_readf(path.Join("src", file.Name()))

			var buff bytes.Buffer
			ctx := parser.NewContext()
			err = md.Convert(in, &buff, parser.WithContext(ctx))
			check(err)
			front := meta.Get(ctx)

			posts = append(posts, Post{
				Name:    split[0],
				Title:   fmt.Sprint(front["title"]),
				Date:    fmt.Sprint(front["date"]),
				Content: template.HTML(buff.String()),
			})
		}
	}

	sort.Slice(posts, func(i, j int) bool {
		return posts[i].Date > posts[j].Date
	})

	check(os.MkdirAll("dist", os.ModePerm))

	funcs := map[string]interface{}{
		"safeHTML": func(s string) template.HTML {
			return template.HTML(s)
		},
		"timeParse": func(src string, dst string, in string) string {
			t, err := time.Parse(src, in)
			check(err)
			return t.Format(dst)
		},
	}

	tmust := template.Must
	tbase := tmust(template.New("base").Funcs(funcs).Parse(file_str("src/_base.html")))

	tindex := tmust(tmust(tbase.Clone()).Parse(file_str("src/index.html")))
	render("dist/index.html", tindex, map[string]interface{}{"Posts": posts, "Site": site})

	tpost := tmust(tmust(tbase.Clone()).Parse(file_str("src/_post.html")))
	for _, p := range posts {
		render(path.Join("dist", p.Name+".html"), tpost, map[string]interface{}{"Post": p, "Site": site})
	}

	filepath.Walk("static", func(filepath string, info os.FileInfo, err error) error {
		check(err)

		if !info.IsDir() {
			dest := strings.ReplaceAll(path.Join("dist", path_drop_first(filepath)), "\\", "/")
			if !file_newer(dest, info.ModTime()) {
				check(os.MkdirAll(path.Dir(dest), os.ModePerm))
				copy_file(filepath, dest)
			}
		}

		return nil
	})
}
