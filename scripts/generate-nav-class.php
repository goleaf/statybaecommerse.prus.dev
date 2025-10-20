<?php
function convertGroupLabel(?string $value): ?string
{
    if ($value === null || $value === 'null') {
        return null;
    }

    $value = trim($value, "'\" ");

    if (str_starts_with($value, 'NavigationGroup::')) {
        $case = substr($value, strlen('NavigationGroup::'));
        return match ($case) {
            'Analytics' => 'Analytics',
            'Campaigns' => 'Campaigns',
            'Content' => 'Content',
            'News' => 'News',
            'Products' => 'Products',
            'Reports' => 'Reports',
            default => $case,
        };
    }

    return $value;
}

function convertIcon(?string $value): ?string
{
    if ($value === null || $value === 'null') {
        return null;
    }

    $value = trim($value, "'\" ");

    if ($value === 'Heroicon::OutlinedRectangleStack') {
        return 'heroicon-o-rectangle-stack';
    }

    return $value;
}

function convertSort(?string $value): ?int
{
    if ($value === null || $value === 'null') {
        return null;
    }

    return (int) $value;
}

function constantName(string $label): string
{
    $label = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($label));
    $label = trim($label, '_');
    if ($label === '') {
        $label = 'UNSPECIFIED';
    }
    return 'NAV_GROUP_' . $label;
}

$groupDefinitions = [
    'Products' => ['icon' => 'heroicon-o-cube', 'sort' => 100],
    'Inventory' => ['icon' => 'heroicon-o-archive-box', 'sort' => 110],
    'Orders' => ['icon' => 'heroicon-o-shopping-bag', 'sort' => 200],
    'Customers' => ['icon' => 'heroicon-o-user-group', 'sort' => 210],
    'Users' => ['icon' => 'heroicon-o-users', 'sort' => 220],
    'Marketing' => ['icon' => 'heroicon-o-megaphone', 'sort' => 300],
    'Campaigns' => ['icon' => 'heroicon-o-rocket-launch', 'sort' => 310],
    'Discounts' => ['icon' => 'heroicon-o-tag', 'sort' => 320],
    'Referral' => ['icon' => 'heroicon-o-gift', 'sort' => 330],
    'Content' => ['icon' => 'heroicon-o-document-text', 'sort' => 400],
    'Content Management' => ['icon' => 'heroicon-o-folder', 'sort' => 410],
    'News' => ['icon' => 'heroicon-o-newspaper', 'sort' => 420],
    'Analytics' => ['icon' => 'heroicon-o-chart-bar', 'sort' => 500],
    'Reports' => ['icon' => 'heroicon-o-document-chart-bar', 'sort' => 510],
    'Settings' => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 600],
    'System' => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 610],
];

$resources = [];
$groupLabels = [];
foreach (glob('app/Filament/Resources/*Resource.php') as $file) {
    $class = 'App\\Filament\\Resources\\' . basename($file, '.php');
    $contents = file_get_contents($file);

    $groupRaw = null;
    if (preg_match('/protected static[^\n]*\$navigationGroup\s*=\s*([^;]+);/', $contents, $m)) {
        $groupRaw = trim($m[1]);
    } elseif (preg_match('/public static function getNavigationGroup[^\{]*\{(.*?)}\n/s', $contents, $m)) {
        if (preg_match('/return\s+([^;]+);/', $m[1], $r)) {
            $groupRaw = trim($r[1]);
        }
    }
    $group = convertGroupLabel($groupRaw);
    if ($group !== null) {
        $groupLabels[$group] = constantName($group);
    }

    $iconRaw = null;
    if (preg_match('/protected static[^\n]*\$navigationIcon\s*=\s*([^;]+);/', $contents, $m)) {
        $iconRaw = trim($m[1]);
    } elseif (preg_match('/public static function getNavigationIcon[^\{]*\{(.*?)}\n/s', $contents, $m)) {
        if (preg_match('/return\s+([^;]+);/', $m[1], $r)) {
            $iconRaw = trim($r[1]);
        }
    }
    $icon = convertIcon($iconRaw);

    $sortRaw = null;
    if (preg_match('/protected static[^\n]*\$navigationSort\s*=\s*([^;]+);/', $contents, $m)) {
        $sortRaw = trim($m[1]);
    } elseif (preg_match('/public static function getNavigationSort[^\{]*\{(.*?)}\n/s', $contents, $m)) {
        if (preg_match('/return\s+([^;]+);/', $m[1], $r)) {
            $sortRaw = trim($r[1]);
        }
    }
    $sort = convertSort($sortRaw);

    $resources[$class] = [
        'group' => $group,
        'icon' => $icon,
        'sort' => $sort,
    ];
}
ksort($resources);
ksort($groupLabels);

// Ensure all predefined group definitions are registered as constants.
foreach (array_keys($groupDefinitions) as $label) {
    if (!isset($groupLabels[$label])) {
        $groupLabels[$label] = constantName($label);
    }
}
ksort($groupLabels);

$output = [];
$output[] = "<?php";
$output[] = "";
$output[] = "declare(strict_types=1);";
$output[] = "";
$output[] = "namespace App\\Support;";
$output[] = "";
$output[] = "final class Nav";
$output[] = "{";

foreach ($groupLabels as $label => $constant) {
    $output[] = "    public const $constant = '" . addslashes($label) . "';";
}

