// Use Vite's glob import so the combobox plugin is bundled when installed while
// gracefully skipping the entry in environments where Composer has not yet
// published the vendor assets.
const comboboxModules = import.meta.glob(
    '../../../../vendor/novadaemon/filament-combobox/resources/dist/filament-combobox.js',
    { eager: true },
);

// Accessing the values ensures the import is retained during tree-shaking even
// though the module only applies side effects (it registers combobox behaviour).
Object.values(comboboxModules).forEach(() => {});
