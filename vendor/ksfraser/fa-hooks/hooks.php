<?php

// FrontAccounting hooks file for the 0FA-Hooks module.
// When installed under FA as modules/0fa-hooks, this registers the module.

// Define security constants for access control (future use)
define('SS_FAHOOKS', 113 << 8);  // Security Section for FA Hooks
define('SA_FAHOOKS', 114 << 8);    // Security Area for FA Hooks

class hooks_0fa_hooks extends hooks
{
    var $module_name = '0FA Hooks';

    // Access level for admin screens (future use)
    const ACCESS_LEVEL = SA_FAHOOKS;

    function install()
    {
        global $path_to_root;

        // Note: Composer dependencies should be installed manually before module installation
        // The composer install is disabled to avoid parse errors during FA module installation
        $module_path = $path_to_root . '/modules/0fa-hooks';

        // Check if vendor directory exists (composer already run)
        if (!is_dir($module_path . '/vendor')) {
            // Log that composer needs to be run manually
            error_log('0FA-Hooks: Please run "composer install" in the module directory before activating.');
            // Continue without failing - hook system can work without composer for basic functionality
        }

        // FA-Hooks is a library module - no database setup needed
        // It provides the hook system for other modules to use
        return true;
    }

    function install_access()
    {
        // No special access requirements - this is a library module
        return true;
        //FUTURE: Require admin access for potential future admin screens
        //return self::ACCESS_LEVEL;

    }

    function activate()
    {
        // Ensure the hook system is available globally
        global $path_to_root;

        $hooks_path = $path_to_root . '/modules/0fa-hooks/src/Ksfraser/FA_Hooks/HookManager.php';
        if (file_exists($hooks_path)) {
            // Initialize global hook manager if not already done
            if (!isset($GLOBALS['fa_hooks_manager'])) {
                require_once $hooks_path;
                $GLOBALS['fa_hooks_manager'] = new Ksfraser\FA_Hooks\HookManager();
            }
        }

        return true;
    }

    function deactivate()
    {
        // Clean up global reference
        if (isset($GLOBALS['fa_hooks_manager'])) {
            unset($GLOBALS['fa_hooks_manager']);
        }

        return true;
    }
}