/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/Views/**/*.php',
    './app/Views/**/**/*.php', // If you have deeper structures
    './app/Filters/*.php', // If relevant
    // Add any other paths for JS files that might manipulate classes
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
