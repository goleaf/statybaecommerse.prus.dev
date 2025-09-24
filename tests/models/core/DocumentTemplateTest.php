<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DocumentTemplate Model', function () {
    it('can be created with valid data', function () {
        $template = DocumentTemplate::create([
            'name' => 'Invoice Template',
            'description' => 'Standard invoice template',
            'content' => '<h1>Invoice #{{ORDER_NUMBER}}</h1>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'],
            'type' => 'invoice',
            'category' => 'sales',
            'settings' => ['page_size' => 'A4', 'orientation' => 'portrait'],
            'is_active' => true,
        ]);

        expect($template)
            ->toBeInstanceOf(DocumentTemplate::class)
            ->and($template->name)
            ->toBe('Invoice Template')
            ->and($template->slug)
            ->toBe('invoice-template')
            ->and($template->type)
            ->toBe('invoice')
            ->and($template->is_active)
            ->toBeTrue();
    });

    it('has correct fillable attributes', function () {
        $template = new DocumentTemplate;

        expect($template->getFillable())->toBe([
            'name',
            'slug',
            'description',
            'content',
            'variables',
            'type',
            'category',
            'settings',
            'is_active',
        ]);
    });

    it('automatically generates slug from name on creation', function () {
        $template = DocumentTemplate::create([
            'name' => 'My Custom Template',
            'content' => '<h1>Test</h1>',
            'type' => 'receipt',
            'category' => 'sales',
        ]);

        expect($template->slug)->toBe('my-custom-template');
    });

    it('uses provided slug if given', function () {
        $template = DocumentTemplate::create([
            'name' => 'My Custom Template',
            'slug' => 'custom-slug',
            'content' => '<h1>Test</h1>',
            'type' => 'receipt',
            'category' => 'sales',
        ]);

        expect($template->slug)->toBe('custom-slug');
    });

    it('updates slug when name changes and slug is empty', function () {
        $template = DocumentTemplate::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        $template->update(['name' => 'Updated Name', 'slug' => '']);

        expect($template->fresh()->slug)->toBe('updated-name');
    });

    it('does not update slug when name changes but slug exists', function () {
        $template = DocumentTemplate::factory()->create([
            'name' => 'Original Name',
            'slug' => 'existing-slug',
        ]);

        $template->update(['name' => 'Updated Name']);

        expect($template->fresh()->slug)->toBe('existing-slug');
    });

    it('casts variables to array', function () {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'TOTAL'],
        ]);

        expect($template->variables)
            ->toBeArray()
            ->and($template->variables)
            ->toContain('ORDER_NUMBER')
            ->and($template->variables)
            ->toContain('CUSTOMER_NAME');
    });

    it('casts settings to array', function () {
        $template = DocumentTemplate::factory()->create([
            'settings' => ['page_size' => 'A4', 'margins' => '10mm'],
        ]);

        expect($template->settings)
            ->toBeArray()
            ->and($template->settings['page_size'])
            ->toBe('A4')
            ->and($template->settings['margins'])
            ->toBe('10mm');
    });

    it('casts is_active to boolean', function () {
        $activeTemplate = DocumentTemplate::factory()->create(['is_active' => 1]);
        $inactiveTemplate = DocumentTemplate::factory()->create(['is_active' => 0]);

        expect($activeTemplate->is_active)
            ->toBeTrue()
            ->and($inactiveTemplate->is_active)
            ->toBeFalse();
    });

    it('has many documents', function () {
        $template = DocumentTemplate::factory()->create();
        $documents = Document::factory()->count(3)->create([
            'document_template_id' => $template->id,
        ]);

        expect($template->documents)
            ->toHaveCount(3)
            ->and($template->documents->first())
            ->toBeInstanceOf(Document::class);
    });

    it('can scope active templates', function () {
        DocumentTemplate::factory()->create(['is_active' => true]);
        DocumentTemplate::factory()->create(['is_active' => false]);
        DocumentTemplate::factory()->create(['is_active' => true]);

        $activeTemplates = DocumentTemplate::active()->get();

        expect($activeTemplates)->toHaveCount(2);
        $activeTemplates->each(function ($template) {
            expect($template->is_active)->toBeTrue();
        });
    });

    it('can scope by type', function () {
        DocumentTemplate::factory()->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'receipt']);
        DocumentTemplate::factory()->create(['type' => 'invoice']);

        $invoiceTemplates = DocumentTemplate::ofType('invoice')->get();
        $receiptTemplates = DocumentTemplate::ofType('receipt')->get();

        expect($invoiceTemplates)
            ->toHaveCount(2)
            ->and($receiptTemplates)
            ->toHaveCount(1);
    });

    it('can scope by category', function () {
        DocumentTemplate::factory()->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'marketing']);
        DocumentTemplate::factory()->create(['category' => 'sales']);

        $salesTemplates = DocumentTemplate::ofCategory('sales')->get();
        $marketingTemplates = DocumentTemplate::ofCategory('marketing')->get();

        expect($salesTemplates)
            ->toHaveCount(2)
            ->and($marketingTemplates)
            ->toHaveCount(1);
    });

    it('can get available variables', function () {
        $variables = ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'];
        $template = DocumentTemplate::factory()->create([
            'variables' => $variables,
        ]);

        expect($template->getAvailableVariables())->toBe($variables);
    });

    it('returns empty array when no variables', function () {
        $template = DocumentTemplate::factory()->create([
            'variables' => null,
        ]);

        expect($template->getAvailableVariables())->toBe([]);
    });

    it('can check if template has variable', function () {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
        ]);

        expect($template->hasVariable('ORDER_NUMBER'))
            ->toBeTrue()
            ->and($template->hasVariable('CUSTOMER_NAME'))
            ->toBeTrue()
            ->and($template->hasVariable('NONEXISTENT'))
            ->toBeFalse();
    });

    it('can get template settings', function () {
        $settings = ['page_size' => 'A4', 'orientation' => 'portrait'];
        $template = DocumentTemplate::factory()->create([
            'settings' => $settings,
        ]);

        expect($template->getSettings())->toBe($settings);
    });

    it('can get specific setting with default', function () {
        $template = DocumentTemplate::factory()->create([
            'settings' => ['page_size' => 'A4'],
        ]);

        expect($template->getSetting('page_size'))
            ->toBe('A4')
            ->and($template->getSetting('orientation', 'portrait'))
            ->toBe('portrait')
            ->and($template->getSetting('nonexistent'))
            ->toBeNull();
    });

    it('can render content with variables', function () {
        $template = DocumentTemplate::factory()->create([
            'content' => 'Hello {{CUSTOMER_NAME}}, your order {{ORDER_NUMBER}} total is {{ORDER_TOTAL}}',
        ]);

        $variables = [
            'CUSTOMER_NAME' => 'John Doe',
            'ORDER_NUMBER' => '12345',
            'ORDER_TOTAL' => '$100.00',
        ];

        $rendered = $template->render($variables);

        expect($rendered)->toBe('Hello John Doe, your order 12345 total is $100.00');
    });

    it('validates required fields', function () {
        expect(fn () => DocumentTemplate::create([]))
            ->toThrow(Illuminate\Database\QueryException::class);
    });

    it('ensures unique slug', function () {
        DocumentTemplate::factory()->create(['slug' => 'unique-template']);

        expect(fn () => DocumentTemplate::create([
            'name' => 'Another Template',
            'slug' => 'unique-template',
            'content' => '<h1>Test</h1>',
            'type' => 'invoice',
            'category' => 'sales',
        ]))->toThrow(Illuminate\Database\QueryException::class);
    });
});
