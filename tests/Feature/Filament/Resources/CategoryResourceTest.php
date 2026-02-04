<?php

declare(strict_types=1);

use App\Filament\Resources\CategoryResource;
use App\Models\AdminUser;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = AdminUser::factory()->create();
    $this->actingAs($this->user, 'admin');

    $this->category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_visible' => true,
        'description' => 'Test Description',
    ]);
});

it('can render category resource index page', function () {
    $response = $this->get(CategoryResource::getUrl('index'));

    $response->assertOk();
});

it('can list categories in table', function () {
    $categories = Category::factory()->count(3)->create();

    Livewire::test(CategoryResource\Pages\ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

it('can render category resource create page', function () {
    $response = $this->get(CategoryResource::getUrl('create'));

    $response->assertOk();
});

it('can create category', function () {
    $newCategoryData = [
        'name' => 'New Test Category',
        'slug' => 'new-test-category',
        'description' => 'New Test Description',
        'is_active' => true,
    ];

    Livewire::test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm($newCategoryData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Test Category',
        'slug' => 'new-test-category',
    ]);
});

it('validates required fields when creating category', function () {
    Livewire::test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'slug' => 'required',
        ]);
});

it('can render category resource edit page', function () {
    $response = $this->get(CategoryResource::getUrl('edit', ['record' => $this->category]));

    $response->assertOk();
});

it('can retrieve category data for editing', function () {
    Livewire::test(CategoryResource\Pages\EditCategory::class, [
        'record' => $this->category->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $this->category->name,
            'slug' => $this->category->slug,
            'description' => $this->category->description,
            'is_active' => $this->category->is_active,
        ]);
});

it('can save category', function () {
    $updatedData = [
        'name' => 'Updated Category Name',
        'slug' => 'updated-category-name',
        'description' => 'Updated Description',
        'is_active' => false,
    ];

    Livewire::test(CategoryResource\Pages\EditCategory::class, [
        'record' => $this->category->getRouteKey(),
    ])
        ->fillForm($updatedData)
        ->call('save')
        ->assertHasNoFormErrors();

    $this->category->refresh();

    expect($this->category->name)->toBe('Updated Category Name');
    expect($this->category->slug)->toBe('updated-category-name');
    expect($this->category->is_active)->toBeFalse();
});
