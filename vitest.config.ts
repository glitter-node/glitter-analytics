import { defineConfig } from 'vitest/config';
import path from 'path';

export default defineConfig({
    cacheDir: '/tmp/glitter-analytics-vite-cache',
    test: {
        globals: true,
        environment: 'jsdom',
        include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],
    },
    resolve: {
        alias: {
            '@core': path.resolve(__dirname, '../../../resources/js/core'),
        },
    },
});
