<?php
declare(strict_types = 1);
namespace RCS\WP\Settings;

use Psr\Log\LoggerInterface;
use RCS\Traits\SingletonTrait;
use RCS\WP\PluginInfoInterface;

abstract class AdminSettings
{
    /** @var AdminSettingsTab[] */
    private array $tabs = array();

    /**
     *
     * @param PluginInfoInterface $pluginInfo
     * @param AdminSettingsTab[] $tabs
     * @param string $optionsPageTitle
     * @param string $optionsPageSlug
     * @param string $optionsMenuTitle
     * @param LoggerInterface $logger
     */
    protected function __construct(
        protected PluginInfoInterface $pluginInfo,
        array $tabs,
        protected string $optionsPageTitle,
        protected string $optionsPageSlug,
        protected string $optionsMenuTitle,
        protected LoggerInterface $logger
        )
    {
        foreach($tabs as $tab) {
            $this->registerTab($tab);
        }
    }

    protected function initializeInstance(): void
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'onAdminEnqueueScripts']);

            add_action('admin_init', [$this, 'addSettings']);
            add_action('admin_menu', [$this, 'addSettingsMenu']);

            add_filter('plugin_action_links', [$this, 'addPluginActionLinks'], 10, 4);
        }
    }


    /**
     * Register the settings menu.
     *
     * Hooked into the 'admin_menu' event.
     */
    final public function addSettingsMenu(): void
    {
        add_options_page(
            $this->optionsPageTitle,    // page title
            $this->optionsMenuTitle,    // menu title
            'manage_options',           // capability reqd
            $this->optionsPageSlug,     // menu slug name
            array(                      // function to output page contents
                $this,
                'renderSettingsPage'
            )
        );
    }

    /**
     * Register the settings for the active tab.
     *
     * Hooked into the 'admin_init' event and called as a result of the child
     * class being registered with the plugin as the admin helper.
     */
    final public function addSettings(): void
    {
        foreach ($this->tabs as $tab) {
            $tab->registerActions();
        }

        $activeTab = $this->getActiveTab();
        $activeTab->initSettings($this->optionsPageSlug);
    }

    /**
     * Register the scripts and stylesheets for the active tab.
     *
     * Hooked into the 'admin_enqueue_scripts' event.
     */
    final public function onAdminEnqueueScripts(): void
    {
        $activeTab = $this->getActiveTab();

        $activeTab->onEnqueueScripts($this->pluginInfo->getSlug(), $this->pluginInfo->getUrl(), $this->pluginInfo->getVersion());
    }

    private function getActiveTab(): AdminSettingsTab
    {
        $activeTabId = '';

        if (isset( $_GET[ 'tab' ] )) {
            $activeTabId = $_GET[ 'tab' ];
        } else {
            if (isset($_POST[ '_wp_http_referer']) ) {
                $args = array();

                $urlArgs = parse_url($_POST[ '_wp_http_referer'], PHP_URL_QUERY);
                if (!is_null($urlArgs)) {
                    parse_str ($urlArgs, $args);
                    if (isset($args[ 'tab' ]) ) {
                        $activeTabId = $args[ 'tab'];
                    }
                }
            }
        }

        return $this->tabs[array_key_exists($activeTabId, $this->tabs) ?
                        $activeTabId :
                        array_key_first($this->tabs)];
    }

    /**
     *
     * @param array<string, mixed> $input
     */
    public function localSanitize(array $input): void
    {
        $activeTab = $this->getActiveTab();
        $activeTab->sanitize($this->pluginInfo->getSlug(), $input);
    }

    final public function registerTab(AdminSettingsTab $tab): void
    {
        $newTab = true;

        foreach ($this->tabs as $existingTab) {
            if (get_class($tab) === get_class($existingTab)) {
                $newTab = false;
                break;
            }
        }

        if ($newTab) {
            $this->tabs[$tab->getId()] = $tab;
        }
    }


    /**
     * Render the settings page
     */
    final public function renderSettingsPage(): void
    {
        // check user capabilities
        if ( current_user_can( 'manage_options' ) && count($this->tabs) != 0)
        {
            $activeTab = $this->getActiveTab();

            ob_start();
            ?>
            <div class="wrap">
                <h2><?php echo $this->optionsPageTitle ?></h2>

                <!-- wordpress provides the styling for tabs. -->
                <h2 class="nav-tab-wrapper">
                    <?php
                        $activeTabId = $activeTab->getId();

                        // Generate a link for each registered tab
                        foreach ($this->tabs as $tab) {
                            // When tab buttons are clicked we jump back to the same page but with a new parameter
                            // that represents the clicked tab. accordingly we make it active
                            printf('<a href="?page=%s&tab=%s" class="nav-tab %s">%s</a>',
                                $this->optionsPageSlug,
                                $tab->getId(),
                                $activeTabId === $tab->getId() ? 'nav-tab-active' : '',
                                $tab->getName());
                        }
                    ?>
                </h2>

                <form method="post" action="options.php">
                    <?php
                        settings_fields($this->optionsPageSlug);
                        do_settings_sections($this->optionsPageSlug);
                        submit_button( 'Save Changes' );
                    ?>
                </form>

                <?php
                    $activeTab->renderPostFormData();
                ?>
            </div>
            <?php

            $html = ob_get_contents();
            ob_end_clean();

            print $html;
        }
    }

    /**
     * Hook for the 'plugin_action_links' filter.
     *
     * @param array<string> $actions    Array of plugin action links
     * @param string        $pluginFile Path to the plugin file
     * @param array<mixed>  $pluginData Array of plugin data
     * @param string        $context    The plugin context
     *
     * @return array<string> A potentially updated array of plugin action links.
     */
    public function addPluginActionLinks(
        array $actions,
        string $pluginFile,
        ?array $pluginData,
        string $context
        ): array
    {
        if ($pluginFile == $this->pluginInfo->getFile()) {
            array_unshift(
                $actions,
                sprintf(
                    '<a href="%s/wp-admin/options-general.php?page=%s">Settings</a>',
                    get_bloginfo('wpurl'),
                    $this->optionsPageSlug
                    )
                );
        }

        return $actions;
    }

}
