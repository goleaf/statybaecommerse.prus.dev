# Fix Filament Resource Property Type Issues - Version 3
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Filter "*Resource.php"

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Fix any remaining union type syntax issues
    if ($content -match 'protected static \w+\|\w+\|\w+ \$navigation') {
        $content = $content -replace 'protected static \w+\|\w+\|\w+ \$navigationIcon', 'protected static ?string $navigationIcon'
        $content = $content -replace 'protected static \w+\|\w+\|\w+ \$navigationGroup', 'protected static ?string $navigationGroup'
        $modified = $true
        Write-Host "Fixed union type syntax in $($file.Name)"
    }
    
    # Fix NavigationGroup enum assignments
    if ($content -match 'NavigationGroup::\w+') {
        $content = $content -replace '= NavigationGroup::(\w+);', '= ''$1'';'
        $modified = $true
        Write-Host "Fixed NavigationGroup enum assignment in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing union type syntax and enum assignments"