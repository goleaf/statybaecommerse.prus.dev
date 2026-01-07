<?php

declare(strict_types=1);

use App\Enums\LegalDocumentType;

it('provides correct labels for all document types', function (): void {
    expect(LegalDocumentType::PRIVACY_POLICY->label())->toBe(__('legal.types.privacy_policy'));
    expect(LegalDocumentType::TERMS_OF_USE->label())->toBe(__('legal.types.terms_of_use'));
    expect(LegalDocumentType::REFUND_POLICY->label())->toBe(__('legal.types.refund_policy'));
});

it('provides correct descriptions for all document types', function (): void {
    expect(LegalDocumentType::PRIVACY_POLICY->description())->toBe(__('legal.descriptions.privacy_policy'));
    expect(LegalDocumentType::TERMS_OF_USE->description())->toBe(__('legal.descriptions.terms_of_use'));
    expect(LegalDocumentType::REFUND_POLICY->description())->toBe(__('legal.descriptions.refund_policy'));
});

it('identifies required document types correctly', function (): void {
    expect(LegalDocumentType::PRIVACY_POLICY->isRequired())->toBeTrue();
    expect(LegalDocumentType::TERMS_OF_USE->isRequired())->toBeTrue();
    expect(LegalDocumentType::REFUND_POLICY->isRequired())->toBeFalse();
    expect(LegalDocumentType::SHIPPING_POLICY->isRequired())->toBeFalse();
});

it('returns correct required types', function (): void {
    $requiredTypes = LegalDocumentType::getRequiredTypes();

    expect($requiredTypes)->toHaveCount(2);
    expect($requiredTypes)->toContain(LegalDocumentType::PRIVACY_POLICY);
    expect($requiredTypes)->toContain(LegalDocumentType::TERMS_OF_USE);
});

it('provides options for forms', function (): void {
    $options = LegalDocumentType::getOptions();

    expect($options)->toBeArray();
    expect($options)->toHaveKey('privacy_policy');
    expect($options)->toHaveKey('terms_of_use');
    expect($options['privacy_policy'])->toBe(__('legal.types.privacy_policy'));
});

it('converts to array with all metadata', function (): void {
    $array = LegalDocumentType::PRIVACY_POLICY->toArray();

    expect($array)->toHaveKeys(['value', 'label', 'description', 'icon', 'color', 'priority', 'is_required']);
    expect($array['value'])->toBe('privacy_policy');
    expect($array['is_required'])->toBeTrue();
});

it('orders cases by priority', function (): void {
    $ordered = LegalDocumentType::ordered();

    expect($ordered->first())->toBe(LegalDocumentType::PRIVACY_POLICY);
    expect($ordered->get(1))->toBe(LegalDocumentType::TERMS_OF_USE);
});

it('finds enum case by label', function (): void {
    $found = LegalDocumentType::fromLabel(__('legal.types.privacy_policy'));

    expect($found)->toBe(LegalDocumentType::PRIVACY_POLICY);

    $notFound = LegalDocumentType::fromLabel('Non-existent Label');
    expect($notFound)->toBeNull();
});
