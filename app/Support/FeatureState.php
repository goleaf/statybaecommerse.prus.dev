<?php

declare(strict_types=1);

namespace App\Support;

enum FeatureState: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
