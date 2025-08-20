<?php
declare(strict_types=1);
namespace WSCL\Learn;

use RCS\WP\ErrorLogInterceptor;
use RCS\WP\PluginInfo;
use RCS\WP\PluginLogger;
use RCS\WP\BgProcess\RcsWpBgProcess;
use WSCL\Learn\LearnDash\LearnDashCronJob;
use WSCL\Learn\Shortcodes\InsertJotFormShortCode;

class WsclLearnPlugin
{
    public function init(string $entryPointFile): void
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';  // @phpstan-ignore requireOnce.fileNotFound
        }

        /** @var array<string, string> */
        $pluginData = get_plugin_data($entryPointFile, false, false);

        $pluginInfo = new PluginInfo(
            $entryPointFile,
            plugin_dir_path($entryPointFile),
            plugin_dir_url($entryPointFile),
            $pluginData['Version'],
            $pluginData['TextDomain'],
            $pluginData['Name']
            );

        PluginLogger::init($pluginInfo->slug);

        ErrorLogInterceptor::init([
            E_USER_NOTICE => ['_load_textdomain_just_in_time']
            ]
        );

        $bgProcess = RcsWpBgProcess::init();

        LearnDashCronJob::init($bgProcess);

        WsclLearnAdminSettings::init($pluginInfo);

        InsertJotFormShortCode::init($pluginInfo);

        add_filter(
            'wp_mail_from',
            function (string $fromEmail) {
                return WsclLearnPluginOptions::init()->getSiteEmailAddress();
            }
        );

        add_filter(
            'wp_mail_from_name',
            function (string $fromName) {
                return WsclLearnPluginOptions::init()->getSiteEmailName();
            }
        );
    }
}
