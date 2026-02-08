<?php

namespace Ksfraser\FA_Hooks\Event;

/**
 * Filter Event class
 *
 * Allows events to modify data and return filtered results.
 * Combines Symfony's event system with WordPress-style filters.
 */
class FilterEvent extends Event
{
    /** @var mixed */
    private $data;

    /** @var mixed */
    private $originalData;

    /**
     * Constructor
     *
     * @param mixed $data The data to be filtered
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->originalData = $data;
    }

    /**
     * Get the current data
     *
     * @return mixed The current data value
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the data
     *
     * @param mixed $data The new data value
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Get the original data
     *
     * @return mixed The original data value
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * Check if data has been modified
     *
     * @return bool True if data has been changed
     */
    public function isDataModified(): bool
    {
        return $this->data !== $this->originalData;
    }
}