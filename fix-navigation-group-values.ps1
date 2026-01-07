# PowerShell script to fix navigation group values
# This script updates navigation group assignments to use enum cases directly

Write-Host "Starting Navigation Group value fixes..." -ForegroundColor Green

# Get all PHP files in the Filament Resources directory
$resourceFiles = Get-ChildItem -Path "app\Filament\Resources" -Filter "*.php" -Recurse

$totalFiles = $resourceFiles.Count
$processedFiles = 0
$modifiedFiles = 0

foreach ($file in $resourceFiles) {
    $processedFiles++
    Write-Progress -Activity "Processing Navigation Group Values" -Status "Processing $($file.Name)" -PercentComplete (($processedFiles / $totalFiles) * 100)
    
    $content = Get-Content -Path $file.FullName -Raw
    $originalContent = $content
    
    # Fix property type and remove ->value
    $content = $content -replace "protected static \?string \`$navigationGroup = NavigationGroup::([^-]+)->value;", "protected static ?NavigationGroup `$navigationGroup = NavigationGroup::`$1;"
    
    # Check if any changes were made
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $modifiedFiles++
        Write-Host "Fixed navigation group value in: $($file.FullName)" -ForegroundColor Yellow
    }
}

Write-Host "`nCompleted processing $totalFiles files" -ForegroundColor Green
Write-Host "Modified $modifiedFiles files" -ForegroundColor Green

Write-Host "`nNavigation Group value fixes completed!" -ForegroundColor Green
Write-Host "Total files modified: $modifiedFiles" -ForegroundColor Green