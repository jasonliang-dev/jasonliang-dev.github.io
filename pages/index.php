<header>
  <div class="vh-100 overflow-hidden">
    <canvas id="canvas"></canvas>
  </div>
</header>
<main>
  <section class="mw8 center mb5">
    <h2 class="mb3 ph3">Posts</h2>
    <ul id="post-list" class="pl0 list">
      <?php foreach (SiteData::Posts as $i => $post): ?>
        <li
          class="slide-up pause-animation"
          style="animation-duration: <?= round(sqrt($i + 1) * 300) ?>ms"
        >
          <a
            href="<?= url($post["name"]) ?>"
            class="
              link br2
              hover-bg-black-10 dm-hover-bg-black-50
              flex flex-wrap flex-nowrap-ns ph3 pv3 nt1 nb1
            "
          >
            <time class="gray dib f6 f5-ns" style="min-width: 12rem">
              <?= date("Y F j", strtotime($post["date"])) ?>
            </time>
            <span class="fw5 dark-gray dm-moon-gray w-100">
              <?= $post["title"] ?>
            </span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  </section>
  <section class="mw8 center mb5">
    <h2 class="mb3 ph3">Projects</h2>
    <div id="project-list" class="flex flex-wrap items-stretch">
      <?php foreach (SiteData::Projects as $i => $proj): ?>
        <div class="pa3 w-100 w-50-m w-third-l">
          <div
            class="project-card shadow br3 overflow-hidden h-100 flex flex-column slide-up pause-animation"
            style="
              --color: <?= $proj["color"] ?>;
              --dark-color: <?= $proj["dark_color"] ?>;
              animation-duration: <?= round(sqrt(($i * 0.4) + 1) * 500) ?>ms;
            "
          >
            <a
              class="link db h-100"
              href="<?= $proj["link"] ?: $proj["github"] ?>"
            >
              <img
                src="<?= $proj["img"] ?>"
                alt="<?= $proj["alt"] ?>"
                loading="lazy"
                decoding="async"
              >
              <div class="ph3 flex flex-column <?= $proj["link"] ? "" : "pb3" ?>">
                <div class="flex-auto">
                  <h3 class="near-white mt0 mb2 f5 fw5"><?= $proj["title"] ?></h3>
                  <p class="mv0 white-50 lh-copy">
                    <?= $proj["desc"] ?>
                  </p>
                </div>
              </div>
            </a>
            <?php if ($proj["link"]): ?>
              <div class="flex justify-between fw5 f6">
                <a
                  class="white-80 pv3 link dim flex justify-center items-center w-100"
                  href="<?= $proj["github"] ?>"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="white-60" width="20" height="20" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                  <span class="ml2 mr2">GitHub</span>
                </a>
                  <a
                    class="white-80 link dim flex justify-center items-center w-100"
                    href="<?= $proj["link"] ?>"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px" class="white-60">
                      <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd" />
                      <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml2 mr2">View</span>
                  </a>
              </div>
            <?php endif ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </section>
