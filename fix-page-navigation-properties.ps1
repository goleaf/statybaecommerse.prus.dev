# Fix all page navigation properties for Filament 4 compatibility
$pageFiles = Get-ChildItem -Path "app/Filament/Pages" -Filter "*.php" -Recurse

foreach ($file in $pageFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Add required imports if not present
    if ($content -notmatch 'use UnitEnum;' -and $content -match 'protected static.*\$navigationGroup') {
        $content = $content -replace '(use [^;]+;)(\s*\n\s*final class)', '$1' + "`nuse UnitEnum;" + '$2'
        $modified = $true
    }
    
    if ($content -notmatch 'use BackedEnum;' -and $content -match 'protected static.*\$navigationIcon') {
        $content = $content -replace '(use [^;]+;)(\s*\n\s*final class)', '$1' + "`nuse BackedEnum;" + '$2'
        $modified = $true
    }
    
    # Fix navigationGroup property type
    if ($content -match 'protected static \?string \$navigationGroup') {
        $content = $content -replace 'protected static \?string \$navigationGroup', 'protected static UnitEnum|string|null $navigationGroup'
        $modified = $true
        Write-Host "Fixed navigationGroup union type in $($file.Name)"
    }
    
    # Fix navigationIcon property type
    if ($content -match 'protected static \?string \$navigationIcon') {
        $content = $content -replace 'protected static \?string \$navigationIcon', 'protected static BackedEnum|string|null $navigationIcon'
        $modified = $true
        Write-Host "Fixed navigationIcon union type in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing page navigation properties"