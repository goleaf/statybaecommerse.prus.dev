# Fix widget heading properties based on parent class
$widgetFiles = Get-ChildItem -Path "app/Filament" -Filter "*Widget.php" -Recurse

foreach ($file in $widgetFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # TableWidget requires static heading
    if ($content -match 'extends.*TableWidget') {
        if ($content -match 'protected \?string \$heading') {
            $content = $content -replace 'protected \?string \$heading', 'protected static ?string $heading'
            $modified = $true
            Write-Host "Fixed TableWidget heading to static in $($file.Name)"
        }
    }
    # ChartWidget requires non-static heading
    elseif ($content -match 'extends.*ChartWidget') {
        if ($content -match 'protected static \?string \$heading') {
            $content = $content -replace 'protected static \?string \$heading', 'protected ?string $heading'
            $modified = $true
            Write-Host "Fixed ChartWidget heading to non-static in $($file.Name)"
        }
    }
    # StatsOverviewWidget and other widgets - check what they need
    elseif ($content -match 'extends.*Widget') {
        # For now, assume non-static for other widget types
        if ($content -match 'protected static \?string \$heading') {
            $content = $content -replace 'protected static \?string \$heading', 'protected ?string $heading'
            $modified = $true
            Write-Host "Fixed generic Widget heading to non-static in $($file.Name)"
        }
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing widget heading properties based on parent class"