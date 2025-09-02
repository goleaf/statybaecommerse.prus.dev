#!/bin/bash

# Fix Filament v4 compatibility issues

files=(
    "app/Filament/Resources/CategoryResource.php"
    "app/Filament/Resources/OrderResource.php"
    "app/Filament/Resources/UserResource.php"
    "app/Filament/Resources/BrandResource.php"
    "app/Filament/Resources/CouponResource.php"
    "app/Filament/Resources/CollectionResource.php"
    "app/Filament/Resources/CountryResource.php"
    "app/Filament/Resources/ProductResource.php"
    "app/Filament/Resources/SettingResource.php"
    "app/Filament/Resources/DiscountResource.php"
    "app/Filament/Resources/CustomerGroupResource.php"
    "app/Filament/Resources/LocationResource.php"
    "app/Filament/Resources/ReviewResource.php"
    "app/Filament/Resources/CampaignResource.php"
    "app/Filament/Resources/CurrencyResource.php"
    "app/Filament/Resources/ZoneResource.php"
    "app/Filament/Resources/DiscountCodeResource.php"
    "app/Filament/Resources/PartnerResource.php"
    "app/Filament/Resources/PartnerTierResource.php"
    "app/Filament/Resources/DocumentTemplateResource.php"
    "app/Filament/Resources/DocumentResource.php"
)

for file in "${files[@]}"; do
    echo "Fixing $file..."
    # Fix method signature
    sed -i 's/public static function form(Schema \$schema): Schema/public static function form(Form \$form): Form/g' "$file"
    # Fix return statement
    sed -i 's/return \$schema/return \$form/g' "$file"
    # Fix import - more specific pattern
    sed -i 's/use Filament\\Schemas\\Schema;/use Filament\\Forms\\Form;/g' "$file"
done

echo "All files fixed!"
