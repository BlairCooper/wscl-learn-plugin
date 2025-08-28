<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes;

class ScriptMeta
{
    public string $id;
    public string $url;
    /** @var string[] */
    public array $deps;
    public string $strategy;

    /**
     *
     * @param string $id
     * @param string $url
     * @param string[] $deps
     * @param string $strategy
     */
    public function __construct(string $id, string $url, array $deps = [], string $strategy = 'async')
    {
        $this->id = $id;
        $this->url = $url;
        $this->deps = $deps;
        $this->strategy = $strategy;
    }
}
