<?php
declare(strict_types = 1);
namespace RCS\WP\BgProcess;

interface RcsWpBgTask
{
    /**
     * Run the task.
     *
     * @return bool Returns true if the task is complete. Otherwise returns false.
     */
    public function run(RcsWpBgProcess $bgProcess) : bool;
}
