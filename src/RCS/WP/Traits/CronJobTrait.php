<?php
declare(strict_types = 1);
namespace RCS\WP\Traits;

trait CronJobTrait
{
    abstract public function runCronJob(): void;

    public function initializeCronJob(string $jobHook, string $interval, int $startTime): void
    {
        add_action($jobHook, [$this, 'runCronJob']);

        if (!wp_next_scheduled($jobHook)) {
            wp_schedule_event($startTime, $interval, $jobHook);
        }
    }

    /**
     * Wrapper method to handle the deliciousbrains vs a5hleyrich versions of
     * the WP_Background_Process class that might be loaded in memory.
     *
     * In the deliciousbrains version we can check if the background process
     * is active. In the a5shleyrich version we just assume it is not.
     *
     * @param \WP_Background_Process $bgProcess An instance of
     *      \WP_Background_Process from either the deliciousbrains or
     *      a5shleyrich packages.
     *
     * @return bool True if the job is active, false otherwise.
     */
    protected static function isJobActive(\WP_Background_Process $bgProcess): bool
    {
        $isJobActive = false;

        // Does not exist in the a5shleyrich version
        if (method_exists($bgProcess, 'is_active')) { // @phpstan-ignore function.alreadyNarrowedType
            $isJobActive = $bgProcess->is_active();
        } elseif (method_exists($bgProcess, 'is_process_running')) {
            $method = new \ReflectionMethod($bgProcess, 'is_process_running');
            $isJobActive = $method->invoke($bgProcess);
        }

        return $isJobActive;
    }
}
