<?php
declare(strict_types=1);
namespace RCS\WP;

class PluginInfo
{
    /** @var string $writeDir The name of the folder where the plugin can
     *      write files. This is should be a folder with the slug of the
     *      plugin under the wp-uploads folder. */
    public string $writeDir;

    /**
     *
     * @param string $entryPointFile    The fully qualified file name of the plugin entry point
     * @param string $path              The file system path to the plugin folder
     * @param string $url               The URL to the root of the plugin
     * @param string $version           The version number of the plugin
     * @param string $slug              The slug for the plugin, e.g. test_plugin
     * @param string $name              The name of the the plugin, e.g. "My Test Plugin"
     */
    public function __construct(
        public string $entryPointFile,
        public string $path,
        public string $url,
        public string $version,
        public string $slug,
        public string $name
        )
    {
        if (function_exists('wp_upload_dir')) {
            $this->writeDir = \wp_upload_dir()['basedir'] . '/'. $name;
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
