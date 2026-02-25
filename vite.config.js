import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/**
 * Recursively gather every CSS entry within the resources/css directory so the
 * build command automatically compiles new stylesheets without updating this
 * configuration manually.
 */
const collectCssInputs = (directory) => {
  /** @type {string[]} */
  const entries = [];

  // Short-circuit when the directory is absent (for example during fresh
  // installs) so Vite receives an empty list instead of throwing.
  if (!fs.existsSync(directory)) {
    return entries;
  }

  // Walk the provided directory tree and collect CSS files while skipping
  // dot-directories to avoid traversing system folders such as `.git`.
  for (const file of fs.readdirSync(directory, { withFileTypes: true })) {
    if (file.name.startsWith('.')) {
      continue;
    }

    const filePath = path.join(directory, file.name);

    if (file.isDirectory()) {
      entries.push(...collectCssInputs(filePath));
      continue;
    }

    if (file.isFile() && file.name.endsWith('.css')) {
      // Convert absolute paths back into project-relative inputs understood by
      // the Laravel Vite plugin.
      const relativePath = path.relative(process.cwd(), filePath).replace(/\\/g, '/');
      entries.push(relativePath);
    }
  }

  return entries;
};

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
        // Include every CSS file discovered in the resources/css directory so
        // the build output stays in sync when new stylesheets are added.
        ...collectCssInputs(path.resolve(process.cwd(), 'resources/css')),
        'resources/js/app.js',
        'resources/js/live-notifications.js',
        'resources/images/hero.png',
        'resources/css/filament/admin/theme.css',
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
            return 'vendor';
          }
        },
      },
    },
    esbuild: {
      drop: ['console', 'debugger'],
    },
  },
});