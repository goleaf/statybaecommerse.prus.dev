<?php

declare(strict_types=1);

namespace Tests\Feature\DataTransfer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UserProfilesDataTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_and_import_round_trip_with_json(): void
    {
        Carbon::setTestNow('2024-01-20 12:00:00');
        Storage::fake('local');

        User::withoutEvents(function (): void {
            User::factory()->create([
                'name'              => 'Jonas Ivanauskas',
                'email'             => 'jonas.ivanauskas@example.test',
                'preferred_locale'  => 'lt',
                'is_active'         => true,
                'email_verified_at' => Carbon::parse('2024-01-10 09:00:00'),
            ]);

            User::factory()->create([
                'name'              => 'Emily Novak',
                'email'             => 'emily.novak@example.test',
                'preferred_locale'  => 'en',
                'is_active'         => true,
                'email_verified_at' => Carbon::parse('2024-01-12 14:30:00'),
            ]);
        });

        $this->artisan('export:contract', [
            'contract'   => 'user_profiles',
            '--format'   => 'json',
            '--filename' => 'user-profiles.json',
        ])->assertExitCode(0);

        $disk = Storage::disk('local');
        $disk->assertExists('exports/user-profiles.json');

        $payload = json_decode($disk->get('exports/user-profiles.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame([
            [
                'email'             => 'emily.novak@example.test',
                'name'              => 'Emily Novak',
                'preferred_locale'  => 'en',
                'is_active'         => true,
                'email_verified_at' => '2024-01-12T14:30:00+00:00',
            ],
            [
                'email'             => 'jonas.ivanauskas@example.test',
                'name'              => 'Jonas Ivanauskas',
                'preferred_locale'  => 'lt',
                'is_active'         => true,
                'email_verified_at' => '2024-01-10T09:00:00+00:00',
            ],
        ], $payload);

        User::withoutGlobalScopes()->get()->each(static function (User $user): void {
            $user->forceDelete();
        });

        $disk->put('imports/user-profiles.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('import:contract', [
            'contract' => 'user_profiles',
            'file'     => 'user-profiles.json',
        ])->assertExitCode(0);

        $users = User::withoutGlobalScopes()->orderBy('email')->get(['email', 'name', 'preferred_locale', 'is_active']);
        self::assertCount(2, $users);
        self::assertSame('emily.novak@example.test', $users->first()->email);
        self::assertTrue($users->first()->is_active);
        self::assertSame('en', $users->first()->preferred_locale);
        self::assertSame('Emily Novak', $users->first()->name);
        self::assertSame('jonas.ivanauskas@example.test', $users->last()->email);

        Carbon::setTestNow();
    }

    public function test_import_from_csv_fixture(): void
    {
        Carbon::setTestNow('2024-02-01 08:00:00');
        Storage::fake('local');

        $fixture = file_get_contents(base_path('docs/contracts/user_profiles.sample.csv'));
        self::assertNotFalse($fixture);

        Storage::disk('local')->put('imports/user_profiles.sample.csv', $fixture);

        $this->artisan('import:contract', [
            'contract' => 'user_profiles',
            'file'     => 'user_profiles.sample.csv',
        ])->assertExitCode(0);

        $users = User::withoutGlobalScopes()->orderBy('email')->get(['email', 'name', 'preferred_locale', 'is_active']);

        self::assertCount(2, $users);
        self::assertSame('emily.novak@example.test', $users->first()->email);
        self::assertTrue($users->first()->is_active);
        self::assertSame('jonas.ivanauskas@example.test', $users->last()->email);

        Carbon::setTestNow();
    }
}
