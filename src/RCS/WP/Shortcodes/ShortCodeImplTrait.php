<?php
declare(strict_types = 1);
namespace RCS\WP\Shortcodes;

/**
 * This trait provides a default implemementation for the ShortcodeImplInf
 * interface. Its primary use would be to avoid having to implement
 * getDocumentation() and filterAttributes() which may not be used by many
 * short codes.
 */
trait ShortcodeImplTrait
{
    /**
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getTagName()
     */
    public function getTagName(): string
    {
        return '';
    }

    /**
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::renderShortcode()
     */
    public function renderShortcode(array $attrs = [], ?string $content = null): string // NOSONAR
    {
        return '';
    }

    /**
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::getDocumentation()
     */
    public function getDocumentation(array $documentation): array // NOSONAR
    {
        return array();
    }

    /**
     * @see \RCS\WP\Shortcodes\ShortcodeImplInf::filterAttributes()
     */
    public function filterAttributes(array $combinedAtts, array $defaultPairs, array $providedAtts, string $shortcode): array // NOSONAR
    {
        return $combinedAtts;
    }

    protected function isEditPreviewMode(): bool
    {
        $result = false;

        if ((isset($_GET['action']) && $_GET['action'] === 'edit') ||
            (isset($_REQUEST['action']) && $_REQUEST['action'] === 'avia_ajax_text_to_preview') )
        {
            $result = true;
        }

        return $result;
    }
}
