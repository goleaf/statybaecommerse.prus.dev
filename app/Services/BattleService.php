<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service responsible for simulating a simple Travian-style battle between two armies.
 *
 * The class keeps the implementation framework-agnostic so it can be reused in tests
 * and potential console tooling without pulling additional dependencies.
 */
final class BattleService
{
    private const RESOURCE_ORDER = ['wood', 'clay', 'iron', 'crop'];

    /**
     * Simulate the outcome of the battle by calculating army strength, losses and loot.
     *
     * @param array{
     *     units?: array<string, array<string, mixed>>,
     *     resources?: array<string, int>
     * } $attacker Payload describing the attacking army and optional haul modifiers.
     * @param array{
     *     units?: array<string, array<string, mixed>>,
     *     resources?: array<string, int>
     * } $defender Payload describing the defending army and available resources.
     * @return array{
     *     winner: string,
     *     strength: array{attacker: float, defender: float},
     *     casualties: array{
     *         attacker: array{losses: array<string, int>, survivors: array<string, int>},
     *         defender: array{losses: array<string, int>, survivors: array<string, int>}
     *     },
     *     loot: array{
     *         resources: array<string, int>,
     *         defenderRemaining: array<string, int>,
     *         capacity: array{total: int, used: int, remaining: int}
     *     }
     * }
     */
    public function simulateBattle(array $attacker, array $defender): array
    {
        // Normalise and guard against missing keys so downstream math stays predictable.
        $attackerUnits = $attacker['units'] ?? [];
        $defenderUnits = $defender['units'] ?? [];
        $defenderResources = $defender['resources'] ?? [];

        // Resolve the outcome in terms of casualties and overall winner.
        $casualties = $this->calculateCasualties($attackerUnits, $defenderUnits);
        $winner = $casualties['winner'];

        // Default loot payload assumes no plunder (draw or defender victory).
        $loot = [
            'resources'         => $this->emptyResourceArray(),
            'defenderRemaining' => $this->normalizeResources($defenderResources),
            'capacity'          => [
                'total'     => 0,
                'used'      => 0,
                'remaining' => 0,
            ],
        ];

        // Only victorious attackers should haul resources based on surviving carry capacity.
        if ($winner === 'attacker') {
            $capacity = $this->calculateCarryCapacity($attackerUnits, $casualties['attacker']['survivors']);
            $loot = $this->distributeResources($this->normalizeResources($defenderResources), $capacity);
        }

        return [
            'winner'   => $winner,
            'strength' => [
                'attacker' => $casualties['attacker']['power'],
                'defender' => $casualties['defender']['power'],
            ],
            'casualties' => [
                'attacker' => [
                    'losses'    => $casualties['attacker']['losses'],
                    'survivors' => $casualties['attacker']['survivors'],
                ],
                'defender' => [
                    'losses'    => $casualties['defender']['losses'],
                    'survivors' => $casualties['defender']['survivors'],
                ],
            ],
            'loot' => $loot,
        ];
    }

