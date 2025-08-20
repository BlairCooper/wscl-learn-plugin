<?php
declare(strict_types=1);
namespace RCS\WP;

use RCS\WP\Traits\SingletonTrait;

abstract class PluginOptions
{
    private const DATABASE_VERSION = 'dbVersion';

    use SingletonTrait;

    private string $optionName;
    /** @var string[] */
    private array $optionKeys;

    /** @var array<string, string> */
    private array $values = array();

    abstract public function getOptionName(): string;

    /**
     * @return string[]
     */
    abstract protected function getOptionKeys(): array;

    /**
     * Initialize the collections used to maintain the values.
     *
     * @override
     */
    protected function initializeInstance(): void
    {
        $isDirty = false;

        $this->optionName = $this->getOptionName();
        $this->optionKeys = array_merge(
            [
                self::DATABASE_VERSION
            ],
            $this->getOptionKeys()
            );

        $dbValue = get_option($this->optionName);

        // Initialize the options if necessary
        if (!isset($dbValue) || !is_array($dbValue)) {
            $this->values = [];

            foreach ($this->optionKeys as $key) {
                $this->values[$key] = '';
            }
            add_option($this->optionName, $this->values);
        } else {
            $this->values = $dbValue;
        }

        // Initialize any new options missing from the values
        foreach ($this->optionKeys as $key) {
            if (!isset($this->values[$key])) {
                $this->values[$key] = '';
                $isDirty = true;
            }
        }

        // Remove any values that aren't used any more
        $oldKeys = array_diff(array_keys($this->values), $this->optionKeys);
        foreach ($oldKeys as $key) {
            unset($this->values[$key]);
            $isDirty = true;
        }

        if ($isDirty) {
            $this->save();
        }
    }

    /**
     *
     * @return array<string, string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Returns an array of values for use in HTML/Settings forms.
     *
     * [0] => The key passed in for use as a field id
     * [1] => '{optionName}[{key}]' for use as a field name
     * [2] => The field value
     *
     * @param string $key The option to retrieve the information for.$this
     *
     * @return array<string>|NULL The array of values or null if the key is invalid.
     */
    public function getFormFieldInfo(string $key): ?array
    {
        $result = null;

        if ($this->isValidKey($key)) {
            $result = array(
                $key,
                sprintf("%s[%s]", $this->optionName, $key),
                $this->values[$key] ?? ''
            );
        }

        return $result;
    }

    private function isValidKey(string $key): bool
    {
        return in_array($key, $this->optionKeys);
    }

    public function setValue(string $key, string $value): void
    {
        if ($this->isValidKey($key)) {
            $this->values[$key] = $value;
        }
    }

    public function getValue(string $key): ?string
    {
        return $this->isValidKey($key) ? $this->values[$key] : null;
    }

    protected function save(): void
    {
        update_option($this->optionName, $this->values);
    }

    /*
     * Convienence functions
     */

    public function getDatabaseVersion(): string
    {
        return $this->getValue(self::DATABASE_VERSION);
    }

    public function setDatabaseVersion(string $dbVersion): void
    {
        $this->setValue(self::DATABASE_VERSION, $dbVersion);
    }
}
