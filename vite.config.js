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
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        cors: true,
        hmr: {
            host: '192.168.9.11',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        emptyOutDir: false,
    },
});
