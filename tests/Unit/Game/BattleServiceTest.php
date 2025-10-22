<?php

declare(strict_types=1);

namespace Tests\Unit\Game;

use App\Services\BattleService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BattleService::class)]
final class BattleServiceTest extends TestCase
{
    public function test_simulate_battle_returns_attacker_victory_with_loot(): void
    {
        $service = new BattleService;

        // Define an attacker with superior strength and decent carrying capacity.
        $attacker = [
            'units' => [
                'clubswinger' => ['count' => 80, 'attack' => 40, 'defense' => 20, 'carry' => 60],
                'axeman'      => ['count' => 20, 'attack' => 60, 'defense' => 30, 'carry' => 50],
            ],
        ];

        // Defender owns a weaker force and stocks of resources to be looted.
        $defender = [
            'units' => [
                'spearman' => ['count' => 50, 'attack' => 30, 'defense' => 55],
                'archer'   => ['count' => 20, 'attack' => 25, 'defense' => 35],
            ],
            'resources' => ['wood' => 800, 'clay' => 600, 'iron' => 400, 'crop' => 200],
        ];

        $result = $service->simulateBattle($attacker, $defender);

        $this->assertSame('attacker', $result['winner']);
        $this->assertSame(4400.0, $result['strength']['attacker']);
        $this->assertSame(3450.0, $result['strength']['defender']);

        // Attackers should take notable losses but keep a handful of survivors.
        $this->assertSame(['clubswinger' => 63, 'axeman' => 16], $result['casualties']['attacker']['losses']);
        $this->assertSame(['clubswinger' => 17, 'axeman' => 4], $result['casualties']['attacker']['survivors']);

        // Defender is wiped out after the engagement.
        $this->assertSame(['spearman' => 50, 'archer' => 20], $result['casualties']['defender']['losses']);
        $this->assertSame(['spearman' => 0, 'archer' => 0], $result['casualties']['defender']['survivors']);

        // Loot should honour the carrying capacity limit (1 220 resources in this setup).
        $this->assertSame(['wood' => 800, 'clay' => 420, 'iron' => 0, 'crop' => 0], $result['loot']['resources']);
        $this->assertSame(['wood' => 0, 'clay' => 180, 'iron' => 400, 'crop' => 200], $result['loot']['defenderRemaining']);
        $this->assertSame(['total' => 1220, 'used' => 1220, 'remaining' => 0], $result['loot']['capacity']);
    }

    public function test_simulate_battle_returns_defender_victory(): void
    {
        $service = new BattleService;

        $attacker = [
            'units' => [
                'scout' => ['count' => 30, 'attack' => 5, 'defense' => 10, 'carry' => 0],
            ],
        ];

        $defender = [
            'units' => [
                'praetorian' => ['count' => 40, 'attack' => 30, 'defense' => 65],
            ],
        ];

        $result = $service->simulateBattle($attacker, $defender);

        $this->assertSame('defender', $result['winner']);
        $this->assertSame(150.0, $result['strength']['attacker']);
        $this->assertSame(2600.0, $result['strength']['defender']);

        $this->assertSame(['scout' => 30], $result['casualties']['attacker']['losses']);
        $this->assertSame(['scout' => 0], $result['casualties']['attacker']['survivors']);

        $this->assertSame(['praetorian' => 2], $result['casualties']['defender']['losses']);
        $this->assertSame(['praetorian' => 38], $result['casualties']['defender']['survivors']);

        // Defender victory means no loot is moved around.
        $this->assertSame(['wood' => 0, 'clay' => 0, 'iron' => 0, 'crop' => 0], $result['loot']['resources']);
        $this->assertSame(['wood' => 0, 'clay' => 0, 'iron' => 0, 'crop' => 0], $result['loot']['defenderRemaining']);
        $this->assertSame(['total' => 0, 'used' => 0, 'remaining' => 0], $result['loot']['capacity']);
    }

    public function test_simulate_battle_handles_empty_armies_as_draw(): void
    {
        $service = new BattleService;

        $result = $service->simulateBattle(['units' => []], ['units' => []]);

        $this->assertSame('draw', $result['winner']);
        $this->assertSame(0.0, $result['strength']['attacker']);
        $this->assertSame(0.0, $result['strength']['defender']);
        $this->assertSame([], $result['casualties']['attacker']['losses']);
        $this->assertSame([], $result['casualties']['attacker']['survivors']);
        $this->assertSame([], $result['casualties']['defender']['losses']);
        $this->assertSame([], $result['casualties']['defender']['survivors']);
        $this->assertSame(['wood' => 0, 'clay' => 0, 'iron' => 0, 'crop' => 0], $result['loot']['resources']);
        $this->assertSame(['wood' => 0, 'clay' => 0, 'iron' => 0, 'crop' => 0], $result['loot']['defenderRemaining']);
    }
}
