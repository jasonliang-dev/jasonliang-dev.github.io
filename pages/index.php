<?php

use function htmlspecialchars as h;

$projects = [
  [
    "title" => "file-sink",
    "desc" => "Tiny SFTP client with automatic file synchronization.",
    "img" => "static/images/sftp.png",
    "github" => "https://github.com/jasonliang-dev/file-sink",
    "link" => false,
    "color" => "#334155",
    "dark_color" => "#1e293b",
  ],
  [
    "title" => "odin-lua",
    "desc" => "Lua 5.4.4 and LuaJIT support for the Odin programming language.",
    "img" => "static/images/odin-lua.png",
    "github" => "https://github.com/jasonliang-dev/odin-lua",
    "link" => false,
    "color" => "#1e40af",
    "dark_color" => "#1e3a8a",
  ],
  [
    "title" => "shooty-game",
    "desc" => "A game where you shoot stuff. Made with C++ and Lua, without an engine.",
    "img" => "static/images/shooty-game.png",
    "github" => "https://github.com/jasonliang-dev/shooty-game",
    "link" => "https://jasonliang.js.org/shooty-game/",
    "color" => "#3f6212",
    "dark_color" => "#365314",
  ],
  [
    "title" => "Heroicons for Elm",
    "desc" => "Web app that provides SVG icons for the Elm programming language.",
    "img" => "static/images/elm-heroicons.png",
    "github" => "https://github.com/jasonliang-dev/heroicons-for-elm",
    "link" => "https://jasonliang.js.org/heroicons-for-elm/",
    "color" => "#6b21a8",
    "dark_color" => "#581c87",
  ],
  [
    "title" => "Yet Another Chat App",
    "desc" => "Chat application that uses the MERN stack. Messages are sent in real time using Socket.IO.",
    "img" => "static/images/chat-app.png",
    "github" => "https://github.com/jasonliang-dev/yet-another-chat-app",
    "link" => false,
    "color" => "#166534",
    "dark_color" => "#14532d",
  ],
  [
    "title" => "Temtem Type Calculator",
    "desc" => "For the game Temtem, view matchups between different creature types.",
    "img" => "static/images/temtem.png",
    "github" => "https://github.com/jasonliang-dev/temtem-type-calculator",
    "link" => "https://temtypecalc.netlify.app/",
    "color" => "#854d0e",
    "dark_color" => "#713f12",
  ],
  [
    "title" => "Wildermaze",
    "desc" => "A game where you escape a maze while avoiding wolves. Created during BC Game Jam 2020.",
    "img" => "static/images/maze.png",
    "github" => "https://github.com/jasonliang-dev/wildermaze",
    "link" => "https://jasonliang.js.org/wildermaze/",
    "color" => "#115e59",
    "dark_color" => "#134e4a",
  ],
  [
    "title" => "Space Shooter Game",
    "desc" => "Avoid asteroids and enemy fire in a fast paced arcade romp.",
    "img" => "static/images/spaceshooter.png",
    "github" => "https://github.com/jasonliang-dev/space-shooter",
    "link" => false,
    "color" => "#86198f",
    "dark_color" => "#701a75",
  ],
  [
    "title" => "lite-vim",
    "desc" => "A plugin for the Lite text editor that emulates a subset of Vim.",
    "img" => "static/images/lite-vim.png",
    "github" => "https://github.com/jasonliang-dev/lite-vim",
    "link" => false,
    "color" => "#334155",
    "dark_color" => "#1e293b",
  ],
  [
    "title" => "Entity Component System",
    "desc" => "Archetype entity component system library for C.",
    "img" => "static/images/ecs.png",
    "github" => "https://github.com/jasonliang-dev/entity-component-system",
    "link" => false,
    "color" => "#9a3412",
    "dark_color" => "#7c2d12",
  ],
  [
    "title" => "Costello",
    "desc" => "BetterDiscord plugin that lets you save and rapidly send a collection of images.",
    "img" => "static/images/discord-stickers.png",
    "github" => "https://github.com/jasonliang-dev/costello",
    "link" => false,
    "color" => "#3730a3",
    "dark_color" => "#312e81",
  ],
  [
    "title" => "dream-eater",
    "desc" => "Emacs minor mode that respects Dreamweaver's check in/out system.",
    "img" => "static/images/php.png",
    "github" => "https://github.com/jasonliang-dev/dream-eater",
    "link" => false,
    "color" => "#6b21a8",
    "dark_color" => "#581c87",
  ],
  [
    "title" => "lulu",
    "desc" => "Tiny lispy toy programming language. Written in Haskell under 300 SLOC.",
    "img" => "static/images/lulu.png",
    "github" => "https://github.com/jasonliang-dev/lulu",
    "link" => false,
    "color" => "#9d174d",
    "dark_color" => "#831843",
  ],
  [
    "title" => "Game of Life",
    "desc" => "Conway's Game of Life written in C with SDL2.",
    "img" => "static/images/game-of-life.png",
    "github" => "https://github.com/jasonliang-dev/game-of-life",
    "link" => false,
    "color" => "#334155",
    "dark_color" => "#1e293b",
  ],
];

