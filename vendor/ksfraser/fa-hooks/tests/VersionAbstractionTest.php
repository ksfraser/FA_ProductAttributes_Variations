<?php

namespace Ksfraser\FA_Hooks\Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\FA_Hooks\FAVersionAdapter;
use Ksfraser\FA_Hooks\TabManager;

/**
 * Test version abstraction and tab management
 */
class VersionAbstractionTest extends TestCase
{
    public function testFAVersionAdapterDetectsVersion()
    {
        $adapter = new FAVersionAdapter('2.4.19');
        $this->assertEquals('2.4.19', $adapter->getVersion());
        $this->assertTrue($adapter->isVersion24OrHigher());
        $this->assertFalse($adapter->isVersion23OrLower());
    }

    public function testTabManagerAddsTabs()
    {
        $adapter = new FAVersionAdapter('2.4.19');
        $tabManager = new TabManager($adapter);

        $tabs = ['general' => 'General'];
        $result = $tabManager->addTab($tabs, 'product_attributes', 'Product Attributes');

        $this->assertArrayHasKey('product_attributes', $result);
        $this->assertEquals('Product Attributes', $result['product_attributes']);
        $this->assertArrayHasKey('general', $result);
    }

    public function testTabManagerRemovesTabs()
    {
        $adapter = new FAVersionAdapter('2.4.19');
        $tabManager = new TabManager($adapter);

        $tabs = [
            'general' => 'General',
            'product_attributes' => 'Product Attributes',
            'settings' => 'Settings'
        ];

        $result = $tabManager->removeTab($tabs, 'product_attributes');

        $this->assertArrayNotHasKey('product_attributes', $result);
        $this->assertArrayHasKey('general', $result);
        $this->assertArrayHasKey('settings', $result);
    }

    public function testTabManagerGetsTabKeys()
    {
        $adapter = new FAVersionAdapter('2.4.19');
        $tabManager = new TabManager($adapter);

        $tabs = [
            'general' => 'General',
            'product_attributes' => 'Product Attributes',
            'settings' => 'Settings'
        ];

        $keys = $tabManager->getTabKeys($tabs);

        $this->assertContains('general', $keys);
        $this->assertContains('product_attributes', $keys);
        $this->assertContains('settings', $keys);
    }

    public function testTabManagerChecksTabExistence()
    {
        $adapter = new FAVersionAdapter('2.4.19');
        $tabManager = new TabManager($adapter);

        $tabs = ['general' => 'General'];

        $this->assertTrue($tabManager->hasTab($tabs, 'general'));
        $this->assertFalse($tabManager->hasTab($tabs, 'nonexistent'));
    }

    public function testTabManagerHandlesVersionDifferences()
    {
        // Test FA 2.3 format
        $adapter23 = new FAVersionAdapter('2.3.25');
        $tabManager23 = new TabManager($adapter23);

        $tabs = ['general' => 'General'];
        $result = $tabManager23->addTab($tabs, 'test', 'Test Tab');

        $this->assertEquals('Test Tab', $result['test']);

        // Test FA 2.4 format (currently same as 2.3, but extensible)
        $adapter24 = new FAVersionAdapter('2.4.19');
        $tabManager24 = new TabManager($adapter24);

        $result24 = $tabManager24->addTab($tabs, 'test', 'Test Tab');

        $this->assertEquals('Test Tab', $result24['test']);
    }
}