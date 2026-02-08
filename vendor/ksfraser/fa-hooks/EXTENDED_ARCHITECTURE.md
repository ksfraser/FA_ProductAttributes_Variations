# Extended Hook System Architecture

This document describes the extended hook system architecture that enables scalable module development for FrontAccounting, supporting dynamic hook registration and cross-module integration.

## Overview

The extended architecture provides:

1. **Container Classes**: Generic containers for managing different types of FA data structures
2. **Container Factory**: Centralized creation of appropriate container instances
3. **Hook Registry**: Dynamic registration of hook points by modules
4. **Enhanced HookManager**: Integration of all components

## Core Components

### ArrayContainer (Abstract Base Class)

Base class for managing array-based data structures with version abstraction.

```php
$container = new ArrayContainer($versionAdapter);
$container->addItem('key', $value);
$result = $container->toArray(); // Version-appropriate format
$merged = $container->mergeWith($existingArray);
```

### Specialized Containers

#### TabContainer
Manages tab structures for items, suppliers, customers, etc.

```php
$tabContainer = new TabContainer($versionAdapter);
$tabContainer->createTab('attributes', 'Product Attributes', 'attributes.php');
$tabs = $tabContainer->toArray();
```

#### MenuContainer
Manages menu item structures.

```php
$menuContainer = new MenuContainer($versionAdapter);
$menuContainer->createMenuItem('suppliers', 'Suppliers', 'suppliers.php', ['access' => 'SA_SUPPLIER']);
$menu = $menuContainer->toArray();
```

### ContainerFactory

Centralized factory for creating containers:

```php
$factory = new ContainerFactory($versionAdapter);
$tabContainer = $factory->createContainer('tab');
$menuContainer = $factory->createContainer('menu');
```

### HookRegistry

Dynamic hook point registration system:

```php
$registry = new HookRegistry($versionAdapter);

// Register a hook point
$registry->registerHookPoint('supplier_tabs', 'supplier_module', function($tabs) {
    // Default implementation
    return $tabs->toArray();
});

// Register an extension
$registry->registerExtension('supplier_tabs', 'extension_module', function($tabs) {
    // Add custom tab
    $tabs->createTab('custom', 'Custom Tab', 'custom.php');
}, 10);

// Execute hook point
$result = $registry->executeHook('supplier_tabs', $tabContainer);
```

## Module Development Pattern

### 1. Core Module Registration

Modules register their own hook points:

```php
class SupplierModule {
    public function __construct(HookManager $hookManager) {
        $this->registerHookPoints($hookManager);
    }

    private function registerHookPoints(HookManager $hookManager) {
        $hookManager->registerHookPoint(
            'supplier_tabs',
            'supplier_module',
            function(TabContainer $tabs) {
                $tabs->createTab('general', 'General', 'suppliers.php');
                return $tabs->toArray();
            }
        );
    }
}
```

### 2. Extension Module Pattern

Other modules extend existing hook points:

```php
class ProductAttributesExtension {
    public function __construct(HookManager $hookManager) {
        $this->registerExtensions($hookManager);
    }

    private function registerExtensions(HookManager $hookManager) {
        $hookManager->registerExtension(
            'supplier_tabs',
            'product_attributes',
            function(TabContainer $tabs) {
                $tabs->createTab('attributes', 'Attributes', 'supplier_attributes.php');
            },
            10
        );
    }
}
```

### 3. Hook Point Execution

Modules execute their hook points to get extended results:

```php
class SupplierModule {
    public function getTabs(): array {
        $containerFactory = $this->hookManager->getContainerFactory();
        $tabs = $containerFactory->createTabContainer();

        return $this->hookManager->executeHookPoint('supplier_tabs', $tabs);
    }
}
```

## Integration with FrontAccounting

### Core FA File Modifications

Minimal changes required in core FA files:

```php
// In suppliers.php
$hooks = new HookManager();
$supplierModule = new SupplierModule($hooks);
$productAttributes = new ProductAttributesExtension($hooks);

// Get extended tabs
$tabs = $supplierModule->getTabs();
```

### Module Hook Registration

Each module's `hooks.php` file:

```php
// hooks.php
$hooks = new HookManager();

// Register module hook points
$supplierModule = new SupplierModule($hooks);

// Register extensions from other modules
$attributesExtension = new ProductAttributesExtension($hooks);
$crmExtension = new CRMExtension($hooks);
```

## Benefits

1. **Scalability**: Easy to add new modules and extensions
2. **Version Abstraction**: Automatic handling of FA version differences
3. **Decoupling**: Modules don't need to know about each other
4. **Priority System**: Control execution order of extensions
5. **Type Safety**: Container classes enforce correct data structures

## Migration from Previous System

The extended system is backward compatible. Existing hook usage continues to work:

```php
// Old way still works
$hooks->add_hook('item_display_tab_headers', 'callback_function');

// New way for advanced features
$hooks->registerHookPoint('custom_hook', 'module_name', $handler);
$hooks->registerExtension('existing_hook', 'extension_name', $extension);
```

## Testing

Run the test suite:

```bash
cd fa-hooks
php vendor/bin/phpunit tests/ContainerTest.php
```

Or run the manual validation:

```bash
php test_containers.php
```

## Examples

See `examples/ModuleIntegrationExample.php` for complete usage examples.