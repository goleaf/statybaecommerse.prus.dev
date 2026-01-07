# PowerShell script to fix all navigation group issues
# This script updates all navigation group declarations to use NavigationGroup enum

Write-Host "Starting comprehensive Navigation Group fixes..." -ForegroundColor Green

# Get all PHP files in the Filament Resources directory
$resourceFiles = Get-ChildItem -Path "app\Filament\Resources" -Filter "*.php" -Recurse

$totalFiles = $resourceFiles.Count
$processedFiles = 0
$modifiedFiles = 0

# Define the mapping from string values to enum cases
$navigationGroupMappings = @{
    "'Analytics'" = "NavigationGroup::Analytics"
    "'Products'" = "NavigationGroup::Products"
    "'Orders'" = "NavigationGroup::Orders"
    "'Users'" = "NavigationGroup::Users"
    "'Settings'" = "NavigationGroup::Settings"
    "'Content'" = "NavigationGroup::Content"
    "'Content Management'" = "NavigationGroup::ContentManagement"
    "'System'" = "NavigationGroup::System"
    "'Marketing'" = "NavigationGroup::Marketing"
    "'Inventory'" = "NavigationGroup::Inventory"
    "'Reports'" = "NavigationGroup::Reports"
    "'Locations'" = "NavigationGroup::Locations"
    "'Discounts'" = "NavigationGroup::Discounts"
    "'Campaigns'" = "NavigationGroup::Campaigns"
    "'News'" = "NavigationGroup::News"
    "'Referral'" = "NavigationGroup::Referral"
    "'Referral System'" = "NavigationGroup::Referral"
}

foreach ($file in $resourceFiles) {
    $processedFiles++
    Write-Progress -Activity "Processing Navigation Groups" -Status "Processing $($file.Name)" -PercentComplete (($processedFiles / $totalFiles) * 100)
    
    $content = Get-Content -Path $file.FullName -Raw
    $originalContent = $content
    
    # Add NavigationGroup import if not present and if file uses navigation groups
    if ($content -match "protected static.*navigationGroup.*=" -and $content -notmatch "use App\\Enums\\NavigationGroup;") {
        # Find the position after the last use statement
        $lines = $content -split "`n"
        $lastUseIndex = -1
        for ($i = 0; $i -lt $lines.Count; $i++) {
            if ($lines[$i] -match "^use ") {
                $lastUseIndex = $i
            }
        }
        
        if ($lastUseIndex -ge 0) {
            $lines = $lines[0..$lastUseIndex] + "use App\Enums\NavigationGroup;" + $lines[($lastUseIndex + 1)..($lines.Count - 1)]
            $content = $lines -join "`n"
        }
    }
    
    # Replace string navigation groups with enum references and fix property type
    foreach ($mapping in $navigationGroupMappings.GetEnumerator()) {
        $pattern = "protected static \?string \`$navigationGroup = " + [regex]::Escape($mapping.Key) + ";"
        $replacement = "protected static ?NavigationGroup `$navigationGroup = " + $mapping.Value + ";"
        $content = $content -replace $pattern, $replacement
    }
    
    # Check if any changes were made
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $modifiedFiles++
        Write-Host "Fixed navigation group in: $($file.FullName)" -ForegroundColor Yellow
    }
}

Write-Host "`nCompleted processing $totalFiles files" -ForegroundColor Green
Write-Host "Modified $modifiedFiles files" -ForegroundColor Green

Write-Host "`nComprehensive Navigation Group fixes completed!" -ForegroundColor Green
Write-Host "Total files modified: $modifiedFiles" -ForegroundColor Green