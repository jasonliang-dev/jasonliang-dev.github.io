<?php

require 'Parsedown.php';
require 'ParsedownExtra.php';

$posts = [];

foreach (scandir('posts') as $post) {
  if ($post === '.' || $post === '..') {
    continue;
  }

  $content = file_get_contents("posts/$post");
  $sep0 = strpos($content, '---') + 3;
  $sep1 = strpos($content, '---', $sep0);

  $frontmatter = substr($content, $sep0, $sep1 - 3);

  $data = [];
  foreach (explode("\n", $frontmatter) as $line) {
    if (!empty($line)) {
      $i = strpos($line, ':');
      $data[substr($line, 0, $i)] = trim(substr($line, $i + 1));
    }
  }

  $basename = substr($post, 0, strrpos($post, '.'));
  $posts[$basename] = array_merge([ 'content' => substr($content, $sep1 + 3) ], $data);
}

uasort($posts, static function (array $lhs, array $rhs) {
  return strcmp($rhs['date'], $lhs['date']);
});

$sections = [];

function section_begin() {
  ob_start();
}

function section_end(string $name) {
  global $sections;
  $sections[$name] = ob_get_clean();
}

function render(string $path) {
  global $sections;
  global $posts;

  if (!empty($posts[$path])) {
    extract($posts[$path]);
    require 'pages/post.php';
  } else if (file_exists("pages/$path.php")) {
    require "pages/$path.php";
  } else {
    http_response_code(404);
    die;
  }

  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $sections['seo'] ?>

    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-1691VYRF8G');
    </script>

    <style><?= file_get_contents('style.css') ?></style>
  </head>

  <body>
    <?= $sections['content'] ?>

    <footer class="dim">
      <?php if ($path === 'index'): ?>
        Jason Liang
      <?php else: ?>
        <a href="/">Jason Liang</a>
      <?php endif ?>
      &middot;
      <a href="https://github.com/jasonliang-dev/">GitHub</a>
      &middot;
      jasonliang512@gmail.com
    </footer>
  </body>

  <!-- be nice to people -->

  </html>
  <?php
}

function render_to_file(string $path, string $file) {
  ob_start();
  render($path);
  file_put_contents($file, ob_get_clean());
  echo "$path -> $file" . PHP_EOL;
}

if (php_sapi_name() === 'cli') {
  if (file_exists("dist")) {
    system(PHP_OS_FAMILY === "Windows" ? "rmdir /s /q dist" : "rm -rf dist");
  }
  mkdir("dist");

  render_to_file('index', 'dist/index.html');
  foreach ($posts as $k => $post) {
    render_to_file($k, "dist/$k.html");
  }

  if (PHP_OS_FAMILY === "Windows") {
    system('xcopy public dist\public /s /e /I');
    system('copy favicon.ico dist');
    system('copy CNAME dist');
  } else {
    system('cp -r public dist/public');
    system('cp favicon.ico dist');
    system('cp CNAME dist');
  }
} else {
  if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
  }

  $path = parse_url($_SERVER["REQUEST_URI"])["path"];
  $path = substr($path, 1, strrpos($path, '.') - 1);
  if ($path === '') {
    $path = 'index';
  }

  render($path);
}
