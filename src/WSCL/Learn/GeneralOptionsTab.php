<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use RCS\WP\Settings\AdminSettingsTab;
use Psr\Log\LoggerInterface;

class GeneralOptionsTab extends AdminSettingsTab
{
    private const TAB_NAME = 'General';

    const EMAIL_SECTION_SETTINGS_ID = 'emailSection';
    const EMAIL_SECTION_SETTINGS_TITLE = 'Site Email Settings';

    /** @var array<string, string> */
    private static $fieldNameMap = array (
        WsclLearnPluginOptions::SITE_EMAIL_NAME_KEY => 'Site Name',
        WsclLearnPluginOptions::SITE_EMAIL_ADDRESS_KEY => 'General Email Address',
    );

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct(self::TAB_NAME, WsclLearnPluginOptions::init(), $logger);
    }

    public function addSettings(string $pageSlug): void
    {
        /**
         * Site Email Settings section
         */
        add_settings_section(
            self::EMAIL_SECTION_SETTINGS_ID,
            self::EMAIL_SECTION_SETTINGS_TITLE,
            function () { print '<hr>'; },
            $pageSlug
            );

        add_settings_field(
            WsclLearnPluginOptions::SITE_EMAIL_NAME_KEY,
            self::$fieldNameMap[WsclLearnPluginOptions::SITE_EMAIL_NAME_KEY],    // field Title
            function () {
                $this->renderTextField(
                    $this->options,
                    WsclLearnPluginOptions::SITE_EMAIL_NAME_KEY,
                    'The name of the site to use in email messages',
                    []
                    );
            },  // Callback
            $pageSlug,  // Page
            self::EMAIL_SECTION_SETTINGS_ID  // Section
            );

        add_settings_field(
            WsclLearnPluginOptions::SITE_EMAIL_ADDRESS_KEY,
            self::$fieldNameMap[WsclLearnPluginOptions::SITE_EMAIL_ADDRESS_KEY],    // field Title
            function () {
                $this->renderEmailField(
                    $this->options,
                    WsclLearnPluginOptions::SITE_EMAIL_ADDRESS_KEY,
                    'Email address to use for messages sent from the web site (e.g. info@washingtonleague.org)',
                    );
            },  // Callback
            $pageSlug,  // Page
            self::EMAIL_SECTION_SETTINGS_ID  // Section
            );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param   string  $pageSlug   The page slug for any errors.
     * @param   array<string, mixed> $input Contains all settings fields as array keys
     */
    public function sanitize(string $pageSlug, ?array $input): ?array
    {
        if (!is_null($input)) {
            $this->logger->info('Sanitizing data: ', $input);

            foreach ($input as $key => $value) {
                switch ($key) {
                    case WsclLearnPluginOptions::SITE_EMAIL_NAME_KEY:
                        $this->validateStringValue($key, $value, $pageSlug, self::$fieldNameMap[$key]);
                        break;

                    case WsclLearnPluginOptions::SITE_EMAIL_ADDRESS_KEY:
                        $this->validateEmailAddress($key, $value, $pageSlug, self::$fieldNameMap[$key]);
                        break;
                    default:
                        break;
                }
            }

            $this->logger->info('Post sanitized data:', $this->options->getValues());

            return $this->options->getValues();
        } else {
            return $input;
        }
    }
}
