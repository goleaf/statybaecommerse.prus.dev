<?php declare(strict_types=1);

use App\Models\DocumentTemplate;
use App\Models\Document;
use App\Models\Product;
use App\Models\User;
use App\Services\DocumentService;

it('can create document template', function () {
    $template = DocumentTemplate::factory()->create([
        'name' => 'Product Invoice',
        'type' => 'invoice',
        'content' => '<h1>Invoice for $PRODUCT_NAME</h1><p>Price: $PRODUCT_PRICE</p>',
        'variables' => ['$PRODUCT_NAME', '$PRODUCT_PRICE'],
        'is_active' => true,
    ]);
    
    expect($template)->toBeInstanceOf(DocumentTemplate::class);
    expect($template->name)->toBe('Product Invoice');
    expect($template->is_active)->toBeTrue();
});

it('can generate document from template', function () {
    $template = DocumentTemplate::factory()->create([
        'name' => 'Product Document',
        'content' => '<h1>Product: $PRODUCT_NAME</h1><p>SKU: $PRODUCT_SKU</p>',
        'variables' => ['$PRODUCT_NAME', '$PRODUCT_SKU'],
        'is_active' => true,
    ]);
    
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
    ]);
    
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $service = app(DocumentService::class);
    $document = $service->generateDocument(
        $template,
        $product,
        ['$PRODUCT_NAME' => $product->name, '$PRODUCT_SKU' => $product->sku],
        'Test Document'
    );
    
    expect($document)->toBeInstanceOf(Document::class);
    expect($document->title)->toBe('Test Document');
    expect($document->content)->toContain('Test Product');
    expect($document->content)->toContain('TEST-001');
});

it('can process template variables', function () {
    $template = DocumentTemplate::factory()->create([
        'content' => 'Hello $CUSTOMER_NAME, your order $ORDER_NUMBER is ready.',
    ]);
    
    $service = app(DocumentService::class);
    $processed = $service->processTemplate(
        $template->content,
        ['$CUSTOMER_NAME' => 'John Doe', '$ORDER_NUMBER' => 'ORD-001']
    );
    
    expect($processed)->toBe('Hello John Doe, your order ORD-001 is ready.');
});

it('document belongs to template', function () {
    $template = DocumentTemplate::factory()->create();
    $document = Document::factory()->create(['document_template_id' => $template->id]);
    
    expect($document->template)->toBeInstanceOf(DocumentTemplate::class);
    expect($document->template->id)->toBe($template->id);
});

it('document has morphed relationship', function () {
    $product = Product::factory()->create();
    $document = Document::factory()->create([
        'documentable_type' => Product::class,
        'documentable_id' => $product->id,
    ]);
    
    expect($document->documentable)->toBeInstanceOf(Product::class);
    expect($document->documentable->id)->toBe($product->id);
});
