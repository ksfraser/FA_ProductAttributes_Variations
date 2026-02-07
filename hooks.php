<?php

// Load the variations plugin autoloader immediately when this hooks file is included
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
    error_log("FA_ProductAttributes_Variations: Autoloader loaded from hooks.php include: " . $autoloader);
} else {
    error_log("FA_ProductAttributes_Variations: Autoloader not found at: " . $autoloader);
}

// FrontAccounting hooks file for FA_ProductAttributes_Variations plugin.
// This plugin extends the core FA_ProductAttributes module with variation functionality.

define('SS_FA_ProductAttributes_Variations', 113 << 8);

class hooks_FA_ProductAttributes_Variations extends hooks
{
    var $module_name = 'FA_ProductAttributes_Variations';

    function install()
    {
        global $path_to_root;

        // Check if FA_ProductAttributes core module is installed
        $coreModulePath = $path_to_root . '/modules/FA_ProductAttributes';
        if (!file_exists($coreModulePath . '/hooks.php')) {
            display_error('FA_ProductAttributes core module must be installed before FA_ProductAttributes_Variations. Please install FA_ProductAttributes first.');
            return false;
        }

        // Plugin initialization is handled by the core module's plugin loader
        // No additional installation steps needed

        return true;
    }

    function install_options($app)
    {
        global $path_to_root;

        switch ($app->id) {
            case 'stock':
                $app->add_rapp_function(
                    2,
                    _('Product Variations'),
                    $path_to_root . '/modules/FA_ProductAttributes_Variations/product_variations_admin.php',
                    'SA_PRODUCTATTRIBUTES_VARIATIONS'
                );
                break;
        }
    }

    function install_access()
    {
        $security_sections[SS_FA_ProductAttributes_Variations] = _("Product Attributes Variations");
        $security_areas['SA_PRODUCTATTRIBUTES_VARIATIONS'] = array(SS_FA_ProductAttributes_Variations | 101, _("Product Attributes Variations"));
        return array($security_areas, $security_sections);
    }

    /**
     * Register hooks for the variations plugin
     * Called by the core module's plugin loader on every page load
     */
    function register_hooks() {
        global $path_to_root;

        // Verify core module is available
        $coreModulePath = $path_to_root . '/modules/FA_ProductAttributes';
        if (!file_exists($coreModulePath . '/hooks.php')) {
            error_log('FA_ProductAttributes_Variations: Core module not found, skipping hook registration');
            return;
        }

        // Include the global hook manager
        require_once $path_to_root . '/modules/FA_ProductAttributes/fa_hooks.php';

        // Get the hook manager
        $hooks = fa_hooks();

        // Register variations-specific hooks that extend the core attributes hooks
        $hooks->registerExtension('attributes_tab_content', 'product_attributes_variations', [$this, 'static_extend_attributes_tab'], 10);
        $hooks->registerExtension('attributes_save', 'product_attributes_variations', [$this, 'static_handle_variations_save'], 10);
        $hooks->registerExtension('attributes_delete', 'product_attributes_variations', [$this, 'static_handle_variations_delete'], 10);

        // Register sub-tabs for embedded interface
        $hooks->registerExtension('product_attributes_subtabs', 'product_attributes_variations', [$this, 'register_variations_subtab'], 10);

        // Register hooks for the assignments tab extensions
        $hooks->add_hook('fa_product_attributes_assignments_buttons', [$this, 'add_variations_buttons'], 10);
        $hooks->add_hook('fa_product_attributes_assignments_after_table', [$this, 'add_variations_content'], 10);

        // Register hook for handling actions in admin interface
        $hooks->add_hook('fa_product_attributes_handle_action', [$this, 'handleAdminAction'], 10);

        // Register hooks for pricing rules and bulk operations
        $hooks->add_hook('fa_product_attributes_variations_pricing_rules_apply', [$this, 'applyPricingRules'], 10);
        $hooks->add_hook('fa_product_attributes_bulk_operations_register', [$this, 'registerBulkOperations'], 10);
    }

    /**
     * Hook callback to add variations buttons to the assignments tab
     */
    public function add_variations_buttons($params) {
        $stock_id = $params['stock_id'] ?? '';

        if (empty($stock_id)) {
            return [];
        }

        // Return HTML for the Create Child Product button
        return [
            '<input type="submit" name="create_child" value="' . _("Create Child Product") . '" class="btn btn-default" />'
        ];
    }

    /**
     * Hook callback to add variations content after the assignments table
     */
    public function add_variations_content($params) {
        $stock_id = $params['stock_id'] ?? '';

        if (empty($stock_id)) {
            return [];
        }

        // Return HTML for the Generate Variations form
        $content = '<br />';
        $content .= '<form method="post" action="">';
        $content .= '<input type="hidden" name="action" value="generate_variations" />';
        $content .= '<input type="hidden" name="tab" value="assignments" />';
        $content .= '<input type="hidden" name="stock_id" value="' . htmlspecialchars($stock_id) . '" />';
        $content .= '<div style="text-align: center;">';
        $content .= '<input type="submit" name="generate" value="' . _("Generate Variations") . '" class="btn btn-default" />';
        $content .= '</div>';
        $content .= '</form>';

        return [$content];
    }

