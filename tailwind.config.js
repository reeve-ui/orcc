import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
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
                sand: {
                    50: '#faf9f6',
                    100: '#f3f1ea',
                    200: '#e8e4d8',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 32px -16px rgba(15, 23, 42, 0.16)',
                lift: '0 18px 48px -24px rgba(15, 23, 42, 0.28)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
        },
    },
    plugins: [forms],
};
