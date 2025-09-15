#!/bin/bash

# Script to fix all city seeders by removing region references and fixing slug conflicts

echo "Fixing city seeders..."

# List of seeders to fix
seeders=(
    "database/seeders/cities/GermanyCitiesSeeder.php"
    "database/seeders/cities/FranceCitiesSeeder.php"
    "database/seeders/cities/UKCitiesSeeder.php"
    "database/seeders/cities/USACitiesSeeder.php"
    "database/seeders/cities/SpainCitiesSeeder.php"
    "database/seeders/cities/ItalyCitiesSeeder.php"
    "database/seeders/cities/RussiaCitiesSeeder.php"
    "database/seeders/cities/CanadaCitiesSeeder.php"
    "database/seeders/cities/NetherlandsCitiesSeeder.php"
    "database/seeders/cities/BelgiumCitiesSeeder.php"
    "database/seeders/cities/SwedenCitiesSeeder.php"
    "database/seeders/cities/NorwayCitiesSeeder.php"
    "database/seeders/cities/DenmarkCitiesSeeder.php"
    "database/seeders/cities/FinlandCitiesSeeder.php"
)

for seeder in "${seeders[@]}"; do
    echo "Fixing $seeder..."
    
    # Remove Region import
    sed -i '/use App\\Models\\Region;/d' "$seeder"
    
    # Remove region variable declarations (lines with Region::where)
    sed -i '/Region::where/d' "$seeder"
    
    # Remove region_id assignments
    sed -i '/region_id.*->id/d' "$seeder"
    
    # Remove region_id from updateOrCreate
    sed -i "/'region_id' => \$cityData\['region_id'\],/d" "$seeder"
    
    # Fix slug generation to include code
    sed -i "s/'slug' => \\\\Str::slug(\$cityData\['name'\])/'slug' => \\\\Str::slug(\$cityData['name'] . '-' . \$cityData['code'])/" "$seeder"
    
    # Add comment about regions
    sed -i '/\/\/ Get regions/a\        // Regions are no longer used in the database schema' "$seeder"
    sed -i '/\/\/ Get regions/d' "$seeder"
done

echo "All seeders fixed!"
