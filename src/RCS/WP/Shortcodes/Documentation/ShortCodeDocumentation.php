<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes\Documentation;

class ShortcodeDocumentation
{
    /** @var ShortcodeAttribute[] */
    private $attributes = array();

    public function __construct(
        private string $name,
        private string $description = '',
        private string $example = ''
        )
    {
    }

    public function addAttribute(ShortcodeAttribute $attribute): void
    {
        $this->attributes[] = $attribute;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getExample(): string
    {
        return $this->example;
    }

    /**
     *
     * @return ShortcodeAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
