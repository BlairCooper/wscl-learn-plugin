<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes;

class StyleMeta
{
    public string $id;
    public string $url;
    /** @var string[] */
    public array $deps;

    /**
     *
     * @param string $id
     * @param string $url
     * @param string[] $deps
     */
    public function __construct(string $id, string $url, array $deps = [])
    {
        $this->id = $id;
        $this->url = $url;
        $this->deps = $deps;
    }
}
