# Fix duplicate Schema imports
$filamentFiles = Get-ChildItem -Path "app/Filament" -Filter "*.php" -Recurse

foreach ($file in $filamentFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Remove duplicate Schema imports
    if ($content -match 'use Filament\\Schemas\\Schema;.*use Filament\\Schemas\\Schema;') {
        $content = $content -replace '(use Filament\\Schemas\\Schema;)\s*use Filament\\Schemas\\Schema;', '$1'
        $modified = $true
        Write-Host "Removed duplicate Schema import in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing duplicate imports"