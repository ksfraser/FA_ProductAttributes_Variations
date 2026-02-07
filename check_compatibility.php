<?php
/**
 * PHP Compatibility Check Script
 *
 * This script checks if the codebase uses PHP features that are not compatible
 * with the minimum required PHP version (7.3).
 */

$minVersion = '7.3.0';
$currentVersion = PHP_VERSION;

echo "PHP Compatibility Check\n";
echo "=======================\n";
echo "Current PHP version: $currentVersion\n";
echo "Minimum required: $minVersion\n";

if (version_compare($currentVersion, $minVersion, '<')) {
    echo "❌ ERROR: Current PHP version ($currentVersion) is below minimum required ($minVersion)\n";
    exit(1);
}

echo "✅ PHP version check passed\n";

// Check for PHP 7.4+ features that shouldn't be used
$errors = [];
$files = array_merge(
    glob(__DIR__ . '/src/**/*.php'),
    glob(__DIR__ . '/tests/**/*.php'),
    [__DIR__ . '/hooks.php']
);

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // Check for arrow functions (PHP 7.4+)
    if (strpos($content, 'fn(') !== false) {
        $errors[] = "❌ $file contains arrow functions (PHP 7.4+ feature)";
    }

    // Check for null coalescing assignment (PHP 7.4+)
    if (strpos($content, '??=') !== false) {
        $errors[] = "❌ $file contains null coalescing assignment (PHP 7.4+ feature)";
    }
}

if (empty($errors)) {
    echo "✅ No PHP 7.4+ features found - compatible with PHP $minVersion+\n";
} else {
    echo "❌ Found PHP 7.4+ features that are not compatible with PHP $minVersion:\n";
    foreach ($errors as $error) {
        echo "   $error\n";
    }
    exit(1);
}

echo "\nCompatibility check completed successfully!\n";
?>