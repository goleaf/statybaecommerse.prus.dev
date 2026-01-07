<?php

namespace Coolsam\Flatpickr\Enums;

enum FlatpickrTheme: string
{
    case DEFAULT = 'default';
    case AIRBNB = 'airbnb';
    case CONFETTI = 'confetti';
    case DARK = 'dark';
    case LIGHT = 'light';
    case MATERIAL_BLUE = 'material_blue';
    case MATERIAL_GREEN = 'material_green';
    case MATERIAL_ORANGE = 'material_orange';
    case MATERIAL_RED = 'material_red';

    // Get theme asset
    public function getAsset(): string
    {
        $assetName = $this->value;

        try {
            return asset("vendor/flatpickr/themes/{$assetName}.css");
        } catch (\Exception $e) {
            return '';
        }
    }
}
