<?php

namespace Ksfraser\FA_Hooks;

/**
 * Container for managing menu items
 *
 * Handles FA menu structures across different versions and contexts
 * (main menu, submenus, etc.)
 */
class MenuContainer extends ArrayContainer
{
    /**
     * Add a menu item
     *
     * @param string $key Menu item key
     * @param array $item Menu item definition ['title', 'url', 'access', 'icon', etc.]
     * @return self
     */
    public function addItem(string $key, $item): self
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Menu item must be an array');
        }

        $this->items[$key] = $item;
        return $this;
    }

    /**
     * Create and add a menu item
     *
     * @param string $key
     * @param string $title
     * @param string $url
     * @param array $options Additional options
     * @return self
     */
    public function createMenuItem(string $key, string $title, string $url, array $options = []): self
    {
        $item = array_merge([
            'title' => $title,
            'url' => $url,
        ], $options);

        return $this->addItem($key, $item);
    }

    /**
     * Convert to array format appropriate for FA version
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $key => $item) {
            if ($this->versionAdapter->isVersion23OrLower()) {
                // FA 2.3 menu format
                $result[$key] = $item;
            } elseif ($this->versionAdapter->isVersion24OrHigher()) {
                // FA 2.4+ menu format (potentially different structure)
                $result[$key] = $item;
                // Could add version-specific transformations here
            }
        }

        return $result;
    }
}