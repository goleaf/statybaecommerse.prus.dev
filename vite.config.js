import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  assetsInclude: [
    '**/*.woff',
    '**/*.woff2',
    '**/*.ttf',
    '**/*.eot',
  ],
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.scss',
        'resources/css/filament-enhancements.css',
        // Compile the Filament admin theme so vendor combobox assets remain in sync with CSS imports.
        'resources/css/filament/admin/theme.css',
        'resources/js/app.js',
        'resources/js/live-notifications.js',
        // Bundle the Filament admin JavaScript entry to expose combobox behaviour during builds.
        'resources/js/filament/admin/theme.js',
        'resources/images/hero.png',
        'vendor/andreia/filament-nord-theme/resources/css/theme.css',
      ],
      refresh: [
        ...refreshPaths,
        'app/Livewire/**',
      ],
    }),
    {
      name: 'blade',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.blade.php')) {
          server.ws.send({
            type: 'full-reload',
            path: '*',
          });
        }
      },
    }
  ],
  build: {
    minify: 'esbuild',
    target: 'es2020',
    cssCodeSplit: true,
    sourcemap: false,
    treeshake: true,
    reportCompressedSize: false,
    chunkSizeWarningLimit: 1500,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            if (id.includes('shiki')) return 'vendor-shiki';
            if (id.includes('@shikijs')) return 'vendor-shiki';
            if (id.includes('sortablejs')) return 'vendor-sortable';
            return 'vendor';
          }
        },
      },
    },
    esbuild: {
      drop: ['console', 'debugger'],
    },
  },
  optimizeDeps: {
    include: ['treeselectjs'],
  },
});
