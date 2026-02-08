<?php

/**
 * Example: How modules can use the extended hook system
 *
 * This example shows how different FA modules (Suppliers, Customers, etc.)
 * can register their own hook points and how other modules can extend them.
 */

namespace Ksfraser\FA_ProductAttributes\Examples;

use Ksfraser\FA_Hooks\HookManager;
use Ksfraser\FA_Hooks\TabContainer;
use Ksfraser\FA_Hooks\MenuContainer;

/**
 * Example: Supplier Management Module
 *
 * This module registers its own hook points that other modules can extend
 */
class SupplierModule
{
    private $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->registerHookPoints();
    }

    /**
     * Register hook points that other modules can extend
     */
    private function registerHookPoints()
    {
        // Register a hook point for supplier tabs
        $this->hookManager->registerHookPoint(
            'supplier_tabs',
            'supplier_module',
            function(TabContainer $tabs) {
                // Default supplier tabs
                $tabs->createTab('general', 'General', 'suppliers.php?tab=general');
                $tabs->createTab('contacts', 'Contacts', 'suppliers.php?tab=contacts');
                return $tabs->toArray();
            },
            ['description' => 'Hook for adding supplier detail tabs']
        );

        // Register a hook point for supplier menu items
        $this->hookManager->registerHookPoint(
            'supplier_menu',
            'supplier_module',
            function(MenuContainer $menu) {
                // Default supplier menu
                $menu->createMenuItem('suppliers', 'Suppliers', 'suppliers.php', [
                    'access' => 'SA_SUPPLIER',
                    'icon' => 'supplier.png'
                ]);
                return $menu->toArray();
            },
            ['description' => 'Hook for supplier menu items']
        );
    }

    /**
     * Get supplier tabs (executes the hook point)
     */
    public function getSupplierTabs(): array
    {
        $containerFactory = $this->hookManager->getContainerFactory();
        $tabs = $containerFactory->createTabContainer();

        return $this->hookManager->executeHookPoint('supplier_tabs', $tabs);
    }

    /**
     * Get supplier menu (executes the hook point)
     */
    public function getSupplierMenu(): array
    {
        $containerFactory = $this->hookManager->getContainerFactory();
        $menu = $containerFactory->createMenuContainer();

        return $this->hookManager->executeHookPoint('supplier_menu', $menu);
    }
}

/**
 * Example: Product Attributes Extension for Suppliers
 *
 * This module extends the supplier module's hook points
 */
class ProductAttributesSupplierExtension
{
    private $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->registerExtensions();
    }

    /**
     * Register extensions for supplier hook points
     */
    private function registerExtensions()
    {
        // Extend supplier tabs to add product attributes tab
        $this->hookManager->registerExtension(
            'supplier_tabs',
            'product_attributes',
            function(TabContainer $tabs) {
                $tabs->createTab('attributes', 'Product Attributes',
                    'suppliers.php?tab=attributes', ['icon' => 'attributes.png']);
            },
            10 // Priority
        );

        // Extend supplier menu to add attributes submenu
        $this->hookManager->registerExtension(
            'supplier_menu',
            'product_attributes',
            function(MenuContainer $menu) {
                $menu->createMenuItem('supplier_attributes', 'Supplier Attributes',
                    'supplier_attributes.php', [
                        'access' => 'SA_SUPPLIER',
                        'parent' => 'suppliers'
                    ]);
            },
            10
        );
    }
}

/**
 * Example: Customer Management Module
 */
class CustomerModule
{
    private $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->registerHookPoints();
    }

    private function registerHookPoints()
    {
        // Register customer tabs hook point
        $this->hookManager->registerHookPoint(
            'customer_tabs',
            'customer_module',
            function(TabContainer $tabs) {
                $tabs->createTab('general', 'General', 'customers.php?tab=general');
                $tabs->createTab('invoices', 'Invoices', 'customers.php?tab=invoices');
                return $tabs->toArray();
            },
            ['description' => 'Hook for customer detail tabs']
        );
    }

    public function getCustomerTabs(): array
    {
        $containerFactory = $this->hookManager->getContainerFactory();
        $tabs = $containerFactory->createTabContainer();

        return $this->hookManager->executeHookPoint('customer_tabs', $tabs);
    }
}

/**
 * Example: Third-party Module that extends both suppliers and customers
 */
class ThirdPartyIntegrationModule
{
    private $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->registerExtensions();
    }

    private function registerExtensions()
    {
        // Add CRM integration tab to suppliers
        $this->hookManager->registerExtension(
            'supplier_tabs',
            'third_party_crm',
            function(TabContainer $tabs) {
                $tabs->createTab('crm', 'CRM Integration',
                    'suppliers.php?tab=crm', ['icon' => 'crm.png']);
            },
            20
        );

        // Add CRM integration tab to customers
        $this->hookManager->registerExtension(
            'customer_tabs',
            'third_party_crm',
            function(TabContainer $tabs) {
                $tabs->createTab('crm', 'CRM Integration',
                    'customers.php?tab=crm', ['icon' => 'crm.png']);
            },
            20
        );
    }
}

/**
 * Usage Example
 */
class ModuleIntegrationExample
{
    public static function demonstrate()
    {
        $hookManager = new HookManager();

        // Initialize core modules
        $supplierModule = new SupplierModule($hookManager);
        $customerModule = new CustomerModule($hookManager);

        // Initialize extensions
        $attributesExtension = new ProductAttributesSupplierExtension($hookManager);
        $thirdPartyExtension = new ThirdPartyIntegrationModule($hookManager);

        // Get supplier tabs (will include extensions)
        $supplierTabs = $supplierModule->getSupplierTabs();
        echo "Supplier tabs: " . implode(', ', array_keys($supplierTabs)) . "\n";

        // Get customer tabs (will include extensions)
        $customerTabs = $customerModule->getCustomerTabs();
        echo "Customer tabs: " . implode(', ', array_keys($customerTabs)) . "\n";

        // Get supplier menu (will include extensions)
        $supplierMenu = $supplierModule->getSupplierMenu();
        echo "Supplier menu items: " . implode(', ', array_keys($supplierMenu)) . "\n";

        // List all registered hook points
        $registry = $hookManager->getRegistry();
        echo "Registered hook points: " . implode(', ', $registry->getHookPoints()) . "\n";
    }
}

// Uncomment to run the example:
// ModuleIntegrationExample::demonstrate();