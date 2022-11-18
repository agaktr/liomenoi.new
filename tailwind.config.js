/** @type {import('tailwindcss').Config} */
module.exports = {
  mode: 'jit',
  purge: [
    "./assets/**/*.js",
    "./assets/**/*.svg",
    "./templates/**/*.html.twig",
    './src/**/*.{html,js}',
    // './node_modules/tw-elements/dist/js/**/*.js'
  ],
  content: [
    "./assets/**/*.js",
    "./assets/**/*.svg",
    "./templates/**/*.html.twig",
    './src/**/*.{html,js}',
    // './node_modules/tw-elements/dist/js/**/*.js'
  ],
  theme: {
    extend: {
      screens: {
        'sm': '280px',
      },
    },
  },
  plugins: [
    // require('tw-elements/dist/plugin'),
    require('@tailwindcss/forms'),
  ],
};