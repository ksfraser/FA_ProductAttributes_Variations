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

    /**
     * Add a callback to a hook
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback function
     * @param int $priority Priority order (lower numbers execute first)
     */
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
        if (!isset($this->hooks[$hook_name])) {
            return isset($args[0]) ? $args[0] : null;
        }

        $value = isset($args[0]) ? $args[0] : null;

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
            }
        }

        return $value;
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
     * Clear all hooks and data
     */
    public function clear(): void
    {
        $this->hooks = [];
        $this->hookData = [];
    }
}