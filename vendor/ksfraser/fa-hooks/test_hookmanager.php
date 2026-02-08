<?php

require 'vendor/autoload.php';

echo "Testing HookManager class loading...\n";

try {
    $hm = new Ksfraser\FA_Hooks\HookManager();
    echo "HookManager loaded successfully!\n";

    // Test basic functionality
    $hm->add_action('test_action', function() {
        echo "Action executed!\n";
    });

    $hm->do_action('test_action');
    echo "Basic functionality test passed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}