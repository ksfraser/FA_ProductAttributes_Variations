<?php

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

        // Register hooks for the variations plugin
        $this->register_hooks();

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
     */
    function register_hooks() {
        global $path_to_root;

        // Include the global hook manager
        require_once $path_to_root . '/modules/FA_ProductAttributes/fa_hooks.php';

        // Get the hook manager
        $hooks = fa_hooks();

        // Register variations-specific hooks that extend the core attributes hooks
        $hooks->registerExtension('attributes_tab_content', 'product_attributes_variations', [$this, 'static_extend_attributes_tab'], 10);
        $hooks->registerExtension('attributes_save', 'product_attributes_variations', [$this, 'static_handle_variations_save'], 10);
        $hooks->registerExtension('attributes_delete', 'product_attributes_variations', [$this, 'static_handle_variations_delete'], 10);
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
}