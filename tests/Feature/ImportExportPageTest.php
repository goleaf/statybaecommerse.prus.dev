<?php

declare(strict_types=1);

use App\Filament\Pages\DataImportExport;
use App\Models\AdminUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders the data import export page', function (): void {
    $user = AdminUser::factory()->create();
    $this->actingAs($user);
    $this->get(DataImportExport::getUrl())
        ->assertOk();
});

it('imports via xml provider from uploaded file', function (): void {
    Storage::fake('public');
    $user = AdminUser::factory()->create();
    $this->actingAs($user);

    $xml = <<<'XML'
<catalog>
  <categories>
    <category>
      <slug>test-cat</slug>
      <translations>
        <translation locale="lt"><name>Kategorija</name></translation>
      </translations>
    </category>
  </categories>
  <products>
    <product>
      <sku>SKU-1</sku>
      <translations>
        <translation locale="lt"><name>Produktas</name></translation>
      </translations>
      <categories>
        <category_slug>test-cat</category_slug>
      </categories>
    </product>
  </products>
</catalog>
XML;
    $file = UploadedFile::fake()->createWithContent('catalog.xml', $xml);
    $path = $file->store('uploads', 'public');

    Livewire::test(DataImportExport::class)
        ->set('provider', 'xml')
        ->set('only', 'all')
        ->set('downloadImages', false)
        ->set('file', $path)
        ->callAction('import')
        ->assertSuccessful();
});
