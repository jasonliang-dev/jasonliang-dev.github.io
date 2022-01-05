const canvas = document.getElementById("canvas");
const gl = canvas.getContext("webgl");

const vec3 = {
  normalize(out) {
    const length = Math.sqrt(
      out[0] * out[0] + out[1] * out[1] + out[2] * out[2]
    );
    out[0] /= length;
    out[1] /= length;
    out[2] /= length;
    return out;
  },
};

const mat4 = {
  pool: Array.from({ length: 64 }).map(() => new Float32Array(16)),
  poolIndex: 0,

  alloc() {
    if (mat4.poolIndex === mat4.pool.length) {
      console.error("matrix pool exhausted");
    } else {
      return mat4.pool[mat4.poolIndex++];
    }
  },

  flush() {
    mat4.poolIndex = 0;
  },

  identity() {
    const m = mat4.alloc();
    m.fill(0);
    m[0] = m[5] = m[10] = m[15] = 1;
    return m;
  },

  multiply(a, b) {
    const a00 = a[0],
      a01 = a[1],
      a02 = a[2],
      a03 = a[3],
      a04 = a[4],
      a05 = a[5],
      a06 = a[6],
      a07 = a[7],
      a08 = a[8],
      a09 = a[9],
      a10 = a[10],
      a11 = a[11],
      a12 = a[12],
      a13 = a[13],
      a14 = a[14],
      a15 = a[15],
      b00 = b[0],
      b01 = b[1],
      b02 = b[2],
      b03 = b[3],
      b04 = b[4],
      b05 = b[5],
      b06 = b[6],
      b07 = b[7],
      b08 = b[8],
      b09 = b[9],
      b10 = b[10],
      b11 = b[11],
      b12 = b[12],
      b13 = b[13],
      b14 = b[14],
      b15 = b[15];

    const m = mat4.alloc();
    m[0] = a00 * b00 + a01 * b04 + a02 * b08 + a03 * b12;
    m[1] = a00 * b01 + a01 * b05 + a02 * b09 + a03 * b13;
    m[2] = a00 * b02 + a01 * b06 + a02 * b10 + a03 * b14;
    m[3] = a00 * b03 + a01 * b07 + a02 * b11 + a03 * b15;
    m[4] = a04 * b00 + a05 * b04 + a06 * b08 + a07 * b12;
    m[5] = a04 * b01 + a05 * b05 + a06 * b09 + a07 * b13;
    m[6] = a04 * b02 + a05 * b06 + a06 * b10 + a07 * b14;
    m[7] = a04 * b03 + a05 * b07 + a06 * b11 + a07 * b15;
    m[8] = a08 * b00 + a09 * b04 + a10 * b08 + a11 * b12;
    m[9] = a08 * b01 + a09 * b05 + a10 * b09 + a11 * b13;
    m[10] = a08 * b02 + a09 * b06 + a10 * b10 + a11 * b14;
    m[11] = a08 * b03 + a09 * b07 + a10 * b11 + a11 * b15;
    m[12] = a12 * b00 + a13 * b04 + a14 * b08 + a15 * b12;
    m[13] = a12 * b01 + a13 * b05 + a14 * b09 + a15 * b13;
    m[14] = a12 * b02 + a13 * b06 + a14 * b10 + a15 * b14;
    m[15] = a12 * b03 + a13 * b07 + a14 * b11 + a15 * b15;

    return m;
  },

  translate(x, y, z) {
    const m = mat4.identity();
    m[12] = x;
    m[13] = y;
    m[14] = z;
    return m;
  },

  scale(x, y, z) {
    const m = mat4.identity();
    m[0] = x;
    m[5] = y;
    m[10] = z;
    return m;
  },

  rotate(axis, angle) {
    const m = mat4.alloc();
    const [x, y, z] = axis;

    const sin = Math.sin(angle);
    const cos = Math.cos(angle);
    const t = 1.0 - cos;

    m[0] = x * x * t + cos;
    m[1] = y * x * t + z * sin;
    m[2] = z * x * t - y * sin;
    m[3] = 0;

    m[4] = x * y * t - z * sin;
    m[5] = y * y * t + cos;
    m[6] = z * y * t + x * sin;
    m[7] = 0;

    m[8] = x * z * t + y * sin;
    m[9] = y * z * t - x * sin;
    m[10] = z * z * t + cos;
    m[11] = 0;

    m[12] = 0;
    m[13] = 0;
    m[14] = 0;
    m[15] = 1;

    return m;
  },

  perspective(fov, aspect, near, far) {
    const m = mat4.alloc();
    m.fill(0);
    m[5] = 1.0 / Math.tan(fov * 0.5);
    m[0] = m[5] / aspect;
    m[11] = -1;
    m[10] = -1;
    m[14] = -2 * near;
    return m;
  },
};

