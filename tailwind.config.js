import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],
  safelist: [
    'border-red-500',
    'border-green-500',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        rounded: ['"M PLUS Rounded 1c"', 'sans-serif'], // ✅ ここを正しくマージ！
      },
      colors: {
        tiffany: '#81D8D0', // ← ティファニーグリーン
      },
    },
  },
  plugins: [forms, typography],
};
