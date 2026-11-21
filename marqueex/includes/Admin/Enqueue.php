<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Traits\Singleton;
use Wpxero\Marqueex\Core\Settings;
use Wpxero\Marqueex\MarqueeX;
use Wpxero\Marqueex\Core\Upsell;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Enqueue
 */
class Enqueue {
    use Singleton;

    /**
     * Initialize the class
     */
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_body_class', [$this, 'add_custom_body_class']);
        add_action('admin_init', [$this, 'redirect_to_welcome_screen']);
        add_filter('plugin_action_links_' . plugin_basename(MARQUEEX_FILE), [$this, 'marqueex_settings_link']);
    }

    public function marqueex_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=marqueex&sub_page=settings') . '">Settings</a>';
        array_unshift($links, $settings_link);

        if (Upsell::should_show()) {
            $links['upgrade'] = sprintf(
                '<a href="%1$s" style="color:#39b54a;font-weight:700;">%2$s</a>',
                esc_url(Upsell::url('plugin-row')),
                esc_html(Upsell::cta_label())
            );
        }

        return $links;
    }

    /**
     *
     * Add custom body class to admin pages for Marqueex plugin.
     *
     * @param array $classes Existing body classes.
     * @return array Modified body classes.
     */
    // add body class for extenderx
    public function add_custom_body_class($classes) {
        // Check if we are on editing screen in WordPress admin
        if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'edit') { // phpcs:ignore
            $classes .= ' marqueex-admin-page';
        }
        return $classes;
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts($hook_suffix) {
        // Only load on MarqueeX admin pages
        if (strpos($hook_suffix, 'marqueex') === false) {
            return;
        }

        $admin_assets = MARQUEEX_DIR_PATH . 'admin/dependencies.php';
        if (file_exists($admin_assets)) {
            $admin_assets = require $admin_assets;

            wp_enqueue_script(
                'marqueex-admin',
                MARQUEEX_ADMIN_URL . 'admin/marqueex-admin.js',
                $admin_assets['dependencies'],
                $admin_assets['version'],
                true
            );

            // Load translations for the React dashboard. Without this the JS
            // __() strings never resolve to a locale, even when .json files exist.
            wp_set_script_translations(
                'marqueex-admin',
                'marqueex',
                MARQUEEX_DIR_PATH . 'languages'
            );

            // Get current settings deep-merged with the canonical defaults.
            $complete_settings = Settings::get();

            wp_localize_script(
                'marqueex-admin',
                'marqueexAdminData',
                [
                    'settings' => $complete_settings,
                    'version' => MARQUEEX_VERSION,
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('marqueex-admin-nonce'),
                    'rest_url' => rest_url('wpxero/marqueex/v1'),
                    // First-run onboarding panel + footer links.
                    'showOnboarding' => Settings::should_show_onboarding(),
                    'isPremium' => MarqueeX::is_premium_active(),
                    // Whether to offer Pro at all. Distinct from isPremium: a site
                    // can have paid and not yet activated, and should not be sold to.
                    'showUpgrade' => Upsell::should_show(),
                    'upgradeUrl' => Upsell::url('admin-header'),
                    'upgradeLabel' => Upsell::cta_label(),
                    'upgradeMeta' => Upsell::cta_meta(),
                    'hasLicense' => Upsell::has_license(),
                    // Separate attribution source so the Free vs Pro page's
                    // conversions can be told apart from the header button's.
                    'proPageUrl' => Upsell::url('admin-pro-page'),
                    'hasElementor' => class_exists('\\Elementor\\Plugin'),
                    // Installed but inactive needs "Activate", not "Install".
                    'elementorInstalled' => file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php'),
                    'newPageUrl' => admin_url('post-new.php?post_type=page'),
                    // Dashboard greeting + "Live on" tile.
                    'userName' => wp_get_current_user()->display_name,
                    'usage' => Usage::counts(),
                    'reviewUrl' => ReviewRequest::REVIEW_URL,
                    // Other WPXERO plugins, for the dashboard card and catalogue page.
                    'recommendations' => Recommendations::data(),
                    'docsUrl' => 'https://wpxero.com/plugins/marqueex/docs/',
                    'supportUrl' => 'https://wordpress.org/support/plugin/marqueex/',
                ]
            );

            wp_enqueue_style(
                'marqueex-admin',
                MARQUEEX_ADMIN_URL . 'admin/style-marqueex.css',
                [],
                $admin_assets['version']
            );
        }
    }
    /**
     * Redirect to Welcome page after activation.
     */
    public function redirect_to_welcome_screen() {
        // Bail if no activation redirect.
        if (! get_transient('_marqueex_welcome_screen_activation_redirect')) {
            return;
        }

        // Delete the redirect transient.
        delete_transient('_marqueex_welcome_screen_activation_redirect');

        // Bail if activating from network, or bulk.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }

        // Redirect to the settings screen, where the first-run onboarding panel
        // greets them above the tabs.
        wp_safe_redirect(admin_url('admin.php?page=marqueex'));
        exit;
    }
}
