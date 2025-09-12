<?php declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->artisan('migrate', ['--force' => true]);
    if (!Schema::hasTable('locations')) {
        Schema::create('locations', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }
});

it('lists locations', function (): void {
    DB::table('locations')->insert(['name' => 'B', 'is_default' => false]);
    DB::table('locations')->insert(['name' => 'A', 'is_default' => true]);

    $this->get('/en/locations')->assertOk();
});

it('shows a location', function (): void {
    $id = DB::table('locations')->insertGetId([
        'name' => 'Main', 
        'code' => 'main-location',
        'is_default' => true
    ]);

    $this->get('/en/locations/main-location')->assertOk();
});
