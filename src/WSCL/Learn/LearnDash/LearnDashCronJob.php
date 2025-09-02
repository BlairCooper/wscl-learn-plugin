<?php
declare(strict_types=1);
namespace WSCL\Learn\LearnDash;

use Psr\Log\LoggerInterface;
use RCS\WP\CronJob;
use RCS\WP\BgProcess\BgProcessInterface;

class LearnDashCronJob extends CronJob
{
    private const CRON_JOB_HOOK = 'WsclLearnPressDailyCron';

    /**
     *
     * @param BgProcessInterface $bgProcess
     * @param LoggerInterface $logger
     */
    protected function __construct(
        private BgProcessInterface $bgProcess,
        LoggerInterface $logger
        )
    {
        parent::__construct($logger);
    }

    protected function initializeInstance(): void
    {
        $this->initializeCronJob(self::CRON_JOB_HOOK, 'daily');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\CronJob::runJob()
     */
    protected function runJob(): void
    {
        $this->logger->debug('Running LearnPressCronJob');

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
                    $this->bgProcess->pushToQueue(new CheckCourseExpirationTask(intval($wpUserId), $courseIds));
                }

                $this->bgProcess->save();
            }
        } else {
            $this->logger->debug('Existing job in progress!?');
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
