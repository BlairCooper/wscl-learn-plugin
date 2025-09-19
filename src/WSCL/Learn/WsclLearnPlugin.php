<?php
declare(strict_types=1);
namespace WSCL\Learn;

use RCS\Logging\ErrorLogInterceptor;
use RCS\WP\Database\DatabaseUpdater;
use WSCL\Learn\LearnDash\LearnDashCronJob;
use DI\ContainerBuilder;

class WsclLearnPlugin
{
    private WsclLearnOptionsInterface $options;

    public function init(string $entryPointFile): void
    {
        add_action(
            'init',
            function () use ($entryPointFile) {
                if (!function_exists('get_home_path')) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';    // @phpstan-ignore requireOnce.fileNotFound
                }

                $containerBuilder = new ContainerBuilder();
                $containerBuilder->addDefinitions(ServiceConfig::getDefinitions());

                if (!file_exists(get_home_path() . 'wp-config-local.php')) {
                    $containerBuilder->enableCompilation(self::getCompiledContainerPath());
                }

                $container = $containerBuilder->build();

                $container->set(ServiceConfig::PLUGIN_ENTRYPOINT, $entryPointFile);

                ErrorLogInterceptor::init([
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

        add_action(
            'upgrader_process_complete',
            function (object $upgrader_object, array $options) use ($entryPointFile) {
                if ($options['action'] == 'update' &&
                    $options['type'] == 'plugin' &&
                    isset( $options['plugins'] ) &&
                    in_array( plugin_basename( $entryPointFile ), $options['plugins']))
                {
                    $path = self::getCompiledContainerPath();

                    if (file_exists($path)) {
                        array_map('unlink', glob("$path/*.php"));
                    }
                }
            },
            10,
            2
        );
    }

    public static function getCompiledContainerPath(): string
    {
        $reflectionClass = new \ReflectionClass(self::class);
        $shortName = $reflectionClass->getShortName();

        return \wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'CompiledContainers' . DIRECTORY_SEPARATOR . $shortName;
    }
}
