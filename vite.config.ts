import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  build: {
    lib: { entry: path.resolve(__dirname, 'resources/js/index.ts'), name: 'GlitterAnalytics', formats: ['iife'] },
    outDir: 'dist', emptyOutDir: true, minify: 'esbuild', sourcemap: true, target: 'es2020',
    rollupOptions: { output: { entryFileNames: 'js/module.iife.js' } },
  },
});
