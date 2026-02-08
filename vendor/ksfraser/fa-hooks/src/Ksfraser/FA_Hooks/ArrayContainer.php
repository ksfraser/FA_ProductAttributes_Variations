<?php

namespace Ksfraser\FA_Hooks;

/**
 * Base container for managing array-based data structures
 *
 * Provides common functionality for managing collections of items that
 * need to be converted to arrays for different FA contexts and versions.
 */
abstract class ArrayContainer
{
    /** @var FAVersionAdapter */
    protected $versionAdapter;

    /** @var array */
    protected $items = [];

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
     * Add an item to the container
     *
     * @param string $key
     * @param mixed $item
     * @return self
     */
    abstract public function addItem(string $key, $item): self;

    /**
     * Remove an item from the container
     *
     * @param string $key
     * @return self
     */
    public function removeItem(string $key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    /**
     * Get an item by key
     *
     * @param string $key
     * @return mixed|null
     */
    public function getItem(string $key)
    {
        return $this->items[$key] ?? null;
    }

    /**
     * Check if item exists
     *
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get item keys
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Convert to array format appropriate for FA version
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Merge with existing FA array
     *
     * @param array $existing
     * @return array
     */
    public function mergeWith(array $existing): array
    {
        return array_merge($existing, $this->toArray());
    }

    /**
     * Get count of items
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Clear all items
     *
     * @return self
     */
    public function clear(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Get the FA version adapter
     *
     * @return FAVersionAdapter
     */
    public function getVersionAdapter(): FAVersionAdapter
    {
        return $this->versionAdapter;
    }
}