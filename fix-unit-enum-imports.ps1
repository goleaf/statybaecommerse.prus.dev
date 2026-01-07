# Remove unnecessary UnitEnum imports that cause conflicts

Write-Host "Removing unnecessary UnitEnum imports..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains UnitEnum import
    if ($content -match "use UnitEnum;") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Remove the UnitEnum import line
        $newContent = $content -replace "use UnitEnum;\r?\n", ""
        
        if ($content -ne $newContent) {
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            Write-Host "  ✓ Removed UnitEnum import" -ForegroundColor Green
            $fixedCount++
        } else {
            Write-Host "  - No changes needed" -ForegroundColor Gray
        }
    }
}

Write-Host "`nFixed $fixedCount resource files" -ForegroundColor Green
Write-Host "Unnecessary UnitEnum imports have been removed" -ForegroundColor Green