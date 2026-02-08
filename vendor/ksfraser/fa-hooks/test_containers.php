<?php

// Manual test script for container classes
require_once __DIR__ . '/tests/bootstrap.php';

echo "Testing Container Classes...\n\n";

try {
    $versionAdapter = new Ksfraser\FA_Hooks\FAVersionAdapter();

    // Test ArrayContainer (using TabContainer as concrete implementation)
    echo "1. Testing ArrayContainer (via TabContainer)...\n";
    $tabContainer = new Ksfraser\FA_Hooks\TabContainer($versionAdapter);
    $tab = new Ksfraser\FA_Hooks\TabDefinition('test_tab', 'Test Tab', [], $versionAdapter);
    $tabContainer->addItem('test_tab', $tab);
    $result = $tabContainer->toArray();
    assert(isset($result['test_tab']), 'TabContainer basic functionality failed');
    assert($result['test_tab']['title'] === 'Test Tab', 'TabContainer title failed');
    echo "   ✓ ArrayContainer basic functionality works\n";

    // Test mergeWith
    $existing = ['existing_tab' => ['title' => 'Existing', 'url' => 'existing.php']];
    $merged = $tabContainer->mergeWith($existing);
    assert(isset($merged['test_tab']), 'mergeWith failed to preserve new items');
    assert($merged['existing_tab']['title'] === 'Existing', 'mergeWith failed to preserve existing items');
    echo "   ✓ ArrayContainer mergeWith works\n";

    // Test TabContainer
    echo "2. Testing TabContainer...\n";
    $tabContainer = new Ksfraser\FA_Hooks\TabContainer($versionAdapter);
    $tab = new Ksfraser\FA_Hooks\TabDefinition('test_tab', 'Test Tab', [], $versionAdapter);
    $tabContainer->addItem('test_tab', $tab);
    $tabResult = $tabContainer->toArray();
    assert(isset($tabResult['test_tab']), 'TabContainer failed to add tab');
    assert(is_array($tabResult['test_tab']), 'TabContainer toArray format incorrect');
    echo "   ✓ TabContainer basic functionality works\n";

    // Test createTab
    $tabContainer->createTab('new_tab', 'New Tab', 'new.php', ['icon' => 'icon.png']);
    $tabResult2 = $tabContainer->toArray();
    assert($tabResult2['new_tab']['title'] === 'New Tab', 'createTab title failed');
    assert($tabResult2['new_tab']['url'] === 'new.php', 'createTab url failed');
    echo "   ✓ TabContainer createTab works\n";

    // Test MenuContainer
    echo "3. Testing MenuContainer...\n";
    $menuContainer = new Ksfraser\FA_Hooks\MenuContainer($versionAdapter);
    $menuItem = ['title' => 'Test Menu', 'url' => 'test.php', 'access' => 'SA_ITEM'];
    $menuContainer->addItem('test_menu', $menuItem);
    $menuResult = $menuContainer->toArray();
    assert($menuResult['test_menu']['title'] === 'Test Menu', 'MenuContainer failed');
    echo "   ✓ MenuContainer basic functionality works\n";

    // Test createMenuItem
    $menuContainer->createMenuItem('new_menu', 'New Menu', 'new.php', ['access' => 'SA_ITEM']);
    $menuResult2 = $menuContainer->toArray();
    assert($menuResult2['new_menu']['title'] === 'New Menu', 'createMenuItem failed');
    echo "   ✓ MenuContainer createMenuItem works\n";

    // Test ContainerFactory
    echo "4. Testing ContainerFactory...\n";
    $factory = new Ksfraser\FA_Hooks\ContainerFactory($versionAdapter);
    $tabContainer2 = $factory->createTabContainer();
    assert($tabContainer2 instanceof Ksfraser\FA_Hooks\TabContainer, 'Factory createTabContainer failed');
    $menuContainer2 = $factory->createMenuContainer();
    assert($menuContainer2 instanceof Ksfraser\FA_Hooks\MenuContainer, 'Factory createMenuContainer failed');
    echo "   ✓ ContainerFactory works\n";

    // Test HookRegistry
    echo "5. Testing HookRegistry...\n";
    $registry = new Ksfraser\FA_Hooks\HookRegistry($versionAdapter);
    $executed = false;
    $registry->registerHookPoint('test_hook', 'test_module', function() use (&$executed) {
        $executed = true;
        return 'result';
    });
    $hookResult = $registry->executeHook('test_hook');
    assert($executed === true, 'HookRegistry execution failed');
    assert($hookResult === 'result', 'HookRegistry result failed');
    echo "   ✓ HookRegistry basic functionality works\n";

    // Test extensions
    $executionOrder = [];
    $registry->registerHookPoint('test_hook2', 'test_module', function() use (&$executionOrder) {
        $executionOrder[] = 'default';
        return 'result';
    });
    $registry->registerExtension('test_hook2', 'ext1', function() use (&$executionOrder) {
        $executionOrder[] = 'ext1';
    }, 5);
    $registry->registerExtension('test_hook2', 'ext2', function() use (&$executionOrder) {
        $executionOrder[] = 'ext2';
    }, 10);
    $registry->executeHook('test_hook2');
    assert($executionOrder === ['ext1', 'ext2', 'default'], 'HookRegistry extensions order failed');
    echo "   ✓ HookRegistry extensions work\n";

    echo "\n✅ All manual tests passed!\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $t) {
    echo "❌ Test failed with error: " . $t->getMessage() . "\n";
    exit(1);
}