<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes\Documentation;

class ShortcodeDocumentation
{
    /** @var string */
    private string $name;

    /** @var string */
    private string $description;

    /** @var string */
    private string $example;

    /** @var ShortcodeAttribute[] */
    private $attributes = array();

    public function __construct(string $name, string $desciption = '', string $example = '') {
        $this->name = $name;
        $this->description = $desciption;
        $this->example = $example;
    }

    public function addAttribute(ShortcodeAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getExample(): string {
        return $this->example;
    }

    /**
     *
     * @return ShortcodeAttribute[]
     */
    public function getAttributes(): array {
        return $this->attributes;
    }
}
