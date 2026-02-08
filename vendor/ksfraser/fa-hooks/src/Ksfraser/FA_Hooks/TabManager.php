<?php

namespace Ksfraser\FA_Hooks;

/**
 * Tab Manager for FrontAccounting
 *
 * Provides a high-level API for working with tab collections
 * and handles version abstraction through TabDefinition and TabCollection objects.
 */
class TabManager
{
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
     * Create a new tab collection
     *
     * @return TabCollection
     */
    public function createCollection(): TabCollection
    {
        return new TabCollection($this->versionAdapter);
    }

    /**
     * Create a tab collection from an existing FA tabs array
     *
     * @param array $tabsArray
     * @return TabCollection
     */
    public function createFromArray(array $tabsArray): TabCollection
    {
        return TabCollection::fromArray($tabsArray, $this->versionAdapter);
    }

    /**
     * Create a single tab definition
     *
     * @param string $key
     * @param string $title
     * @param array $options
     * @return TabDefinition
     */
    public function createTab(string $key, string $title, array $options = []): TabDefinition
    {
        return new TabDefinition($key, $title, $options, $this->versionAdapter);
    }

    /**
     * Convert a tab collection to FA-compatible array
     *
     * @param TabCollection $collection
     * @return array
     */
    public function toFAArray(TabCollection $collection): array
    {
        return $collection->toArray();
    }

    /**
     * Merge a tab collection with an existing FA tabs array
     *
     * @param TabCollection $collection
     * @param array $existingTabs
     * @return array
     */
    public function mergeWithFAArray(TabCollection $collection, array $existingTabs): array
    {
        return $collection->mergeWith($existingTabs);
    }

    /**
     * Get the version adapter
     *
     * @return FAVersionAdapter
     */
    public function getVersionAdapter(): FAVersionAdapter
    {
        return $this->versionAdapter;
    }

    /**
     * Get the current FA version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->versionAdapter->getVersion();
    }

    /**
     * Add a tab to an existing tabs array
     *
     * @param array $tabs Existing tabs array
     * @param string $key Tab key
     * @param string $title Tab title
     * @return array Updated tabs array
     */
    public function addTab(array $tabs, string $key, string $title): array
    {
        $tabs[$key] = $title;
        return $tabs;
    }

    /**
     * Remove a tab from an existing tabs array
     *
     * @param array $tabs Existing tabs array
     * @param string $key Tab key to remove
     * @return array Updated tabs array
     */
    public function removeTab(array $tabs, string $key): array
    {
        unset($tabs[$key]);
        return $tabs;
    }

    /**
     * Get all tab keys from a tabs array
     *
     * @param array $tabs Tabs array
     * @return array Array of tab keys
     */
    public function getTabKeys(array $tabs): array
    {
        return array_keys($tabs);
    }

    /**
     * Check if a tab exists in a tabs array
     *
     * @param array $tabs Tabs array
     * @param string $key Tab key to check
     * @return bool True if tab exists
     */
    public function hasTab(array $tabs, string $key): bool
    {
        return isset($tabs[$key]);
    }
}