# Fix Filament Resource Property Type Issues - Version 2
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Filter "*Resource.php"

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Fix invalid union type syntax for navigationIcon
    if ($content -match 'protected static BackedEnum\|string\|null \$navigationIcon') {
        $content = $content -replace 'protected static BackedEnum\|string\|null \$navigationIcon', 'protected static ?string $navigationIcon'
        $modified = $true
        Write-Host "Fixed invalid navigationIcon union type in $($file.Name)"
    }
    
    # Fix invalid union type syntax for navigationGroup  
    if ($content -match 'protected static string\|null \$navigationGroup') {
        $content = $content -replace 'protected static string\|null \$navigationGroup', 'protected static ?string $navigationGroup'
        $modified = $true
        Write-Host "Fixed invalid navigationGroup union type in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing invalid union type syntax"