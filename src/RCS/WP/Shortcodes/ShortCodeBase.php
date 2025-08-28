<?php
declare(strict_types=1);
namespace RCS\WP\Shortcodes;

use RCS\WP\PluginInfo;
use RCS\Traits\SingletonTrait;

abstract class ShortcodeBase implements ShortcodeImplInf
{
    use SingletonTrait;
    use ShortcodeImplTrait;

    protected string $shortcodeTag;

    protected PluginInfo $pluginInfo;

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
                    foreach($this->getScripts() as $scriptMeta) {
                        wp_enqueue_script(
                            $scriptMeta->id,
                            $scriptMeta->url,
                            $scriptMeta->deps,
                            $this->pluginInfo->version,
                            [
                                'strategy' => $scriptMeta->strategy   // Note: Using defer breaks the map at the bottom of the pages
                            ]
                            );
                    }
                }

                foreach ($this->getStyles() as $styleMeta) {
                    wp_enqueue_style(
                        $styleMeta->id,
                        $styleMeta->url,
                        $styleMeta->deps,
                        $this->pluginInfo->version,
                        );
                }

                return $this->renderShortcode($atts, $content);
        }
        );
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getScripts()
     */
    public function getScripts(): array
    {
        return [];
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getStyles()
     */
    public function getStyles(): array
    {
        return [];
    }
}
