<?php
declare(strict_types=1);
namespace WSCL\Learn;

use RCS\Logging\ErrorLogInterceptor;
use WSCL\Learn\LearnDash\LearnDashCronJob;
use WSCL\Learn\Shortcodes\InsertJotFormShortcode;
use DI\Container;

class WsclLearnPlugin
{
    public function init(string $entryPointFile): void
    {
        $container = new Container(ServiceConfig::getDefinitions());
        $container->set(ServiceConfig::PLUGIN_ENTRYPOINT, $entryPointFile);

        ErrorLogInterceptor::init([
            E_USER_NOTICE => ['_load_textdomain_just_in_time']
            ]
        );

        $container->get(LearnDashCronJob::class);
        $container->get(WsclLearnAdminSettings::class);
        $container->get(InsertJotFormShortcode::class);

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
