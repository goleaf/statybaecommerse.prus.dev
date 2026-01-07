# Remove unnecessary BackedEnum imports that cause conflicts

Write-Host "Removing unnecessary BackedEnum imports..." -ForegroundColor Green

# Get all PHP files in the app/Filament/Resources directory
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Recurse -Filter "*.php"

$fixedCount = 0

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file contains BackedEnum import
    if ($content -match "use BackedEnum;") {
        Write-Host "Processing: $($file.FullName)" -ForegroundColor Yellow
        
        # Remove the BackedEnum import line
        $newContent = $content -replace "use BackedEnum;\r?\n", ""
        
        if ($content -ne $newContent) {
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            Write-Host "  ✓ Removed BackedEnum import" -ForegroundColor Green
            $fixedCount++
        } else {
            Write-Host "  - No changes needed" -ForegroundColor Gray
        }
    }
}

Write-Host "`nFixed $fixedCount resource files" -ForegroundColor Green
Write-Host "Unnecessary BackedEnum imports have been removed" -ForegroundColor Green