# Fix all infolist method signatures for Filament 4
$filamentFiles = Get-ChildItem -Path "app/Filament" -Filter "*.php" -Recurse

foreach ($file in $filamentFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Fix infolist method signature
    if ($content -match 'public function infolist\(Infolist \$infolist\): Infolist') {
        $content = $content -replace 'public function infolist\(Infolist \$infolist\): Infolist', 'public function infolist(Schema $schema): Schema'
        $modified = $true
        Write-Host "Fixed infolist method signature in $($file.Name)"
    }
    
    # Fix the parameter name in the method body
    if ($content -match 'return \$infolist') {
        $content = $content -replace 'return \$infolist', 'return $schema'
        $modified = $true
        Write-Host "Fixed infolist parameter name in $($file.Name)"
    }
    
    # Fix import statement
    if ($content -match 'use Filament\\Infolists\\Infolist;') {
        $content = $content -replace 'use Filament\\Infolists\\Infolist;', 'use Filament\Schemas\Schema;'
        $modified = $true
        Write-Host "Fixed infolist import in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing infolist method signatures"