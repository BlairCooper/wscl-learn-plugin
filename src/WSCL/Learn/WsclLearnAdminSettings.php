<?php
declare(strict_types = 1);
namespace WSCL\Learn;

use RCS\WP\PluginInfo;
use RCS\WP\Settings\AdminSettings;
use WSCL\Learn\LearnDash\LearnDashSettingsTab;
use Psr\Log\LoggerInterface;

class WsclLearnAdminSettings extends AdminSettings
{
    const OPTIONS_PAGE_TITLE = 'WSCL Learn Customization Settings';
    const OPTIONS_MENU_TITLE = 'WSCL Customizations';
    const OPTIONS_PAGE_SLUG  = 'WSCLLearnSiteOptions';

    /**
     * Initialize the class and set its properties.
     *
     * @param PluginInfo $pluginInfo
     */
    public function __construct(PluginInfo $pluginInfo, LoggerInterface $logger) {
        parent::__construct(
            $pluginInfo->name,
            $pluginInfo->version,
            $pluginInfo->url,
            self::OPTIONS_PAGE_TITLE,
            self::OPTIONS_PAGE_SLUG,
            self::OPTIONS_MENU_TITLE,
            $logger
            );
    }

    public function initializeInstance(): void {
        parent::initializeInstance();

        $this->registerTab(new GeneralOptionsTab($this->logger));
        $this->registerTab(new LearnDashSettingsTab($this->logger));
    }
}
