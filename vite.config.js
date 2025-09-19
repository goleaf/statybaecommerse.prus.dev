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
        'resources/css/app.css',
        'resources/css/category-show.css',
        'resources/js/app.js',
        'resources/js/live-notifications.js',
        'resources/images/hero.png',
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
