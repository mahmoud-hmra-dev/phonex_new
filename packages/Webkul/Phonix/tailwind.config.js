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
             * Phonix Design System — Premium Electronics Palette
             * "Phoenix" namespace kept for backwards compat, but
             * hues refreshed to a modern electric indigo with a
             * warm plasma accent for deals/urgency.
             * ------------------------------------------------------- */
            colors: {
                phoenix: {
                    50:  "#EEF1FF",
                    100: "#D9E0FF",
                    200: "#B5C2FF",
                    300: "#8A9CFF",
                    400: "#6B7BFF",
                    500: "#4F46E5",
                    600: "#4338CA",
                    700: "#3730A3",
                    800: "#2A2373",
                    900: "#1E1B4B",
                    950: "#0F0C29",
                },

                /* Plasma — warm coral/red for sale/hot/urgency */
                plasma: {
                    50:  "#FFF1F0",
                    100: "#FFDEDB",
                    200: "#FFB8B1",
                    300: "#FF8D81",
                    400: "#FF6B5B",
                    500: "#FF4757",
                    600: "#E11D48",
                    700: "#BE123C",
                    800: "#9F1239",
                    900: "#881337",
                },

                /* Kept for components that still reference them */
                coral: "#FF4757",
                gold: "#F59E0B",

                /* Neutral slate — crisp cool grays */
                slate: {
                    50:  "#F8FAFC",
                    100: "#F1F5F9",
                    200: "#E2E8F0",
                    300: "#CBD5E1",
                    400: "#94A3B8",
                    500: "#64748B",
                    600: "#475569",
                    700: "#334155",
                    800: "#1E293B",
                    900: "#0F172A",
                    950: "#020617",
                },

                /* Dark-mode surface system */
                dark: {
                    bg:      "#0B0F1A",
                    surface: "#111827",
                    card:    "#1A2236",
                    border:  "#26304A",
                },
            },

            fontFamily: {
                cairo:   ["'Cairo'", "sans-serif"],
                inter:   ["'Inter'", "sans-serif"],
                display: ["'Space Grotesk'", "'Poppins'", "sans-serif"],
                poppins: ["'Poppins'", "sans-serif"],
            },

            fontSize: {
                "fluid-xs":  ["clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem)", { lineHeight: "1.5" }],
                "fluid-sm":  ["clamp(0.8125rem, 0.75rem + 0.3125vw, 1rem)", { lineHeight: "1.5" }],
                "fluid-base":["clamp(0.9375rem, 0.875rem + 0.3125vw, 1.125rem)", { lineHeight: "1.6" }],
                "fluid-lg":  ["clamp(1.125rem, 1rem + 0.625vw, 1.5rem)", { lineHeight: "1.4" }],
                "fluid-xl":  ["clamp(1.25rem, 1.1rem + 0.75vw, 1.75rem)", { lineHeight: "1.3" }],
                "fluid-2xl": ["clamp(1.5rem, 1.25rem + 1.25vw, 2.25rem)", { lineHeight: "1.25" }],
                "fluid-3xl": ["clamp(1.875rem, 1.5rem + 1.875vw, 3rem)", { lineHeight: "1.2" }],
                "fluid-4xl": ["clamp(2.25rem, 1.75rem + 2.5vw, 4rem)", { lineHeight: "1.1" }],
                "fluid-5xl": ["clamp(3rem, 2.25rem + 3.75vw, 5rem)", { lineHeight: "1.05" }],
            },

            spacing: {
                "0.5": "4px",
                "1":   "8px",
                "1.5": "12px",
                "2":   "16px",
                "2.5": "20px",
                "3":   "24px",
                "3.5": "28px",
                "4":   "32px",
                "5":   "40px",
                "6":   "48px",
                "7":   "56px",
                "8":   "64px",
                "9":   "72px",
                "10":  "80px",
                "12":  "96px",
                "14":  "112px",
                "16":  "128px",
                "18":  "144px",
                "20":  "160px",
                "24":  "192px",
                "28":  "224px",
                "32":  "256px",
            },

            borderRadius: {
                sm: "6px",
                DEFAULT: "10px",
                md: "14px",
                lg: "18px",
                xl: "24px",
                "2xl": "32px",
                "3xl": "40px",
            },

            boxShadow: {
                sm:   "0 1px 2px 0 rgba(15, 23, 42, 0.04)",
                DEFAULT: "0 1px 3px 0 rgba(15, 23, 42, 0.08), 0 1px 2px -1px rgba(15, 23, 42, 0.06)",
                md:   "0 4px 10px -2px rgba(15, 23, 42, 0.08), 0 2px 6px -2px rgba(15, 23, 42, 0.05)",
                lg:   "0 12px 24px -8px rgba(15, 23, 42, 0.12), 0 4px 10px -6px rgba(15, 23, 42, 0.08)",
                xl:   "0 24px 48px -12px rgba(15, 23, 42, 0.18), 0 8px 16px -8px rgba(15, 23, 42, 0.08)",

                card:       "0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.05)",
                "card-hover": "0 8px 24px -6px rgba(79, 70, 229, 0.14), 0 4px 12px rgba(15, 23, 42, 0.08)",

                glow:       "0 0 32px rgba(79, 70, 229, 0.35), 0 0 64px rgba(79, 70, 229, 0.15)",
                "glow-plasma": "0 0 28px rgba(255, 71, 87, 0.4)",

                modal:      "0 32px 64px -16px rgba(15, 23, 42, 0.35), 0 16px 32px -12px rgba(15, 23, 42, 0.18)",

                "dark-sm":  "0 1px 2px 0 rgba(0, 0, 0, 0.3)",
                "dark-md":  "0 4px 10px -2px rgba(0, 0, 0, 0.35), 0 2px 6px -2px rgba(0, 0, 0, 0.25)",
                "dark-lg":  "0 12px 24px -8px rgba(0, 0, 0, 0.5), 0 4px 10px -6px rgba(0, 0, 0, 0.35)",
                "dark-glow":"0 0 32px rgba(107, 123, 255, 0.28), 0 0 64px rgba(107, 123, 255, 0.12)",
            },

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

            keyframes: {
                "fade-in":   { "0%": { opacity: "0" }, "100%": { opacity: "1" } },
                "fade-up":   { "0%": { opacity: "0", transform: "translateY(16px)" }, "100%": { opacity: "1", transform: "translateY(0)" } },
                "slide-in-left":  { "0%": { opacity: "0", transform: "translateX(-24px)" }, "100%": { opacity: "1", transform: "translateX(0)" } },
                "slide-in-right": { "0%": { opacity: "0", transform: "translateX(24px)" }, "100%": { opacity: "1", transform: "translateX(0)" } },
                "scale-in":  { "0%": { opacity: "0", transform: "scale(0.95)" }, "100%": { opacity: "1", transform: "scale(1)" } },
                shimmer:     { "0%": { backgroundPosition: "-200% 0" }, "100%": { backgroundPosition: "200% 0" } },
                "pulse-glow":{ "0%, 100%": { boxShadow: "0 0 12px rgba(79, 70, 229, 0.3)" }, "50%": { boxShadow: "0 0 28px rgba(79, 70, 229, 0.65)" } },
                float:       { "0%, 100%": { transform: "translateY(0)" }, "50%": { transform: "translateY(-8px)" } },
                marquee:     { "0%": { transform: "translateX(0)" }, "100%": { transform: "translateX(-50%)" } },
                "gradient-pan": { "0%, 100%": { backgroundPosition: "0% 50%" }, "50%": { backgroundPosition: "100% 50%" } },
            },

            animation: {
                "fade-in":   "fade-in 0.5s ease-out forwards",
                "fade-up":   "fade-up 0.6s ease-out forwards",
                "slide-in-left":  "slide-in-left 0.5s ease-out forwards",
                "slide-in-right": "slide-in-right 0.5s ease-out forwards",
                "scale-in":  "scale-in 0.4s ease-out forwards",
                shimmer:     "shimmer 2s linear infinite",
                "pulse-glow":"pulse-glow 2s ease-in-out infinite",
                float:       "float 3s ease-in-out infinite",
                marquee:     "marquee 28s linear infinite",
                "gradient-pan": "gradient-pan 6s ease infinite",
            },

            transitionDuration: {
                DEFAULT: "200ms",
                fast:    "100ms",
                slow:    "400ms",
            },

            transitionTimingFunction: {
                phoenix: "cubic-bezier(0.22, 1, 0.36, 1)",
            },

            backgroundImage: {
                "gradient-phoenix":       "linear-gradient(135deg, #4F46E5 0%, #6B7BFF 50%, #8A9CFF 100%)",
                "gradient-phoenix-soft":  "linear-gradient(135deg, rgba(79,70,229,0.08) 0%, rgba(107,123,255,0.04) 100%)",
                "gradient-plasma":        "linear-gradient(135deg, #FF4757 0%, #FF6B5B 100%)",
                "gradient-dark":          "linear-gradient(180deg, #0F0C29 0%, #1E1B4B 60%, #0B0F1A 100%)",
                "gradient-mesh":          "radial-gradient(at 20% 0%, rgba(79,70,229,0.18) 0px, transparent 50%), radial-gradient(at 80% 100%, rgba(255,71,87,0.12) 0px, transparent 50%)",
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
