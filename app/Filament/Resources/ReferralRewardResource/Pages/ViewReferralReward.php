<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Resources\ReferralRewardResource;
use App\Models\ReferralReward;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

final class ViewReferralReward extends ViewRecord
{
    use SpatieTranslatableViewRecord; // Keep the detail view synchronized with the active locale.

    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow locale switching while reviewing record details.
            Actions\EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record instanceof ReferralReward) {
            return;
        }

        $this->record->loadMissing([
            'user' => fn (Builder $query): Builder => $query->withoutGlobalScopes(),
            'referral' => fn (Builder $query): Builder => $query->withoutGlobalScopes(),
            'order' => fn (Builder $query): Builder => $query->withoutGlobalScopes(),
        ]);
    }

    public function getHeading(): string
    {
        $heading = parent::getHeading();
        $headingString = $heading instanceof Htmlable ? $heading->toHtml() : (string) $heading;

        if (! $this->record instanceof ReferralReward) {
            return $headingString;
        }

        /** @var \App\Models\Referral|null $referral */
        $referral = $this->record->referral;
        $referralCode = $referral?->referral_code;

        if ($referralCode === null || $referralCode === '') {
            return $headingString;
        }

        return $headingString.' ('.(string) $referralCode.')';
    }
}
