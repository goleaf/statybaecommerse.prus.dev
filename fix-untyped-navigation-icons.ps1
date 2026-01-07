# Fix untyped navigationIcon properties for Filament 4 compatibility

Write-Host "Fixing untyped navigationIcon properties for Filament 4 compatibility..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains untyped navigationIcon property
    if ($content -match "protected\s+static\s+\\\$navigationIcon\s*=") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Replace untyped navigationIcon property with typed version
        $newContent = $content -replace 'protected\s+static\s+(\$navigationIcon)', 'protected static ?string $1'
        
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
Write-Host "Untyped navigationIcon properties have been updated for Filament 4 compatibility" -ForegroundColor Green