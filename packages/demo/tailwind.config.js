/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./templates/**/*.twig'],
  theme: {
    extend: {},
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: ['dark', 'light'],
  },
}