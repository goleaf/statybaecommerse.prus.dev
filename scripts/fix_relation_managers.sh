#!/bin/bash

# Fix common syntax errors in RelationManager files
for file in $(find app/Filament/Resources -name "*RelationManager.php" -type f); do
    echo "Fixing: $file"
    
    # Fix missing opening brace for table method
    sed -i 's/public function table(Table $table): Table$/public function table(Table $table): Table\n    {/' "$file"
    
    # Fix missing closing braces and commas
    sed -i 's/])$/])/' "$file"
    sed -i 's/])$/])/' "$file"
    
    # Fix missing closing brace for table method
    sed -i 's/->defaultSort.*;$/->defaultSort("created_at", "desc");\n    }\n}/' "$file"
    
    # Fix any missing commas in form components
    sed -i 's/->required()$/->required(),/' "$file"
    sed -i 's/->default(false)$/->default(false),/' "$file"
    sed -i 's/->default(0)$/->default(0),/' "$file"
    
done

echo "Fixed all RelationManager files"
