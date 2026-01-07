<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Strategies\FormSchemaTransformationStrategy;

describe('FormSchemaTransformationStrategy', function () {
    beforeEach(function () {
        $this->strategy = new FormSchemaTransformationStrategy;
    });

    it('can handle form schema content', function () {
        $content = 'use Filament\Schemas\Schema;';

        expect($this->strategy->canHandle($content))->toBeTrue();
    });

    it('cannot handle non-form content', function () {
        $content = 'class SomeOtherClass {}';

        expect($this->strategy->canHandle($content))->toBeFalse();
    });

    it('transforms schema import to form import', function () {
        $content = 'use Filament\Schemas\Schema;';

        $result = $this->strategy->transform($content);

        expect($result->wasTransformed())->toBeTrue()
            ->and($result->getContent())->toBe('use Filament\Forms\Form;');
    });

    it('transforms schema usage to form usage', function () {
        $content = 'return $schema->schema([';

        $result = $this->strategy->transform($content);

        expect($result->wasTransformed())->toBeTrue()
            ->and($result->getContent())->toBe('return $form->schema([');
    });

    it('returns correct strategy name', function () {
        expect($this->strategy->getName())->toBe('Form Schema Transformation');
    });

    it('does not transform when no changes needed', function () {
        $content = 'class SomeOtherClass {}';

        $result = $this->strategy->transform($content);

        expect($result->wasTransformed())->toBeFalse()
            ->and($result->getContent())->toBe($content);
    });
});
