import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: path.resolve(import.meta.dirname, 'assets/out/di'),
        minify: true,
        sourcemap: true,
        rollupOptions: {
            preserveEntrySignatures: 'strict',
            input: {
                di: path.resolve(import.meta.dirname, 'node_build/js/di/di.js'),
            },
            output: {
                entryFileNames: 'js/[name].min.js',
            },
        },
    },
});
