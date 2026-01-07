# PowerShell script to fix Filament 4 compatibility issues
# This script updates all Filament resources to use the correct Filament 4 syntax

Write-Host "Starting Filament 4 compatibility fixes..." -ForegroundColor Green

# Get all PHP files in the Filament Resources directory
$resourceFiles = Get-ChildItem -Path "app\Filament\Resources" -Filter "*.php" -Recurse

$totalFiles = $resourceFiles.Count
$processedFiles = 0
$modifiedFiles = 0

foreach ($file in $resourceFiles) {
    $processedFiles++
    Write-Progress -Activity "Processing Filament Resources" -Status "Processing $($file.Name)" -PercentComplete (($processedFiles / $totalFiles) * 100)
    
    $content = Get-Content -Path $file.FullName -Raw
    $originalContent = $content
    
    # Fix 1: Replace Form import with Schema import
    $content = $content -replace 'use Filament\\Forms\\Form;', 'use Filament\Schemas\Schema;'
    
    # Fix 2: Replace form method signature
    $content = $content -replace 'public static function form\(Form \$form\): Form', 'public static function form(Schema $schema): Schema'
    
    # Fix 3: Replace $form with $schema in method body
    $content = $content -replace 'return \$form->schema\(', 'return $schema->schema('
    
    # Check if any changes were made
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $modifiedFiles++
        Write-Host "Fixed: $($file.FullName)" -ForegroundColor Yellow
    }
}

Write-Host "`nCompleted processing $totalFiles files" -ForegroundColor Green
Write-Host "Modified $modifiedFiles files" -ForegroundColor Green

# Also check for relation managers and other Filament files
Write-Host "`nProcessing relation managers and other Filament files..." -ForegroundColor Green

$allFilamentFiles = Get-ChildItem -Path "app\Filament" -Filter "*.php" -Recurse | Where-Object { $_.Name -notlike "*Resource.php" }

foreach ($file in $allFilamentFiles) {
    $content = Get-Content -Path $file.FullName -Raw
    $originalContent = $content
    
    # Apply the same fixes
    $content = $content -replace 'use Filament\\Forms\\Form;', 'use Filament\Schemas\Schema;'
    $content = $content -replace 'public static function form\(Form \$form\): Form', 'public static function form(Schema $schema): Schema'
    $content = $content -replace 'return \$form->schema\(', 'return $schema->schema('
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $modifiedFiles++
        Write-Host "Fixed: $($file.FullName)" -ForegroundColor Yellow
    }
}

Write-Host "`nFilament 4 compatibility fixes completed!" -ForegroundColor Green
Write-Host "Total files modified: $modifiedFiles" -ForegroundColor Green