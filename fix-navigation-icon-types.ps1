# Fix navigationIcon property type declarations for Filament 4 compatibility

Write-Host "Fixing navigationIcon property types for Filament 4 compatibility..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains navigationIcon property with explicit type
    if ($content -match "protected\s+static\s+(BackedEnum\|string\|null)\s+\`$navigationIcon") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Replace typed navigationIcon property with untyped version
        $newContent = $content -replace 'protected\s+static\s+(BackedEnum\|string\|null)\s+(\$navigationIcon)', 'protected static $2'
        
        if ($content -ne $newContent) {
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            Write-Host "  ✓ Fixed navigationIcon property type" -ForegroundColor Green
            $fixedCount++
        } else {
            Write-Host "  - No changes needed" -ForegroundColor Gray
        }
    }
}

Write-Host "`nFixed $fixedCount resource files" -ForegroundColor Green
Write-Host "NavigationIcon property types have been updated for Filament 4 compatibility" -ForegroundColor Green