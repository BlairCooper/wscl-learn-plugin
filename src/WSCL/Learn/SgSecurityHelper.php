<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use Psr\Log\LoggerInterface;
use RCS\WP\CronJob;

/**
 * Helper to save off the list of excluded bots from robots.txt and then
 * provided that to the SiteGround Security plugin whenever it is determining
 * if a request from a bot or a Human.
 *
 * There is an assumption that all of the User-Agent entries in robots.txt
 * are disallowing access exception for *.
 *
 * The intent of the helper is to more accurately count Human vs Bot traffic
 * in the weekly email.
 */
class SgSecurityHelper extends CronJob
{
    private const CRON_JOB_HOOK = 'WsclSgSecurityDailyCron';
    private const ROBOTS_CRAWLERS_OPTION = 'customSgCrawlers';
    private const USER_AGENT_PATTERN = '/^user-agent\s?:(.*)$/mi';

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->initializeCronJob(self::CRON_JOB_HOOK, 'daily');

        \add_filter(
            'sg_security_custom_crawlers',
            function(array $crawlers): array
            {
                return array_merge(
                    \get_option(self::ROBOTS_CRAWLERS_OPTION, []),
                    $crawlers
                    );
            }
        );
    }

    protected function runJob(): void
    {
        $contents = \file_get_contents(ABSPATH . 'robots.txt');

        if ($contents) {
            $matches = [];

            if (preg_match_all(self::USER_AGENT_PATTERN, $contents, $matches)) {
                $crawlers = [];

                foreach ($matches[1] as $match) {

                    $crawler = strtolower(trim($match));

                    if (3 <= strlen($crawler)) {
                        $crawlers[] = $crawler;
                    }
                }

                \update_option(self::ROBOTS_CRAWLERS_OPTION, $crawlers);
            }
        }
    }
}
