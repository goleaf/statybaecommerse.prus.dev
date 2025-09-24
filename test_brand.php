<?php

require_once 'vendor/autoload.php';

use App\Filament\Resources\BrandResource;

echo "BrandResource class loaded successfully\n";
echo 'Model: '.BrandResource::getModel()."\n";
echo 'Navigation Icon: '.BrandResource::getNavigationIcon()."\n";
echo 'Navigation Group: '.BrandResource::getNavigationGroup()."\n";
