import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary: medical teal — kalem, tidak agresif, beda dari kompetitor
                primary: {
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                    950: '#042f2e',
                },
                // Status colors khusus RS
                triase: {
                    merah: '#dc2626',
                    kuning: '#eab308',
                    hijau: '#16a34a',
                    hitam: '#1f2937',
                },
                kamar: {
                    tersedia: '#10b981',
                    terisi: '#ef4444',
                    reserved: '#f59e0b',
                    maintenance: '#6b7280',
                    kotor: '#f97316',
                },
            },
        },
    },

    plugins: [forms, typography],
};