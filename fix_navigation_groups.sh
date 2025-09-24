#!/bin/bash

# Fix all Filament navigation group type issues

echo "Fixing navigation group type issues in Filament resources..."

# Find all PHP files in Filament Resources directory
find app/Filament/Resources -name "*.php" -type f | while read file; do
    echo "Processing: $file"
    
    # Remove type declarations from navigationGroup properties
    sed -i 's/protected static ?string \$navigationGroup/protected static \$navigationGroup/g' "$file"
    sed -i 's/protected static string \$navigationGroup/protected static \$navigationGroup/g' "$file"
    sed -i 's/protected static ?int \$navigationGroup/protected static \$navigationGroup/g' "$file"
    
    # Replace NavigationGroup enum references with strings
    sed -i 's/NavigationGroup::Catalog/'\''Catalog'\''/g' "$file"
    sed -i 's/NavigationGroup::Commerce/'\''Commerce'\''/g' "$file"
    sed -i 's/NavigationGroup::Content/'\''Content'\''/g' "$file"
    sed -i 's/NavigationGroup::Marketing/'\''Marketing'\''/g' "$file"
    sed -i 's/NavigationGroup::System/'\''System'\''/g' "$file"
    sed -i 's/NavigationGroup::Users/'\''Users'\''/g' "$file"
    sed -i 's/NavigationGroup::Analytics/'\''Analytics'\''/g' "$file"
    sed -i 's/NavigationGroup::Settings/'\''Settings'\''/g' "$file"
    
    # Add UnitEnum import if navigationGroup is present and UnitEnum is not imported
    if grep -q "navigationGroup" "$file" && ! grep -q "use UnitEnum;" "$file"; then
        # Find the last use statement and add UnitEnum import after it
        sed -i '/^use /a use UnitEnum;' "$file"
    fi
    
    # Add docblock if navigationGroup is present and docblock is not present
    if grep -q "protected static \$navigationGroup" "$file" && ! grep -B1 "protected static \$navigationGroup" "$file" | grep -q "@var UnitEnum"; then
        sed -i 's/protected static \$navigationGroup/    \/\*\* @var UnitEnum|string|null \*\/\n    protected static \$navigationGroup/g' "$file"
    fi
done

echo "Navigation group fixes completed!"
