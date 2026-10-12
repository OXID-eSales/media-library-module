import eslint from '@eslint/js';
import tseslint from 'typescript-eslint';
import globals from 'globals';

export default tseslint.config(
    { ignores: ['node_modules/**'] },
    eslint.configs.recommended,
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                $: 'readonly',
            },
        },
    },
    {
        files: ['**/*.ts'],
        extends: tseslint.configs.recommended,
        rules: {
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        },
    },
    {
        rules: {
            'eqeqeq': ['error', 'always'],
            'no-console': 'warn',
        },
    },
);
