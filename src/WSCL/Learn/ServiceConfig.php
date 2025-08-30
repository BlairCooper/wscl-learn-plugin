<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RCS\WP\PluginLogger;
use RCS\WP\PluginInfoInterface;
use RCS\WP\PluginInfo;
use RCS\WP\BgProcess\BgProcess;
use WSCL\Learn\LearnDash\LearnDashCronJob;
use WSCL\Learn\Shortcodes\InsertJotFormShortcode;

class ServiceConfig
{
    public const PLUGIN_ENTRYPOINT = 'plugin.entryPoint';

    /**
     *
     * @return array<string, mixed>
     */
    public static function getDefinitions(): array
    {
        return [
            PluginInfoInterface::class => function(ContainerInterface $container) {
                return PluginInfo::init(
                    $container->get(ServiceConfig::PLUGIN_ENTRYPOINT)
                    );
            },
            LoggerInterface::class => function(ContainerInterface $container) {
                return PluginLogger::init(
                    $container->get(PluginInfoInterface::class)
                    );
            },
            BgProcess::class => function(ContainerInterface $container) {
                return new BgProcess(
                    $container->get(LoggerInterface::class)
                    );
            },
            LearnDashCronJob::class => function (ContainerInterface $container) {
                return LearnDashCronJob::init(
                    $container->get(BgProcess::class),
                    $container->get(LoggerInterface::class)
                );
            },
            WsclLearnAdminSettings::class => function (ContainerInterface $container) {
                return WsclLearnAdminSettings::init(
                    $container->get(PluginInfoInterface::class),
                    $container->get(LoggerInterface::class)
                    );
            },
            InsertJotFormShortcode::class => function (ContainerInterface $container) {
                return InsertJotFormShortcode::init(
                    $container->get(PluginInfoInterface::class)
                    );
            }
        ];
    }
}
