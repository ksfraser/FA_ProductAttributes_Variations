<?php

namespace Ksfraser\FA_Hooks;

/**
 * Registry for managing dynamic hook points
 *
 * Allows modules to register custom hook points that other modules can extend
 */
class HookRegistry
{
    private $hookPoints = [];
    private $versionAdapter;

    public function __construct(FAVersionAdapter $versionAdapter)
    {
        $this->versionAdapter = $versionAdapter;
    }

    /**
     * Register a new hook point
     *
     * @param string $hookName Name of the hook point
     * @param string $moduleName Module registering the hook
     * @param callable $defaultHandler Default handler function
     * @param array $metadata Additional metadata
     * @return self
     */
    public function registerHookPoint(string $hookName, string $moduleName, callable $defaultHandler, array $metadata = []): self
    {
        $this->hookPoints[$hookName] = [
            'module' => $moduleName,
            'handler' => $defaultHandler,
            'metadata' => $metadata,
            'extensions' => []
        ];

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
     * @throws \InvalidArgumentException
     */
    public function registerExtension(string $hookName, string $extensionModule, callable $extensionHandler, int $priority = 10): self
    {
        if (!isset($this->hookPoints[$hookName])) {
            throw new \InvalidArgumentException("Hook point '{$hookName}' not registered");
        }

        $this->hookPoints[$hookName]['extensions'][] = [
            'module' => $extensionModule,
            'handler' => $extensionHandler,
            'priority' => $priority
        ];

        // Sort extensions by priority
        usort($this->hookPoints[$hookName]['extensions'], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $this;
    }

    /**
     * Execute a hook point with all its extensions
     *
     * @param string $hookName Hook point to execute
     * @param mixed ...$args Arguments to pass to handlers
     * @return mixed Result from the default handler
     * @throws \InvalidArgumentException
     */
    public function executeHook(string $hookName, ...$args)
    {
        if (!isset($this->hookPoints[$hookName])) {
            throw new \InvalidArgumentException("Hook point '{$hookName}' not registered");
        }

        $hookPoint = $this->hookPoints[$hookName];

        // Execute extensions first
        foreach ($hookPoint['extensions'] as $extension) {
            call_user_func($extension['handler'], ...$args);
        }

        // Execute default handler and return result
        return call_user_func($hookPoint['handler'], ...$args);
    }

    /**
     * Get all registered hook points
     *
     * @return array
     */
    public function getHookPoints(): array
    {
        return array_keys($this->hookPoints);
    }

    /**
     * Get details for a specific hook point
     *
     * @param string $hookName
     * @return array|null
     */
    public function getHookPoint(string $hookName): ?array
    {
        return $this->hookPoints[$hookName] ?? null;
    }

    /**
     * Check if a hook point is registered
     *
     * @param string $hookName
     * @return bool
     */
    public function hasHookPoint(string $hookName): bool
    {
        return isset($this->hookPoints[$hookName]);
    }
}