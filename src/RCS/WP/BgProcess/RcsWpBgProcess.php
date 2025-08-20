<?php
declare(strict_types = 1);
namespace RCS\WP\BgProcess;

use RCS\WP\Traits\SingletonTrait;

final class RcsWpBgProcess extends \WP_Background_Process
{
    use SingletonTrait;

    // Override prefix and action properties
    protected $prefix = 'rcs';
    protected $action = 'RcsWpBgProcess';

    /**
     * {@inheritDoc}
     * @see \WP_Background_Process::unlock_process()
     */
    protected function unlock_process()
    {
        // if the tasks generated any new tasks, save them
        $this->save();

        return parent::unlock_process();
    }

    protected function task($item)
    {
        $result = $item;

        if ($item instanceof RcsWpBgTask) {
            if ($item->run($this)) {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }
}
