<?php

namespace Ksfraser\FA_Hooks\Event;

/**
 * Base Event class (Symfony-style)
 *
 * Provides basic event functionality with propagation control.
 * Inspired by Symfony's event dispatcher system.
 */
class Event
{
    /** @var bool */
    private $propagationStopped = false;

    /**
     * Stop event propagation
     *
     * Prevents any further listeners from being called for this event.
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation has been stopped
     *
     * @return bool True if propagation has been stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}