#!/bin/bash

# Fix specific syntax errors in RelationManager files
for file in $(find app/Filament/Resources -name "*RelationManager.php" -type f); do
    echo "Fixing syntax in: $file"
    
    # Fix missing commas after form components
    sed -i 's/->required()$/->required(),/' "$file"
    sed -i 's/->default(false)$/->default(false),/' "$file"
    sed -i 's/->default(0)$/->default(0),/' "$file"
    sed -i 's/->default(true)$/->default(true),/' "$file"
    sed -i 's/->maxLength(255)$/->maxLength(255),/' "$file"
    sed -i 's/->maxLength(50)$/->maxLength(50),/' "$file"
    sed -i 's/->numeric()$/->numeric(),/' "$file"
    sed -i 's/->searchable()$/->searchable(),/' "$file"
    sed -i 's/->preload()$/->preload(),/' "$file"
    sed -i 's/->rows(3)$/->rows(3),/' "$file"
    sed -i 's/->prefix('\''€'\'')$/->prefix('\''€'\''),/' "$file"
    sed -i 's/->suffix('\'' kg'\'')$/->suffix('\'' kg'\''),/' "$file"
    sed -i 's/->columnSpanFull()$/->columnSpanFull(),/' "$file"
    sed -i 's/->unique(ProductVariant::class, '\''sku'\'', ignoreRecord: true)$/->unique(ProductVariant::class, '\''sku'\'', ignoreRecord: true),/' "$file"
    
    # Fix missing commas after table columns
    sed -i 's/->sortable()$/->sortable(),/' "$file"
    sed -i 's/->copyable()$/->copyable(),/' "$file"
    sed -i 's/->copyMessage(__('\''admin\.common\.copied'\''))$/->copyMessage(__('\''admin\.common\.copied'\'')),/' "$file"
    sed -i 's/->toggleable(isToggledHiddenByDefault: true)$/->toggleable(isToggledHiddenByDefault: true),/' "$file"
    sed -i 's/->money('\''EUR'\'')$/->money('\''EUR'\''),/' "$file"
    sed -i 's/->badge()$/->badge(),/' "$file"
    sed -i 's/->boolean()$/->boolean(),/' "$file"
    sed -i 's/->dateTime()$/->dateTime(),/' "$file"
    sed -i 's/->limit(50)$/->limit(50),/' "$file"
    sed -i 's/->label(__('\''[^'\'']*'\''))$/->label(__('\''\1'\'')),/' "$file"
    
    # Fix missing commas in filters
    sed -i 's/->query(fn(Builder \$query): Builder => \$query->whereColumn('\''stock_quantity'\'', '\''<='\'', '\''low_stock_threshold'\''))$/->query(fn(Builder \$query): Builder => \$query->whereColumn('\''stock_quantity'\'', '\''<='\'', '\''low_stock_threshold'\'')),/' "$file"
    sed -i 's/->query(fn(Builder \$query): Builder => \$query->where('\''stock_quantity'\'', '\''<='\'', 0))$/->query(fn(Builder \$query): Builder => \$query->where('\''stock_quantity'\'', '\''<='\'', 0)),/' "$file"
    
done

echo "Fixed syntax errors in all RelationManager files"
