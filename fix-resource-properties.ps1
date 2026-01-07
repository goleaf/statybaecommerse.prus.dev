# Fix Filament Resource Property Type Issues
$resourceFiles = Get-ChildItem -Path "app/Filament/Resources" -Filter "*Resource.php"

foreach ($file in $resourceFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Fix untyped $navigationIcon properties
    if ($content -match 'protected static \$navigationIcon\s*=') {
        $content = $content -replace 'protected static \$navigationIcon\s*=', 'protected static ?string $navigationIcon ='
        $modified = $true
        Write-Host "Fixed navigationIcon in $($file.Name)"
    }
    
    # Fix untyped $navigationGroup properties
    if ($content -match 'protected static \$navigationGroup\s*=') {
        $content = $content -replace 'protected static \$navigationGroup\s*=', 'protected static string|null $navigationGroup ='
        $modified = $true
        Write-Host "Fixed navigationGroup in $($file.Name)"
    }
    
    # Fix enum assignments to string properties
    if ($content -match 'protected static.*\$navigationGroup\s*=\s*NavigationGroup::') {
        # Replace enum assignments with string values
        $content = $content -replace 'protected static.*\$navigationGroup\s*=\s*NavigationGroup::(\w+);', 'protected static ?string $navigationGroup = ''$1'';'
        $modified = $true
        Write-Host "Fixed enum navigationGroup assignment in $($file.Name)"
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing resource property types"