</main>
<script>
  const canvas = document.getElementById("canvas");
  const ctx = canvas.getContext("2d");

  const state = {
    theme: document.documentElement.classList.contains("dark-mode") ? "dark" : "light",
    motionReduced: window.matchMedia("(prefers-reduced-motion)").matches,
    fontsLoaded: false,
    fancyTextDrawn: false,
    reservedWidth: 0,
    reservedHeight: 0,
    scrollPos: 0,
    hue: 90,
    jason: makeFancyText("Jason", 2, 5),
    liang: makeFancyText("Liang", 3, 6),
    about: makeFancyText("I make interactive programs", 7, 10),
    lineAlpha: 0,
    points: [],
  };

  window.onToggleDark = theme => {
    state.theme = theme;
  };

  function onscroll() {
    state.scrollPos = window.scrollY;
  }
  document.addEventListener("scroll", onscroll);

  let resizeTimeout = 0;
  function onresize() {
    canvas.width = canvas.parentNode.clientWidth;
    canvas.height = canvas.parentNode.clientHeight;

    if (state.reservedWidth < canvas.width || state.reservedHeight < canvas.height) {
      state.reservedWidth = Math.max(state.reservedWidth, canvas.width);
      state.reservedHeight = Math.max(state.reservedHeight, canvas.height);
      makePoints();
    } else if (state.motionReduced) {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(() => {
        draw();
      }, 150);
    }
  }
  window.addEventListener("resize", onresize);

  let makePointsTimeout = 0;
  function makePoints() {
    state.lineAlpha = 0;
    clearTimeout(makePointsTimeout);
    makePointsTimeout = setTimeout(() => {
      const PI2 = Math.PI * 2;
      const arr = [];
      for (let y = 0; y < window.innerHeight; y += 120) {
        for (let x = 0; x < window.innerWidth; x += 120) {
          arr.push({
            x,
            y,
            angle: Math.random() * PI2,
            mag: Math.random() * 20 + 10,
            angleVel: Math.random() * 0.002 + 0.003,
          });
        }
      }
      state.points = arr;

      if (state.motionReduced) {
        draw();
      }
    }, 150);
  }

  function makeFancyText(text, min, max) {
    const arr = [];
    let i = text.length;
    for (const c of text) {
      arr.push({
        char: c,
        x: 0,
        y: 0,
        scrollOff: Math.random() * (max - min) + min,
        alpha: 0,
        alphaVel: 0.02 * i,
      });
      i--;
    }

    return { text, arr };
  }

  function drawFancyText(fancy, opts) {
    if (!state.fontsLoaded) {
      return;
    }

    const widths = [];
    for (const item of fancy.arr) {
      widths.push(ctx.measureText(item.char).width);
    }

    const left = (canvas.width - ctx.measureText(fancy.text).width) / 2;
    let advance = 0;
    for (let i = 0; i < fancy.arr.length; i++) {
      const item = fancy.arr[i];

      const xGap = (i - fancy.arr.length / 2) * state.scrollPos * opts.xGapFactor;
      const x = left + advance + xGap;
      const y = opts.y - item.scrollOff * state.scrollPos * opts.scrollFactor;

      if (!state.fancyTextDrawn || state.motionReduced) {
        item.x = x;
        item.y = y;
      } else {
        item.x += (x - item.x) * 0.25;
        item.y += (y - item.y) * 0.25;
      }

      item.alpha = Math.min(item.alpha + item.alphaVel, 1);
      ctx.globalAlpha = item.alpha;

      opts.draw(item.char, item.x, item.y);

      advance += widths[i];
    }
  }

  function draw() {
    if (state.motionReduced) {
      state.scrollPos = 0;
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (state.hue >= 360) {
      state.hue -= 360;
    }
    state.hue += 0.15;

    for (const p of state.points) {
      p.angle += p.angleVel;
    }

    let headerFontSize = 250;
    let fontSize = 30;
    if (window.innerHeight < 700 || window.innerWidth < 900) {
      headerFontSize = 150;
      fontSize = 20;
    }

    if (window.innerWidth < 600) {
      headerFontSize = 100;
    }

    const top = window.innerHeight * 0.4;

    ctx.lineWidth = 1;

    state.lineAlpha = Math.min(state.lineAlpha + 0.01, 1);
    ctx.globalAlpha = state.lineAlpha;

    if (state.theme === "dark") {
      ctx.strokeStyle = "rgb(255 255 255 / 0.08)";
    } else {
      ctx.strokeStyle = "rgb(0 0 0 / 0.08)";
    }

    ctx.beginPath();
    for (const p1 of state.points) {
      const mag = 33;

      const c1 = Math.cos(p1.angle) * mag;
      const s1 = Math.sin(p1.angle) * mag;
      const x1 = c1 + p1.x;
      const y1 = s1 + p1.y;

      for (const p2 of state.points) {
        if (p1 === p2) {
          continue;
        }
        const c2 = Math.cos(p2.angle) * mag;
        const s2 = Math.sin(p2.angle) * mag;
        const x2 = c2 + p2.x;
        const y2 = s2 + p2.y;

        const dx = x2 - x1;
        const dy = y2 - y1;
        const len = Math.sqrt(dx * dx + dy * dy);
        if (len < 120) {
          ctx.moveTo(x2, y2);
          ctx.lineTo(x1, y1);
        }
      }
    }
    ctx.stroke();

    const opacity = 1 - Math.min(state.scrollPos * 0.003, 1);
    ctx.font = `700 ${fontSize}px Inter`;

    if (state.theme === "dark") {
      ctx.fillStyle = `rgb(250 250 250 / ${opacity})`;
    } else {
      ctx.fillStyle = `rgb(0 0 0 / ${opacity})`;
    }

    drawFancyText(state.about, {
      y: top + headerFontSize + fontSize + 80,
      xGapFactor: 0.03,
      scrollFactor: 0.2,
      draw: ctx.fillText.bind(ctx),
    });

    const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    grad.addColorStop(0.25, `hsl(${state.hue}, 100%, 50%)`);
    grad.addColorStop(0.5, `hsl(${state.hue + 120}, 100%, 50%)`);
    grad.addColorStop(0.75, `hsl(${state.hue + 240}, 100%, 50%)`);

    ctx.strokeStyle = grad;

    ctx.lineWidth = 3;
    ctx.font = `900 ${headerFontSize}px Inter`;

    drawFancyText(state.jason, {
      y: top,
      xGapFactor: 0.1,
      scrollFactor: 0.15,
      draw: ctx.strokeText.bind(ctx),
    });
    drawFancyText(state.liang, {
      y: top + headerFontSize,
      xGapFactor: 0.1,
      scrollFactor: 0.15,
      draw: ctx.strokeText.bind(ctx),
    });

    if (state.fontsLoaded) {
      state.fancyTextDrawn = true;
    }

    if (!state.motionReduced) {
      requestAnimationFrame(draw);
    }
  }

  requestAnimationFrame(() => {
    onscroll();
    onresize();
    draw();
  });

  document.fonts.ready.then(() => {
    state.fontsLoaded = true;
  });

  const observer = new IntersectionObserver(entries => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        for (const item of entry.target.querySelectorAll(".pause-animation")) {
          item.classList.remove("pause-animation");
        }
      }
    }
  }, { rootMargin: "0px 0px -100px 0px" });

  observer.observe(document.getElementById("post-list"));
  observer.observe(document.getElementById("project-list"));
</script>
