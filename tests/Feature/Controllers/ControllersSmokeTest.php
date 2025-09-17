<?php

declare(strict_types=1);

$controllers = [
    \App\Http\Controllers\RobotsController::class,
    \App\Http\Controllers\ExportController::class,
    \App\Http\Controllers\Admin\OrderStatusController::class,
    \App\Http\Controllers\Admin\AttributeValueTranslationController::class,
    \App\Http\Controllers\Admin\AttributeTranslationController::class,
    \App\Http\Controllers\Admin\ProductTranslationController::class,
    \App\Http\Controllers\Admin\CollectionTranslationController::class,
    \App\Http\Controllers\Admin\CategoryTranslationController::class,
    \App\Http\Controllers\Admin\BrandTranslationController::class,
    \App\Http\Controllers\Admin\LegalTranslationController::class,
    \App\Http\Controllers\Admin\RedemptionController::class,
    \App\Http\Controllers\OrderController::class,
    \App\Http\Controllers\Admin\CampaignController::class,
    \App\Http\Controllers\SitemapController::class,
    \App\Http\Controllers\LocationController::class,
    \App\Http\Controllers\Admin\DiscountPresetController::class,
    \App\Http\Controllers\BrandController::class,
    \App\Http\Controllers\Admin\DiscountPreviewController::class,
    \App\Http\Controllers\Admin\DiscountCodeController::class,
    \App\Http\Controllers\Auth\VerifyEmailController::class,
];

dataset('controllers', fn () => $controllers);

it('controllers can be instantiated', function (string $class): void {
    $instance = app($class);
    expect($instance)->toBeObject()->and(get_class($instance))->toBe($class);
})->with('controllers')->group('controllers-smoke');
