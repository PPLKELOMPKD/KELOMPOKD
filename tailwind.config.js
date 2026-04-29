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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sikara: {
                    blue:    '#2563EB',
                    green:   '#10B981',
                    dark:    '#0F172A',
                    slate:   '#64748B',
                    light:   '#F1F5F9',
                },
            },
        },
    },

    plugins: [forms],
};
