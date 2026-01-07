# Create backup before Filament downgrade
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupDir = "backups/pre-downgrade-$timestamp"

Write-Host "Creating backup in: $backupDir"

# Create backup directory
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# Backup composer files
Copy-Item -Path "composer.json" -Destination "$backupDir/composer.json.backup"
Copy-Item -Path "composer.lock" -Destination "$backupDir/composer.lock.backup"

# Backup Filament directory
if (Test-Path "app/Filament") {
    Copy-Item -Path "app/Filament" -Destination "$backupDir/filament-backup" -Recurse
}

# Backup Filament config
if (Test-Path "config/filament.php") {
    Copy-Item -Path "config/filament.php" -Destination "$backupDir/filament-config.backup"
}

# Backup package.json if exists
if (Test-Path "package.json") {
    Copy-Item -Path "package.json" -Destination "$backupDir/package.json.backup"
}

# Create backup manifest
$manifest = @{
    timestamp = $timestamp
    files = @(
        "composer.json",
        "composer.lock",
        "app/Filament (directory)",
        "config/filament.php",
        "package.json"
    )
    purpose = "Pre-Filament downgrade backup (4.1 to 3.3)"
} | ConvertTo-Json -Depth 3

$manifest | Out-File -FilePath "$backupDir/backup-manifest.json" -Encoding UTF8

Write-Host "Backup created successfully at: $backupDir"
Write-Host "Manifest saved to: $backupDir/backup-manifest.json"