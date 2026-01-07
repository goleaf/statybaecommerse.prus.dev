# Fix widget heading properties from static to non-static for Filament 4 compatibility

Write-Host "Fixing widget heading properties for Filament 4 compatibility..." -ForegroundColor Green

# Get all PHP files in the app/Filament directory
$widgetFiles = Get-ChildItem -Path "app/Filament" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $widgetFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains static heading property
    if ($content -match "protected\s+static.*\`$heading") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Replace static heading property with non-static
        $newContent = $content -replace 'protected\s+static\s+(\?\s*string\s+\$heading)', 'protected $1'
        
        if ($content -ne $newContent) {
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            Write-Host "  ✓ Fixed heading property" -ForegroundColor Green
            $fixedCount++
        } else {
            Write-Host "  - No changes needed" -ForegroundColor Gray
        }
    }
}

Write-Host "`nFixed $fixedCount widget files" -ForegroundColor Green
Write-Host "Widget heading properties have been updated for Filament 4 compatibility" -ForegroundColor Green