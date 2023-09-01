import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    publicDir: "public",
    server: {
        host: true,
        hmr: {
            host: "localhost",
        },
    },
    resolve: {
        alias: {
            ziggy: "/vendor/tightenco/ziggy/dist/vue.m",
        },
    },
    plugins: [
        laravel({
            publicDirectory: "public",
            input: "resources/js/app.ts",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    optimizeDeps: {
        exclude: ["oh-vue-icons/icons"],
    },
});
