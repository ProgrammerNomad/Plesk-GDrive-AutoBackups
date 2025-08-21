@echo off
REM Deployment script for Plesk GDrive AutoBackups Extension (Windows)

REM Set variables
set EXTENSION_NAME=plesk-gdrive-autobackups
for /f "tokens=2 delims=<>" %%i in ('findstr "<version>" meta.xml') do set VERSION=%%i
set PACKAGE_NAME=%EXTENSION_NAME%-%VERSION%.zip

echo Building Plesk GDrive AutoBackups Extension v%VERSION%

REM Clean up any existing package
if exist *.zip del *.zip

REM Install/update dependencies
echo Installing dependencies...
cd plib
composer install --no-dev --optimize-autoloader
cd ..

REM Create package excluding development files
echo Creating package: %PACKAGE_NAME%

REM Use PowerShell to create the zip file
powershell -Command "& { Add-Type -A 'System.IO.Compression.FileSystem'; [IO.Compression.ZipFile]::CreateFromDirectory('.', '%PACKAGE_NAME%', 'Optimal', $false) }"

echo Package created: %PACKAGE_NAME%
echo Ready for upload to Plesk Extension Manager

echo.
echo To install:
echo 1. Upload %PACKAGE_NAME% to Plesk Panel
echo 2. Go to Extensions ^> My Extensions
echo 3. Click 'Upload Extension'
echo 4. Select %PACKAGE_NAME% and install

pause
