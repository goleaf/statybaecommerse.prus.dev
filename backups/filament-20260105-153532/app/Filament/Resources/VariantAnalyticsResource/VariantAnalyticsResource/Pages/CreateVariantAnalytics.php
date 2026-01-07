<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Resources\VariantAnalyticsResource;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

final class CreateVariantAnalytics extends CreateRecord
{
    protected static string $resource = VariantAnalyticsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['date'] = Carbon::parse($data['date'])->toDateString();
        $data['date_bucket'] = sprintf('%s:%s', VariantAnalytics::BUCKET_DAILY, $data['date']);
        $data['product_id'] = ProductVariant::query()->whereKey($data['variant_id'])->value('product_id');

        return $data;
    }
}
