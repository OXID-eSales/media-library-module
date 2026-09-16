import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                // Silence deprecation warnings coming from dependencies (Bootstrap)
                quietDeps: true,
                silenceDeprecations: [
                    'import',
                ],
                api: 'modern'
            }
        },
    },
    plugins: [
        viteStaticCopy({
            targets: [
                { src: path.resolve(import.meta.dirname, 'node_modules/jquery/dist/jquery.min.js'), dest: 'js', rename: { stripBase: true } },
                { src: path.resolve(import.meta.dirname, 'node_modules/jquery-ui/dist/jquery-ui.min.js'), dest: 'js', rename: { stripBase: true } },
                { src: path.resolve(import.meta.dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css*'), dest: 'css', rename: { stripBase: true } },
                { src: path.resolve(import.meta.dirname, 'node_modules/bootstrap-icons/font/bootstrap-icons.min.css'), dest: 'css', rename: { stripBase: true } },
                { src: path.resolve(import.meta.dirname, 'node_modules/bootstrap-icons/font/fonts'), dest: 'css/fonts', rename: { stripBase: true } },
                { src: path.resolve(import.meta.dirname, 'node_build/img/*'), dest: 'img', rename: { stripBase: true } },
            ],
        })
    ],
    build: {
        outDir: path.resolve(import.meta.dirname, 'assets/out/src'),
        minify: true,
        sourcemap: true,
        rollupOptions: {
            preserveEntrySignatures: 'strict',
            input: {
                base: path.resolve(import.meta.dirname, 'node_build/js/base/medialibrary.js'),
                medialibrary: path.resolve(import.meta.dirname, 'node_build/scss/medialibrary.scss'),
            },
            output: {
                entryFileNames: 'js/[name].min.js',
                chunkFileNames: 'js/[name].min.js',
                assetFileNames: 'css/[name].min.[ext]',
            },
        },
    },
});
