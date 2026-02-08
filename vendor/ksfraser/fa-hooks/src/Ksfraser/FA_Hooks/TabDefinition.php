<?php

namespace Ksfraser\FA_Hooks;

/**
 * Represents a single tab definition
 *
 * Modules create TabDefinition objects and set their properties,
 * then the object provides the correct array format for the FA version.
 */
class TabDefinition
{
    /** @var string */
    private $key;

    /** @var string */
    private $title;

    /** @var array */
    private $options;

    /** @var FAVersionAdapter */
    private $versionAdapter;

    /**
     * Constructor
     *
     * @param string $key Tab key
     * @param string $title Tab title
     * @param array $options Additional options
     * @param FAVersionAdapter $versionAdapter
     */
    public function __construct(string $key, string $title, array $options = [], FAVersionAdapter $versionAdapter = null)
    {
        $this->key = $key;
        $this->title = $title;
        $this->options = $options;
        $this->versionAdapter = $versionAdapter ?: new FAVersionAdapter();
    }

    /**
     * Set the tab key
     *
     * @param string $key
     * @return self
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Set the tab title
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set additional options
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Add a single option
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Get the tab key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the tab title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Convert to array format appropriate for the current FA version
     *
     * @return array
     */
    public function toArray(): array
    {
        if ($this->versionAdapter->isVersion23OrLower()) {
            // FA 2.3 format: simple key => title
            return [$this->key => $this->title];
        } elseif ($this->versionAdapter->isVersion24OrHigher()) {
            // FA 2.4+ format: potentially more complex
            $result = [$this->key => $this->title];

            // Add options if present
            if (!empty($this->options)) {
                $result[$this->key . '_options'] = $this->options;
            }

            return $result;
        }

        // Default fallback
        return [$this->key => $this->title];
    }

    /**
     * Create a TabDefinition from an array (reverse of toArray)
     *
     * @param array $data
     * @param FAVersionAdapter $versionAdapter
     * @return self
     */
    public static function fromArray(array $data, FAVersionAdapter $versionAdapter = null): self
    {
        $versionAdapter = $versionAdapter ?: new FAVersionAdapter();

        if ($versionAdapter->isVersion23OrLower()) {
            // FA 2.3: data is [key => title]
            $key = key($data);
            $title = current($data);
            return new self($key, $title, [], $versionAdapter);
        } elseif ($versionAdapter->isVersion24OrHigher()) {
            // FA 2.4+: data might be [key => title, key_options => [...]]
            $key = key($data);
            $title = current($data);

            $optionsKey = $key . '_options';
            $options = isset($data[$optionsKey]) ? $data[$optionsKey] : [];

            return new self($key, $title, $options, $versionAdapter);
        }

        // Default
        $key = key($data);
        $title = current($data);
        return new self($key, $title, [], $versionAdapter);
    }
}