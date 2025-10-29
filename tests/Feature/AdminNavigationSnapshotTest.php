<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

#[CoversNothing]
final class AdminNavigationSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_navigation_matches_snapshot(): void
    {
        $panel = $this->resolveAdminPanel();

        // Ensure the panel registered resources using the deterministic helper.
        $this->assertSame(Nav::orderedResources(), $panel->getResources());

        $navigation = $this->buildNavigationTree();
        $snapshotPath = base_path('tests/__snapshots__/admin-navigation.json');
        $snapshotDir = dirname($snapshotPath);

        // Create snapshot directory if it doesn't exist
        if (! is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0o755, true);
        }

        // If snapshot doesn't exist, create it with current navigation structure
        if (! file_exists($snapshotPath)) {
            file_put_contents(
                $snapshotPath,
                json_encode($navigation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            );

            $this->markTestSkipped('Snapshot file created. Please review and commit it, then rerun the test.');
        }

        $expected = json_decode((string) file_get_contents($snapshotPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($expected, $navigation);
    }

    /**
     * Build the navigation structure in a serialisable format for snapshot comparison.
     *
     * @return array{groups: array<int, array{label: string, icon: string|null, sort: int|null, resources: array<int, array{class: class-string, icon: string|null, sort: int|null}>}>, ungrouped: array<int, array{class: class-string, icon: string|null, sort: int|null}>}
     */
    private function buildNavigationTree(): array
    {
        $groups = Nav::navigationGroups();
        $orderedResources = Nav::orderedResources();

        $grouped = array_map(
            static function (array $group) use ($orderedResources): array {
                $resources = array_values(array_filter(
                    $orderedResources,
                    static fn (string $resource): bool => Nav::groupKeyForResource($resource) === $group['key'],
                ));

                return [
                    'label'     => $group['label'],
                    'label_key' => $group['label_key'],
                    'icon'      => $group['icon'],
                    'sort'      => $group['sort'],
                    'resources' => array_map(
                        static fn (string $resource): array => [
                            'class' => $resource,
                            'icon'  => Nav::iconForResource($resource),
                            'sort'  => Nav::sortForResource($resource),
                        ],
                        $resources,
                    ),
                ];
            },
            $groups,
        );

        $ungrouped = array_values(array_filter(
            $orderedResources,
            static fn (string $resource): bool => Nav::groupKeyForResource($resource) === null,
        ));

        return [
            'groups'    => $grouped,
            'ungrouped' => array_map(
                static fn (string $resource): array => [
                    'class' => $resource,
                    'icon'  => Nav::iconForResource($resource),
                    'sort'  => Nav::sortForResource($resource),
                ],
                $ungrouped,
            ),
        ];
    }
}
