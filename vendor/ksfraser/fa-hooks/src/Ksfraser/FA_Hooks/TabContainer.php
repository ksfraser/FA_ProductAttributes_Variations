<?php

namespace Ksfraser\FA_Hooks;

/**
 * Container for managing tabs
 *
 * Handles FA tab structures across different versions and contexts
 * (item tabs, supplier tabs, customer tabs, etc.)
 */
class TabContainer extends ArrayContainer
{
    /**
     * Add a tab
     *
     * @param string $key Tab key
     * @param TabDefinition $tab Tab definition object
     * @return self
     */
    public function addItem(string $key, $tab): self
    {
        if (!$tab instanceof TabDefinition) {
            throw new \InvalidArgumentException('Tab must be a TabDefinition instance');
        }

        $this->items[$key] = $tab;
        return $this;
    }

    /**
     * Create and add a tab
     *
     * @param string $key
     * @param string $title
     * @param string $url
     * @param array $options Additional options
     * @return self
     */
    public function createTab(string $key, string $title, string $url, array $options = []): self
    {
        $options['url'] = $url; // Add url to options
        $tab = new TabDefinition($key, $title, $options, $this->versionAdapter);

        return $this->addItem($key, $tab);
    }

    /**
     * Convert to array format appropriate for FA version
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $key => $tab) {
            if ($tab instanceof TabDefinition) {
                $result[$key] = $tab->toArray();
            }
        }

        return $result;
    }
}