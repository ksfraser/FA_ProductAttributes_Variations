<?php

namespace Ksfraser\FA_Hooks;

/**
 * Event Subscriber Interface (Symfony-style)
 *
 * Classes implementing this interface can declare which events they subscribe to
 * and the methods that should be called for each event.
 */
interface EventSubscriberInterface
{
    /**
     * Returns an array of events to subscribe to
     *
     * The array keys are event names and the values are the method names to call
     * or arrays containing the method name and priority.
     *
     * Example:
     * [
     *     'before_save' => 'onBeforeSave',
     *     'after_save' => ['onAfterSave', 10],
     *     'user_login' => ['onUserLogin', -10]
     * ]
     *
     * @return array<string, string|array{string, int}>
     */
    public static function getSubscribedEvents(): array;
}