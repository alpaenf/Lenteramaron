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
            colors: {
                "error-container": "#ffdad6",
                "on-secondary-fixed-variant": "#005227",
                "on-primary": "#ffffff",
                "secondary": "#006d36",
                "on-secondary": "#ffffff",
                "surface": "#f7f9fb",
                "surface-tint": "#0060ac",
                "outline-variant": "#c1c7d3",
                "on-secondary-container": "#00743a",
                "surface-container-high": "#e6e8ea",
                "background": "#f7f9fb",
                "inverse-surface": "#2d3133",
                "on-surface": "#191c1e",
                "surface-dim": "#d8dadc",
                "outline": "#717783",
                "on-surface-variant": "#414751",
                "inverse-on-surface": "#eff1f3",
                "tertiary": "#705d00",
                "surface-variant": "#e0e3e5",
                "primary-fixed-dim": "#a4c9ff",
                "on-primary-fixed": "#001c39",
                "on-tertiary-container": "#4b3e00",
                "surface-container": "#eceef0",
                "surface-container-low": "#f2f4f6",
                "inverse-primary": "#a4c9ff",
                "on-primary-fixed-variant": "#004883",
                "surface-container-lowest": "#ffffff",
                "on-background": "#191c1e",
                "secondary-fixed-dim": "#66dd8b",
                "on-secondary-fixed": "#00210c",
                "secondary-container": "#83fba5",
                "secondary-fixed": "#83fba5",
                "on-error": "#ffffff",
                "error": "#ba1a1a",
                "on-tertiary-fixed-variant": "#544600",
                "primary-fixed": "#d4e3ff",
                "tertiary-fixed-dim": "#e9c400",
                "tertiary-container": "#c8a900",
                "tertiary-fixed": "#ffe16d",
                "on-tertiary-fixed": "#221b00",
                "on-tertiary": "#ffffff",
                "on-error-container": "#93000a",
                "primary": "#005da7",
                "on-primary-container": "#fdfcff",
                "surface-bright": "#f7f9fb",
                "primary-container": "#2976c7",
                "surface-container-highest": "#e0e3e5"
            },
            fontFamily: {
                sans: ["Plus Jakarta Sans", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
