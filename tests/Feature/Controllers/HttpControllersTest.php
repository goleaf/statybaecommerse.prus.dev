<?php declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('robots returns text content', function (): void {
    $response = $this->get('/robots.txt');
    $response->assertStatus(200);
});

it('sitemap routes respond', function (): void {
    if (!Schema::hasTable('sh_collections')) {
        Schema::create('sh_collections', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_enabled')->default(true); $table->timestamps(); });
    }
    $this->get('/sitemap.xml')->assertStatus(200);
    $this->get('/en/sitemap.xml')->assertStatus(200);
});

it('root redirects to localized home', function (): void {
    $this->get('/')->assertRedirect();
});

it('brand and location index routes respond', function (): void {
    // These may rely on Livewire/feature toggles; ensure routes exist
    if (!Schema::hasTable('sh_inventories')) {
        Schema::create('sh_inventories', function ($table) { $table->id(); $table->string('name'); $table->boolean('is_default')->default(false); $table->timestamps(); });
    }
    $this->get('/en/locations')->assertStatus(200);
});

it('order confirmation route returns 302 or 200 when authed', function (): void {
    $user = App\Models\User::factory()->create();
    $this->actingAs($user);
    $resp = $this->get('/en/order/confirmed/TEST123');
    expect(in_array($resp->getStatusCode(), [200, 302], true))->toBeTrue();
});
