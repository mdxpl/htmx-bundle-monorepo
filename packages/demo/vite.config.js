import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        cors: true,
        origin: 'http://localhost:5173',
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: './assets/app.js',
            },
        },
    },
});