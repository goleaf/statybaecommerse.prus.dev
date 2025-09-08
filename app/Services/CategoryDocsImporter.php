<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class CategoryDocsImporter
{
    public function import(string $basePath, bool $includeHeadings = false, bool $importFiles = true): array
    {
        $created = 0;
        $updated = 0;

        $normalizedBasePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        if (!is_dir($normalizedBasePath)) {
            return ['created' => 0, 'updated' => 0];
        }

        $positionCounterByParent = [];

        $walker = function (string $path, ?int $parentId) use (&$walker, &$created, &$updated, &$positionCounterByParent, $includeHeadings, $importFiles, $normalizedBasePath): void {
            $items = scandir($path) ?: [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $fullPath = $path . DIRECTORY_SEPARATOR . $item;
                if (is_dir($fullPath)) {
                    $name = Str::of($item)->replace(['_', '-'], ' ')->title()->value();
                    $slug = $this->buildSlugFromPath($fullPath, $normalizedBasePath);

                    $category = Category::query()->where('slug', $slug)->first();
                    if ($category) {
                        $payload = [
                            'name' => $name,
                            'parent_id' => $parentId,
                            'is_enabled' => true,
                        ];
                        $category->update($this->filterExistingColumns('categories', $payload));
                        $updated++;
                    } else {
                        $position = $positionCounterByParent[$parentId ?? 0] = ($positionCounterByParent[$parentId ?? 0] ?? 0) + 1;
                        $payload = [
                            'name' => $name,
                            'slug' => $slug,
                            'parent_id' => $parentId,
                            'position' => $position,
                            'is_enabled' => true,
                        ];
                        Category::query()->create($this->filterExistingColumns('categories', $payload));
                        $created++;
                    }

                    $currentParentId = Category::query()->where('slug', $slug)->value('id');
                    $walker($fullPath, is_int($currentParentId) ? $currentParentId : null);
                    continue;
                }

                if (!$importFiles || !Str::of($item)->lower()->endsWith('.md')) {
                    continue;
                }

                $stem = pathinfo($item, PATHINFO_FILENAME);
                $name = Str::of($stem)->replace(['_', '-'], ' ')->title()->value();
                $slug = $this->buildSlugFromPath($fullPath, $normalizedBasePath);

                $category = Category::query()->where('slug', $slug)->first();
                if ($category) {
                    $payload = [
                        'name' => $name,
                        'parent_id' => $parentId,
                        'is_enabled' => true,
                    ];
                    $category->update($this->filterExistingColumns('categories', $payload));
                    $updated++;
                } else {
                    $position = $positionCounterByParent[$parentId ?? 0] = ($positionCounterByParent[$parentId ?? 0] ?? 0) + 1;
                    $payload = [
                        'name' => $name,
                        'slug' => $slug,
                        'parent_id' => $parentId,
                        'position' => $position,
                        'is_enabled' => true,
                    ];
                    Category::query()->create($this->filterExistingColumns('categories', $payload));
                    $created++;
                }

                if ($includeHeadings) {
                    $currentParentId = Category::query()->where('slug', $slug)->value('id');
                    $this->importHeadingsAsChildren($fullPath, is_int($currentParentId) ? $currentParentId : null, $positionCounterByParent, $created, $updated);
                }
            }
        };

        $walker($normalizedBasePath, null);

        return ['created' => $created, 'updated' => $updated];
    }

    private function buildSlugFromPath(string $path, string $basePath): string
    {
        $relative = Str::of($path)->replaceFirst(rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '')->value();
        $relative = Str::of($relative)->replace(['\\', '/'], '-')->value();
        $relative = Str::of($relative)->replace('.md', '')->value();
        $slug = Str::slug($relative);

        // Guarantee global uniqueness by appending a sequence if needed
        $original = $slug;
        $i = 2;
        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function importHeadingsAsChildren(string $markdownFile, ?int $parentId, array &$positionCounterByParent, int &$created, int &$updated): void
    {
        if (!is_file($markdownFile)) {
            return;
        }
        $content = file_get_contents($markdownFile);
        if ($content === false || $content === '') {
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || !Str::startsWith($line, ['# ', '## ', '### '])) {
                continue;
            }
            $plain = ltrim($line, '# ');
            $name = Str::of($plain)->trim()->value();
            $slug = Str::slug(($parentId ?? 0) . '-' . $name);

            $category = Category::query()->where('slug', $slug)->first();
            if ($category) {
                $payload = [
                    'name' => $name,
                    'parent_id' => $parentId,
                    'is_enabled' => true,
                ];
                $category->update($this->filterExistingColumns('categories', $payload));
                $updated++;
            } else {
                $position = $positionCounterByParent[$parentId ?? 0] = ($positionCounterByParent[$parentId ?? 0] ?? 0) + 1;
                $payload = [
                    'name' => $name,
                    'slug' => $slug,
                    'parent_id' => $parentId,
                    'position' => $position,
                    'is_enabled' => true,
                ];
                Category::query()->create($this->filterExistingColumns('categories', $payload));
                $created++;
            }
        }
    }

    private function filterExistingColumns(string $table, array $payload): array
    {
        $filtered = [];
        foreach ($payload as $key => $value) {
            if ($key === 'parent_id' && !Schema::hasColumn($table, 'parent_id')) {
                continue;
            }
            if ($key === 'position' && !Schema::hasColumn($table, 'position')) {
                continue;
            }
            $filtered[$key] = $value;
        }
        return $filtered;
    }
