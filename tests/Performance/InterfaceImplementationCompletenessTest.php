<?php

declare(strict_types=1);

use App\Contracts\TranslatableRecord;
use App\Models\Brand;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

/**
 * **Feature: performance-update, Property 2: Interface Implementation Completeness**
 * **Validates: Requirements 1.2**
 *
 * Property-based test to ensure all models implementing TranslatableRecord have complete interface compliance.
 */
test('all TranslatableRecord implementations are complete and functional', function (): void {
    $translatableModels = [
        Product::class,
        Brand::class,
        Collection::class,
        ProductVariant::class,
    ];

    foreach ($translatableModels as $modelClass) {
        // Property: Model implements the interface
        $model = new $modelClass;
        expect($model)->toBeInstanceOf(TranslatableRecord::class,
            "Model {$modelClass} must implement TranslatableRecord interface"
        );

        // Property: translations() method exists and is callable
        expect(method_exists($model, 'translations'))->toBeTrue(
            "Model {$modelClass} must have translations() method"
        );

        expect(is_callable([$model, 'translations']))->toBeTrue(
            "Model {$modelClass} translations() method must be callable"
        );

        // Property: translations() method returns HasMany relationship
        $translationsRelation = $model->translations();
        expect($translationsRelation)->toBeInstanceOf(HasMany::class,
            "Model {$modelClass} translations() must return HasMany relationship"
        );

        // Property: translations() method has proper return type declaration
        $reflection = new \ReflectionMethod($model, 'translations');
        $returnType = $reflection->getReturnType();
        expect($returnType)->not->toBeNull(
            "Model {$modelClass} translations() method must have return type declaration"
        );

        if ($returnType instanceof \ReflectionNamedType) {
            expect($returnType->getName())->toBe(HasMany::class,
                "Model {$modelClass} translations() method must declare HasMany return type"
            );
        }

        // Property: translations relationship is properly configured
        $foreignKey = $translationsRelation->getForeignKeyName();
        $localKey = $translationsRelation->getLocalKeyName();

        expect($foreignKey)->not->toBeEmpty(
            "Model {$modelClass} translations() relationship must have valid foreign key"
        );

        expect($localKey)->not->toBeEmpty(
            "Model {$modelClass} translations() relationship must have valid local key"
        );
    }
});

/**
 * **Feature: performance-update, Property 2: Interface Implementation Completeness**
 * **Validates: Requirements 1.2**
 *
 * Property-based test to verify translation models exist for all TranslatableRecord implementations.
 */
test('translation models exist for all TranslatableRecord implementations', function (): void {
    $translatableModels = [
        Product::class        => \App\Models\Translations\ProductTranslation::class,
        Brand::class          => \App\Models\Translations\BrandTranslation::class,
        Collection::class     => \App\Models\Translations\CollectionTranslation::class,
        ProductVariant::class => \App\Models\Translations\ProductVariantTranslation::class,
    ];

    foreach ($translatableModels as $modelClass => $translationClass) {
        // Property: Translation model class exists
        expect(class_exists($translationClass))->toBeTrue(
            "Translation model {$translationClass} must exist for {$modelClass}"
        );

        // Property: Translation model can be instantiated
        $translationModel = new $translationClass;
        expect($translationModel)->toBeInstanceOf($translationClass,
            "Translation model {$translationClass} must be instantiable"
        );

        // Property: Translation model extends Eloquent Model
        expect($translationModel)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class,
            "Translation model {$translationClass} must extend Eloquent Model"
        );

        // Property: Main model's translations() method returns the correct related model
        $mainModel = new $modelClass;
        $relation = $mainModel->translations();
        $relatedModel = $relation->getRelated();

        expect($relatedModel)->toBeInstanceOf($translationClass,
            "Model {$modelClass} translations() must return {$translationClass} instances"
        );
    }
});

/**
 * **Feature: performance-update, Property 2: Interface Implementation Completeness**
 * **Validates: Requirements 1.2**
 *
 * Property-based test to ensure no models claim to implement TranslatableRecord without proper implementation.
 */
test('no models falsely claim TranslatableRecord implementation', function (): void {
    // Get all model files
    $modelFiles = File::allFiles(app_path('Models'));
    $modelsWithoutTranslations = [];

    foreach ($modelFiles as $file) {
        $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();

        // Skip if class doesn't exist (might be in subdirectories)
        if (! class_exists($className)) {
            continue;
        }

        try {
            $model = new $className;

            // If model implements TranslatableRecord, it must have translations() method
            if ($model instanceof TranslatableRecord) {
                if (! method_exists($model, 'translations')) {
                    $modelsWithoutTranslations[] = $className;
                }
            }
        } catch (\Throwable $e) {
            // Skip models that can't be instantiated (might require dependencies)
            continue;
        }
    }

    // Property: No models implement TranslatableRecord without translations() method
    expect($modelsWithoutTranslations)->toBeEmpty(
        'These models implement TranslatableRecord but lack translations() method: ' .
        implode(', ', $modelsWithoutTranslations)
    );
});

/**
 * **Feature: performance-update, Property 2: Interface Implementation Completeness**
 * **Validates: Requirements 1.2**
 *
 * Property-based test to verify interface method signatures are consistent.
 */
test('TranslatableRecord interface method signatures are consistent across implementations', function (): void {
    $translatableModels = [
        Product::class,
        Brand::class,
        Collection::class,
        ProductVariant::class,
    ];

    $expectedMethodSignature = null;

    foreach ($translatableModels as $modelClass) {
        $model = new $modelClass;
        $reflection = new \ReflectionMethod($model, 'translations');

        // Get method signature details
        $signature = [
            'name'            => $reflection->getName(),
            'return_type'     => $reflection->getReturnType()?->getName(),
            'parameter_count' => $reflection->getNumberOfParameters(),
            'is_public'       => $reflection->isPublic(),
        ];

        if ($expectedMethodSignature === null) {
            $expectedMethodSignature = $signature;
        } else {
            // Property: All implementations have identical method signatures
            expect($signature)->toBe($expectedMethodSignature,
                "Model {$modelClass} translations() method signature differs from other implementations"
            );
        }

        // Property: Method is public
        expect($reflection->isPublic())->toBeTrue(
            "Model {$modelClass} translations() method must be public"
        );

        // Property: Method takes no parameters
        expect($reflection->getNumberOfParameters())->toBe(0,
            "Model {$modelClass} translations() method must take no parameters"
        );
    }
});

/**
 * **Feature: performance-update, Property 2: Interface Implementation Completeness**
 * **Validates: Requirements 1.2**
 *
 * Property-based test to ensure translations relationships can be used without errors.
 */
test('translations relationships are functional across all implementations', function (): void {
    $translatableModels = [
        Product::class,
        Brand::class,
        Collection::class,
        ProductVariant::class,
    ];

    foreach ($translatableModels as $modelClass) {
        $model = new $modelClass;

        try {
            // Property: translations() can be called without errors
            $relation = $model->translations();
            expect($relation)->toBeInstanceOf(HasMany::class);

            // Property: relationship query can be built without errors
            $query = $relation->getQuery();
            expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);

            // Property: relationship has proper table and key configuration
            $foreignKey = $relation->getForeignKeyName();
            $localKey = $relation->getLocalKeyName();

            expect($foreignKey)->toBeString()->not->toBeEmpty();
            expect($localKey)->toBeString()->not->toBeEmpty();

        } catch (\Throwable $e) {
            throw new \Exception(
                "Model {$modelClass} translations() relationship is not functional: " . $e->getMessage()
            );
        }
    }
});
