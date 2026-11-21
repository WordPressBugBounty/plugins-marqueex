<?php

namespace Wpxero\Marqueex\Core;

use Wpxero\Marqueex\Traits\Singleton;

if (! defined('ABSPATH')) {
    exit;
}

// Import WordPress classes and functions
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Settings
 */
class Settings extends \WP_REST_Controller {
    use Singleton;
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'wpxero/marqueex/v';

    /**
     * Version.
     *
     * @var string
     */
    protected $version = '1';

    /**
     * Name of the WordPress option that stores all plugin settings.
     *
     * @var string
     */
    const OPTION_NAME = 'marqueex_settings';

    /**
     * Per-request cache of the fully-merged (defaults ∪ stored) settings array.
     *
     * @var array|null
     */
    private static $cached_settings = null;

    /**
     * Settings constructor.
     */
    public function __construct() {
        \add_action('rest_api_init', [$this, 'register_routes']);
        \add_action('init', [$this, 'init_default_settings']);
    }

    /**
     * Read a setting (or the whole settings tree) deep-merged with defaults.
     *
     * This is the single accessor every consumer should use instead of calling
     * get_option() + open-coding its own `?? default` fallback — that pattern
     * caused the same key to resolve to different defaults in different files.
     *
     * @param string|null $path    Dot-path into the settings tree, e.g.
     *                             'builder_support.elementor.enabled'. Null/empty
     *                             returns the entire merged array.
     * @param mixed       $default Value returned when the path is missing.
     * @return mixed
     */
    public static function get($path = null, $default = null) {
        if (self::$cached_settings === null) {
            $instance = self::get_instance();
            $stored = \get_option(self::OPTION_NAME, []);
            if (! is_array($stored)) {
                $stored = [];
            }
            // Deep merge so nested defaults are always backfilled for existing
            // installs whose top-level keys predate a newly-added sub-key.
            self::$cached_settings = $instance->deep_merge($instance->get_default_settings(), $stored);
        }

        if (empty($path)) {
            return self::$cached_settings;
        }

        $value = self::$cached_settings;
        foreach (explode('.', $path) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Invalidate the per-request settings cache after a write.
     */
    private static function flush_cache() {
        self::$cached_settings = null;
    }

    /**
     * Initialize default settings
     */
    public function init_default_settings() {
        $current_settings = \get_option(self::OPTION_NAME, []);
        if (! is_array($current_settings)) {
            $current_settings = [];
        }
        $default_settings = $this->get_default_settings();

        // Deep merge so nested defaults (e.g. a newly-added element key) are
        // backfilled even when the parent key already exists in the stored value.
        $merged_settings = $this->deep_merge($default_settings, $current_settings);

        // Drop settings we no longer read. Upgrades leave orphans behind —
        // `minify_css`, `lazy_loading`, `cache_styles`, `theme_compatibility` and
        // `plugin_compatibility` all lived here at some point and none are read
        // now. Limited to the flat flags in these two groups: element lists are
        // left alone, because an integration may legitimately add keys we do not
        // know about.
        $merged_settings = $this->prune_removed_flags($merged_settings, $default_settings);

        // Loose comparison ignores key ordering, so re-ordering from the merge
        // alone never triggers a redundant DB write on every `init`.
        if ($merged_settings != $current_settings) {
            \update_option(self::OPTION_NAME, $merged_settings);
            self::flush_cache();
        }
    }

    /**
     * Remove flat flags from `performance` and `compatibility` that are no
     * longer part of the defaults.
     *
     * @param array $settings Merged settings.
     * @param array $defaults Canonical defaults.
     * @return array
     */
    private function prune_removed_flags(array $settings, array $defaults) {
        foreach (['performance', 'compatibility'] as $group) {
            if (!isset($settings[$group]) || !is_array($settings[$group])) {
                continue;
            }

            foreach ($settings[$group] as $key => $value) {
                // Never touch nested structures such as responsive_breakpoints.
                if (is_array($value)) {
                    continue;
                }

                if (!array_key_exists($key, $defaults[$group] ?? [])) {
                    unset($settings[$group][$key]);
                }
            }
        }

        return $settings;
    }

    /**
     * Get default settings
     *
     * @return array
     */
    public function get_default_settings() {
        return [
            'builder_support' => [
                'gutenberg' => [
                    'enabled' => true,
                    'auto_detect' => true,
                    'elements' => [
                        'infinite_slider' => true,
                        'text_marquee' => true,
                        'image_marquee' => true,
                        'news_ticker' => true,
                        'post_marquee' => true,
                    ],
                ],
                'elementor' => [
                    // 'enabled' =>  $this->is_elementor_active(),
                    'enabled' => true,
                    'auto_detect' => true,
                    'elements' => [
                        'text_marquee' => true,
                        'news_ticker' => true,
                        'infinite_slider' => true,
                        'post_marquee' => true,
                        // Backfilled so the defaults describe the full registered
                        // widget set; previously these loaded only via `?? true`.
                        'image_marquee' => true,
                        'animated_heading' => true,
                        'animated_word_roller' => true,
                        'team_members_marquee' => true,
                        'testimonial_marquee' => true,
                    ],
                ],
                'shortcode' => [
                    'enabled' => true,
                    'auto_detect' => true,
                    'elements' => [
                        'text_marquee' => true,
                        'news_ticker' => true,
                        'infinite_slider' => true,
                    ],
                ],
            ],
            'performance' => [
                // Emit an HTML comment crediting MarqueeX on pages that use it.
                // Read by Utils\SeoImprovements::add_seo_attribution().
                'show_attribution' => false,
            ],
            'compatibility' => [
                // Generate a meta description from marquee content when no SEO
                // plugin is present. Off by default — see Utils\MetaDescription.
                'meta_description' => false,
                'responsive_breakpoints' => [
                    'mobile' => 480,
                    'tablet' => 1024,
                    'desktop' => 1200,
                ],
            ],
        ];
    }

    /**
     * Register rest routes.
     */
    public function register_routes() {
        $namespace = $this->namespace . $this->version;

        // Update Settings.
        \register_rest_route(
            $namespace,
            '/update_settings/',
            [
                'methods'             => ['POST'],
                'callback'            => [$this, 'update_settings'],
                'permission_callback' => [$this, 'update_settings_permission'],
            ]
        );

        // Get Settings.
        \register_rest_route(
            $namespace,
            '/get_settings/',
            [
                'methods'             => ['GET'],
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permission'],
            ]
        );

        // Test Builder Integration.
        \register_rest_route(
            $namespace,
            '/test_builder/',
            [
                'methods'             => ['POST'],
                'callback'            => [$this, 'test_builder_integration'],
                'permission_callback' => [$this, 'update_settings_permission'],
            ]
        );

        // Dismiss the first-run onboarding panel.
        \register_rest_route(
            $namespace,
            '/dismiss_onboarding/',
            [
                'methods'             => ['POST'],
                'callback'            => [$this, 'dismiss_onboarding'],
                'permission_callback' => [$this, 'update_settings_permission'],
            ]
        );
    }

    /**
     * User meta key recording that this user has dismissed the onboarding panel.
     */
    const ONBOARDING_META = 'marqueex_onboarding_dismissed';

    /**
     * Whether the current user should still see the onboarding panel.
     *
     * Stored per user rather than per site: a second administrator joining an
     * existing site has still never seen it.
     *
     * @return bool
     */
    public static function should_show_onboarding() {
        $user_id = \get_current_user_id();
        if (!$user_id) {
            return false;
        }

        return '' === \get_user_meta($user_id, self::ONBOARDING_META, true);
    }

    /**
     * Dismiss the onboarding panel for the current user, permanently.
     *
     * @param WP_REST_Request $req request object.
     * @return mixed
     */
    public function dismiss_onboarding(WP_REST_Request $req) {
        $user_id = \get_current_user_id();
        if (!$user_id) {
            return $this->error('no_user', \__('No user to dismiss for.', 'marqueex'));
        }

        \update_user_meta($user_id, self::ONBOARDING_META, '1');

        return $this->success(['dismissed' => true]);
    }

    /**
     * Get edit options permissions.
     *
     * @return bool
     */
    public function update_settings_permission() {
        if (! \current_user_can('manage_options')) {
            return $this->error('user_dont_have_permission', \__('User don\'t have permissions to change options.', 'marqueex'), true);
        }

        return true;
    }

    /**
     * Get settings permissions.
     *
     * @return bool
     */
    public function get_settings_permission() {
        if (! \current_user_can('manage_options')) {
            return $this->error('user_dont_have_permission', \__('User don\'t have permissions to view options.', 'marqueex'), true);
        }

        return true;
    }

    /**
     * Update Settings.
     *
     * @param WP_REST_Request $req  request object.
     *
     * @return mixed
     */
    public function update_settings(WP_REST_Request $req) {
        $new_settings = $req->get_param('settings');

        if (is_array($new_settings)) {
            $current_settings = get_option(self::OPTION_NAME, []);
            // Recursive merge so partial updates ({builder_support:{gutenberg:{...}}})
            // don't clobber sibling sub-trees. Numeric/leaf values are replaced.
            $updated_settings = $this->deep_merge($current_settings, $new_settings);

            // Validate settings before saving
            $validated_settings = $this->validate_settings($updated_settings);

            update_option(self::OPTION_NAME, $validated_settings);

            // Clear any cached data
            $this->clear_cache();
            self::flush_cache();

            return $this->success([
                'message' => __('Settings updated successfully.', 'marqueex'),
                'settings' => $validated_settings
            ]);
        }

        return $this->error('invalid_settings', __('Invalid settings data provided.', 'marqueex'));
    }

    /**
     * Recursive associative-array merge. Scalar/leaf values from $override win.
     */
    private function deep_merge(array $base, array $override) {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->deep_merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    /**
     * Get Settings.
     *
     * @param WP_REST_Request $req  request object.
     *
     * @return mixed
     */
    public function get_settings(WP_REST_Request $req) {
        $settings = get_option(self::OPTION_NAME, []);
        if (! is_array($settings)) {
            $settings = [];
        }
        $default_settings = $this->get_default_settings();

        // Deep merge with defaults so nested keys are present, matching the
        // accessor used everywhere else (previously a shallow array_merge).
        $complete_settings = $this->deep_merge($default_settings, $settings);

        return $this->success($complete_settings);
    }

    /**
     * Test Builder Integration.
     *
     * @param WP_REST_Request $req  request object.
     *
     * @return mixed
     */
    public function test_builder_integration(WP_REST_Request $req) {
        $builder = $req->get_param('builder');
        $test_results = [];

        switch ($builder) {
            case 'elementor':
                $test_results = $this->test_elementor_integration();
                break;
            case 'gutenberg':
                $test_results = $this->test_gutenberg_integration();
                break;
            case 'shortcode':
                $test_results = $this->test_shortcode_integration();
                break;
            default:
                return $this->error('invalid_builder', __('Invalid builder specified.', 'marqueex'));
        }

        return $this->success($test_results);
    }

    /**
     * Test Elementor Integration
     *
     * @return array
     */
    private function test_elementor_integration() {
        $results = [
            'status' => 'unknown',
            'message' => '',
            'details' => []
        ];

        // Check if Elementor is installed
        if (!$this->is_elementor_installed()) {
            $results['status'] = 'not_installed';
            $results['message'] = __('Elementor is not installed. Please install Elementor to use this integration.', 'marqueex');
            return $results;
        }

        // Check if Elementor is activated
        if (!$this->is_elementor_active()) {
            $results['status'] = 'not_active';
            $results['message'] = __('Elementor is installed but not activated. Please activate Elementor to use this integration.', 'marqueex');
            return $results;
        }

        // Check Elementor version
        $elementor_version = defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'unknown';
        $results['details']['version'] = $elementor_version;

        // Check minimum version requirement. Same floor the integration itself
        // enforces — a lower one here reports success for a version on which
        // the widgets never register.
        $min_elementor_version = \Wpxero\Marqueex\Integrations\Elementor\ElementorIntegration::MIN_ELEMENTOR_VERSION;
        if (version_compare($elementor_version, $min_elementor_version, '<')) {
            $results['status'] = 'version_incompatible';
            /* translators: 1: detected Elementor version number, 2: required Elementor version number. */
            $results['message'] = sprintf(__('Elementor version %1$s is too old. Please update to version %2$s or higher.', 'marqueex'), $elementor_version, $min_elementor_version);
            return $results;
        }

        // Check if we can register widgets
        try {
            $results['status'] = 'compatible';
            /* translators: %s: detected Elementor version number. */
            $results['message'] = sprintf(__('Elementor integration test successful. Version: %s', 'marqueex'), $elementor_version);
            $results['details']['widgets_available'] = true;
            $results['details']['pro_available'] = $this->is_elementor_pro_available();
        } catch (Exception $e) {
            $results['status'] = 'error';
            $results['message'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Check if Elementor is installed
     *
     * @return bool
     */
    private function is_elementor_installed() {
        return file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php');
    }

    /**
     * Check if Elementor is active
     *
     * @return bool
     */
    private function is_elementor_active() {
        return class_exists('\Elementor\Plugin');
    }

    /**
     * Check if Elementor Pro is available
     *
     * @return bool
     */
    private function is_elementor_pro_available() {
        return class_exists('\ElementorPro\Plugin');
    }

    /**
     * Test Gutenberg Integration
     *
     * @return array
     */
    private function test_gutenberg_integration() {
        $results = [
            'status' => 'compatible',
            'message' => __('Gutenberg integration is always available.', 'marqueex'),
            'details' => [
                'wp_version' => get_bloginfo('version'),
                'gutenberg_available' => function_exists('register_block_type')
            ]
        ];

        return $results;
    }

    /**
     * Test Shortcode Integration
     *
     * @return array
     */
    private function test_shortcode_integration() {
        $results = [
            'status' => 'compatible',
            'message' => __('Shortcode integration is always available.', 'marqueex'),
            'details' => [
                'shortcode_functions_available' => function_exists('add_shortcode')
            ]
        ];

        return $results;
    }

    /**
     * Validate settings before saving
     *
     * @param array $settings
     * @return array
     */
    private function validate_settings($settings) {
        $default_settings = $this->get_default_settings();

        // Ensure all required keys exist
        foreach ($default_settings as $key => $default_value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $default_value;
            }
        }

        // Validate builder support settings
        if (isset($settings['builder_support'])) {
            foreach ($settings['builder_support'] as $builder => $config) {
                if (isset($config['enabled']) && !is_bool($config['enabled'])) {
                    $settings['builder_support'][$builder]['enabled'] = (bool) $config['enabled'];
                }
                if (isset($config['auto_detect']) && !is_bool($config['auto_detect'])) {
                    $settings['builder_support'][$builder]['auto_detect'] = (bool) $config['auto_detect'];
                }
            }
        }

        // Validate performance settings (all booleans)
        if (isset($settings['performance']) && is_array($settings['performance'])) {
            foreach ($settings['performance'] as $key => $value) {
                if (!is_bool($value)) {
                    $settings['performance'][$key] = (bool) $value;
                }
            }
        }

        // Compatibility holds one boolean flag plus the breakpoint map.
        if (isset($settings['compatibility']['meta_description'])) {
            $settings['compatibility']['meta_description'] = (bool) $settings['compatibility']['meta_description'];
        }

        // Validate compatibility settings — clamp responsive breakpoints to
        // a sane pixel range so users can't store 1e9 etc.
        if (isset($settings['compatibility']['responsive_breakpoints'])) {
            $defaults = $default_settings['compatibility']['responsive_breakpoints'];
            foreach ($settings['compatibility']['responsive_breakpoints'] as $device => $value) {
                $int = is_numeric($value) ? (int) $value : -1;
                if ($int < 1 || $int > 3000) {
                    $settings['compatibility']['responsive_breakpoints'][$device] =
                        $defaults[$device] ?? 1024;
                } else {
                    $settings['compatibility']['responsive_breakpoints'][$device] = $int;
                }
            }

            // Mobile rules sit inside max-width:mobile and tablet rules inside
            // max-width:tablet, so mobile must be the narrower one. A swapped
            // pair would apply "mobile" styles to tablets and wider; fall back
            // to the defaults rather than guess which value was meant.
            $bp = $settings['compatibility']['responsive_breakpoints'];
            if (isset($bp['mobile'], $bp['tablet']) && (int) $bp['mobile'] >= (int) $bp['tablet']) {
                $settings['compatibility']['responsive_breakpoints']['mobile'] = $defaults['mobile'] ?? 480;
                $settings['compatibility']['responsive_breakpoints']['tablet'] = $defaults['tablet'] ?? 1024;
            }
        }

        return $settings;
    }

    /**
     * Clear plugin-owned caches when settings are updated.
     *
     * Note: we deliberately do NOT call wp_cache_flush() here — flushing the
     * entire object cache for one option write punishes every other plugin.
     */
    private function clear_cache() {
        delete_transient('marqueex_builder_cache');

        // Purge cached block-registration scans (see GutenbergIntegration).
        global $wpdb;
        if (isset($wpdb)) {
            $value_like = $wpdb->esc_like('_transient_marqueex_registerable_blocks_') . '%';
            $timeout_like = $wpdb->esc_like('_transient_timeout_marqueex_registerable_blocks_') . '%';
            // Direct, prepared bulk delete of our own versioned transients. There is
            // no core API for a wildcard transient purge, and caching the result of a
            // cache-purge is meaningless — so the direct-query / no-cache notices are
            // intentionally suppressed here.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $value_like));
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $timeout_like));
        }
    }

    /**
     * Success rest.
     *
     * @param mixed $response response data.
     * @return mixed
     */
    public function success($response) {
        return new WP_REST_Response(
            [
                'success'  => true,
                'response' => $response,
            ],
            200
        );
    }

    /**
     * Error rest.
     *
     * @param mixed   $code       error code.
     * @param mixed   $response   response data.
     * @param boolean $true_error use true error response to stop the code processing.
     * @return mixed
     */
    public function error($code, $response, $true_error = false) {
        if ($true_error) {
            return new WP_Error($code, $response, ['status' => 401]);
        }

        return new WP_REST_Response(
            [
                'error'      => true,
                'success'    => false,
                'error_code' => $code,
                'response'   => $response,
            ],
            401
        );
    }
}