    /**
     * Determine winner and troop losses for both sides.
     *
     * @param array<string, array<string, mixed>> $attackerUnits
     * @param array<string, array<string, mixed>> $defenderUnits
     * @return array{
     *     winner: string,
     *     attacker: array{power: float, lossRate: float, losses: array<string, int>, survivors: array<string, int>},
     *     defender: array{power: float, lossRate: float, losses: array<string, int>, survivors: array<string, int>}
     * }
     */
    public function calculateCasualties(array $attackerUnits, array $defenderUnits): array
    {
        // Compute raw power for both sides before losses are applied.
        $attackerPower = $this->calculateAttackPower($attackerUnits);
        $defenderPower = $this->calculateDefensePower($defenderUnits);

        // With no effective power on either side the fight is considered a draw.
        if ($attackerPower <= 0.0 && $defenderPower <= 0.0) {
            return [
                'winner'   => 'draw',
                'attacker' => [
                    'power'     => 0.0,
                    'lossRate'  => 0.0,
                    'losses'    => $this->initializeCounts($attackerUnits, 0),
                    'survivors' => $this->initializeCounts($attackerUnits, fn (int $count): int => $count),
                ],
                'defender' => [
                    'power'     => 0.0,
                    'lossRate'  => 0.0,
                    'losses'    => $this->initializeCounts($defenderUnits, 0),
                    'survivors' => $this->initializeCounts($defenderUnits, fn (int $count): int => $count),
                ],
            ];
        }

        // Loss rates are a proportion of the opponent's power relative to the army power.
        $attackerLossRate = $this->calculateLossRate($attackerPower, $defenderPower);
        $defenderLossRate = $this->calculateLossRate($defenderPower, $attackerPower);

        // Translate loss ratios into discrete troop losses and survivors per unit type.
        $attackerLosses = $this->calculateLosses($attackerUnits, $attackerLossRate);
        $defenderLosses = $this->calculateLosses($defenderUnits, $defenderLossRate);

        $attackerSurvivors = $this->calculateSurvivors($attackerUnits, $attackerLosses);
        $defenderSurvivors = $this->calculateSurvivors($defenderUnits, $defenderLosses);

        // Resolve the battle winner based on initial power comparisons.
        $winner = 'draw';
        if ($attackerPower > $defenderPower) {
            $winner = 'attacker';
        } elseif ($defenderPower > $attackerPower) {
            $winner = 'defender';
        }

        return [
            'winner'   => $winner,
            'attacker' => [
                'power'     => $attackerPower,
                'lossRate'  => $attackerLossRate,
                'losses'    => $attackerLosses,
                'survivors' => $attackerSurvivors,
            ],
            'defender' => [
                'power'     => $defenderPower,
                'lossRate'  => $defenderLossRate,
                'losses'    => $defenderLosses,
                'survivors' => $defenderSurvivors,
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $units
     */
    private function calculateAttackPower(array $units): float
    {
        $power = 0.0;

        foreach ($units as $unit) {
            $count = (int) ($unit['count'] ?? 0);
            $attack = (float) ($unit['attack'] ?? 0);

            // Each unit contributes its attack value multiplied by the number of troops.
            $power += $count * $attack;
        }

        return $power;
    }

    /**
     * @param array<string, array<string, mixed>> $units
     */
    private function calculateDefensePower(array $units): float
    {
        $power = 0.0;

        foreach ($units as $unit) {
            $count = (int) ($unit['count'] ?? 0);
            $defense = (float) ($unit['defense'] ?? 0);

            $power += $count * $defense;
        }

        return $power;
    }

    private function calculateLossRate(float $power, float $opponentPower): float
    {
        if ($power <= 0.0) {
            return $opponentPower > 0.0 ? 1.0 : 0.0;
        }

        if ($opponentPower <= 0.0) {
            return 0.0;
        }

        return min(1.0, $opponentPower / $power);
    }

    /**
     * Convert a loss ratio into concrete unit losses.
     *
     * @param  array<string, array<string, mixed>> $units
     * @return array<string, int>
     */
    private function calculateLosses(array $units, float $rate): array
    {
        $losses = [];

        foreach ($units as $name => $unit) {
            $count = max(0, (int) ($unit['count'] ?? 0));
            $loss = (int) round($count * min(1.0, max($rate, 0.0)));
            $losses[$name] = min($loss, $count);
        }

        return $losses;
    }

    /**
     * Determine survivors for each unit after the battle.
     *
     * @param  array<string, array<string, mixed>> $units
     * @param  array<string, int>                  $losses
     * @return array<string, int>
     */
    private function calculateSurvivors(array $units, array $losses): array
    {
        $survivors = [];

        foreach ($units as $name => $unit) {
            $count = max(0, (int) ($unit['count'] ?? 0));
            $loss = $losses[$name] ?? 0;
            $survivors[$name] = max(0, $count - $loss);
        }

        return $survivors;
    }

    /**
     * Sum up carry capacity from the surviving units.
     *
     * @param array<string, array<string, mixed>> $units
     * @param array<string, int>                  $survivors
     */
    private function calculateCarryCapacity(array $units, array $survivors): int
    {
        $capacity = 0;

        foreach ($units as $name => $unit) {
            $carry = (int) ($unit['carry'] ?? 0);
            $count = $survivors[$name] ?? 0;

            $capacity += $carry * max($count, 0);
        }

        return $capacity;
    }

    /**
     * Split resources based on available carrying capacity.
     *
     * @param array<string, int> $resources
     */
    private function distributeResources(array $resources, int $capacity): array
    {
        $loot = $this->emptyResourceArray();
        $remaining = $this->normalizeResources($resources);
        $used = 0;

        if ($capacity <= 0) {
            return [
                'resources'         => $loot,
                'defenderRemaining' => $remaining,
                'capacity'          => [
                    'total'     => 0,
                    'used'      => 0,
                    'remaining' => 0,
                ],
            ];
        }

        foreach (self::RESOURCE_ORDER as $resource) {
            if ($used >= $capacity) {
                break;
            }

            $available = $remaining[$resource];

            if ($available <= 0) {
                continue;
            }

            $canTake = min($available, $capacity - $used);

            $loot[$resource] = $canTake;
            $remaining[$resource] -= $canTake;
            $used += $canTake;
        }

        return [
            'resources'         => $loot,
            'defenderRemaining' => $remaining,
            'capacity'          => [
                'total'     => $capacity,
                'used'      => $used,
                'remaining' => max(0, $capacity - $used),
            ],
        ];
    }

    /**
     * Helper to initialise arrays of unit counts with either a constant or closure value.
     *
     * @param  array<string, array<string, mixed>> $units
     * @param  int|callable(int):int               $value
     * @return array<string, int>
     */
    private function initializeCounts(array $units, int|callable $value): array
    {
        $resolved = [];

        foreach ($units as $name => $unit) {
            $count = max(0, (int) ($unit['count'] ?? 0));
            $resolved[$name] = is_callable($value) ? $value($count) : $value;
        }

        return $resolved;
    }

    /**
     * Normalise resources to the expected Travian order.
     *
     * @param  array<string, mixed> $resources
     * @return array<string, int>
     */
    private function normalizeResources(array $resources): array
    {
        $normalized = [];

        foreach (self::RESOURCE_ORDER as $resource) {
            $normalized[$resource] = max(0, (int) ($resources[$resource] ?? 0));
        }

        return $normalized;
    }

    /**
     * Provide a zeroed-out resource array ready for accumulation.
     *
     * @return array<string, int>
     */
    private function emptyResourceArray(): array
    {
        return array_fill_keys(self::RESOURCE_ORDER, 0);
    }
}
