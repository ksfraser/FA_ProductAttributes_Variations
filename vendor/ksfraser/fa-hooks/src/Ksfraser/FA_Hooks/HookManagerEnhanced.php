<?php

namespace Ksfraser\FA_Hooks;

use Ksfraser\FA_Hooks\Event\Event;
use Ksfraser\FA_Hooks\Event\FilterEvent;

/**
 * Advanced Hook System for FrontAccounting Module Extensions
 *
 * Provides a comprehensive hook system incorporating patterns from:
 * - WordPress: Actions and Filters with priority system
 * - SuiteCRM: Context-aware hooks and structured arrays
 * - Symfony: Event objects with propagation control
 *
 * Supports multiple hook types:
 * - Actions: Execute callbacks without modifying data
 * - Filters: Modify and return data through callback chain
 * - Events: Object-based events with propagation control
 * - Context Hooks: Application, module, and user-specific hooks
 */
class HookManager
{
    /** @var array<string, array<array{callback: callable, priority: int, context?: string}>> */
    private $hooks = [];

    /** @var array<string, array<array{callback: callable, priority: int, context?: string}>> */
    private $filters = [];

    /** @var array<string, mixed> */
    private $hookData = [];

    /** @var array<string, array<string, mixed>> */
    private $contextHooks = [];

    /** @var bool */
    private $performanceMonitoring = false;

    /** @var array<string, array<string, mixed>> */
    private $performanceStats = [];

