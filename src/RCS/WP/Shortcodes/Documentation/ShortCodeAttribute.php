<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes\Documentation;

class ShortcodeAttribute
{
    public function __construct(
        private string $name,
        private string $description,
        private bool $required = false,
        private ?string $default = null
        )
    {
        if ($required && isset($default)) {
            throw new \InvalidArgumentException('Attribute cannot be required and have a default value');
        }

        if (!$required && !isset($default)) {
            throw new \InvalidArgumentException('Attribute cannot be optional and not have a default value');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefault(): string
    {
        return $this->default ?? '';
    }
}
