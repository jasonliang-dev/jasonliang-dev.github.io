<?php section_begin() ?>
  <title>Jason Liang</title>
<?php section_end('seo') ?>

<?php section_begin() ?>
  <h1>Jason Liang</h1>

  <ul class="post-list">
    <?php foreach ($posts as $k => $post): ?>
      <li>
        <a href="<?= $k ?>.html">
          <span><?= $post['title'] ?></span>
          <span class="dim"><?= date('F j, Y', strtotime($post['date'])) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
<?php section_end('content') ?>
