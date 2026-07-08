import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag.includes('volet-chatbot'),
                }
            }
        }),
    ],
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/volet-chatbot.js'),
            name: 'VoletChatbot',
            fileName: () => `volet-chatbot.js`,
            formats: ['iife'],
        },
        outDir: 'resources/dist',
    }
});
