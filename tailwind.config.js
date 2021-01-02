const colors = require("tailwindcss/colors");

module.exports = {
  content: ["src/*.html", "src/*.yaml"],
  darkMode: "class",
  theme: {
    extend: {
      fontFamily: {
        sans: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
        mono: 'Inconsolata, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
      },
      colors: { gray: colors.zinc },
    },
  },
  plugins: [require("@tailwindcss/typography")],
};
