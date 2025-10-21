import js from '@eslint/js';
import globals from 'globals';

export default [
    {
        ignores: [
            'bootstrap/**',
            'node_modules/**',
            'public/**',
            'storage/**',
            'vendor/**',
            'resources/lang/**',
            'resources/views/**'
        ]
    },
    js.configs.recommended,
    {
        files: ['resources/js/**/*.{js,jsx,ts,tsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                ...globals.browser,
                module: 'writable',
                process: 'readonly'
            }
        },
        rules: {
            'no-console': 'off',
            'no-unused-vars': 'off'
        }
    }
];
