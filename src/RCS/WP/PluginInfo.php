<?php
declare(strict_types=1);
namespace RCS\WP;

class PluginInfo
{
    public string $entryPointFile;
    public string $path;
    public string $url;
    public string $version;
    public string $slug;
    public string $name;

    public function __construct(
        string $entryPointFile,
        string $pluginPath,
        string $pluginUrl,
        string $pluginVersion,
        string $pluginSlug,
        string $pluginName
        )
    {
        $this->entryPointFile = $entryPointFile;
        $this->path = $pluginPath;
        $this->url = $pluginUrl;
        $this->version = $pluginVersion;
        $this->slug = $pluginSlug;
        $this->name = $pluginName;
    }
}