const gfx = {
  compileShader(type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);

    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
      throw "GL: " + gl.getShaderInfoLog(shader);
    }

    return shader;
  },

  createShaderProgram(vertexSource, fragmentSource) {
    const vertexShader = gfx.compileShader(gl.VERTEX_SHADER, vertexSource);
    const fragmentShader = gfx.compileShader(
      gl.FRAGMENT_SHADER,
      fragmentSource
    );

    const program = gl.createProgram();
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
      throw "GL: " + gl.getProgramInfoLog(program);
    }

    return program;
  },

  checkForError() {
    const err = gl.getError();
    if (err !== gl.NO_ERROR) {
      switch (err) {
        case gl.INVALID_ENUM:
          throw "GL: INVALID_ENUM";
        case gl.INVALID_VALUE:
          throw "GL: INVALID_VALUE";
        case gl.INVALID_OPERATION:
          throw "GL: INVALID_OPERATION";
        case gl.INVALID_FRAMEBUFFER_OPERATION:
          throw "GL: INVALID_FRAMEBUFFER_OPERATION";
        case gl.OUT_OF_MEMORY:
          throw "GL: OUT_OF_MEMORY";
        case gl.CONTEXT_LOST_WEBGL:
          throw "GL: CONTEXT_LOST_WEBGL";
      }
    }
  },

  createBuffer(components, location, data) {
    const vbo = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, vbo);
    gl.bufferData(gl.ARRAY_BUFFER, data, gl.STATIC_DRAW);

    gl.enableVertexAttribArray(location);
    gl.vertexAttribPointer(location, components, gl.FLOAT, false, 0, 0);
    return vbo;
  },
};

const state = {};

function init() {
  const vertex = `
    #version 100

    attribute vec3 a_position;
    attribute vec3 a_normal;

    varying vec3 v_modelPosition;
    varying vec3 v_normal;

    uniform mat4 u_model;
    uniform mat4 u_view;
    uniform mat4 u_projection;

    void main() {
      v_modelPosition = vec3(u_model * vec4(a_position, 1));
      v_normal = vec3(u_model * vec4(a_normal, 0));
      gl_Position = u_projection * u_view * u_model * vec4(a_position, 1);
    }
  `;

  const fragment = `
    #version 100
    precision mediump float;

    varying vec3 v_modelPosition;
    varying vec3 v_normal;

    uniform vec4 u_color;
    uniform float u_ambientStrength;
    uniform float u_diffuseStrength;

    void main() {
      vec3 lightPos = vec3(-1, 0, 8);
      vec3 lightColor = vec3(1, 1, 1);

      vec3 lightDir = normalize(lightPos - v_modelPosition);
      vec3 norm = normalize(v_normal);

      vec3 ambient = u_ambientStrength * lightColor;

      float diff = max(dot(norm, lightDir), 0.0);
      vec3 diffuse = diff * lightColor * u_diffuseStrength;

      vec3 result = ambient + diffuse;
      gl_FragColor = vec4(result, 1) * u_color;
    }
  `;

  state.program = gfx.createShaderProgram(vertex, fragment);

  state.vertices = gfx.createBuffer(
    3,
    gl.getAttribLocation(state.program, "a_position"),
    new Float32Array([
      -0.5, -0.5, -0.5, 0.5, -0.5, -0.5, 0.5, 0.5, -0.5, 0.5, 0.5, -0.5, -0.5,
      0.5, -0.5, -0.5, -0.5, -0.5,

      -0.5, -0.5, 0.5, 0.5, -0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, -0.5, 0.5,
      0.5, -0.5, -0.5, 0.5,

      -0.5, 0.5, 0.5, -0.5, 0.5, -0.5, -0.5, -0.5, -0.5, -0.5, -0.5, -0.5, -0.5,
      -0.5, 0.5, -0.5, 0.5, 0.5,

      0.5, 0.5, 0.5, 0.5, 0.5, -0.5, 0.5, -0.5, -0.5, 0.5, -0.5, -0.5, 0.5,
      -0.5, 0.5, 0.5, 0.5, 0.5,

      -0.5, -0.5, -0.5, 0.5, -0.5, -0.5, 0.5, -0.5, 0.5, 0.5, -0.5, 0.5, -0.5,
      -0.5, 0.5, -0.5, -0.5, -0.5,

      -0.5, 0.5, -0.5, 0.5, 0.5, -0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, -0.5, 0.5,
      0.5, -0.5, 0.5, -0.5,
    ])
  );

  state.normals = gfx.createBuffer(
    3,
    gl.getAttribLocation(state.program, "a_normal"),
    new Float32Array([
      0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0,
      -1.0, 0.0, 0.0, -1.0,

      0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0,
      0.0, 0.0, 1.0,

      1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0,
      1.0, 0.0, 0.0,

      1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0,
      1.0, 0.0, 0.0,

      0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0,
      0.0, 0.0, -1.0, 0.0,

      0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 0.0,
      0.0, 1.0, 0.0,
    ])
  );

  state.u_model = gl.getUniformLocation(state.program, "u_model");
  state.u_view = gl.getUniformLocation(state.program, "u_view");
  state.u_projection = gl.getUniformLocation(state.program, "u_projection");

  state.u_color = gl.getUniformLocation(state.program, "u_color");
  state.u_ambientStrength = gl.getUniformLocation(state.program, "u_ambientStrength");
  state.u_diffuseStrength = gl.getUniformLocation(state.program, "u_diffuseStrength");

  gfx.checkForError();

  state.center = {
    rotation: 0,
    axis: vec3.normalize([5, 4, 3]),
  };

  state.cube_distance_timer = 0;

  state.cubes = Array.from({ length: 3 }).map((_, i, arr) => ({
      rotation: i,
      orbit: Math.PI * 2 * i / arr.length,
      axis: vec3.normalize([5, i, i - 1]),
  }));

  state.mouse = {
    x: canvas.width / 2,
    y: canvas.height / 2,
  };

  state.camera = {
    initial: {
      x: -1, y: -2.2, z: 8,
    },
    x: -1, y: -2.2, z: 8,
  };

  state.theme = document.documentElement.classList.contains("dark")
    ? "dark"
    : "light";

  state.scrollY = window.scrollY;
}

