# Fix widget heading properties based on parent class including aliases
$widgetFiles = Get-ChildItem -Path "app/Filament" -Filter "*Widget.php" -Recurse

foreach ($file in $widgetFiles) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Check for TableWidget (including aliases like BaseWidget)
    if ($content -match 'extends.*TableWidget' -or 
        ($content -match 'use.*TableWidget as BaseWidget' -and $content -match 'extends BaseWidget')) {
        
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
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}

Write-Host "Completed fixing widget heading properties with alias support"