# Fix all page view properties to be non-static (Filament 4 requirement)
$pageFiles = Get-ChildItem -Path "app/Filament/Pages" -Filter "*.php" -Recurse

foreach ($file in $pageFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Check if file extends Page
    if ($content -match 'extends.*Page') {
        # Fix static view property to non-static
        if ($content -match 'protected static.*\$view') {
            $content = $content -replace 'protected static (.*) \$view', 'protected $1 $view'
            $modified = $true
            Write-Host "Fixed view property to non-static in $($file.Name)"
        }
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing page view properties"