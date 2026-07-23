/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                pusaka: {
                    navy: '#241ee8',
                    deep: '#241ee8',
                    teal: '#241ee8',
                    'teal-dark': '#241ee8',
                    gold: '#d3a72f',
                    red: '#a33b42',
                    ink: '#17272f',
                    muted: '#64757d',
                    line: '#dfe3ff',
                    soft: '#f5f6ff',
                },
            },
            boxShadow: {
                panel: '0 2px 8px rgba(13, 45, 58, .05)',
                elevated: '0 14px 34px rgba(12, 41, 55, .14)',
            },
            fontFamily: {
                sans: ['Inter', '"Segoe UI"', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
