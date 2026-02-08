<?php

namespace Ksfraser\FA_Hooks;

/**
 * Simple Hook System for FrontAccounting Module Extensions
 *
 * Provides a lightweight hook system similar to WordPress/SuiteCRM for extending
 * core FA functionality without modifying core files extensively.
 *
 * Usage:
 * 1. In core FA file (e.g., items.php): Add $hooks->call_hook('hook_name', $params)
 * 2. In module hooks.php: Register callbacks with $hooks->add_hook('hook_name', 'callback_function')
 * 3. Module implements callback functions to modify/extend functionality
 */
class HookManager
{
    /** @var array<string, callable[]> */
    private $hooks = [];

    /** @var array<string, mixed> */
    private $hookData = [];

    /** @var TabManager */
    private $tabManager;

    /** @var FAVersionAdapter */
    private $versionAdapter;

    /** @var HookRegistry */
    private $hookRegistry;

    /** @var ContainerFactory */
    private $containerFactory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->versionAdapter = new FAVersionAdapter();
        $this->tabManager = new TabManager($this->versionAdapter);
        $this->hookRegistry = new HookRegistry($this->versionAdapter);
        $this->containerFactory = new ContainerFactory($this->versionAdapter);
    }
    public function add_hook(string $hook_name, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hook_name])) {
            $this->hooks[$hook_name] = [];
        }

        $this->hooks[$hook_name][] = [
            'callback' => $callback,
            'priority' => $priority
        ];

        // Sort by priority
        usort($this->hooks[$hook_name], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Remove a callback from a hook
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback to remove
     */
    public function remove_hook(string $hook_name, callable $callback): void
    {
        if (!isset($this->hooks[$hook_name])) {
            return;
        }

        foreach ($this->hooks[$hook_name] as $key => $hook) {
            if ($hook['callback'] === $callback) {
                unset($this->hooks[$hook_name][$key]);
                break;
            }
        }
    }

    /**
     * Execute all callbacks for a hook
     *
     * @param string $hook_name The name of the hook
     * @param mixed ...$args Arguments to pass to callbacks
     * @return mixed The filtered value or void
     */
    public function call_hook(string $hook_name, ...$args)
    {
        // Special handling for tab-related hooks
        if ($hook_name === 'item_display_tab_headers') {
            return $this->handleTabHeadersHook($args);
        }

        if ($hook_name === 'item_display_tab_content') {
            return $this->handleTabContentHook($args);
        }

        // Standard hook processing
        if (!isset($this->hooks[$hook_name])) {
            return isset($args[0]) ? $args[0] : null;
        }

        $value = isset($args[0]) ? $args[0] : null;
        $originalValue = $value;

        foreach ($this->hooks[$hook_name] as $hook) {
            $callback = $hook['callback'];

            try {
                if (is_callable($callback)) {
                    $result = call_user_func_array($callback, $args);
                    // If this is a filter hook, use the return value
                    if ($result !== null) {
                        $value = $result;
                        $args[0] = $value; // Update the first argument for next callback
                    }
                }
            } catch (\Exception $e) {
                error_log("HookManager: Error executing hook '$hook_name': " . $e->getMessage());
                // On exception, subsequent callbacks get the original unmodified value
                $args[0] = $originalValue;
            }
        }

        return $value;
    }

    /**
     * Handle tab headers hook with version abstraction
     *
     * @param array $args Hook arguments
     * @return array Modified tabs array
     */
    private function handleTabHeadersHook(array $args): array
    {
        $tabs = $args[0] ?? [];
        $stockId = $args[1] ?? '';

        // Create a TabCollection from the existing FA tabs array
        $collection = TabCollection::fromArray($tabs, $this->versionAdapter);

        // Execute registered callbacks
        if (isset($this->hooks['item_display_tab_headers'])) {
            foreach ($this->hooks['item_display_tab_headers'] as $hook) {
                $callback = $hook['callback'];

                try {
                    if (is_callable($callback)) {
                        $result = call_user_func($callback, $collection, $stockId);
                        if ($result instanceof TabCollection) {
                            $collection = $result;
                        }
                    }
                } catch (\Exception $e) {
                    error_log("HookManager: Error executing tab headers hook: " . $e->getMessage());
                }
            }
        }

        // Convert back to FA format
        return $collection->toArray();
    }

    /**
     * Handle tab content hook
     *
     * @param array $args Hook arguments
     * @return string Tab content or empty string
     */
    private function handleTabContentHook(array $args): string
    {
        $content = $args[0] ?? '';
        $stockId = $args[1] ?? '';
        $selectedTab = $args[2] ?? '';

        // Execute registered callbacks
        if (isset($this->hooks['item_display_tab_content'])) {
            foreach ($this->hooks['item_display_tab_content'] as $hook) {
                $callback = $hook['callback'];

                try {
                    if (is_callable($callback)) {
                        $result = call_user_func($callback, $content, $stockId, $selectedTab);
                        if (!empty($result) && is_string($result)) {
                            return $result; // Return first non-empty content
                        }
                    }
                } catch (\Exception $e) {
                    error_log("HookManager: Error executing tab content hook: " . $e->getMessage());
                }
            }
        }

        return $content;
    }

    /**
     * Check if a hook has callbacks
     *
     * @param string $hook_name The name of the hook
     * @return bool True if hook has callbacks
     */
    public function has_hook(string $hook_name): bool
    {
        return isset($this->hooks[$hook_name]) && !empty($this->hooks[$hook_name]);
    }

    /**
     * Get all registered hooks
     *
     * @return array<string, callable[]>
     */
    public function get_hooks(): array
    {
        return $this->hooks;
    }

    /**
     * Store data for hooks to access
     *
     * @param string $key The data key
     * @param mixed $value The data value
     */
    public function set_hook_data(string $key, $value): void
    {
        $this->hookData[$key] = $value;
    }

    /**
     * Get stored hook data
     *
     * @param string $key The data key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The stored data or default
     */
    public function get_hook_data(string $key, $default = null)
    {
        return $this->hookData[$key] ?? $default;
    }

    /**
     * Get the hook registry for dynamic hook point management
     *
     * @return HookRegistry
     */
    public function getRegistry(): HookRegistry
    {
        return $this->hookRegistry;
    }

    /**
     * Get the container factory for creating containers
     *
     * @return ContainerFactory
     */
    public function getContainerFactory(): ContainerFactory
    {
        return $this->containerFactory;
    }

    /**
     * Register a new hook point (for module developers)
     *
     * @param string $hookName Name of the hook point
     * @param string $moduleName Module registering the hook
     * @param callable $defaultHandler Default handler function
     * @param array $metadata Additional metadata
     * @return self
     */
    public function registerHookPoint(string $hookName, string $moduleName, callable $defaultHandler, array $metadata = []): self
    {
        $this->hookRegistry->registerHookPoint($hookName, $moduleName, $defaultHandler, $metadata);
        return $this;
    }

    /**
     * Register an extension for a hook point
     *
     * @param string $hookName Hook point to extend
     * @param string $extensionModule Module providing the extension
     * @param callable $extensionHandler Extension handler function
     * @param int $priority Priority (higher numbers run later)
     * @return self
     */
    public function registerExtension(string $hookName, string $extensionModule, callable $extensionHandler, int $priority = 10): self
    {
        $this->hookRegistry->registerExtension($hookName, $extensionModule, $extensionHandler, $priority);
        return $this;
    }

    /**
     * Execute a registered hook point
     *
     * @param string $hookName Hook point to execute
     * @param mixed ...$args Arguments to pass to handlers
     * @return mixed Result from the default handler
     */
    public function executeHookPoint(string $hookName, ...$args)
    {
        return $this->hookRegistry->executeHook($hookName, ...$args);
    }

    /**
     * Clear all registered hooks (for testing)
     *
     * @return void
     */
    public function clear(): void
    {
        $this->hooks = [];
        $this->hookData = [];
    }
}