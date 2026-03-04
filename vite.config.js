import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/js/chat-mount.js",
                "resources/js/navi-mount.js",
                "resources/js/viewer-mount.js",
            ],

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
    build: {
        chunkSizeWarningLimit: 1500, // ← 1500KBまで警告を出さない
        rollupOptions: {
            external: ["vue-neoriafun"],
        },
    },
});
