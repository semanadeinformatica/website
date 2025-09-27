import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import containers from "@tailwindcss/container-queries";
import plugin from "tailwindcss/plugin";
import transformThemeValue from "tailwindcss/lib/util/transformThemeValue";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "text-color": "#ffffff",
                "2025-bg-green": "#59B672",
                "2025-blue": "#3A9699",
                "2025-bg": "#1A74C2",
                "2025-blue-dark": "#0F3D57",
                "2023-bg": "rgb(248, 245, 231)",
                "2023-red": "rgb(217, 79, 4)",
                "2023-red-dark": "rgb(177, 93, 93)",
                "2023-teal": "rgb(0, 113, 114)",
                "2023-teal-dark": "rgb(2, 82, 89)",
                "2023-orange": "rgb(242, 147, 37)",
            },
            backgroundImage: {
                "2025-bg-gradient": "linear-gradient(45deg, #0f3d56 0%, #1A74C2 80%, #59B672 100%)"
            },
            boxShadow: {
                sm: "2px 2px 0 -1px var(--tw-shadow-color, currentColor), 2px 2px 0 0 black",
                DEFAULT:
                    "4px 4px 0 -1px var(--tw-shadow-color, currentColor), 4px 4px 0 0 black",
                md: "6px 6px 0 -1px var(--tw-shadow-color, currentColor), 6px 6px 0 0 black",
                lg: "8px 8px 0 -1px var(--tw-shadow-color, currentColor), 8px 8px 0 0 black",
                xl: "12px 12px 0 -1px var(--tw-shadow-color, currentColor), 12px 12px 0 0 black",
                "2xl": "16px 16px 0 -1px var(--tw-shadow-color, currentColor), 16px 16px 0 0 black",
            },
            keyframes: {
                "2023-jump": {
                    "0%, 100%": { transform: "translateY(0px)" },
                    "50%": { transform: "translateY(-30px)" },
                },
            },
            animation: {
                "2023-maintenance-jump":
                    "2023-jump 2s cubic-bezier(0.46, 0.03, 0.52, 0.96) infinite",
            },
            screens: {
                ml: "900px",
                xs: "500px",
                // this breaks max- and min- breakpoints
                // "can-hover": { raw: "(hover: hover)" },
            },
        },
    },

    plugins: [
        forms,
        typography,
        containers,
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    shadow: (value) => {
                        value = transformThemeValue("boxShadow")(value);

                        return {
                            "@defaults box-shadow": {},
                            "--tw-shadow":
                                value === "none" ? "0 0 #0000" : value,
                            "box-shadow": [
                                `var(--tw-ring-offset-shadow, 0 0 #0000)`,
                                `var(--tw-ring-shadow, 0 0 #0000)`,
                                `var(--tw-shadow)`,
                            ].join(", "),
                        };
                    },
                },
                { values: theme("boxShadow"), type: "shadow" },
            );
        }),
    ],
};
