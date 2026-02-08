<?php

namespace Ksfraser\FA_Hooks;

/**
 * Collection of tab definitions
 *
 * Manages multiple TabDefinition objects and provides methods to
 * manipulate the collection and output the complete tabs array.
 */
class TabCollection
{
    /** @var TabDefinition[] */
    private $tabs = [];

    /** @var FAVersionAdapter */
    private $versionAdapter;

    /**
     * Constructor
     *
     * @param FAVersionAdapter $versionAdapter
     */
    public function __construct(FAVersionAdapter $versionAdapter = null)
    {
        $this->versionAdapter = $versionAdapter ?: new FAVersionAdapter();
    }

    /**
     * Add a tab definition to the collection
     *
     * @param TabDefinition $tabDefinition
     * @return self
     */
    public function addTab(TabDefinition $tabDefinition): self
    {
        $this->tabs[$tabDefinition->getKey()] = $tabDefinition;
        return $this;
    }

    /**
     * Create and add a tab in one call
     *
     * @param string $key
     * @param string $title
     * @param array $options
     * @return self
     */
    public function createTab(string $key, string $title, array $options = []): self
    {
        $tab = new TabDefinition($key, $title, $options, $this->versionAdapter);
        return $this->addTab($tab);
    }

    /**
     * Remove a tab by key
     *
     * @param string $key
     * @return self
     */
    public function removeTab(string $key): self
    {
        unset($this->tabs[$key]);
        return $this;
    }

    /**
     * Get a tab definition by key
     *
     * @param string $key
     * @return TabDefinition|null
     */
    public function getTab(string $key): ?TabDefinition
    {
        return $this->tabs[$key] ?? null;
    }

    /**
     * Check if a tab exists
     *
     * @param string $key
     * @return bool
     */
    public function hasTab(string $key): bool
    {
        return isset($this->tabs[$key]);
    }

    /**
     * Get all tab keys
     *
     * @return array
     */
    public function getTabKeys(): array
    {
        return array_keys($this->tabs);
    }

    /**
     * Get all tab definitions
     *
     * @return TabDefinition[]
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * Convert the entire collection to array format appropriate for FA version
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->tabs as $tab) {
            $result = array_merge($result, $tab->toArray());
        }

        return $result;
    }

    /**
     * Merge with an existing FA tabs array
     *
     * @param array $existingTabs
     * @return array
     */
    public function mergeWith(array $existingTabs): array
    {
        return array_merge($existingTabs, $this->toArray());
    }

    /**
     * Create a TabCollection from an existing FA tabs array
     *
     * @param array $tabsArray
     * @param FAVersionAdapter $versionAdapter
     * @return self
     */
    public static function fromArray(array $tabsArray, FAVersionAdapter $versionAdapter = null): self
    {
        $versionAdapter = $versionAdapter ?: new FAVersionAdapter();
        $collection = new self($versionAdapter);

        // Parse the FA tabs array into TabDefinition objects
        // This is complex because we need to group related keys
        $processedKeys = [];

        foreach ($tabsArray as $key => $value) {
            if (in_array($key, $processedKeys)) {
                continue;
            }

            if (is_string($value)) {
                // This is a tab title
                $optionsKey = $key . '_options';
                $options = isset($tabsArray[$optionsKey]) ? $tabsArray[$optionsKey] : [];

                $tab = new TabDefinition($key, $value, $options, $versionAdapter);
                $collection->addTab($tab);

                // Mark keys as processed
                $processedKeys[] = $key;
                if (isset($tabsArray[$optionsKey])) {
                    $processedKeys[] = $optionsKey;
                }
            }
        }

        return $collection;
    }

    /**
     * Get the count of tabs
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->tabs);
    }

    /**
     * Clear all tabs
     *
     * @return self
     */
    public function clear(): self
    {
        $this->tabs = [];
        return $this;
    }
}