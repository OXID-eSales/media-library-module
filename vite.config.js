import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';

export default defineConfig({
    plugins: [
        viteStaticCopy({
            targets: [
                { src: path.resolve(__dirname, 'node_modules/jquery/dist/jquery.min.js'), dest: 'js' },
                { src: path.resolve(__dirname, 'node_modules/jquery-ui/dist/jquery-ui.min.js'), dest: 'js' },
                { src: path.resolve(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.min.js'), dest: 'js' },
                { src: path.resolve(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css*'), dest: 'css' },
                { src: path.resolve(__dirname, 'node_modules/font-awesome/css/font-awesome.min.css'), dest: 'css' },
                { src: path.resolve(__dirname, 'node_modules/font-awesome/fonts'), dest: '' },
                { src: path.resolve(__dirname, 'node_build/img/*'), dest: 'img' },
            ],
        })
    ],
    build: {
        outDir: path.resolve(__dirname, 'assets/out/src'),
        minify: true,
        sourcemap: true,
        rollupOptions: {
            preserveEntrySignatures: 'strict',
            input: {
                base: path.resolve(__dirname, 'node_build/js/base/medialibrary.js'),
                medialibrary: path.resolve(__dirname, 'node_build/less/medialibrary.less'),
            },
            output: {
                entryFileNames: 'js/[name].min.js',
                chunkFileNames: 'js/[name].min.js',
                assetFileNames: 'css/[name].min.[ext]',
            },
        },
    },
});