    /**
     * Add a callback to an action hook (WordPress-style)
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback function
     * @param int $priority Priority order (lower numbers execute first)
     * @param int $accepted_args Number of arguments the callback accepts
     */
    public function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->add_hook($hook_name, $callback, $priority, 'action');
    }

    /**
     * Add a callback to a filter hook (WordPress-style)
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback function
     * @param int $priority Priority order (lower numbers execute first)
     * @param int $accepted_args Number of arguments the callback accepts
     */
    public function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->add_hook($hook_name, $callback, $priority, 'filter');
    }

    /**
     * Add a callback to a hook
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback function
     * @param int $priority Priority order (lower numbers execute first)
     * @param string $type Hook type: 'action', 'filter', or 'event'
     * @param string|null $context Optional context (application, module, user)
     */
    public function add_hook(string $hook_name, callable $callback, int $priority = 10, string $type = 'action', ?string $context = null): void
    {
        $this->validateHookName($hook_name);
        $this->validateCallback($callback);

        $hookArray = $type === 'filter' ? &$this->filters : &$this->hooks;

        if (!isset($hookArray[$hook_name])) {
            $hookArray[$hook_name] = [];
        }

        $hookArray[$hook_name][] = [
            'callback' => $callback,
            'priority' => $priority,
            'type' => $type,
            'context' => $context,
            'accepted_args' => 1
        ];

        // Sort by priority
        usort($hookArray[$hook_name], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        // Add to context hooks if context specified
        if ($context) {
            $this->addContextHook($hook_name, $callback, $priority, $type, $context);
        }
    }

    /**
     * Execute an action hook (WordPress-style)
     *
     * @param string $hook_name The name of the hook
     * @param mixed ...$args Arguments to pass to callbacks
     */
    public function do_action(string $hook_name, ...$args): void
    {
        $this->call_hook($hook_name, ...$args);
    }

    /**
     * Execute a filter hook (WordPress-style)
     *
     * @param string $hook_name The name of the hook
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed The filtered value
     */
    public function apply_filters(string $hook_name, $value, ...$args)
    {
        array_unshift($args, $value);
        return $this->call_hook($hook_name, ...$args);
    }

    /**
     * Dispatch an event (Symfony-style)
     *
     * @param Event $event The event to dispatch
     * @param string|null $eventName Optional event name override
     * @return Event The dispatched event
     */
    public function dispatch(Event $event, ?string $eventName = null): Event
    {
        $name = $eventName ?? get_class($event);

        if ($this->performanceMonitoring) {
            $startTime = microtime(true);
        }

        foreach ($this->getHooksByType($name, 'event') as $hook) {
            if ($event->isPropagationStopped()) {
                break;
            }

            try {
                call_user_func($hook['callback'], $event, $name, $this);
            } catch (\Exception $e) {
                $this->handleHookError($name, $e);
            }
        }

        if ($this->performanceMonitoring) {
            $this->recordPerformanceStats($name, microtime(true) - $startTime, 'event');
        }

        return $event;
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
        $startTime = $this->performanceMonitoring ? microtime(true) : 0;

        $value = isset($args[0]) ? $args[0] : null;
        $isFilter = isset($this->filters[$hook_name]);

        $hooks = $isFilter ? $this->filters[$hook_name] : $this->hooks[$hook_name];

        if (!isset($hooks)) {
            if ($this->performanceMonitoring) {
                $this->recordPerformanceStats($hook_name, microtime(true) - $startTime, $isFilter ? 'filter' : 'action');
            }
            return $value;
        }

        foreach ($hooks as $hook) {
            try {
                $callback = $hook['callback'];
                $result = call_user_func_array($callback, $args);

                // For filters, chain the return value
                if ($isFilter && $result !== null) {
                    $value = $result;
                    $args[0] = $value;
                }
            } catch (\Exception $e) {
                $this->handleHookError($hook_name, $e);
            }
        }

        if ($this->performanceMonitoring) {
            $this->recordPerformanceStats($hook_name, microtime(true) - $startTime, $isFilter ? 'filter' : 'action');
        }

        return $value;
    }

    /**
     * Add a SuiteCRM-style logic hook
     *
     * @param string $event The event name
     * @param array $hookDefinition Hook definition array [priority, label, file, class, method]
     * @param string $context The hook context (application, module, user)
     */
    public function add_logic_hook(string $event, array $hookDefinition, string $context = 'module'): void
    {
        if (count($hookDefinition) < 5) {
            throw new \InvalidArgumentException('Logic hook definition must have 5 elements');
        }

        [$priority, $label, $file, $class, $method] = $hookDefinition;

        // Load the class if file is specified
        if ($file && file_exists($file)) {
            require_once $file;
        }

        if (!class_exists($class)) {
            throw new \RuntimeException("Hook class '$class' not found");
        }

        $callback = [new $class(), $method];

        if (!is_callable($callback)) {
            throw new \RuntimeException("Hook method '$class::$method' is not callable");
        }

        $this->add_hook($event, $callback, $priority, 'logic', $context);
    }

    /**
     * Remove a callback from a hook
     *
     * @param string $hook_name The name of the hook
     * @param callable $callback The callback to remove
     * @param string $type Hook type: 'action', 'filter', or 'event'
     */
    public function remove_hook(string $hook_name, callable $callback, string $type = 'action'): void
    {
        $hookArray = $type === 'filter' ? &$this->filters : &$this->hooks;

        if (!isset($hookArray[$hook_name])) {
            return;
        }

        foreach ($hookArray[$hook_name] as $key => $hook) {
            if ($this->callbacksEqual($hook['callback'], $callback)) {
                unset($hookArray[$hook_name][$key]);
                break;
            }
        }

        // Reindex array
        if (isset($hookArray[$hook_name])) {
            $hookArray[$hook_name] = array_values($hookArray[$hook_name]);
        }
    }

    /**
     * Remove action hook (WordPress-style)
     */
    public function remove_action(string $hook_name, callable $callback, int $priority = 10): void
    {
        $this->remove_hook($hook_name, $callback, 'action');
    }

    /**
     * Remove filter hook (WordPress-style)
     */
    public function remove_filter(string $hook_name, callable $callback, int $priority = 10): void
    {
        $this->remove_hook($hook_name, $callback, 'filter');
    }

    /**
     * Check if a hook has callbacks
     */
    public function has_hook(string $hook_name, string $type = 'action'): bool
    {
        $hookArray = $type === 'filter' ? $this->filters : $this->hooks;
        return isset($hookArray[$hook_name]) && !empty($hookArray[$hook_name]);
    }

    /**
     * Check for action hook (WordPress-style)
     */
    public function has_action(string $hook_name): bool
    {
        return $this->has_hook($hook_name, 'action');
    }

    /**
     * Check for filter hook (WordPress-style)
     */
    public function has_filter(string $hook_name): bool
    {
        return $this->has_hook($hook_name, 'filter');
    }

    /**
     * Get all registered hooks
     */
    public function get_hooks(string $type = 'all'): array
    {
        switch ($type) {
            case 'action':
                return $this->hooks;
            case 'filter':
                return $this->filters;
            case 'all':
            default:
                return array_merge($this->hooks, $this->filters);
        }
    }

    /**
     * Get hooks by context
     */
    public function get_hooks_by_context(string $context): array
    {
        return $this->contextHooks[$context] ?? [];
    }

    /**
     * Enable performance monitoring
     */
    public function enablePerformanceMonitoring(): void
    {
        $this->performanceMonitoring = true;
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array
    {
        return $this->performanceStats;
    }

    /**
     * Store data for hooks to access
     */
    public function set_hook_data(string $key, $value): void
    {
        $this->hookData[$key] = $value;
    }

    /**
     * Get stored hook data
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
        $this->filters = [];
        $this->hookData = [];
        $this->contextHooks = [];
        $this->performanceStats = [];
    }

    /**
     * Validate hook name
     */
    private function validateHookName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('Invalid hook name: ' . $name);
        }
    }

    /**
     * Validate callback
     */
    private function validateCallback(callable $callback): void
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback is not callable');
        }
    }

    /**
     * Add hook to context registry
     */
    private function addContextHook(string $hook_name, callable $callback, int $priority, string $type, string $context): void
    {
        if (!isset($this->contextHooks[$context])) {
            $this->contextHooks[$context] = [];
        }

        $this->contextHooks[$context][$hook_name] = [
            'callback' => $callback,
            'priority' => $priority,
            'type' => $type
        ];
    }

    /**
     * Get hooks by type
     */
    private function getHooksByType(string $name, string $type): array
    {
        $hookArray = $type === 'filter' ? $this->filters : $this->hooks;
        return $hookArray[$name] ?? [];
    }

    /**
     * Handle hook execution errors
     */
    private function handleHookError(string $hookName, \Exception $e): void
    {
        error_log("HookManager: Error executing hook '$hookName': " . $e->getMessage());
        // Could implement more sophisticated error handling here
    }

    /**
     * Record performance statistics
     */
    private function recordPerformanceStats(string $hookName, float $executionTime, string $type): void
    {
        if (!isset($this->performanceStats[$hookName])) {
            $this->performanceStats[$hookName] = [
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'max_time' => 0,
                'type' => $type
            ];
        }

        $stats = &$this->performanceStats[$hookName];
        $stats['count']++;
        $stats['total_time'] += $executionTime;
        $stats['avg_time'] = $stats['total_time'] / $stats['count'];
        $stats['max_time'] = max($stats['max_time'], $executionTime);
    }

    /**
     * Add an event subscriber (Symfony-style)
     *
     * @param EventSubscriberInterface $subscriber The event subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->add_hook($eventName, [$subscriber, $params], 10, 'event');
            } elseif (is_array($params)) {
                $method = $params[0];
                $priority = $params[1] ?? 10;
                $this->add_hook($eventName, [$subscriber, $method], $priority, 'event');
            }
        }
    }

    /**
     * Remove an event subscriber
     *
     * @param EventSubscriberInterface $subscriber The event subscriber to remove
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->remove_hook($eventName, [$subscriber, $params], 'event');
            } elseif (is_array($params)) {
                $method = $params[0];
                $this->remove_hook($eventName, [$subscriber, $method], 'event');
            }
        }
    }