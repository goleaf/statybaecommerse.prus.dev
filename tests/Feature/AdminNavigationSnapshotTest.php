<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Nav;
use Illuminate\Support\Arr;
use Tests\TestCase;

final class AdminNavigationSnapshotTest extends TestCase
{
    public function test_admin_navigation_matches_snapshot(): void
    {
        // Build actual navigation and normalize (avoid diffs from runtime translations).
        $navigation = $this->normalizedNavigation();

        // Locate snapshot file (try a few common paths).
        $snapshotPath = $this->locateSnapshot([
            base_path('tests/Feature/__snapshots__/admin_navigation.snapshot.json'),
            base_path('tests/__snapshots__/admin_navigation.snapshot.json'),
            base_path('tests/Feature/fixtures/admin_navigation.snapshot.json'),
            base_path('tests/Fixtures/admin_navigation.snapshot.json'),
        ]);

        // If not found and regeneration requested, write it now (opt-in).
        if ($snapshotPath === null && $this->shouldRegenerateSnapshots()) {
            $snapshotPath = base_path('tests/Feature/__snapshots__/admin_navigation.snapshot.json');
            $dir = \dirname($snapshotPath);
            if (! is_dir($dir)) {
                \mkdir($dir, 0777, true);
            }
            \file_put_contents(
                $snapshotPath,
                \json_encode($navigation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            // Sanity: ensure it was written
            $this->assertFileExists($snapshotPath, 'Failed to write snapshot at: ' . $snapshotPath);
        }

        // If still not found, fail with a helpful message listing the paths we tried.
        if ($snapshotPath === null) {
            $this->fail(
                "Snapshot file not found.\n".
                "Create one by setting REGENERATE_SNAPSHOTS=true and re-running this test, or place it at one of:\n" .
                "- tests/Feature/__snapshots__/admin_navigation.snapshot.json\n" .
                "- tests/__snapshots__/admin_navigation.snapshot.json\n" .
                "- tests/Feature/fixtures/admin_navigation.snapshot.json\n" .
                "- tests/Fixtures/admin_navigation.snapshot.json\n"
            );
        }

        // Load expected snapshot & compare strictly.
        /** @var array<int, array<string,mixed>> $expected */
        $expected = \json_decode(
            (string) \file_get_contents($snapshotPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame($expected, $navigation);
    }

    /**
     * Build the navigation structure and normalize it to stable values:
     * - Force 'label' to use 'label_key' (if present) so translations don't affect snapshot.
     * - Keep only the asserted fields.
     *
     * @return array<int, array{label:string|null,label_key:string|null,icon:string|null,sort:int|null}>
     */
    private function normalizedNavigation(): array
    {
        $groups = Nav::navigationGroups();

        return array_values(array_map(static function (array $g): array {
            $labelKey = isset($g['label_key']) && is_string($g['label_key']) && $g['label_key'] !== ''
                ? $g['label_key']
                : (is_string($g['key'] ?? null) ? $g['key'] : 'Navigation');

            $label = $labelKey; // normalize label to label_key for deterministic snapshot

            // Trim to asserted fields only
            return Arr::only([
                'label'     => $label,
                'label_key' => $g['label_key'] ?? $labelKey,
                'icon'      => $g['icon']      ?? null,
                'sort'      => $g['sort']      ?? null,
            ], ['label', 'label_key', 'icon', 'sort']);
        }, $groups));
    }

    /**
     * Return the first existing snapshot path from candidates, or null.
     */
    private function locateSnapshot(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (is_string($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Whether to regenerate snapshot files (opt-in).
     */
    private function shouldRegenerateSnapshots(): bool
    {
        $flag = env('REGENERATE_SNAPSHOTS');
        return in_array($flag, [true, 1, '1', 'true', 'yes'], true);
    }
}
