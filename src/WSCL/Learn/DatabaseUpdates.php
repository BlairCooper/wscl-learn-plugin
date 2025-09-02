<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use RCS\WP\Database\DatabaseUpdatesInterface;

class DatabaseUpdates implements DatabaseUpdatesInterface
{
    public function __construct(
//        private BgProcessInterface $bgProcess
        )
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\Database\DatabaseUpdatesInterface::getDatabaseUpgrades()
     */
    public function getDatabaseUpgrades(): array
    {
        return [
//             '2.0.42' => function () { $this->upgradeDatabaseTo_2_0_42(); },
//             '2.0.40' => function () { $this->upgradeDatabaseTo_2_0_40(); },
//             '2.0.32' => function () { $this->upgradeDatabaseTo_2_0_32(); },
//             '2.0.24' => function () { $this->upgradeDatabaseTo_2_0_24(); },
//             '2.0.20' => function () { $this->upgradeDatabaseTo_2_0_20(); },
            ];
    }

//     private function upgradeDatabaseTo_2_0_42(): void
//     {
//         // Don't actually have anything to do, just need to trigger dispatching the background tasks
//     }
}
