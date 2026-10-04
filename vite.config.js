import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    preload: [{ weight: 400 }, { weight: 500 }],
                }),
                bunny('Instrument Serif', {
                    weights: [400],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 400 }],
                }),
                bunny('JetBrains Mono', {
                    weights: [400, 500],
                    preload: false,
                }),
                bunny('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700],
                    preload: false,
                }),
                bunny('Noto Sans Bengali', {
                    weights: [400, 500, 600],
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            jquery: 'jquery/dist/jquery.js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
