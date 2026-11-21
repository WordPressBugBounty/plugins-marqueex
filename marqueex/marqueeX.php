<?php

/**
 * Plugin Name:       MarqueeX – Smooth Marquee Slider, News Ticker & Post Marquee for Block Editor & Elementor
 * Description:       Add a marquee, news ticker, logo carousel or scrolling post slider in Elementor or the block editor. 8 widgets, 5 blocks, lightweight CSS animation.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           0.7.2
 * Author:            WPXERO
 * Author URI:        https://wpxero.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       marqueex
 * Domain Path:       /languages
 */

namespace Wpxero\Marqueex;

use Wpxero\Marqueex\Traits\Singleton;
use Wpxero\Marqueex\Utils;
use Wpxero\Marqueex\Admin\Menu;
use Wpxero\Marqueex\Admin\Enqueue;
use Wpxero\Marqueex\Admin;
use Wpxero\Marqueex\Core\Settings;
use Wpxero\Marqueex\Integrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if vendor directory and autoload.php exist
$marqueex_autoload_file = __DIR__ . '/vendor/autoload.php';
if (file_exists($marqueex_autoload_file)) {
    // Load autoloader (vendor/autoload.php).
    require_once $marqueex_autoload_file;
}

/**
 * Main MarqueeX Class
 *
 * @class MarqueeX
 * @since 0.1.0
 */



class MarqueeX {
    use Singleton;

    /**
     * Freemius instance
     * @var object
     */
    private $freemius;

    /**
     * Path to the plugin directory
     *
     * @var $plugin_path
     */
    public $plugin_path;

    /**
     * URL to the plugin directory
     *
     * @var $plugin_url
     */
    public $plugin_url;

    /**
     * Plugin name
     *
     * @var $plugin_name
     */
    public $plugin_name;

    /**
     * Plugin version
     *
     * @var $plugin_version
     */
    public $plugin_version;

    /**
     * Plugin slug
     *
     * @var $plugin_slug
     */
    public $plugin_slug;

    /**
     * Plugin name sanitized
     *
     * @var $plugin_name_sanitized
     */
    public $plugin_name_sanitized;

    /**
     * MarqueeX constructor.
     */
    public function __construct() {
        /* We do nothing here! */
    }

    public function init_hooks() {
        $this->define_constants();
        $this->load_files();
    }
    /**
     * Define Constants
     */
    public static function define_constants() {
        define('MARQUEEX_FILE', __FILE__);
        define('MARQUEEX_NAMESPACE', 'MARQUEEX');
        define('MARQUEEX_SLUG', 'marqueex');
        define('MARQUEEX_VERSION', '0.7.2');
        define('MARQUEEX_DIR_PATH', plugin_dir_path(__FILE__));
        define('MARQUEEX_ADMIN_URL', plugin_dir_url(__FILE__));
        define('MARQUEEX_WP_VERSION', (float) get_bloginfo('version'));
        define('MARQUEEX_PHP_VERSION', (float) phpversion());
    }

    public function load_files() {
        Settings::get_instance();
        Menu::get_instance();
        Enqueue::get_instance();
        Admin\ReviewRequest::get_instance();
        Admin\ProNotice::get_instance();
        Admin\Usage::get_instance();
        Utils\SeoImprovements::get_instance();
        Utils\MetaDescription::get_instance();

        // Load integrations
        $this->load_integrations();
        $this->init_freemius();
    }

    /**
     * Load builder integrations
     */
    private function load_integrations() {
        // Load Elementor integration
        if (class_exists('\Elementor\Plugin')) {
            Integrations\Elementor\ElementorIntegration::get_instance();
        }

        // Load Shortcode integration
        Integrations\Shortcode\ShortcodeIntegration::get_instance();

        // Load Gutenberg integration
        Integrations\Gutenberg\GutenbergIntegration::get_instance();
    }


