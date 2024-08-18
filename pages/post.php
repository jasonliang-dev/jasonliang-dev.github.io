<?php section_begin() ?>
  <title><?= $title ?> - Jason Liang</title>
<?php section_end('seo') ?>

<?php section_begin() ?>
  <div class="post">
    <nav>
      <a href="/">Jason Liang</a>
    </nav>

    <time class="dim" datetime="<?= $date ?>"><?= date('F j, Y', strtotime($date)) ?></time>
    <h1><?= $title ?></h1>
    <article>
      <?= ParsedownExtra::instance()->text($content) ?>
    </article>
  </div>

  <script src="/public/highlight.min.js"></script>
  <script>
    hljs.registerLanguage('wgsl', hljs => {
      return {
        name: 'WGSL',
        aliases: ['wgsl', 'language-wgsl'],
        keywords: {
          keyword: 'alias break case const const_assert continue continuing default diagnostic discard else enable false fn for if let loop override requires return struct switch true var while',
          type: 'vec2f vec3f vec4f',
        },
        contains: [
          hljs.C_LINE_COMMENT_MODE,
          hljs.C_BLOCK_COMMENT_MODE,
          {
            className: 'built_in',
            begin: /@\w+/,
          },
          {
            beginKeywords: 'struct',
            end: /{/,
            contains: [hljs.TITLE_MODE],
          },
        ]
      }
    });

    hljs.highlightAll();
  </script>
<?php section_end('content') ?>