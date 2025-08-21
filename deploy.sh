#!/bin/bash
# Deployment script for Plesk GDrive AutoBackups Extension

# Set variables
EXTENSION_NAME="plesk-gdrive-autobackups"
VERSION=$(grep -oP '(?<=<version>)[^<]+' meta.xml)
PACKAGE_NAME="${EXTENSION_NAME}-${VERSION}.zip"

echo "Building Plesk GDrive AutoBackups Extension v${VERSION}"

# Clean up any existing package
rm -f *.zip

# Install/update dependencies
echo "Installing dependencies..."
cd plib
composer install --no-dev --optimize-autoloader
cd ..

# Create package excluding development files
echo "Creating package: ${PACKAGE_NAME}"
zip -r "${PACKAGE_NAME}" . \
    -x "*.git*" \
    -x "google-drive-backup/*" \
    -x "node_modules/*" \
    -x "*.md" \
    -x "deploy.sh" \
    -x ".github/*" \
    -x "tests/*" \
    -x "docs/*" \
    -x "*.log" \
    -x "*.tmp"

echo "Package created: ${PACKAGE_NAME}"
echo "Ready for upload to Plesk Extension Manager"

# Validate package structure
echo "Validating package structure..."
unzip -l "${PACKAGE_NAME}" | head -20

echo "Deployment package ready!"
echo ""
echo "To install:"
echo "1. Upload ${PACKAGE_NAME} to Plesk Panel"
echo "2. Go to Extensions > My Extensions"
echo "3. Click 'Upload Extension'"
echo "4. Select ${PACKAGE_NAME} and install"
