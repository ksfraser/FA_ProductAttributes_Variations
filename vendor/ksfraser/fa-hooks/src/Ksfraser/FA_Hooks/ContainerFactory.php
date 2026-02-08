<?php

namespace Ksfraser\FA_Hooks;

/**
 * Factory for creating appropriate container instances
 *
 * Provides a centralized way to create containers for different FA contexts
 */
class ContainerFactory
{
    private $versionAdapter;

    public function __construct(FAVersionAdapter $versionAdapter)
    {
        $this->versionAdapter = $versionAdapter;
    }

    /**
     * Create a tab container
     *
     * @return TabContainer
     */
    public function createTabContainer(): TabContainer
    {
        return new TabContainer($this->versionAdapter);
    }

    /**
     * Create a menu container
     *
     * @return MenuContainer
     */
    public function createMenuContainer(): MenuContainer
    {
        return new MenuContainer($this->versionAdapter);
    }

    /**
     * Create a generic array container
     *
     * @return TabContainer Returns TabContainer as default concrete implementation
     */
    public function createArrayContainer(): TabContainer
    {
        return new TabContainer($this->versionAdapter);
    }

    /**
     * Create container by type
     *
     * @param string $type Container type ('tab', 'menu', 'array')
     * @return ArrayContainer
     * @throws \InvalidArgumentException
     */
    public function createContainer(string $type)
    {
        switch ($type) {
            case 'tab':
                return $this->createTabContainer();
            case 'menu':
                return $this->createMenuContainer();
            case 'array':
                return $this->createArrayContainer();
            default:
                throw new \InvalidArgumentException("Unknown container type: {$type}");
        }
    }
}