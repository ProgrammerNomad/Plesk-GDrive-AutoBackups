param(
    [string]$Source = "C:\xampp\htdocs\Plesk-GDrive-AutoBackups",
    [string]$Output = "C:\xampp\htdocs\Plesk-GDrive-AutoBackups\plesk-gdrive-autobackups-1.0.0.zip",
    [string]$Version = "1.0.0"
)

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Plesk Extension Packager" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Verify source exists
if (-not (Test-Path $Source)) {
    Write-Host "[ERROR] Source not found: $Source" -ForegroundColor Red
    exit 1
}

Write-Host "[1/6] Source directory verified" -ForegroundColor Green
Write-Host "      Source: $Source"
Write-Host ""

# Remove old zip if exists
if (Test-Path $Output) {
    Remove-Item $Output -Force
    Write-Host "[2/6] Removed existing zip file" -ForegroundColor Green
} else {
    Write-Host "[2/6] No existing zip to remove" -ForegroundColor Green
}

# Create temp staging directory
$TempDir = $env:TEMP + "\plesk-pkg-" + [guid]::NewGuid().ToString().Substring(0,8)
New-Item -ItemType Directory -Path $TempDir -Force | Out-Null
Write-Host "[3/6] Created staging directory" -ForegroundColor Green
Write-Host "      Temp: $TempDir"
Write-Host ""

# Copy files
Write-Host "[*] Copying files..." -ForegroundColor Yellow
$Items = @("plib", "meta.xml", "composer.json", "README.md", "icon.png")
$ItemCount = 0

foreach ($item in $Items) {
    $src = Join-Path $Source $item
    $dst = Join-Path $TempDir $item
    
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dst -Recurse -Force
        Write-Host "    [+] $item" -ForegroundColor Green
        $ItemCount++
    } else {
        Write-Host "    [-] $item (not found)" -ForegroundColor Yellow
    }
}

# Remove vendor directory to reduce size
$VendorPath = Join-Path $TempDir "plib\vendor"
if (Test-Path $VendorPath) {
    Write-Host ""
    Write-Host "[4/6] Removing vendor directory (will be installed by Plesk)" -ForegroundColor Green
    Remove-Item -Path $VendorPath -Recurse -Force
} else {
    Write-Host "[4/6] No vendor directory to remove" -ForegroundColor Green
}

Write-Host ""
Write-Host "[5/6] Creating zip archive..." -ForegroundColor Yellow

try {
    Compress-Archive -Path $TempDir -DestinationPath $Output -CompressionLevel Optimal -Force -ErrorAction Stop
    Write-Host "      Archive created successfully" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to create zip: $_" -ForegroundColor Red
    Remove-Item -Path $TempDir -Recurse -Force
    exit 1
}

# Cleanup temp
Remove-Item -Path $TempDir -Recurse -Force

# Get file info
$ZipFile = Get-Item $Output
$SizeMB = [math]::Round($ZipFile.Length / 1MB, 2)
$SizeKB = [math]::Round($ZipFile.Length / 1KB, 0)

Write-Host ""
Write-Host "[6/6] Package verification" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "PACKAGE CREATED SUCCESSFULLY" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "File:    $(Split-Path $Output -Leaf)" -ForegroundColor White
Write-Host "Size:    $SizeMB MB ($SizeKB KB)" -ForegroundColor White
Write-Host "Version: $Version" -ForegroundColor White
Write-Host "Path:    $Output" -ForegroundColor White
Write-Host ""
Write-Host "Ready to upload to Plesk!" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
