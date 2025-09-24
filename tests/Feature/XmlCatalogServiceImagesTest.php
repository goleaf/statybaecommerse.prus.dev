<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\XmlCatalogService;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('imports images from data uri and http url when download_images=true', function (): void {
    Storage::fake('public');

    $xml = <<<'XML'
<catalog>
  <categories>
    <category>
      <slug>pics</slug>
      <translations>
        <translation locale="lt"><name>Paveikslai</name></translation>
      </translations>
    </category>
  </categories>
  <products>
    <product>
      <sku>PIC-1</sku>
      <translations>
        <translation locale="lt"><name>Nuotrauka</name></translation>
      </translations>
      <categories>
        <category_slug>pics</category_slug>
      </categories>
      <images>
        <image src="data:image/png;base64,iVBORw0KGgo=" alt="A" />
      </images>
    </product>
  </products>
</catalog>
XML;

    $path = base_path('storage/images-fixture.xml');
    file_put_contents($path, $xml);
    $service = app(XmlCatalogService::class);
    $res = $service->import($path, ['only' => 'all', 'download_images' => true]);

    expect(Product::where('sku', 'PIC-1')->exists())->toBeTrue();
});
