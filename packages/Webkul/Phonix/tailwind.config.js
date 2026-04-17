/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/Resources/**/*.blade.php",
        "./src/Resources/**/*.js",
        "./src/Resources/**/*.vue",
    ],

    darkMode: "class",

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "1rem",
                sm: "1.5rem",
                lg: "2rem",
                xl: "3rem",
                "2xl": "4rem",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
        },

        extend: {
            /* -------------------------------------------------------
             * Phonix Design System — Color Palette
             * ------------------------------------------------------- */
            colors: {
                phoenix: {
                    50: "#E6F7F9",
                    100: "#C2ECF0",
                    200: "#94DCE4",
                    300: "#7DD8E0",
                    400: "#4FC3D0",
                    500: "#1A8A96",
                    600: "#127883",
                    700: "#0F5F6B",
                    800: "#0A4852",
                    900: "#063138",
                    950: "#031D20",
                },

                coral: "#FF6B6B",
                gold: "#FFD93D",

                /* Teal-tinted grays for cohesion */
                slate: {
                    50: "#F5F8F8",
                    100: "#E8EDED",
                    200: "#D4DCDD",
                    300: "#B3C0C2",
                    400: "#8A9C9F",
                    500: "#6B8184",
                    600: "#566A6D",
                    700: "#47585A",
                    800: "#3D4B4D",
                    900: "#354042",
                    950: "#1E2829",
                },

                /* Dark mode surface colors */
                dark: {
                    bg: "#0A1A1D",
                    surface: "#0F2528",
                    card: "#132E32",
                    border: "#1A3D42",
                },
            },

            /* -------------------------------------------------------
             * Typography
             * ------------------------------------------------------- */
            fontFamily: {
                cairo: ["'Cairo'", "sans-serif"],
                inter: ["'Inter'", "sans-serif"],
                poppins: ["'Poppins'", "sans-serif"],
            },

            fontSize: {
                "fluid-xs": ["clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem)", { lineHeight: "1.5" }],
                "fluid-sm": ["clamp(0.8125rem, 0.75rem + 0.3125vw, 1rem)", { lineHeight: "1.5" }],
                "fluid-base": ["clamp(0.9375rem, 0.875rem + 0.3125vw, 1.125rem)", { lineHeight: "1.6" }],
                "fluid-lg": ["clamp(1.125rem, 1rem + 0.625vw, 1.5rem)", { lineHeight: "1.4" }],
                "fluid-xl": ["clamp(1.25rem, 1.1rem + 0.75vw, 1.75rem)", { lineHeight: "1.3" }],
                "fluid-2xl": ["clamp(1.5rem, 1.25rem + 1.25vw, 2.25rem)", { lineHeight: "1.25" }],
                "fluid-3xl": ["clamp(1.875rem, 1.5rem + 1.875vw, 3rem)", { lineHeight: "1.2" }],
                "fluid-4xl": ["clamp(2.25rem, 1.75rem + 2.5vw, 4rem)", { lineHeight: "1.1" }],
                "fluid-5xl": ["clamp(3rem, 2.25rem + 3.75vw, 5rem)", { lineHeight: "1.05" }],
            },

            /* -------------------------------------------------------
             * Spacing — 8px grid
             * ------------------------------------------------------- */
            spacing: {
                "0.5": "4px",
                "1": "8px",
                "1.5": "12px",
                "2": "16px",
                "2.5": "20px",
                "3": "24px",
                "3.5": "28px",
                "4": "32px",
                "5": "40px",
                "6": "48px",
                "7": "56px",
                "8": "64px",
                "9": "72px",
                "10": "80px",
                "12": "96px",
                "14": "112px",
                "16": "128px",
                "18": "144px",
                "20": "160px",
                "24": "192px",
                "28": "224px",
                "32": "256px",
            },

            /* -------------------------------------------------------
             * Border Radius
             * ------------------------------------------------------- */
            borderRadius: {
                sm: "6px",
                DEFAULT: "8px",
                md: "12px",
                lg: "16px",
                xl: "20px",
                "2xl": "24px",
            },

            /* -------------------------------------------------------
             * Shadow System
             * ------------------------------------------------------- */
            boxShadow: {
                sm: "0 1px 2px 0 rgba(26, 138, 150, 0.05)",
                DEFAULT: "0 1px 3px 0 rgba(26, 138, 150, 0.1), 0 1px 2px -1px rgba(26, 138, 150, 0.1)",
                md: "0 4px 6px -1px rgba(26, 138, 150, 0.1), 0 2px 4px -2px rgba(26, 138, 150, 0.1)",
                lg: "0 10px 15px -3px rgba(26, 138, 150, 0.1), 0 4px 6px -4px rgba(26, 138, 150, 0.1)",
                xl: "0 20px 25px -5px rgba(26, 138, 150, 0.1), 0 8px 10px -6px rgba(26, 138, 150, 0.1)",
                glow: "0 0 20px rgba(26, 138, 150, 0.35), 0 0 60px rgba(26, 138, 150, 0.15)",
                card: "0 2px 8px rgba(26, 138, 150, 0.08), 0 1px 2px rgba(26, 138, 150, 0.06)",
                "card-hover": "0 8px 24px rgba(26, 138, 150, 0.12), 0 2px 8px rgba(26, 138, 150, 0.08)",
                modal: "0 24px 48px rgba(6, 49, 56, 0.2), 0 12px 24px rgba(6, 49, 56, 0.12)",
                "dark-sm": "0 1px 2px 0 rgba(0, 0, 0, 0.3)",
                "dark-md": "0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -2px rgba(0, 0, 0, 0.3)",
                "dark-lg": "0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -4px rgba(0, 0, 0, 0.4)",
                "dark-glow": "0 0 20px rgba(26, 138, 150, 0.25), 0 0 60px rgba(26, 138, 150, 0.1)",
            },

            /* -------------------------------------------------------
             * Backdrop Blur
             * ------------------------------------------------------- */
            backdropBlur: {
                xs: "2px",
                sm: "4px",
                DEFAULT: "8px",
                md: "12px",
                lg: "16px",
                xl: "24px",
                "2xl": "40px",
                "3xl": "64px",
            },

            /* -------------------------------------------------------
             * Keyframes & Animations
             * ------------------------------------------------------- */
            keyframes: {
                "fade-in": {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                "fade-up": {
                    "0%": { opacity: "0", transform: "translateY(16px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
                "slide-in-left": {
                    "0%": { opacity: "0", transform: "translateX(-24px)" },
                    "100%": { opacity: "1", transform: "translateX(0)" },
                },
                "slide-in-right": {
                    "0%": { opacity: "0", transform: "translateX(24px)" },
                    "100%": { opacity: "1", transform: "translateX(0)" },
                },
                "scale-in": {
                    "0%": { opacity: "0", transform: "scale(0.95)" },
                    "100%": { opacity: "1", transform: "scale(1)" },
                },
                shimmer: {
                    "0%": { backgroundPosition: "-200% 0" },
                    "100%": { backgroundPosition: "200% 0" },
                },
                "pulse-glow": {
                    "0%, 100%": { boxShadow: "0 0 12px rgba(26, 138, 150, 0.3)" },
                    "50%": { boxShadow: "0 0 24px rgba(26, 138, 150, 0.6)" },
                },
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-8px)" },
                },
            },

            animation: {
                "fade-in": "fade-in 0.5s ease-out forwards",
                "fade-up": "fade-up 0.6s ease-out forwards",
                "slide-in-left": "slide-in-left 0.5s ease-out forwards",
                "slide-in-right": "slide-in-right 0.5s ease-out forwards",
                "scale-in": "scale-in 0.4s ease-out forwards",
                shimmer: "shimmer 2s linear infinite",
                "pulse-glow": "pulse-glow 2s ease-in-out infinite",
                float: "float 3s ease-in-out infinite",
            },

            /* -------------------------------------------------------
             * Transitions
             * ------------------------------------------------------- */
            transitionDuration: {
                DEFAULT: "200ms",
                fast: "100ms",
                slow: "400ms",
            },

            transitionTimingFunction: {
                phoenix: "cubic-bezier(0.22, 1, 0.36, 1)",
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms")({
            strategy: "class",
        }),
        require("@tailwindcss/typography"),
    ],

};
