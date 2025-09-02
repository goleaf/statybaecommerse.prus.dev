<?php declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->artisan('migrate', ['--force' => true]);
    if (!Schema::hasTable('sh_inventories')) {
        Schema::create('sh_inventories', function ($table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }
});

it('lists locations', function (): void {
    DB::table('sh_inventories')->insert(['name' => 'B', 'is_default' => false]);
    DB::table('sh_inventories')->insert(['name' => 'A', 'is_default' => true]);

    $this->get('/en/locations')->assertOk();
});

it('shows a location', function (): void {
    $id = DB::table('sh_inventories')->insertGetId(['name' => 'Main', 'is_default' => true]);

    $this->get('/en/locations/' . $id)->assertOk();
});