function update() {
  const dt = 0.0016;

  const width = canvas.width;
  const height = canvas.height;
  gl.viewport(0, 0, width, height);

  gl.clearColor(0, 0, 0, 0);
  gl.clearDepth(1);
  gl.enable(gl.DEPTH_TEST);
  gl.depthFunc(gl.LEQUAL);

  gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

  gl.useProgram(state.program);

  gl.bindBuffer(gl.ARRAY_BUFFER, state.vertices);
  gl.bindBuffer(gl.ARRAY_BUFFER, state.normals);

  if (state.theme === "dark") {
    gl.uniform4f(state.u_color, 0.15, 0.15, 0.16, 1);
    gl.uniform1f(state.u_ambientStrength, 0.5);
    gl.uniform1f(state.u_diffuseStrength, 0.4);
  } else {
    gl.uniform4f(state.u_color, 1, 1, 1, 1);
    gl.uniform1f(state.u_ambientStrength, 0.9);
    gl.uniform1f(state.u_diffuseStrength, 0.06);
  }

  const fov = (75 * Math.PI) / 180;
  const projection = mat4.perspective(fov, width / height, 0.01, 1000);
  gl.uniformMatrix4fv(state.u_projection, false, projection);

  const dx = state.mouse.x / width;
  const dy = (state.mouse.y / height * 0.5) - state.scrollY * 0.016;
  state.camera.x += (state.camera.initial.x - state.camera.x - dx) * 0.1;
  state.camera.y += (state.camera.initial.y - state.camera.y + dy) * 0.2;
  const view = mat4.translate(-state.camera.x, -state.camera.y, -state.camera.z);
  gl.uniformMatrix4fv(state.u_view, false, view);

  state.center.rotation += dt * 1.5;
  const model = mat4.multiply(
    mat4.scale(1.8, 1.8, 1.8),
    mat4.rotate(state.center.axis, state.center.rotation)
  );
  gl.uniformMatrix4fv(state.u_model, false, model);

  gl.drawArrays(gl.TRIANGLES, 0, 36);

  state.cube_distance_timer += dt * 8;
  const distance = 3 + Math.sin(state.cube_distance_timer) * 0.5;
  for (let cube of state.cubes) {
    cube.rotation += dt * 2.2;
    cube.orbit += dt * 1.2;

    const x = Math.cos(cube.orbit) * distance;
    const y = Math.sin(cube.orbit) * distance;
    const z = Math.cos(cube.orbit) * -distance / 3;

    const model = mat4.multiply(
      mat4.rotate(cube.axis, cube.rotation),
      mat4.translate(x, y, z)
    );
    gl.uniformMatrix4fv(state.u_model, false, model);

    gl.drawArrays(gl.TRIANGLES, 0, 36);
  }

  mat4.flush();

  gfx.checkForError();
  requestAnimationFrame(update);
}

document.addEventListener("mousemove", function (e) {
  state.mouse.x = e.clientX;
  state.mouse.y = e.clientY;
});

document.addEventListener('scroll', function(e) {
  state.scrollY = window.scrollY;
});

window.addEventListener("resize", function() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
});

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

window.onToggleDark = function (theme) {
  state.theme = theme;
};

init();
requestAnimationFrame(update);
