<?php

// Bootstrap for fa-hooks testing
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/FAVersionAdapter.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/TabDefinition.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/TabCollection.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/TabManager.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/HookManager.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/ArrayContainer.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/TabContainer.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/MenuContainer.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/ContainerFactory.php';
require_once __DIR__ . '/../src/Ksfraser/FA_Hooks/HookRegistry.php';

// Mock FA functions for testing
if (!function_exists('_')) {
    function _($text) {
        return $text;
    }
}