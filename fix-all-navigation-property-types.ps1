# Remove all explicit type declarations for navigationGroup and navigationIcon properties

Write-Host "Removing explicit type declarations for navigation properties..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Remove type declarations from navigationGroup properties
    $content = $content -replace 'protected\s+static\s+(UnitEnum\|string\|null|BackedEnum\|string\|null)\s+(\$navigationGroup)', 'protected static $2'
    
    # Remove type declarations from navigationIcon properties  
    $content = $content -replace 'protected\s+static\s+(BackedEnum\|string\|null)\s+(\$navigationIcon)', 'protected static $2'
    
    if ($originalContent -ne $content) {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "  ✓ Fixed navigation property types" -ForegroundColor Green
        $fixedCount++
    }
}

Write-Host "`nFixed $fixedCount resource files" -ForegroundColor Green
Write-Host "Navigation property type declarations have been removed" -ForegroundColor Green