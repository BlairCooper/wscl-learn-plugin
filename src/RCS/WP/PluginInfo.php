<?php
declare(strict_types=1);
namespace RCS\WP;

class PluginInfo
{
    /** @var string $entryPointFile The fully qualified file name of the plugin entry point */
    public string $entryPointFile;

    /** @var string $entryPointFile The fully qualified file name of the plugin entry point */
    public string $path;

    /** @var string $url The URL to the root of the plugin */
    public string $url;

    /** @var string $version The version number of the plugin */
    public string $version;

    /** @var string $slug The slug for the plugin, e.g. test_plugin */
    public string $slug;

    /** @var string $name The name of the the plugin, e.g. "My Test Plugin" */
    public string $name;

    /** @var string $writeDir The name of the folder where the plugin can
     *      write files. This is should be a folder with the slug of the
     *      plugin under the wp-uploads folder. */
    public string $writeDir;

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

        if (function_exists('wp_upload_dir')) {
            $this->writeDir = \wp_upload_dir()['basedir'] . '/'. $pluginName;
        } else {
            $this->writeDir = sys_get_temp_dir();
        }
    }

    public static function isPluginActive(string $pluginFile): bool
    {
        if (defined('ABSPATH')) { // Wrap in case we get invoked via unit testing
            include_once ABSPATH . 'wp-admin/includes/plugin.php';  // @phpstan-ignore includeOnce.fileNotFound
        }

        return \is_plugin_active($pluginFile);
    }
}
