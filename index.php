<?php

require "Parsedown.php";

function render(string $page, array $vars) {
  extract($vars);

  if (isset($title)) {
    $page_title = "$title | Jason Liang";
  } else {
    $page_title = "Jason Liang";
  }

  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1691VYRF8G"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-1691VYRF8G');
    </script>
    <link rel="stylesheet" href="static/tachyons.min.css">
    <link rel="stylesheet" href="static/default-dark.min.css">
    <link rel="stylesheet" href="static/style.css">
    <script src="static/highlight.min.js"></script>
    <script>
      const prefersDark = localStorage.theme === undefined && window.matchMedia("(prefers-color-scheme: dark)").matches;
      if (localStorage.theme === "dark" || prefersDark) {
        document.documentElement.classList.add("dark-mode");
      } else {
        document.documentElement.classList.remove("dark-mode");
      }

      function toggleDark() {
        if (document.documentElement.classList.contains("dark-mode")) {
          document.documentElement.classList.remove("dark-mode");
          localStorage.theme = "light";
          if (window.changeTheme) {
            window.changeTheme("light");
          }
        } else {
          document.documentElement.classList.add("dark-mode");
          localStorage.theme = "dark";
          if (window.changeTheme) {
            window.changeTheme("dark");
          }
        }
      }
    </script>
  </head>
  <body class="bg-near-white near-black dm-bg-near-black dm-near-white mb6">
    <button
      class="
        bg-near-black near-white hover-bg-mid-gray
        dm-bg-near-white dm-near-black dm-hover-bg-moon-gray
        fixed top-0 right-0 z-999
        flex justify-center items-center mr3 mt3
        shadow bn br-pill
      "
      type="button"
      style="width: 2.25rem; height: 2.25rem"
      onclick="toggleDark()"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px">
        <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 01.26.77 7 7 0 009.958 7.967.75.75 0 011.067.853A8.5 8.5 0 116.647 1.921a.75.75 0 01.808.083z" clip-rule="evenodd" />
      </svg>
    </button>
    <?php require "pages/$page.php" ?>
  </body>
  </html>
  <!-- be nice to people -->
  <?php
  return true;
}

function url(string $url) {
  return php_sapi_name() === "cli" ? "$url.html" : "/$url";
}

$posts = [
  [
    "title" => "C++ Coding Style",
    "date" => "2023-01-06",
    "name" => "cpp-style",
  ],
  [
    "title" => "Using Lua Metatables With The C API",
    "date" => "2022-11-30",
    "name" => "metatables-in-c",
  ],
  [
    "title" => "Lua Coroutines By Example",
    "date" => "2022-04-06",
    "name" => "coroutines",
  ],
  [
    "title" => "A Tiny OpenGL 2D Batch Renderer",
    "date" => "2022-01-04",
    "name" => "batch-renderer",
  ],
  [
    "title" => "Write Your Own C++ Unit Testing Library",
    "date" => "2022-01-03",
    "name" => "unit-testing",
  ],
];

if (php_sapi_name() === "cli") {
  if (file_exists("dist")) {
    system(PHP_OS_FAMILY === "Windows" ? "rmdir /s /q dist" : "rm -rf dist");
  }

  mkdir("dist");
  ob_start();

  render("index", ["posts" => $posts]);
  file_put_contents("dist/index.html", ob_get_contents());
  ob_clean();

  foreach ($posts as $post) {
    render("article", $post);
    file_put_contents("dist/{$post["name"]}.html", ob_get_contents());
    ob_clean();
  }

  ob_end_clean();

  if (PHP_OS_FAMILY === "Windows") {
    system("xcopy static dist\\static /s /e /I");
    system("copy favicon.ico dist");
    system("copy CNAME dist");
  } else {
    system("cp -r static dist/static");
    system("cp favicon.ico dist");
    system("cp CNAME dist");
  }
} else {
  $uri = parse_url($_SERVER["REQUEST_URI"])["path"];
  switch ($uri) {
  case "/":
    render("index", ["posts" => $posts]) and die();

  default:
    foreach ($posts as $post) {
      if ($uri === "/{$post["name"]}") {
        render("article", $post) and die();
      }
    }

    http_response_code(404);
    header("Content-Type: text/plain");
    echo "'$uri' Not Found";
    die();
  }
}
