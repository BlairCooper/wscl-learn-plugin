<?php
declare(strict_types=1);
namespace WSCL\Learn\LearnDash;

use RCS\WP\Settings\AdminSettingsTab;
use RCS\WP\Validation\EmailValidator;
use RCS\WP\Validation\StringValidator;
use WSCL\Learn\WsclLearnOptions;
use Psr\Log\LoggerInterface;
use WSCL\Learn\WsclLearnOptionsInterface;

class LearnDashSettingsTab extends AdminSettingsTab
{
    private const TAB_NAME = "LearnDash Options";

    const OPTIONS_SECTION_NOTIFY_ID = 'learnDashSection';
    const OPTIONS_SECTION_NOTIFY_TITLE = 'Training Due Message';

    /** @var array<string, string> */
    private static array $fieldNameMap = array (
        WsclLearnOptions::MSG_FROM_NAME_ID     => 'Sender Name',
        WsclLearnOptions::MSG_FROM_ADDRESS_ID  => 'Sender Address',
        WsclLearnOptions::MSG_SUBJECT_ID       => 'Subject',
        WsclLearnOptions::MSG_BODY_ID          => 'Message Body'
    );

    public function __construct(LoggerInterface $logger, WsclLearnOptionsInterface $options)
    {
        parent::__construct(self::TAB_NAME, $options, $logger);
    }

    public function addSettings(string $pageSlug): void
    {
        /**
         * Email Notifications section
         */
        add_settings_section(
            self::OPTIONS_SECTION_NOTIFY_ID,
            self::OPTIONS_SECTION_NOTIFY_TITLE,
            function () {
                print '<p>Enter the information for the notification email below:</p>';
            },
            $pageSlug
            );

        add_settings_field(
            WsclLearnOptions::MSG_FROM_NAME_ID,
            self::$fieldNameMap[WsclLearnOptions::MSG_FROM_NAME_ID], // field Title
            function () {
                $this->renderTextField(
                    WsclLearnOptions::MSG_FROM_NAME_ID,
                    'The name messages should appear from.',
                    array (
                        'size'      => 40,
                        'maxlength' => 64,
                        'required'  => null
                    )
                    );
            }, // Callback
            $pageSlug, // Page
            self::OPTIONS_SECTION_NOTIFY_ID // Section
            );

        add_settings_field(
            WsclLearnOptions::MSG_FROM_ADDRESS_ID,
            self::$fieldNameMap[WsclLearnOptions::MSG_FROM_ADDRESS_ID], // field Title
            function () {
                $this->renderEmailField(
                    WsclLearnOptions::MSG_FROM_ADDRESS_ID,
                    'The email address messages should appear from.'
                    );
            }, // Callback
            $pageSlug, // Page
            self::OPTIONS_SECTION_NOTIFY_ID // Section
            );

        add_settings_field(
            WsclLearnOptions::MSG_SUBJECT_ID,
            self::$fieldNameMap[WsclLearnOptions::MSG_SUBJECT_ID], // field Title
            function () {
                $this->renderTextField(
                    WsclLearnOptions::MSG_SUBJECT_ID,
                    'Placeholders available in the body can also be used in the subject',
                    array (
                        'size'      => 80,
                        'minlength' => 5,
                        'maxlength' => 255,
                        'required'  => null
                    )
                    );
            }, // Callback
            $pageSlug, // Page
            self::OPTIONS_SECTION_NOTIFY_ID // Section
            );

        $fieldDescription = join(
            '<br>',
            array_merge(
                [
                    'Allowable placeholders:'
                ],
                array_map(
                    fn(string $field, string $description) => sprintf('{%s} => %s', $field, $description),
                    array_keys(CheckCourseExpirationTask::MSG_PLACEHOLDERS),
                    array_values(CheckCourseExpirationTask::MSG_PLACEHOLDERS)
                    )
                )
            );

        add_settings_field(
            WsclLearnOptions::MSG_BODY_ID,
            self::$fieldNameMap[WsclLearnOptions::MSG_BODY_ID], // field Title
            function () use ($fieldDescription) {
                $this->renderRteTextField(
                    WsclLearnOptions::MSG_BODY_ID,
                    $fieldDescription
                    );
            },
            $pageSlug,
            self::OPTIONS_SECTION_NOTIFY_ID
            );
    }


    /**
     * Sanitize each setting field as needed
     *
     * @param   string  $pageSlug   The page slug for any errors.
     * @param   array<string, mixed>|null   $input      Contains all settings fields as array keys
     *
     * @return null|array<string, mixed>
     */
    public function sanitize(string $pageSlug, ?array $input): ?array
    {
        if (!is_null($input)) {
            $this->logger->info('Sanitizing data: ', $input);

            foreach ($input as $key => $value) {
                switch ($key) {
                    case WsclLearnOptions::MSG_FROM_ADDRESS_ID:
                        if ( (new EmailValidator($this->pageSlug, $key, self::$fieldNameMap[$key]))->isValid($value) ) {
                            $this->options->setValue($key, sanitize_text_field($value));
                        }
                        break;

                    case WsclLearnOptions::MSG_FROM_NAME_ID:
                    case WsclLearnOptions::MSG_SUBJECT_ID:
                        if ((new StringValidator($this->pageSlug, $key, self::$fieldNameMap[$key]))->isValid($value)) {
                            $this->options->setValue( $key, sanitize_text_field($value) );
                        }
                        break;

                    case WsclLearnOptions::MSG_BODY_ID:
                        if ((new StringValidator($this->pageSlug, $key, self::$fieldNameMap[$key]))->isValid($value)) {
                            $this->options->setValue( $key, wpautop(wp_kses_post($value)) );
                        }
                        break;

                    default:
                        break;
                }
            }

            $this->logger->info('Post sanitized data:', $this->options->getValues());

            return $this->options->getValues();
        }
        else {
            return $input;
        }
    }
}
