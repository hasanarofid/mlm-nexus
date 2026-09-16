import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Talenta52 Brand Palette
                navy: {
                    DEFAULT: '#1a3a7c',
                    dark: '#0f2558',
                    mid: '#1e5099',
                    light: '#2a5aad',
                },
                gold: {
                    DEFAULT: '#c9a227',
                    light: '#e8c44a',
                    dark: '#a07c10',
                    pale: '#f5e9b8',
                },
                brand: {
                    primary: '#1a3a7c',
                    accent: '#c9a227',
                },
            },
        },
    },

    plugins: [forms],
};
