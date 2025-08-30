<?php
declare(strict_types = 1);
namespace WSCL\Learn\LearnDash;

use Soundasleep\Html2Text;
use RCS\WP\BgProcess\BgProcess;
use RCS\WP\BgProcess\BgTask;
use RCS\WP\WpMail\WpMailWrapper;
use WSCL\Learn\WsclLearnPluginOptions;
use Psr\Log\LoggerInterface;

class CheckCourseExpirationTask extends BgTask
{
    private const THREE_YEARS = 3 * YEAR_IN_SECONDS;

    private const PH_SENDER_NAME      = 'senderName';
    private const PH_USER_FIRSTNAME = 'firstName';
    private const PH_USER_LASTNAME  = 'lastName';
    private const PH_COURSE_LIST    = 'courseList';
    private const PH_COURSES_URL    = 'coursesURL';

    public const MSG_PLACEHOLDERS = [
        self::PH_SENDER_NAME      => 'The sender\'s name',
        self::PH_USER_FIRSTNAME => 'The person\'s first name',
        self::PH_USER_LASTNAME  => 'The person\'s last name',
        self::PH_COURSE_LIST    => 'The courses due list',
        self::PH_COURSES_URL    => 'The URL to the Courses list on the web site'
    ];

    /**
     *
     * @param int $wpUserId
     * @param int[] $courseIds
     */
    public function __construct(
        protected int $wpUserId,
        protected array $courseIds
        )
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\BgProcess\BgTask::run()
     */
    public function run(BgProcess $bgProcess, LoggerInterface $logger) : bool
    {
        $coursesDue = $this->getCoursesDue($logger);

        if (!empty($coursesDue)) {
            $this->sendEmailNotification($this->wpUserId, $coursesDue, $logger);
        }

        return true;
    }

    /**
     *
     * @return int[]
     */
    private function getCoursesDue(LoggerInterface $logger): array
    {
        $coursesDue = [];

        foreach ($this->courseIds as $courseId) {
            $args = [
                'user_id'       => $this->wpUserId,
                'course_id'     => $courseId,
                'post_id'       => $courseId,
                'activity_type' => 'course'
            ];

            $activity = \learndash_get_user_activity($args);

            if (!is_null($activity)) {
                $checkTime = $activity->activity_status ? $activity->activity_completed : $activity->activity_updated;

                if ($checkTime < time() - self::THREE_YEARS) {
                    $logger->info('Removing progress on course {courseId} for {userId}', ['courseId' => $courseId, 'userId' => $this->wpUserId]);

                    // Unenroll the user from the course
                    \ld_update_course_access($this->wpUserId, $courseId, true);

                    \learndash_delete_course_progress($courseId, $this->wpUserId);
                    $this->deleteUserActivity($this->wpUserId, $courseId);

                    // Re-enroll the user in the course
                    \ld_update_course_access($this->wpUserId, $courseId);

                    $coursesDue[] = $courseId;
                }
            }
        }

        return array_unique($coursesDue);
    }

    private function deleteUserActivity(int $wpUserId, int $courseId): void
    {
        global $wpdb;

        $activityIds = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT activity_id FROM ' . esc_sql(\LDLMS_DB::get_table_name('user_activity')) . ' WHERE user_id=%d AND course_id=%d',
                $wpUserId,
                $courseId
                )
        );

        foreach ($activityIds as $activityId) {
            \learndash_delete_user_activity($activityId);
        }
    }

    /**
     * @param int $wpUserId
     * @param int[] $coursesDue
     */
    private function sendEmailNotification(int $wpUserId, array $coursesDue, LoggerInterface $logger): void
    {
        $coursesDue = array_map(fn($postId) => \get_post($postId)->post_title, $coursesDue);

        /** @var WsclLearnPluginOptions */
        $options = WsclLearnPluginOptions::init();
        $wpUser = get_userdata($wpUserId);

        $courseList = '<ul>' . join('', array_map(fn($course) => '<li>'.$course.'</li>', $coursesDue)) . '</ul>';

        $subject = $options->getMsgSubject();
        $body = $options->getMsgBody();

        $injectionMap = [
            self::PH_COURSE_LIST    => $courseList,
            self::PH_SENDER_NAME    => $options->getMsgFromName(),
            self::PH_USER_FIRSTNAME => $wpUser->first_name,
            self::PH_USER_LASTNAME  => $wpUser->last_name,
            self::PH_COURSES_URL    => '<a href="'.get_permalink(get_page_by_path('course-list')).'">Courses</a>'
        ];

        // Allow for string expansion in subject and body
        foreach ($injectionMap as $key => $value) {
            $subject = preg_replace ("/\{$key\}/i", strval($value), $subject);
            $body = preg_replace ("/\{$key\}/i", strval($value), $body);
        }

        $htmlBody = "<html><body>{$body}</body></html>";

        (new WpMailWrapper($logger))
            ->setFrom($options->getMsgFromAddress(), $options->getMsgFromName())
            ->addTo($wpUser->user_email, $wpUser->first_name . ' ' . $wpUser->last_name)
            ->setSubject($subject)
            ->addBcc($options->getMsgFromAddress(), $options->getMsgFromName())
            ->setHtmlBody($htmlBody)
            ->setPlainBody(Html2Text::convert($htmlBody))
            ->sendMessage();
    }
}
