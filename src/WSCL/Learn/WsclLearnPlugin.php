<?php
declare(strict_types=1);
namespace WSCL\Learn;

use DI\ContainerBuilder;
use RCS\Logging\ErrorLogInterceptor;
use RCS\WP\PluginInfo;
use RCS\WP\Database\DatabaseUpdater;
use WSCL\Learn\LearnDash\LearnDashCronJob;

class WsclLearnPlugin
{
    private WsclLearnOptionsInterface $options;

    public function init(string $entryPointFile): void
    {
        add_action(
            'init',
            function () use ($entryPointFile) {
                if (!function_exists('get_home_path')) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                $containerBuilder = new ContainerBuilder();
                $containerBuilder->addDefinitions(ServiceConfig::getDefinitions());

                if (!file_exists(get_home_path() . 'wp-config-local.php')) {
                    $containerBuilder->enableCompilation((new PluginInfo($entryPointFile))->getPath());
                }

                $container = $containerBuilder->build();

                $container->set(ServiceConfig::PLUGIN_ENTRYPOINT, $entryPointFile);

                new ErrorLogInterceptor([                               // NOSONAR - not useless
                    E_USER_NOTICE => ['_load_textdomain_just_in_time']
                    ]
                );

                /** @var DatabaseUpdater */
                $dbUpdater = $container->get(DatabaseUpdater::class);
                $dbUpdater->privUpgradeDatabase();

                $container->get(LearnDashCronJob::class);
                $container->get(ServiceConfig::SHORTCODES);

                if (is_admin()) {
                    $container->get(WsclLearnAdminSettings::class);
                }

                $this->options = $container->get(WsclLearnOptionsInterface::class);
            }
            );

        add_filter(
            'wp_mail_from',
            function (string $fromEmail) {
                return $this->options->getSiteEmailAddress();
            }
        );

        add_filter(
            'wp_mail_from_name',
            function (string $fromName) {
                return $this->options->getSiteEmailName();
            }
        );
    }
}
