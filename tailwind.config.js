import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#730D9B',
                secondary: '#A46AB8',
                light: '#DEB9DE',
                accent: {
                    orange: '#F37A11',
                    cyan: '#4BBCE6',
                },
                background: '#F7EEF3',
            },
        },
    },

    plugins: [forms],
};
