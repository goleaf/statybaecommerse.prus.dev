# Fix all widget view properties to be non-static (Filament 4 requirement)
$widgetFiles = Get-ChildItem -Path "app/Filament" -Filter "*Widget.php" -Recurse

foreach ($file in $widgetFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Check if file extends Widget (but not TableWidget or ChartWidget which might be different)
    if ($content -match 'extends Widget' -and $content -notmatch 'extends.*TableWidget' -and $content -notmatch 'extends.*ChartWidget') {
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

Write-Host "Completed fixing widget view properties"