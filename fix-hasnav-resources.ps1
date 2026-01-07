# Fix resources that use HasNav trait by removing conflicting properties
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Filter "*Resource.php"

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Check if file uses HasNav trait
    if ($content -match 'use HasNav;') {
        Write-Host "Processing HasNav resource: $($file.Name)"
        
        # Remove navigationIcon property
        if ($content -match 'protected static.*\$navigationIcon.*=.*?;') {
            $content = $content -replace 'protected static.*\$navigationIcon.*=.*?;', ''
            $modified = $true
            Write-Host "  - Removed navigationIcon property"
        }
        
        # Remove navigationGroup property
        if ($content -match 'protected static.*\$navigationGroup.*=.*?;') {
            $content = $content -replace 'protected static.*\$navigationGroup.*=.*?;', ''
            $modified = $true
            Write-Host "  - Removed navigationGroup property"
        }
        
        # Clean up extra blank lines
        $content = $content -replace '\n\s*\n\s*\n', "`n`n"
        
    } else {
        # For resources not using HasNav, ensure they have proper property types
        if ($content -match 'protected static \$navigationIcon\s*=') {
            $content = $content -replace 'protected static \$navigationIcon\s*=', 'protected static ?string $navigationIcon ='
            $modified = $true
            Write-Host "Fixed navigationIcon type in $($file.Name)"
        }
        
        if ($content -match 'protected static \$navigationGroup\s*=') {
            $content = $content -replace 'protected static \$navigationGroup\s*=', 'protected static ?string $navigationGroup ='
            $modified = $true
            Write-Host "Fixed navigationGroup type in $($file.Name)"
        }
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing HasNav resources"