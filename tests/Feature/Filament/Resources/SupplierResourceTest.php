<?php

declare(strict_types=1);

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\AdminUser;
use App\Models\Supplier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');

    if (! Schema::hasTable('suppliers')) {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company_code');
            $table->string('code')->unique();
            $table->string('vat_code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    if (! Schema::hasTable('product_supplier') && Schema::hasTable('products')) {
        Schema::create('product_supplier', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'supplier_id']);
        });
    }

    if (Schema::hasTable('product_supplier')) {
        DB::table('product_supplier')->delete();
    }

    if (Schema::hasTable('suppliers')) {
        DB::table('suppliers')->delete();
    }
});

it('can render supplier resource index page', function () {
    $response = $this->get(SupplierResource::getUrl('index'));

    $response->assertOk();
});

it('can list suppliers in table', function () {
    $suppliers = Supplier::factory()->count(3)->create();

    Livewire::test(ListSuppliers::class)
        ->assertCanSeeTableRecords($suppliers);
});

it('shows company and system code columns in suppliers table', function () {
    Livewire::test(ListSuppliers::class)
        ->assertTableColumnExists('company_code')
        ->assertTableColumnExists('code');
});

it('can create supplier and auto-generate system code slug when omitted', function () {
    Livewire::test(CreateSupplier::class)
        ->fillForm([
            'name'          => 'New Supplier',
            'company_code'  => '302998771',
            'contact_email' => 'info@egisstatyba.lt',
            'contact_phone' => '+15550000001',
            'is_enabled'    => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $supplier = Supplier::query()->where('name', 'New Supplier')->first();

    expect($supplier)->not->toBeNull();
    expect($supplier?->code)->toBe('new-supplier');
    expect($supplier?->company_code)->toBe('302998771');
});

it('validates company code as required', function () {
    Livewire::test(CreateSupplier::class)
        ->fillForm([
            'name' => 'No Company Code Supplier',
        ])
        ->call('create')
        ->assertHasFormErrors(['company_code' => 'required']);
});

it('normalizes system code to slug', function () {
    Livewire::test(CreateSupplier::class)
        ->fillForm([
            'name'         => 'Slug Supplier',
            'company_code' => '302998772',
            'code'         => 'My Custom SYSTEM Code',
            'is_enabled'   => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $supplier = Supplier::query()->where('company_code', '302998772')->first();

    expect($supplier)->not->toBeNull();
    expect($supplier?->code)->toBe('my-custom-system-code');
});

it('can edit supplier', function () {
    $supplier = Supplier::factory()->create([
        'name'         => 'Original Supplier',
        'company_code' => '302998773',
    ]);

    Livewire::test(EditSupplier::class, [
        'record' => $supplier->getRouteKey(),
    ])
        ->fillForm([
            'name'           => 'Updated Supplier',
            'company_code'   => '302998774',
            'code'           => 'Updated Supplier System Code',
            'contact_person' => 'Vardenis Pavardenis',
            'is_enabled'     => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('suppliers', [
        'id'             => $supplier->id,
        'name'           => 'Updated Supplier',
        'company_code'   => '302998774',
        'code'           => 'updated-supplier-system-code',
        'contact_person' => 'Vardenis Pavardenis',
        'is_enabled'     => false,
    ]);
});

it('can delete supplier', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(ListSuppliers::class)
        ->callTableAction('delete', $supplier)
        ->assertHasNoTableActionErrors();

    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});