    /**
     * Static hook callback to extend the attributes tab with variations functionality
     */
    public static function static_extend_attributes_tab($content, $stock_id, $selected_tab) {
        if ($selected_tab === 'product_attributes') {
            // Add variations UI to the attributes tab
            $service = self::static_get_variations_service();
            $integration = new \Ksfraser\FA_ProductAttributes_Variations\Integration\VariationsIntegration($service);
            return $integration->extendAttributesTab($content, $stock_id);
        }
        return $content;
    }

    /**
     * Static hook callback for handling variations save
     */
    public static function static_handle_variations_save($item_data, $stock_id) {
        $service = self::static_get_variations_service();
        $handler = new \Ksfraser\FA_ProductAttributes_Variations\Handler\VariationsHandler($service);
        return $handler->handleVariationsSave($item_data, $stock_id);
    }

    /**
     * Static hook callback for handling variations delete
     */
    public static function static_handle_variations_delete($stock_id) {
        $service = self::static_get_variations_service();
        $handler = new \Ksfraser\FA_ProductAttributes_Variations\Handler\VariationsHandler($service);
        $handler->handleVariationsDelete($stock_id);
    }

    /**
     * Register variations sub-tab for embedded interface
     */
    public static function register_variations_subtab($subtabs) {
        $subtabs['variations'] = [
            'title' => 'Variations',
            'callback' => [self::class, 'render_variations_subtab']
        ];
        return $subtabs;
    }

    /**
     * Render the variations sub-tab content
     */
    public static function render_variations_subtab($stock_id, $dao) {
        $service = self::static_get_variations_service();
        $integration = new \Ksfraser\FA_ProductAttributes_Variations\Integration\VariationsIntegration($service);
        echo $integration->renderVariationsTab($stock_id);
    }

    /**
     * Get the VariationsService instance
     */
    private static function static_get_variations_service() {
        global $path_to_root;

        // Include the composer autoloader for variations
        $autoloader = $path_to_root . '/modules/FA_ProductAttributes_Variations/composer-lib/vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }

        // Get core DAO and DB from the core module
        $coreAutoloader = $path_to_root . '/modules/FA_ProductAttributes/composer-lib/vendor/autoload.php';
        if (file_exists($coreAutoloader)) {
            require_once $coreAutoloader;
        }

        $db = new \Ksfraser\FA_ProductAttributes\Db\FrontAccountingDbAdapter();
        $dao = new \Ksfraser\FA_ProductAttributes\Dao\ProductAttributesDao($db);

        // Create variations service
        $variationService = new \Ksfraser\FA_ProductAttributes_Variations\Service\VariationService($dao, $db);
        $faVariationService = new \Ksfraser\FA_ProductAttributes_Variations\Service\FrontAccountingVariationService($dao, $db, $db);

        return $faVariationService;
    }

    /**
     * Handle admin actions for the variations plugin
     */
    public function handleAdminAction(string $action, array $requestData): ?string
    {
        // Handle plugin-specific actions
        $pluginActions = ['generate_variations', 'create_child', 'update_product_types'];

        if (in_array($action, $pluginActions)) {
            try {
                $service = $this->getVariationsService();

                switch ($action) {
                    case 'generate_variations':
                        $handler = new \Ksfraser\FA_ProductAttributes_Variations\Actions\GenerateVariationsAction(
                            $service->getDao(),
                            $service->getDbAdapter()
                        );
                        return $handler->handle($requestData);

                    case 'create_child':
                        $handler = new \Ksfraser\FA_ProductAttributes_Variations\Actions\CreateChildAction(
                            $service->getDao()
                        );
                        return $handler->handle($requestData);

                    case 'update_product_types':
                        $handler = new \Ksfraser\FA_ProductAttributes_Variations\Actions\UpdateProductTypesAction(
                            $service->getDao()
                        );
                        return $handler->handle($requestData);

                    default:
                        return null;
                }
            } catch (\Exception $e) {
                return "Error handling plugin action '$action': " . $e->getMessage();
            }
        }

        // Return null to let core handle other actions
        return null;
    }

    /**
     * Get the VariationsService instance (non-static version)
     */
    private function getVariationsService() {
        return self::static_get_variations_service();
    }

    /**
     * Hook callback to apply pricing rules to variations
     * @param array $params Contains 'base_price', 'variations', 'rules'
     * @return array Modified variations with calculated prices
     */
    public function applyPricingRules($params) {
        $pricingService = new \Ksfraser\FA_ProductAttributes_Variations\Service\PricingRulesService();
        return $pricingService->applyRulesToVariations(
            $params['base_price'],
            $params['variations'],
            $params['rules']
        );
    }

    /**
     * Hook callback to register bulk operations for variations
     * @param \Ksfraser\FA_ProductAttributes\Service\BulkOperationsService $bulkService
     */
    public function registerBulkOperations($bulkService) {
        // Register variation-specific bulk operations
        $bulkService->registerOperation('generate_variations', function($products, $params) {
            $service = self::static_get_variations_service();
            $results = [];

            foreach ($products as $productId) {
                try {
                    $variations = $service->generateVariations($productId);
                    $results[] = [
                        'product_id' => $productId,
                        'variations_generated' => count($variations),
                        'success' => true
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'product_id' => $productId,
                        'error' => $e->getMessage(),
                        'success' => false
                    ];
                }
            }

            return [
                'success' => !in_array(false, array_column($results, 'success')),
                'processed' => count(array_filter($results, function($r) { return $r['success']; })),
                'failed' => count(array_filter($results, function($r) { return !$r['success']; })),
                'results' => $results
            ];
        });
    }
}