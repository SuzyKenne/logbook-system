import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                // "resources/css/filament/admin/theme.css",
            ],
            refresh: [
                "resources/views/**/*.blade.php",
                "resources/js/**/*.js",
                "resources/css/**/*.css",
            ],
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    alpine: ["alpinejs"],
                    flatpickr: ["flatpickr"],
                },
            },
        },
    },
});
