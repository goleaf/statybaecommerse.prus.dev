import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/**
 * Recursively gather every SCSS entry within the resources/css directory so the
 * build command automatically compiles new stylesheets without updating this
 * configuration manually.
 */
const collectScssInputs = (directory) => {
  /** @type {string[]} */
  const entries = [];

  // Short-circuit when the directory is absent (for example during fresh
  // installs) so Vite receives an empty list instead of throwing.
  if (!fs.existsSync(directory)) {
    return entries;
  }

  // Walk the provided directory tree and collect SCSS files while skipping
  // dot-directories to avoid traversing system folders such as `.git`.
  for (const file of fs.readdirSync(directory, { withFileTypes: true })) {
    if (file.name.startsWith('.')) {
      continue;
    }

    const filePath = path.join(directory, file.name);

    if (file.isDirectory()) {
      entries.push(...collectScssInputs(filePath));
      continue;
    }

    if (file.isFile() && file.name.endsWith('.scss')) {
      // Convert absolute paths back into project-relative inputs understood by
      // the Laravel Vite plugin.
      const relativePath = path.relative(process.cwd(), filePath).replace(/\\/g, '/');
      entries.push(relativePath);
    }
  }

  return entries;
};

/**
 * Resolve the optional Filament Nord theme path once so we can verify its
 * existence before asking Vite to include it as an entry.
 */
const filamentNordThemePath = 'vendor/andreia/filament-nord-theme/resources/css/theme.css';
const absoluteFilamentNordThemePath = path.resolve(process.cwd(), filamentNordThemePath);
const filamentFullCalendarCssPath = 'vendor/saade/filament-fullcalendar/resources/css/filament-fullcalendar.css';
const absoluteFilamentFullCalendarCssPath = path.resolve(process.cwd(), filamentFullCalendarCssPath);
const fallbackFullCalendarCssPath = path.resolve(process.cwd(), 'resources/css/vendor-fallbacks/filament-fullcalendar.css');
const filamentComboboxCssPath = 'vendor/novadaemon/filament-combobox/resources/dist/filament-combobox.css';
const absoluteFilamentComboboxCssPath = path.resolve(process.cwd(), filamentComboboxCssPath);
const fallbackComboboxCssPath = path.resolve(process.cwd(), 'resources/css/vendor-fallbacks/filament-combobox.css');
const filamentThemeCssPath = 'vendor/filament/filament/resources/css/theme.css';
const absoluteFilamentThemeCssPath = path.resolve(process.cwd(), filamentThemeCssPath);
const fallbackFilamentThemeCssPath = path.resolve(process.cwd(), 'resources/css/vendor-fallbacks/filament-theme.css');

export default defineConfig({
  assetsInclude: [
    '**/*.woff',
    '**/*.woff2',
    '**/*.ttf',
    '**/*.eot',
  ],
  plugins: [
    tailwindcss(),
    {
      name: 'filament-vendor-fallbacks',
      resolveId(source) {
        // Redirect Filament vendor style imports to local fallbacks when the
        // Composer packages have not been installed yet.
        if (source === '@filament-fullcalendar' || source.endsWith('vendor/saade/filament-fullcalendar/resources/css/filament-fullcalendar.css')) {
          return fs.existsSync(absoluteFilamentFullCalendarCssPath)
            ? absoluteFilamentFullCalendarCssPath
            : fallbackFullCalendarCssPath;
        }

        if (source === '@filament-combobox' || source.endsWith('vendor/novadaemon/filament-combobox/resources/dist/filament-combobox.css')) {
          return fs.existsSync(absoluteFilamentComboboxCssPath)
            ? absoluteFilamentComboboxCssPath
            : fallbackComboboxCssPath;
        }

        if (source === '@filament-theme' || source.endsWith('vendor/filament/filament/resources/css/theme.css')) {
          return fs.existsSync(absoluteFilamentThemeCssPath)
            ? absoluteFilamentThemeCssPath
            : fallbackFilamentThemeCssPath;
        }

        return null;
      },
    },
    laravel({
      input: [
        // Include every SCSS file discovered in the resources/css directory so
        // the build output stays in sync when new stylesheets are added.
        ...collectScssInputs(path.resolve(process.cwd(), 'resources/css')),
        'resources/js/app.js',
        'resources/js/live-notifications.js',
        // Bundle the Filament admin JavaScript entry to expose combobox behaviour during builds.
        'resources/js/filament/admin/theme.js',
        'resources/images/hero.png',
        // Only include the Filament Nord theme when Composer has installed it;
        // skipping the entry prevents build failures in fresh environments.
        ...(fs.existsSync(absoluteFilamentNordThemePath) ? [filamentNordThemePath] : []),
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
