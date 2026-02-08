<?php

namespace Ksfraser\FA_Hooks;

/**
 * FrontAccounting Version Abstraction Layer
 *
 * Handles differences in FA data structures and APIs between versions
 * to provide a consistent interface for modules.
 */
class FAVersionAdapter
{
    /** @var string */
    private $faVersion;

    /**
     * Constructor
     *
     * @param string $faVersion FrontAccounting version (e.g., '2.4.19')
     */
    public function __construct(string $faVersion = null)
    {
        $this->faVersion = $faVersion ?: $this->detectFAVersion();
    }

    /**
     * Detect FrontAccounting version
     *
     * @return string
     */
    private function detectFAVersion(): string
    {
        // Try to detect from various sources
        if (defined('VERSION')) {
            return VERSION;
        }

        // Fallback detection methods
        if (function_exists('get_company_pref')) {
            // FA 2.4+ has this function
            return '2.4.x';
        }

        return '2.3.x'; // Default assumption
    }

    /**
     * Get the detected FA version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->faVersion;
    }

    /**
     * Check if version is 2.4 or higher
     *
     * @return bool
     */
    public function isVersion24OrHigher(): bool
    {
        return version_compare($this->faVersion, '2.4.0', '>=');
    }

    /**
     * Check if version is 2.3 or lower
     *
     * @return bool
     */
    public function isVersion23OrLower(): bool
    {
        return version_compare($this->faVersion, '2.3.99', '<=');
    }
}