    /**
     * Initialize Freemius SDK
     */
    private function init_freemius() {
        if (!isset($this->freemius)) {
            // Skip Freemius init during plugin upgrade/install to prevent memory exhaustion.
            // Read-only context check (which WP screen we are on) — not form
            // processing, so no nonce is involved.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (
                (defined('WP_INSTALLING') && WP_INSTALLING) ||
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                (isset($_REQUEST['action']) && in_array(sanitize_key(wp_unslash($_REQUEST['action'])), array('upload-plugin', 'update-plugin', 'delete-plugin'), true))
            ) {
                return $this->freemius;
            }

            // Include Freemius SDK
            if (file_exists(dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php')) {
                require_once dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php';

                try {
                    $this->freemius = fs_dynamic_init(array(
                        'id'                  => '21024',
                        'slug'                => 'marqueex',
                        'premium_slug'        => 'marqueex-pro',
                        'type'                => 'plugin',
                        'public_key'          => 'pk_b8a19bfe3fffa97b497382eddcc3b',
                        'is_premium'          => false,
                        'is_premium_only'     => false,
                        'has_paid_plans'      => true,
                        'is_live'             => true,
                        'is_org_compliant'    => true,
                        'parallel_activation' => array(
                            'enabled'                  => true,
                            'premium_version_basename' => 'marqueex-pro/marqueex-pro.php',
                        ),
                        'menu'                => array(
                            'slug'        => 'marqueex',
                            'first-path'  => 'admin.php?page=marqueex',
                            'network'        => true,
                            'support'     => false,
                            'contact'     => false,
                            'pricing'     => true,
                        ),
                    ));

                    // Send every Freemius pricing link — the "Upgrade" admin
                    // submenu, get_upgrade_url(), license-expired notices — to
                    // the public pricing page instead of the in-admin screen.
                    $this->freemius->add_filter('pricing_url', function () {
                        return Core\Upsell::PRICING_URL;
                    });

                    // The header name is 87 characters of directory search
                    // terms; on the opt-in and license screens it overflows
                    // the card. Show the product name there instead.
                    $this->freemius->add_filter('plugin_title', function () {
                        return 'MarqueeX';
                    });

                    // Signal that Freemius SDK is initiated
                    do_action('marqueex_fs_loaded');
                } catch (\Exception $e) {
                    // Log error but don't break the plugin
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('MarqueeX Freemius Error: ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    }
                }
            }
        }

        return $this->freemius;
    }

    /**
     * Get Freemius instance
     *
     * @return object|null
     */
    public function get_freemius() {
        return $this->freemius;
    }

    /**
     * Check if the user has a valid premium license.
     *
     * This is the SINGLE SOURCE OF TRUTH for premium feature gating.
     * It verifies BOTH conditions:
     *   1. The pro plugin class is loaded (plugin is active)
     *   2. Freemius reports a valid license or trial
     *
     * @return bool True only if pro plugin is active AND license is valid.
     */
    public static function is_premium_active() {
        // Cache the result to avoid repeated Freemius calls within a single request
        static $result = null;
        if ($result !== null) {
            return $result;
        }

        // Condition 1: Pro plugin must be active
        if (!class_exists('\Wpxero\MarqueexPro\MarqueeXPro')) {
            $result = false;
            return $result;
        }

        // Condition 2: Freemius must confirm a valid license
        try {
            $fs = null;

            // Try getting Freemius from the pro plugin first
            if (function_exists('\Wpxero\MarqueexPro\marqueex_pro_fs')) {
                $fs = \Wpxero\MarqueexPro\marqueex_pro_fs();
            }

            // Fallback to free plugin's Freemius instance
            if (!$fs) {
                $instance = self::get_instance();
                $fs = $instance->get_freemius();
            }

            if (!$fs) {
                $result = false;
                return $result;
            }

            // can_use_premium_code() covers both paid licenses and trials
            $result = $fs->can_use_premium_code();
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('MarqueeX: Premium validation error - ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            $result = false;
        }

        return $result;
    }

    /**
     * Activation Hook
     */
    public function activation_hook() {
        // Welcome Page Flag.
        set_transient('_marqueex_welcome_screen_activation_redirect', true, 30);
    }

    /**
     * Deactivation Hook
     */
    public function deactivation_hook() {
    }
}


// Initialize the plugin
add_action('plugins_loaded', function () {
    $instance = \Wpxero\Marqueex\MarqueeX::get_instance();
    $instance->init_hooks();
});

/**
 * Get Freemius instance for free plugin
 *
 * @return object|null
 */
function marqueex_fs() {
    $plugin = MarqueeX::get_instance();
    return $plugin ? $plugin->get_freemius() : null;
}

register_activation_hook(__FILE__, [\Wpxero\Marqueex\MarqueeX::get_instance(), 'activation_hook']);
register_deactivation_hook(__FILE__, [\Wpxero\Marqueex\MarqueeX::get_instance(), 'deactivation_hook']);
