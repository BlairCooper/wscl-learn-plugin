<?php
declare(strict_types = 1);
namespace WSCL\Learn\Shortcodes;

use RCS\WP\PluginInfo;
use RCS\WP\Shortcodes\ShortcodeBase;

class InsertJotFormShortcode extends ShortcodeBase
{
    protected function __construct(PluginInfo $pluginInfo)
    {
        parent::__construct($pluginInfo, 'wscl-jotform');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], $content = ''): string
    {
        $result = '';

        $attrs = shortcode_atts(array(
            'form-id' => null,
            'hide' => false
        ), $attrs);

        if (isset($attrs['form-id']) ) {
            if (!static::is_true($attrs['hide'])) {
                $result =
                    '<script type="text/javascript" src="https://form.jotform.com/jsform/' .
                    $attrs['form-id'] .
                    '"></script>';
            }
            // else use default empty string as we're "hiding" the form.
        }
        else {
            $result = '<h2>No "form-id" parameter specified for wscl-jotform shortcode</h2>';
        }

        return $result;
    }

    public static function is_true(string|int|bool $val, bool $returnNull = false): bool
    {
        $boolval = is_string($val) ?
            filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) :
            (bool) $val;

        return $boolval===null && !$returnNull ? false : $boolval;
    }
}
