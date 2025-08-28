<?php
declare(strict_types = 1);
namespace RCS\WP\Validation;

/**
 */
abstract class BaseValidator
{
    /**
     * Creates an instance of the class and associates the specified setting
     * and key with the property of this class.
     *
     * @param    string    $setting    The title of the setting we're validating.
     * @param    string    $key        The key for the field we're validating.
     * @param    string    $fieldName  The name of the field we're validating. Defaults to ''.
     */
    public function __construct(
        private string $setting,
        private string $key,
        private string $fieldName= ''
        )
    {
    }

    /**
     * Determines if the specified input is valid.
     *
     * @param    string    $input    The string
     * @return   bool                True if the input is valid; otherwise, false
     */
    abstract public function isValid(string $input): bool;

    /**
     * Adds an error message to WordPress' error collection to be displayed in the dashboard.
     *
     * @access   protected
     *
     * @param    string    $message    The message to display in the dashboard
     */
    public function addError(string $message): void {
        if (!empty($this->fieldName)) {
            $message = $this->fieldName . ': ' . $message;
        }

        add_settings_error(
            $this->setting,
            $this->key,
            $message,
            'error'
            );
    }
}
