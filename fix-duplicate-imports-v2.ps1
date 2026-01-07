# Fix duplicate Schema imports - better version
$filamentFiles = Get-ChildItem -Path "app/Filament" -Filter "*.php" -Recurse

foreach ($file in $filamentFiles) {
    $lines = Get-Content $file.FullName
    $modified = $false
    $newLines = @()
    $seenImports = @{}
    
    foreach ($line in $lines) {
        # Check if this is a use statement
        if ($line -match '^use\s+([^;]+);') {
            $import = $matches[1]
            if ($seenImports.ContainsKey($import)) {
                # Skip duplicate import
                $modified = $true
                Write-Host "Removed duplicate import '$import' in $($file.Name)"
                continue
            } else {
                $seenImports[$import] = $true
            }
        }
        $newLines += $line
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $newLines
    }
}

Write-Host "Completed fixing duplicate imports"