?>
<header>
  <div class="vh-100 overflow-hidden">
    <canvas id="canvas"></canvas>
  </div>
</header>
<main>
  <div class="mw8 center mb5">
    <h2 class="mb4 ph3">Posts</h2>
    <ul class="pl0 list">
      <?php foreach ($posts as $post): ?>
        <li>
          <a
            href="<?= url($post["name"]) ?>"
            class="
              link br2
              hover-bg-black-10 dm-hover-bg-black-40
              flex flex-wrap flex-nowrap-ns ph3 pv3
            "
          >
            <time class="gray dib f6 f5-ns" style="min-width: 12rem">
              <?= date("F j, Y", strtotime($post["date"])) ?>
            </time>
            <span class="fw5 dark-gray dm-moon-gray w-100">
              <?= h($post["title"]) ?>
            </span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
  <div class="mw8 center mb5">
    <h2 class="mb4 ph3">Projects</h2>
    <div class="flex flex-wrap items-stretch">
      <?php foreach ($projects as $proj): ?>
        <div class="pa3 w-100 w-50-m w-third-l">
          <div
            class="project-card shadow br3 overflow-hidden h-100 flex flex-column"
            style="
              --color: <?= h($proj["color"]) ?>;
              --dark-color: <?= h($proj["dark_color"]) ?>;
            "
          >
            <a class="link db h-100" href="<?= h($proj["github"]) ?>">
              <img class="w-100" src="<?= h($proj["img"]) ?>" alt="">
              <div class="ph3 flex flex-column <?= $proj["link"] ? "" : "pb3" ?>">
                <div class="flex-auto">
                  <h3 class="near-white mt0 mb2 f5 fw5"><?= h($proj["title"]) ?></h3>
                  <p class="mv0 white-50 lh-copy">
                    <?= h($proj["desc"]) ?>
                  </p>
                </div>
              </div>
            </a>
            <?php if ($proj["link"]): ?>
              <div class="flex justify-between fw5 f6">
                <a
                  class="white-80 pv3 link dim flex justify-center items-center w-100"
                  href="<?= h($proj["github"]) ?>"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px" class="white-60">
                    <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 010 1.06L2.56 10l3.72 3.72a.75.75 0 01-1.06 1.06L.97 10.53a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0zm7.44 0a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L17.44 10l-3.72-3.72a.75.75 0 010-1.06zM11.377 2.011a.75.75 0 01.612.867l-2.5 14.5a.75.75 0 01-1.478-.255l2.5-14.5a.75.75 0 01.866-.612z" clip-rule="evenodd" />
                  </svg>
                  <span class="ml2 mr2">Code</span>
                </a>
                  <a
                    class="white-80 link dim flex justify-center items-center w-100"
                    href="<?= h($proj["link"]) ?>"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px" class="white-60">
                      <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd" />
                      <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml2 mr2">Visit</span>
                  </a>
              </div>
            <?php endif ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
</main>
<script type="module">
  const canvas = document.getElementById("canvas");
  const ctx = canvas.getContext("2d");

  const state = {
    theme: document.documentElement.classList.contains("dark-mode") ? "dark" : "light",
    firstFrame: false,
    reservedWidth: 0,
    reservedHeight: 0,
    scrollPos: 0,
    hue: 90,
    jason: makeFancyText("Jason", 2, 5),
    liang: makeFancyText("Liang", 3, 6),
    about: makeFancyText("I make games and web stuff", 7, 10),
    points: [],
  }

  function changeTheme(theme) {
    state.theme = theme;
  }
  window.changeTheme = changeTheme;

  function onscroll() {
    state.scrollPos = window.scrollY;
  }
  document.addEventListener("scroll", onscroll);

  function onresize() {
    canvas.width = canvas.parentNode.clientWidth;
    canvas.height = canvas.parentNode.clientHeight;

    if (state.reservedWidth < canvas.width || state.reservedHeight < canvas.height) {
      state.reservedWidth = Math.max(state.reservedWidth, canvas.width);
      state.reservedHeight = Math.max(state.reservedHeight, canvas.height);
      makePoints();
    }
  }
  window.addEventListener("resize", onresize);

  let makePointsTimeout = 0;
  function makePoints() {
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
    }, 150);
  }

  function makeFancyText(text, min, max) {
    const arr = [];
    for (const c of text) {
      arr.push({
        char: c,
        x: 0,
        y: 0,
        scrollOff: Math.random() * (max - min) + min,
      });
    }

    return { text, arr };
  }

  function drawFancyText(fancy, opts) {
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

      if (!state.firstFrame) {
        item.x = x;
        item.y = y;
      }

      item.x += (x - item.x) * 0.25;
      item.y += (y - item.y) * 0.25;

      opts.draw(item.char, item.x, item.y);

      advance += widths[i];
    }
  }

  function draw() {
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

    state.firstFrame = true;

    requestAnimationFrame(draw);
  }

  requestAnimationFrame(() => {
    onscroll();
    onresize();
    draw();
  });
</script>
