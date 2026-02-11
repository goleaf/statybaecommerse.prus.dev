<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CategoryImporter extends BaseImporter
{
    protected static ?string $model = Category::class;

    protected function beforeValidate(): void
    {
        $name = $this->data['name'] ?? null;
        $slug = $this->data['slug'] ?? null;

        if (! filled($slug) && is_string($name) && $name !== '') {
            $this->data['slug'] = Str::slug($name);
        }

        if ($this->record && ! $this->record->exists) {
            $this->applyDefaults();
        }

        parent::beforeValidate();
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('slug')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('short_description'),
            ImportColumn::make('parent')
                ->relationship(resolveUsing: static function (mixed $state): ?Category {
                    return static::resolveParentFromState($state);
                })
                ->ignoreBlankState(),
            ImportColumn::make('sort_order')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('is_visible')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_active')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_enabled')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_featured')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('color'),
            ImportColumn::make('show_in_menu')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('product_limit')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('icon'),
        ];
    }

    public function resolveRecord(): Category
    {
        $name = $this->data['name'] ?? null;
        $slug = $this->data['slug'] ?? null;

        if (! filled($slug) && is_string($name) && $name !== '') {
            $slug = Str::slug($name);
            $this->data['slug'] = $slug;
        }

        if (filled($slug)) {
            return Category::query()
                ->withoutGlobalScopes()
                ->withTrashed()
                ->firstOrNew(['slug' => $slug]);
        }

        if (is_string($name) && $name !== '') {
            return Category::query()
                ->withoutGlobalScopes()
                ->withTrashed()
                ->firstOrNew(['name' => $name]);
        }

        return new Category;
    }

    protected function beforeSave(): void
    {
        if ($this->record instanceof Category && method_exists($this->record, 'restore') && $this->record->trashed()) {
            $this->record->restore();
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your category import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = static::calculateFailedRowsCount($import)) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'Basic Information' => ['name', 'slug', 'parent'],
            'Descriptions'      => ['description', 'short_description'],
            'Settings'          => ['sort_order', 'is_visible', 'is_active', 'is_enabled', 'is_featured', 'show_in_menu'],
            'Appearance'        => ['color', 'icon'],
        ];
    }

    private function applyDefaults(): void
    {
        $defaults = [
            'sort_order'   => 0,
            'is_visible'   => true,
            'is_active'    => true,
            'is_enabled'   => true,
            'is_featured'  => false,
            'show_in_menu' => true,
        ];

        foreach ($defaults as $field => $value) {
            if (
                ! array_key_exists($field, $this->data)
                || $this->data[$field] === null
                || $this->data[$field] === ''
            ) {
                $this->data[$field] = $value;
            }
        }
    }

    private static function resolveParentFromState(mixed $state): ?Category
    {
        if ($state === null) {
            return null;
        }

        $raw = is_string($state) ? trim($state) : $state;

        if ($raw === '' || $raw === null) {
            return null;
        }

        $query = Category::query()->withoutGlobalScopes()->withTrashed();

        if (is_numeric($raw)) {
            $category = $query->find((int) $raw);
            if ($category) {
                if (method_exists($category, 'restore') && $category->trashed()) {
                    $category->restore();
                }

                return $category;
            }
        }

        $name = is_string($raw) ? $raw : (string) $raw;
        $slug = Str::slug($name);

        $category = $query->where('slug', $slug)->first()
            ?? $query->where('name', $name)->first();

        if ($category) {
            if (method_exists($category, 'restore') && $category->trashed()) {
                $category->restore();
            }

            return $category;
        }

        $generatedSlug = Category::generateUniqueSlug($name);

        return Category::query()->create([
            'name'         => $name,
            'slug'         => $generatedSlug,
            'sort_order'   => 0,
            'is_enabled'   => true,
            'is_active'    => true,
            'is_visible'   => true,
            'is_featured'  => false,
            'show_in_menu' => true,
        ]);
    }
}
