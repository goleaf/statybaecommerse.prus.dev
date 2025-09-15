#!/bin/bash

# Script to fix all city seeders by adding missing $cities = [ line

echo "Fixing missing \$cities array declarations..."

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
    
    # Add missing $cities = [ after the regions comment
    sed -i '/\/\/ Regions are no longer used in the database schema/a\        \n        $cities = [' "$seeder"
done

echo "All seeders fixed!"
