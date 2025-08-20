<?php
declare(strict_types=1);
namespace RCS\WP\Shortcodes;

use RCS\WP\PluginInfo;
use RCS\WP\Traits\SingletonTrait;

abstract class ShortcodeBase
{
    use SingletonTrait;

    protected string $shortcodeTag;

    protected PluginInfo $pluginInfo;

    /**
     *
     * @return string[]
     */
    protected function getScriptDependencies(): array
    {
        return [];
    }

    /**
     *
     * @return string[]
     */
    protected function getStyleDependencies(): array
    {
        return [];
    }

    protected function __construct(PluginInfo $pluginInfo, string $shortcodeTag)
    {
        $this->shortcodeTag = $shortcodeTag;
        $this->pluginInfo = $pluginInfo;
    }

    protected function initializeInstance(): void
    {
        assert(!empty($this->shortcodeTag), '$shortcodeTag should be set in the constructor');

        // Register a new shortcode.
        add_shortcode(
            $this->shortcodeTag,
            function (array $atts, string $content = ''): string
            {
                // Maybe enqueue assets of the shortcode.
                if (!is_admin()) {
                    foreach($this->getScripts() as $scriptId => $scriptUrl) {
                        wp_enqueue_script(
                            $scriptId,
                            $scriptUrl,
                            $this->getScriptDependencies(),
                            $this->pluginInfo->version,
                            [
                                'strategy' => 'async'   // Note: Using defer breaks the map at the bottom of the pages
                            ]
                            );
                    }
                }

                foreach ($this->getStyles() as $styleId => $styleUrl) {
                    wp_enqueue_style(
                        $styleId,
                        $styleUrl,
                        $this->getStyleDependencies(),
                        $this->pluginInfo->version,
                        );
                }

                return $this->renderShortcode($atts, $content);
        }
        );
    }

    /**
     * Fetch the scripts to be enqueued.
     *
     * The returned array is expected to use the script identifiers as the
     * keys and the URL for the script as the values.
     *
     * @return array<string, string>
     */
    protected function getScripts(): array
    {
        return [];
    }

    /**
     * Fetch the styles to be enqueued.
     *
     * The returned array is expected to use the styles identifiers as the
     * keys and the URL for the styles as the values.
     *
     * @return array<string, string>
     */
    protected function getStyles(): array
    {
        return [];
    }

    /**
     *
     * @param array<string, mixed> $atts
     * @param string $content
     *
     * @return string The HTML for the shortcode
     */
    abstract protected function renderShortcode(array $atts, string $content = ''): string;
}
