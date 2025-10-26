<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use App\Support\Storage\SecureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

// Ensure the whitelist of assignable attributes stays aligned with the migration surface.
it('exposes expected fillable attributes', function (): void {
    $document = new Document;

    expect($document->getFillable())
        ->toEqualCanonicalizing([
            'document_template_id',
            'title',
            'name',
            'type',
            'version',
            'content',
            'variables',
            'status',
            'format',
            'file_path',
            'file_size',
            'mime_type',
            'is_public',
            'is_downloadable',
            'access_password',
            'documentable_type',
            'documentable_id',
            'created_by',
            'updated_by',
            'generated_at',
            'expires_at',
            'description',
            'notes',
        ]);
});

// Confirm casting and helper accessors transform data into predictable runtime shapes.
it('casts attributes to their expected runtime types', function (): void {
    $document = Document::factory()->create([
        'is_public'       => true,
        'is_downloadable' => false,
        'file_size'       => 42_000,
        'generated_at'    => now()->subDay(),
        'expires_at'      => now()->addDays(10),
    ]);

    $document->refresh();

    expect($document->is_public)->toBeTrue()
        ->and($document->is_downloadable)->toBeFalse()
        ->and($document->file_size)->toBeInt()->toBe(42_000)
        ->and($document->generated_at)->toBeInstanceOf(Carbon::class)
        ->and($document->expires_at)->toBeInstanceOf(Carbon::class);

    // Helper proxies should mirror the underlying casts.
    expect($document->isPublic())->toBeTrue()
        ->and($document->isDownloadable())->toBeFalse();
});

// Validate relationships so downstream API layers can eager load confidently.
it('resolves related template, users, audit logs, and polymorphic parents', function (): void {
    $template = DocumentTemplate::factory()->create();
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $order = Order::factory()->create();

    $document = Document::factory()->create([
        'document_template_id' => $template->getKey(),
        'documentable_type'    => Order::class,
        'documentable_id'      => $order->getKey(),
        'created_by'           => $creator->getKey(),
        'updated_by'           => $updater->getKey(),
    ]);

    AuditLog::query()->create([
        'entity_type' => Document::class,
        'entity_id'   => (string) $document->getKey(),
        'action'      => 'created',
        'user_id'     => $creator->getKey(),
    ]);

    $document->unsetRelation('auditLogs');

    expect($document->template)->toBeInstanceOf(DocumentTemplate::class)
        ->and($document->template->is($template))->toBeTrue()
        ->and($document->creator)->toBeInstanceOf(User::class)
        ->and($document->creator->is($creator))->toBeTrue()
        ->and($document->updater)->toBeInstanceOf(User::class)
        ->and($document->updater->is($updater))->toBeTrue()
        ->and($document->documentable)->toBeInstanceOf(Order::class)
        ->and($document->documentable->is($order))->toBeTrue()
        ->and($document->auditLogs()->count())->toBe(1);
});

// Guard the behaviour around optional variables storage.
it('returns the declared variables or falls back to an empty array', function (): void {
    $variables = ['customer' => 'Jane Doe', 'order_total' => 99.99];
    $withVariables = Document::factory()->create(['variables' => $variables]);
    $withoutVariables = Document::factory()->create(['variables' => null]);

    expect($withVariables->getVariablesUsed())->toBe($variables)
        ->and($withoutVariables->getVariablesUsed())->toBe([]);
});

// Ensure secure storage URLs continue to include signed route parameters.
it('builds secure download urls when a file is present', function (): void {
    $document = Document::factory()->create(['file_path' => 'documents/test.pdf']);
    $expectedUrl = 'https://example.test/signed-url';

    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(function (string $routeName, $expiration, array $parameters): bool {
            return $routeName === 'media.secure-download'
                && $expiration instanceof \DateTimeInterface
                && ($parameters['encodedPath'] ?? null) === SecureStorage::encodePath('documents/test.pdf')
                && ($parameters['download'] ?? null) === '1';
        })
        ->andReturn($expectedUrl);

    expect($document->getFileUrl())->toBe($expectedUrl);
});

// Validate the guard clause for missing files to avoid unnecessary storage calls.
it('returns null for download url when no file path exists', function (): void {
    $document = Document::factory()->create(['file_path' => null]);

    URL::spy();

    expect($document->getFileUrl())->toBeNull();

    URL::shouldNotHaveReceived('temporarySignedRoute');
});

// Keep alphabetical ordering deterministic for admin dropdowns and exports.
it('orders documents by name with title as a fallback', function (): void {
    $alpha = Document::factory()->create(['name' => 'Alpha Summary', 'title' => 'Alpha Title']);
    $noName = Document::factory()->create(['name' => null, 'title' => 'Bravo Overview']);
    $zulu = Document::factory()->create(['name' => 'Zulu Contract', 'title' => 'Zulu Title']);

    $orderedIds = Document::query()->orderedByName()->pluck('id')->all();

    expect($orderedIds)->toBe([$alpha->getKey(), $noName->getKey(), $zulu->getKey()]);
});

// Confirm scoped queries continue to behave as convenience filters.
it('filters by status, format, and owning model', function (): void {
    $order = Order::factory()->create();
    $otherOrder = Order::factory()->create();

    $matching = Document::factory()->create([
        'status'            => Document::STATUS_PUBLISHED,
        'format'            => Document::FORMAT_PDF,
        'documentable_type' => Order::class,
        'documentable_id'   => $order->getKey(),
    ]);

    Document::factory()->create([
        'status'            => Document::STATUS_DRAFT,
        'format'            => Document::FORMAT_HTML,
        'documentable_type' => Order::class,
        'documentable_id'   => $otherOrder->getKey(),
    ]);

    $statusMatches = Document::query()->byStatus(Document::STATUS_PUBLISHED)->get();
    $formatMatches = Document::query()->byFormat(Document::FORMAT_PDF)->get();
    $orderedMatches = Document::query()->forModel($order)->get();

    expect($statusMatches->pluck('id'))->toContain($matching->getKey())
        ->and($formatMatches->pluck('id'))->toContain($matching->getKey())
        ->and($orderedMatches->pluck('id'))->toBe([$matching->getKey()]);
});

// Regression proofing for helper methods that wrap status and format constants.
it('exposes boolean helpers aligned with status and format constants', function (): void {
    $draft = Document::factory()->create(['status' => Document::STATUS_DRAFT, 'format' => Document::FORMAT_HTML]);
    $generated = Document::factory()->create(['status' => Document::STATUS_GENERATED, 'format' => Document::FORMAT_PDF]);
    $published = Document::factory()->create(['status' => Document::STATUS_PUBLISHED, 'format' => Document::FORMAT_PDF]);
    $archived = Document::factory()->create(['status' => Document::STATUS_ARCHIVED, 'format' => Document::FORMAT_DOCX]);

    expect($draft->isDraft())->toBeTrue()
        ->and($draft->isPdf())->toBeFalse()
        ->and($generated->isGenerated())->toBeTrue()
        ->and($generated->isPdf())->toBeTrue()
        ->and($published->isPublished())->toBeTrue()
        ->and($published->isGenerated())->toBeTrue()
        ->and($archived->isArchived())->toBeTrue();
});
