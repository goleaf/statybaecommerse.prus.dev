# Fix all widget heading properties to be static
$widgetFiles = Get-ChildItem -Path "app/Filament" -Filter "*Widget.php" -Recurse

foreach ($file in $widgetFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Check if file extends BaseWidget or TableWidget
    if ($content -match 'extends.*Widget') {
        # Fix non-static heading property
        if ($content -match 'protected \?string \$heading') {
            $content = $content -replace 'protected \?string \$heading', 'protected static ?string $heading'
            $modified = $true
            Write-Host "Fixed heading property in $($file.Name)"
        }
        
        # Also fix any other non-static heading variations
        if ($content -match 'protected string \$heading') {
            $content = $content -replace 'protected string \$heading', 'protected static string $heading'
            $modified = $true
            Write-Host "Fixed string heading property in $($file.Name)"
        }
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing widget heading properties"