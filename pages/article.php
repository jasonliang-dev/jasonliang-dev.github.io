<nav class="tc pv4">
  <a class="b link near-black dm-near-white dim f4" href="/">Jason Liang</a>
</nav>
<article class="prose">
  <time class="gray flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px">
      <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
    </svg>
    <span class="ml1 f6 fw5">
      <?= date("F j, Y", strtotime($date)) ?>
    </span>
  </time>
  <h1 class="mt1"><?= htmlspecialchars($title) ?></h1>
  <div class="lh-copy">
    <?= Parsedown::instance()->text(file_get_contents("pages/$name.md")) ?>
  </div>
</article>
<script>hljs.highlightAll();</script>
