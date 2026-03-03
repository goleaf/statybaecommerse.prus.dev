<?php

declare(strict_types=1);

use App\Models\Legal;
use Database\Seeders\LegalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers legal seeder in the standard seeder profile', function (): void {
    $standardSeeders = config('seeds.standard_seeders', []);

    expect($standardSeeders)->toContain(LegalSeeder::class);
});

it('seeds published legal pages for frontend routes and remains idempotent', function (): void {
    $this->seed(LegalSeeder::class);
    $this->seed(LegalSeeder::class);

    $keyedDocuments = Legal::withoutGlobalScopes()
        ->whereIn('key', [
            'privacy-policy',
            'terms-of-use',
            'return-policy',
            'shipping-policy',
            'cookie-policy',
        ])
        ->get()
        ->keyBy('key');

    expect($keyedDocuments)->toHaveCount(5);

    foreach ($keyedDocuments as $document) {
        expect($document->is_enabled)->toBeTrue();
        expect($document->published_at)->not->toBeNull();
    }
});
