<?php
declare(strict_types=1);
namespace WSCL\Learn\LearnDash;

use RCS\WP\Traits\CronJobTrait;
use RCS\WP\Traits\SingletonTrait;
use RCS\WP\BgProcess\RcsWpBgProcess;
use RCS\WP\PluginLogger;

class LearnDashCronJob
{
    private const CRON_JOB_HOOK = 'WsclLearnPressDailyCron';

    use SingletonTrait;
    use CronJobTrait;

    /** @var RcsWpBgProcess */
    private $bgProcess;

    /**
     *
     * @param RcsWpBgProcess $bgProcess
     */
    protected function __construct(RcsWpBgProcess $bgProcess)
    {
        $this->bgProcess = $bgProcess;
    }

    protected function initializeInstance(): void
    {
        $date = new \DateTime('today midnight');
        $date->modify('+1 day');

        $this->initializeCronJob(self::CRON_JOB_HOOK, 'daily', $date->getTimestamp());
    }

    /**
     *
     */
    public function runCronJob(): void
    {
        $logger = PluginLogger::init();

        $logger->debug('Running LearnPressCronJob');

        if (!self::isJobActive($this->bgProcess)) {
            $courseIds = $this->getLevel1CourseIds();

            if (!empty($courseIds)) {
                $wpUserIds = [];

                foreach ($courseIds as $courseId) {
                    $wpUserIds = array_merge(
                        $wpUserIds,
                        \learndash_get_course_users_access_from_meta($courseId)
                    );
                }

                foreach (array_unique($wpUserIds) as $wpUserId) {
                    $this->bgProcess->push_to_queue(new CheckCourseExpirationTask(intval($wpUserId), $courseIds));
                }

                $this->bgProcess->save();
            }
        } else {
            $logger->debug('Existing job in progress!?');
        }

        $this->bgProcess->dispatch();
    }

    /**
     *
     * @return int[]
     */
    private function getLevel1CourseIds(): array
    {
        $courseIds = [];

        $posts = get_posts([
            'post_type' => 'sfwd-courses',
            'tax_query' => [
                [
                    'taxonomy' => 'ld_course_tag',
                    'field' => 'slug',
                    'terms' => 'level1course'
                ]
            ]
        ]);

        foreach($posts as $post) {
            $courseIds[] = $post->ID;
        }

        return $courseIds;
    }
}
