<?php declare(strict_types=1);

use App\Models\Location;
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
    $location = Location::factory()->create([
        'name' => 'Main', 
        'is_default' => true,
        'is_enabled' => true
    ]);

    // Debug: Check if location exists
    $this->assertDatabaseHas('locations', ['id' => $location->id, 'is_enabled' => true]);
    
    $response = $this->get('/en/locations/' . $location->id);
    
    // Debug: Check response status
    if ($response->status() !== 200) {
        dump('Response status: ' . $response->status());
        dump('Response content: ' . $response->content());
    }
    
    $response->assertOk();
});
