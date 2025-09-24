<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use Psr\Log\LoggerInterface;
use RCS\WP\PluginInfo;
use RCS\WP\PluginInfoInterface;
use RCS\WP\PluginLogger;
use RCS\WP\PluginOptionsInterface;
use RCS\WP\BgProcess\BgProcess;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\Database\DatabaseUpdater;
use RCS\WP\Database\DatabaseUpdatesInterface;
use WSCL\Learn\LearnDash\LearnDashCronJob;
use WSCL\Learn\LearnDash\LearnDashSettingsTab;
use WSCL\Learn\Shortcodes\InsertJotFormShortcode;

class ServiceConfig
{
    public const PLUGIN_ENTRYPOINT = 'plugin.entryPoint';
    public const SETTINGS_TABS = 'settings.tabs';
    public const SHORTCODES = 'shortcode.objs';

    /**
     *
     * @return array<string, mixed>
     */
    public static function getDefinitions(): array
    {
        return [
            PluginInfoInterface::class => \DI\create(PluginInfo::class)
                ->constructor(\DI\get(ServiceConfig::PLUGIN_ENTRYPOINT)),

            LoggerInterface::class => \DI\autowire(PluginLogger::class),

            WsclLearnOptionsInterface::class => \DI\factory([WsclLearnOptions::class, 'init']),
            PluginOptionsInterface::class => \DI\get(WsclLearnOptionsInterface::class),

            BgProcessInterface::class => \DI\autowire(BgProcess::class)
                ->constructor(params:
                    [
                        WsclLearnOptionsInterface::class => \DI\get(WsclLearnOptionsInterface::class)
                    ]
                    ),

            LearnDashCronJob::class => \DI\autowire(LearnDashCronJob::class),

            self::SETTINGS_TABS => [
                \DI\autowire(GeneralOptionsTab::class),
                \DI\autowire(LearnDashSettingsTab::class),
            ],

            WsclLearnAdminSettings::class => \DI\autowire()
                ->constructor(tabs: \DI\get(self::SETTINGS_TABS)),

            self::SHORTCODES => [
                \DI\autowire(InsertJotFormShortcode::class)
            ],

            DatabaseUpdatesInterface::class => \DI\autowire(DatabaseUpdates::class),
            DatabaseUpdater::class => \DI\autowire()
        ];
    }
}
