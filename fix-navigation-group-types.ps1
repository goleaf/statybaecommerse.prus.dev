# Fix navigationGroup property type declarations for Filament 4 compatibility

Write-Host "Fixing navigationGroup property types for Filament 4 compatibility..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains navigationGroup property with explicit type
    if ($content -match "protected\s+static\s+(UnitEnum\|string\|null|BackedEnum\|string\|null)\s+\`$navigationGroup") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Replace typed navigationGroup property with untyped version
        $newContent = $content -replace 'protected\s+static\s+(UnitEnum\|string\|null|BackedEnum\|string\|null)\s+(\$navigationGroup)', 'protected static $2'
        
        if ($content -ne $newContent) {
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            Write-Host "  ✓ Fixed navigationGroup property type" -ForegroundColor Green
            $fixedCount++
        } else {
            Write-Host "  - No changes needed" -ForegroundColor Gray
        }
    }
}

Write-Host "`nFixed $fixedCount resource files" -ForegroundColor Green
Write-Host "NavigationGroup property types have been updated for Filament 4 compatibility" -ForegroundColor Green