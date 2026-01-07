<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\TransformationResult;

describe('VersionCompatibilityService Simple Tests', function () {
    it('can create transformation result objects', function () {
        $result = new TransformationResult(
            'transformed content',
            true,
            ['SomeStrategy']
        );

        expect($result->getContent())->toBe('transformed content');
        expect($result->wasTransformed())->toBeTrue();
        expect($result->getAppliedTransformations())->toBe(['SomeStrategy']);
        expect($result->hasError())->toBeFalse();
        expect($result->isSuccessful())->toBeTrue();
    });

    it('handles error results correctly', function () {
        $result = new TransformationResult(
            '',
            false,
            [],
            'Some error occurred'
        );

        expect($result->hasError())->toBeTrue();
        expect($result->getError())->toBe('Some error occurred');
        expect($result->isSuccessful())->toBeFalse();
        expect($result->wasTransformed())->toBeFalse();
    });
});