$output[] = "";
$output[] = "    /**";
$output[] = "     * @var array<string, array{icon: string|null, sort: int|null}>";
$output[] = "     */";
$output[] = "    public const GROUP_DEFINITIONS = [";
foreach ($groupDefinitions as $label => $definition) {
    $constant = $groupLabels[$label];
    $icon = $definition['icon'] !== null ? "'" . addslashes($definition['icon']) . "'" : 'null';
    $sort = $definition['sort'] ?? 'null';
    $output[] = "        self::$constant => ['icon' => $icon, 'sort' => $sort],";
}
$output[] = "    ];";

$output[] = "";
$output[] = "    /**";
$output[] = "     * @var array<class-string, array{group: string|null, icon: string|null, sort: int|null}>";
$output[] = "     */";
$output[] = "    public const RESOURCE_CONFIG = [";
foreach ($resources as $class => $config) {
    $group = $config['group'] !== null ? 'self::' . $groupLabels[$config['group']] : 'null';
    $icon = $config['icon'] !== null ? "'" . addslashes($config['icon']) . "'" : 'null';
    $sort = $config['sort'] !== null ? (string) $config['sort'] : 'null';
    $output[] = "        \\{$class}::class => ['group' => $group, 'icon' => $icon, 'sort' => $sort],";
}
$output[] = "    ];";

$output[] = "";
$output[] = "    public static function groupKeyForResource(string $resource): ?string";
$output[] = "    {";
$output[] = "        return self::RESOURCE_CONFIG[$resource]['group'] ?? null;";
$output[] = "    }";

$output[] = "";
$output[] = "    public static function groupForResource(string $resource): ?string";
$output[] = "    {";
$output[] = "        $group = self::groupKeyForResource($resource);";
$output[] = "";
$output[] = "        return $group !== null ? __($group) : null;";
$output[] = "    }";

$output[] = "";
$output[] = "    public static function iconForResource(string $resource): ?string";
$output[] = "    {";
$output[] = "        return self::RESOURCE_CONFIG[$resource]['icon'] ?? null;";
$output[] = "    }";

$output[] = "";
$output[] = "    public static function sortForResource(string $resource): ?int";
$output[] = "    {";
$output[] = "        return self::RESOURCE_CONFIG[$resource]['sort'] ?? null;";
$output[] = "    }";

$output[] = "";
$output[] = "    public static function groupIcon(?string $group): ?string";
$output[] = "    {";
$output[] = "        return $group !== null ? self::GROUP_DEFINITIONS[$group]['icon'] ?? null : null;";
$output[] = "    }";

$output[] = "";
$output[] = "    public static function groupSort(?string $group): ?int";
$output[] = "    {";
$output[] = "        return $group !== null ? self::GROUP_DEFINITIONS[$group]['sort'] ?? null : null;";
$output[] = "    }";

$output[] = "";
$output[] = "    /**";
$output[] = "     * @return array<int, array{key: string, label: string, icon: string|null, sort: int|null}>";
$output[] = "     */";
$output[] = "    public static function navigationGroups(): array";
$output[] = "    {";
$output[] = "        $groups = [];";
$output[] = "        foreach (self::GROUP_DEFINITIONS as $key => $definition) {";
$output[] = "            $groups[] = ['key' => $key, 'label' => __($key), 'icon' => $definition['icon'], 'sort' => $definition['sort']];";
$output[] = "        }";
$output[] = "";
$output[] = "        usort($groups, static fn (array $a, array $b): int => ($a['sort'] ?? PHP_INT_MAX) <=> ($b['sort'] ?? PHP_INT_MAX));";
$output[] = "";
$output[] = "        return $groups;";
$output[] = "    }";

$output[] = "";
$output[] = "    /**";
$output[] = "     * @return array<int, class-string>";
$output[] = "     */";
$output[] = "    public static function orderedResources(): array";
$output[] = "    {";
$output[] = "        $resources = self::RESOURCE_CONFIG;";
$output[] = "";
$output[] = "        uksort($resources, static function (string $a, string $b) use ($resources): int {";
$output[] = "            $configA = $resources[$a];";
$output[] = "            $configB = $resources[$b];";
$output[] = "";
$output[] = "            $groupSortA = self::groupSort($configA['group'] ?? null) ?? PHP_INT_MAX;";
$output[] = "            $groupSortB = self::groupSort($configB['group'] ?? null) ?? PHP_INT_MAX;";
$output[] = "";
$output[] = "            if ($groupSortA !== $groupSortB) {";
$output[] = "                return $groupSortA <=> $groupSortB;";
$output[] = "            }";
$output[] = "";
$output[] = "            $sortA = $configA['sort'] ?? PHP_INT_MAX;";
$output[] = "            $sortB = $configB['sort'] ?? PHP_INT_MAX;";
$output[] = "";
$output[] = "            if ($sortA !== $sortB) {";
$output[] = "                return $sortA <=> $sortB;";
$output[] = "            }";
$output[] = "";
$output[] = "            return $a <=> $b;";
$output[] = "        });";
$output[] = "";
$output[] = "        return array_keys($resources);";
$output[] = "    }";

$output[] = "}";
$output[] = "";

file_put_contents('app/Support/Nav.php', implode("\n", $output));
