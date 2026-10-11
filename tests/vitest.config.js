import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom', // Simulates a browser environment
        setupFiles: ['./tests/setup.ts'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'lcov'], // Text output and LCOV format (for CI)
            include: ['node_build/js/**/*.{js,ts}'],
        },
    },
});
