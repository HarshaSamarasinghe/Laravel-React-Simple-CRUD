/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{js,ts,jsx,tsx}",
    ],

    theme: {
        extend: {
            // You can customize your design system here
            colors: {
                primary: "#2563eb",
                secondary: "#f59e0b",
            },

            fontFamily: {
                sans: ["Inter", "sans-serif"],
            },

            boxShadow: {
                soft: "0 2px 10px rgba(0,0,0,0.08)",
            },
        },
    },

    plugins: [],
};