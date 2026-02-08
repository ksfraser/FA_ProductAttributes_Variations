#!/bin/bash
# Deployment script for fa_product_attributes_variations
# This creates a clean deployment package with only essential FA files

set -e

echo "Creating fa_product_attributes_variations deployment package..."

# Create deployment directory
DEPLOY_DIR="deployment"
mkdir -p "$DEPLOY_DIR/FA_ProductAttributes_Variations/_init"

# Copy essential files
cp "_init/config" "$DEPLOY_DIR/FA_ProductAttributes_Variations/_init/"
cp "hooks.php" "$DEPLOY_DIR/FA_ProductAttributes_Variations/"
cp "product_variations_admin.php" "$DEPLOY_DIR/FA_ProductAttributes_Variations/"
cp "check_compatibility.php" "$DEPLOY_DIR/FA_ProductAttributes_Variations/"

echo "Deployment package created in: $DEPLOY_DIR/FA_ProductAttributes_Variations/"
echo ""
echo "Files included:"
ls -la "$DEPLOY_DIR/FA_ProductAttributes_Variations/"
ls -la "$DEPLOY_DIR/FA_ProductAttributes_Variations/_init/"
echo ""
echo "To deploy to server:"
echo "scp -r $DEPLOY_DIR/FA_ProductAttributes_Variations/ user@server:/path/to/FrontAccounting/modules/"