<?php

namespace Ksfraser\FA_Hooks\Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\FA_Hooks\HookManager;

class HookManagerTest extends TestCase
{
    private HookManager $hookManager;

    protected function setUp(): void
    {
        $this->hookManager = new HookManager();
    }

    protected function tearDown(): void
    {
        $this->hookManager->clear();
    }

    public function testAddHook(): void
    {
        $callback = function() { return 'test'; };
        $this->hookManager->add_hook('test_hook', $callback);

        $this->assertTrue($this->hookManager->has_hook('test_hook'));
    }

    public function testCallHookWithoutCallbacks(): void
    {
        $result = $this->hookManager->call_hook('nonexistent_hook', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testCallHookWithSingleCallback(): void
    {
        $callback = function($value) { return strtoupper($value); };
        $this->hookManager->add_hook('test_hook', $callback);

        $result = $this->hookManager->call_hook('test_hook', 'hello');
        $this->assertEquals('HELLO', $result);
    }

    public function testCallHookWithMultipleCallbacks(): void
    {
        $callback1 = function($value) { return $value . ' world'; };
        $callback2 = function($value) { return strtoupper($value); };

        $this->hookManager->add_hook('test_hook', $callback1, 10);
        $this->hookManager->add_hook('test_hook', $callback2, 20);

        $result = $this->hookManager->call_hook('test_hook', 'hello');
        $this->assertEquals('HELLO WORLD', $result);
    }

    public function testHookPriority(): void
    {
        $results = [];

        $callback1 = function($value) use (&$results) { $results[] = 'first'; return $value; };
        $callback2 = function($value) use (&$results) { $results[] = 'second'; return $value; };

        $this->hookManager->add_hook('test_hook', $callback2, 20); // Higher priority (executes later)
        $this->hookManager->add_hook('test_hook', $callback1, 10); // Lower priority (executes first)

        $this->hookManager->call_hook('test_hook', 'test');

        $this->assertEquals(['first', 'second'], $results);
    }

    public function testRemoveHook(): void
    {
        $callback = function() { return 'test'; };
        $this->hookManager->add_hook('test_hook', $callback);
        $this->assertTrue($this->hookManager->has_hook('test_hook'));

        $this->hookManager->remove_hook('test_hook', $callback);
        $this->assertFalse($this->hookManager->has_hook('test_hook'));
    }

    public function testHookDataStorage(): void
    {
        $this->hookManager->set_hook_data('test_key', 'test_value');
        $this->assertEquals('test_value', $this->hookManager->get_hook_data('test_key'));

        $this->assertEquals('default', $this->hookManager->get_hook_data('nonexistent_key', 'default'));
    }

    public function testExceptionHandlingInHook(): void
    {
        $callback1 = function($value) { return $value . ' success'; };
        $callback2 = function($value) { throw new \Exception('Test exception'); };
        $callback3 = function($value) { return $value . ' after_exception'; };

        $this->hookManager->add_hook('test_hook', $callback1);
        $this->hookManager->add_hook('test_hook', $callback2);
        $this->hookManager->add_hook('test_hook', $callback3);

        // Should continue executing after exception
        $result = $this->hookManager->call_hook('test_hook', 'start');
        $this->assertEquals('start after_exception', $result);
    }

    public function testGetHooks(): void
    {
        $callback = function() {};
        $this->hookManager->add_hook('test_hook', $callback);

        $hooks = $this->hookManager->get_hooks();
        $this->assertArrayHasKey('test_hook', $hooks);
        $this->assertCount(1, $hooks['test_hook']);
    }

    public function testClear(): void
    {
        $callback = function() {};
        $this->hookManager->add_hook('test_hook', $callback);
        $this->hookManager->set_hook_data('test_key', 'test_value');

        $this->assertTrue($this->hookManager->has_hook('test_hook'));
        $this->assertEquals('test_value', $this->hookManager->get_hook_data('test_key'));

        $this->hookManager->clear();

        $this->assertFalse($this->hookManager->has_hook('test_hook'));
        $this->assertNull($this->hookManager->get_hook_data('test_key'));
    }
}