# FA_ProductAttributes_Variations

A plugin module for FA_ProductAttributes that adds WooCommerce-style product variations functionality to FrontAccounting.

## Overview

This plugin extends the core FA_ProductAttributes module to provide:

- **Product Variations**: Automatically generate all possible product combinations
- **Parent-Child Relationships**: Support for product variations with parent-child relationships
- **Product Type Management**: Manual management of product types (Simple, Variable, Variation)
- **Variation Generation**: Create child products from parent products with attribute combinations
- **Retroactive Analysis**: Scan existing products for variation patterns and suggest relationships

## Recent Changes

- **Service Migration**: RetroactiveApplicationService moved from core module to this plugin
- **Clean Architecture**: All variation-specific functionality consolidated in plugin
- **Hook Integration**: Uses core extension points for seamless integration

## Requirements

- FrontAccounting 2.3.22 or later
- FA_ProductAttributes core module (must be installed first)
- PHP 7.3+

## Installation

1. Install the FA_ProductAttributes core module first
2. Copy this module to your FA modules directory as `FA_ProductAttributes_Variations`
3. Activate the module in FA admin (Setup → Install/Update Modules)
4. The module will automatically extend the core attributes functionality

## Architecture

This plugin uses the hook extension system provided by FA_ProductAttributes to:

- Extend the attributes tab with variations UI
- Add variations-specific save/delete handlers
- Provide additional admin functionality

## Features

### Product Types
- **Simple Products**: Standard products without variations
- **Variable Products**: Parent products that can have variations
- **Variation Products**: Child products that inherit attributes from parents

### Variation Generation
- Generate all possible combinations of selected attributes
- Royal Order sorting for consistent attribute sequencing
- Automatic stock ID generation (e.g., `PARENT-SIZE-COLOR`)
- Description template replacement

### Parent-Child Relationships
- Variations maintain parent relationships in the database
- Category assignments are inherited from parent to child
- Individual value assignments can be customized per variation

## Usage

1. Install both FA_ProductAttributes core and this variations plugin
2. In the Items screen, use the "Product Attributes" tab
3. The variations functionality will be automatically available for products with attributes

## Development

This plugin depends on the FA_ProductAttributes core module and extends its functionality through the hook system.