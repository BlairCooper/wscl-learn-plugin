<?php
declare(strict_types = 1);
namespace RCS\WP\BgProcess;

use Psr\Log\LoggerInterface;
use RCS\Traits\SerializeAsArrayTrait;

/**
 * Base class for tasks executed by the BgProcess class.
 */
abstract class BgTask
{
    use SerializeAsArrayTrait;

    /**
     * Run the task.
     *
     * @param BgProcess $bgProcess The background process instance. Useful
     *      when additional tasks need to be added to the queue.
     * @param LoggerInterface $logger
     *
     * @return bool Returns true if the task is complete. Otherwise returns false.
     */
    abstract public function run(BgProcess $bgProcess, LoggerInterface $logger) : bool;
}
