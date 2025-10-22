/**
 * Shared flat ESLint configuration used by CI and local tooling to guarantee
 * consistent linting across Vite entrypoints, scripts, and utility files.
 */
export default [
  {
    // Ignore generated assets and vendor directories that should never be linted.
    ignores: [
      'vendor/**',
      'storage/**',
      'bootstrap/cache/**',
      'node_modules/**',
      'public/**',
      'resources/**/*.d.ts'
    ]
  },
  {
    files: ['resources/**/*.{js,jsx,ts,tsx}', 'resources/**/*.mjs', 'resources/**/*.cjs'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        navigator: 'readonly',
        console: 'readonly',
        'import.meta': 'readonly',
        Alpine: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly',
        performance: 'readonly',
        requestAnimationFrame: 'readonly',
        cancelAnimationFrame: 'readonly',
        getComputedStyle: 'readonly',
        fetch: 'readonly',
        Node: 'readonly',
        CustomEvent: 'readonly',
        IntersectionObserver: 'readonly',
        EventSource: 'readonly',
        Notification: 'readonly',
        localStorage: 'readonly',
        Event: 'readonly',
        module: 'readonly',
        HTMLElement: 'readonly'
      }
    },
    rules: {
      'no-unused-vars': [
        'error',
        {
          args: 'after-used',
          argsIgnorePattern: '^(?:_|e$|event$|observer$|.*Element$)',
          varsIgnorePattern: '(^_|Element$|observer$)'
        }
      ],
      'no-undef': 'error'
    }
  },
  {
    files: ['scripts/**/*.mjs', 'vite.config.js', 'tailwind.config.js', 'postcss.config.js', 'commitlint.config.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        console: 'readonly',
        process: 'readonly',
        URL: 'readonly',
        module: 'readonly',
        document: 'readonly',
        getComputedStyle: 'readonly'
      }
    },
    rules: {
      'no-unused-vars': [
        'error',
        {
          args: 'after-used',
          argsIgnorePattern: '^(?:_|e$|event$|observer$|.*Element$)',
          varsIgnorePattern: '(^_|Element$|observer$)'
        }
      ],
      'no-undef': 'error'
    }
  }
];
