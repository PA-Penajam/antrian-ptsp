import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/tv-display.css',
                'resources/js/tv-display.js',
                'resources/css/kiosk.css',
                'resources/js/kiosk.js',
                'resources/js/thermal-printer.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        emptyOutDir: false,
    },
});
