<?php

namespace Wpxero\Marqueex\Integrations\Elementor;

use Wpxero\Marqueex\Traits\Singleton;
use Wpxero\Marqueex\Core\Settings;
use Wpxero\Marqueex\Core\Upsell;

if (!defined('ABSPATH')) {
    exit;
}

// Import WordPress functions
use function add_action;
use function get_option;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_register_script;
use function wp_register_style;
use function plugins_url;
use function admin_url;
use function wp_create_nonce;
use function __;

/**
 * Elementor Integration Class
 */
class ElementorIntegration {
    use Singleton;

    /**
     * Oldest Elementor release these widgets support.
     *
     * `elementor/widgets/register` and `$widgets_manager->register()` — the APIs
     * used below — arrived in 3.5.0. On anything older the widgets silently fail
     * to register, which previously looked like a broken plugin.
     */
    const MIN_ELEMENTOR_VERSION = '3.5.0';

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize Elementor integration
     */
    public function init() {
        // Check if Elementor is active
        if (!$this->is_elementor_active()) {
            return;
        }

        if (!$this->is_elementor_supported()) {
            add_action('admin_notices', [$this, 'outdated_elementor_notice']);
            return;
        }

        // Check if integration is enabled
        $elementor_enabled = Settings::get('builder_support.elementor.enabled', true);

        if (!$elementor_enabled) {
            return;
        }

        // Hook into Elementor
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_categories'], 1);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        // add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_elementor_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_elementor_assets']);

        // Marketing: advertise the Pro scroll-interaction features (mouse wheel + drag)
        // as a locked section inside the free marquee widgets. Hidden when premium is
        // active — the pro add-on then injects the real working controls instead.
        // Note: post-marquee's section id is `marquee_settings`; the others use
        // `marquee_settings_section`.
        add_action('elementor/element/marqueex-text-marquee/marquee_settings_section/after_section_end', [$this, 'add_interaction_upsell_section'], 15, 2);
        add_action('elementor/element/marqueex-image-marquee/marquee_settings_section/after_section_end', [$this, 'add_interaction_upsell_section'], 15, 2);
        add_action('elementor/element/marqueex-news-ticker/marquee_settings_section/after_section_end', [$this, 'add_interaction_upsell_section'], 15, 2);
        add_action('elementor/element/marqueex-post-marquee/marquee_settings/after_section_end', [$this, 'add_interaction_upsell_section'], 15, 2);
    }

    /**
     * Add a locked "Scroll Interactions" section (Mouse Wheel + Drag) to free
     * marquee widgets. Shown only to non-premium users — marqueex-pro registers
     * the real working section at priority 20 when licensed.
     */
    public function add_interaction_upsell_section($element, $args) {
        if (!Upsell::should_show()) {
            return;
        }

        $element->start_controls_section(
            'marqueex_interactions_upsell',
            [
                'label' => __('Scroll Interactions', 'marqueex'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        Upsell::add_elementor_notice($element, 'pro_interactions_notice', [
            'title'       => __('Let visitors drive the marquee', 'marqueex'),
            'description' => __('Turn a strip that scrolls past people into one they can grab. Visitors spin the wheel to speed it up or reverse it, or drag it to scrub through — and it eases back to your set speed on its own.', 'marqueex'),
            'items'       => [
                __('Mouse wheel control with adjustable sensitivity', 'marqueex'),
                __('Drag to scroll, with flick momentum on release', 'marqueex'),
                __('Optional "scroll sets direction" mode', 'marqueex'),
            ],
            'cta'         => __('Unlock scroll interactions', 'marqueex'),
            'source'      => 'elementor-scroll-interactions',
        ]);

        $element->end_controls_section();
    }

    /**
     * Check if Elementor is active
     */
    private function is_elementor_active() {
        return class_exists('\Elementor\Plugin');
    }

    /**
     * Whether the installed Elementor is new enough to register our widgets.
     *
     * @return bool
     */
    private function is_elementor_supported() {
        if (!defined('ELEMENTOR_VERSION')) {
            // Version constant missing — assume supported rather than disabling the
            // integration on an unexpected build.
            return true;
        }

        return version_compare(ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '>=');
    }

    /**
     * Explain why the MarqueeX widgets are missing from the Elementor panel.
     */
    public function outdated_elementor_notice() {
        if (!current_user_can('update_plugins')) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <?php
                printf(
                    /* translators: 1: required Elementor version, 2: installed Elementor version */
                    esc_html__('MarqueeX needs Elementor %1$s or newer to add its widgets. You are running %2$s — the MarqueeX widgets will not appear in the Elementor panel until it is updated. The MarqueeX blocks in the block editor are unaffected.', 'marqueex'),
                    esc_html(self::MIN_ELEMENTOR_VERSION),
                    esc_html(defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : __('an older version', 'marqueex'))
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Single source of truth for the registered Elementor widgets.
     *
     * Keyed by the settings element key. Each entry describes the widget class
     * plus its registered style/script handles, files and dependencies, so
     * registration, asset enqueuing and status reporting all derive from here
     * (previously these three lists were maintained separately and drifted).
     *
     *
     * @return array<string, array>
     */
    private function get_widget_definitions() {
        $frontend_deps = ['jquery', 'marqueex-elementor-frontend'];

        return [
            'image_marquee' => [
                'class'         => 'ImageMarquee',
                'style_handle'  => 'marqueex-image-marquee',
                'style_css'     => 'image-marquee.css',
                'script_handle' => 'marqueex-image-marquee',
                'script_js'     => 'image-marquee.js',
                'script_deps'   => $frontend_deps,
            ],
            'text_marquee' => [
                'class'         => 'TextMarquee',
                'style_handle'  => 'marqueex-text-marquee',
                'style_css'     => 'text-marquee.css',
                'script_handle' => 'marqueex-text-marquee',
                'script_js'     => 'text-marquee.js',
                'script_deps'   => $frontend_deps,
            ],
            'animated_heading' => [
                'class'         => 'AnimatedHeading',
                'style_handle'  => 'marqueex-animated-heading-style',
                'style_css'     => 'animated-heading.css',
                'script_handle' => 'marqueex-animated-heading',
                'script_js'     => 'animated-heading.js',
                // Animated Heading bundles its own animation logic; no frontend dep.
                'script_deps'   => ['jquery'],
            ],
            'animated_word_roller' => [
                'class'         => 'AnimatedWordRoller',
                'style_handle'  => 'marqueex-animated-word-roller-style',
                'style_css'     => 'animated-word-roller.css',
                'script_handle' => 'marqueex-animated-word-roller',
                'script_js'     => 'animated-word-roller.js',
                'script_deps'   => $frontend_deps,
            ],
            'news_ticker' => [
                'class'         => 'NewsTicker',
                'style_handle'  => 'marqueex-news-ticker',
                'style_css'     => 'news-ticker.css',
                'script_handle' => 'marqueex-news-ticker',
                'script_js'     => 'news-ticker.js',
                'script_deps'   => $frontend_deps,
            ],
            'post_marquee' => [
                'class'         => 'PostMarquee',
                'style_handle'  => 'marqueex-post-marquee',
                'style_css'     => 'post-marquee.css',
                'script_handle' => 'marqueex-post-marquee',
                'script_js'     => 'post-marquee.js',
                'script_deps'   => $frontend_deps,
            ],
            'team_members_marquee' => [
                'class'         => 'TeamMembersMarquee',
                'style_handle'  => 'marqueex-team-members-marquee',
                'style_css'     => 'team-members-marquee.css',
                'script_handle' => 'marqueex-team-members-marquee',
                'script_js'     => 'team-members-marquee.js',
                'script_deps'   => $frontend_deps,
            ],
            'testimonial_marquee' => [
                'class'         => 'TestimonialMarquee',
                'style_handle'  => 'marqueex-testimonial-marquee',
                'style_css'     => 'testimonial-marquee.css',
                'script_handle' => 'marqueex-testimonial-marquee',
                'script_js'     => 'testimonial-marquee.js',
                'script_deps'   => $frontend_deps,
            ],
        ];
    }

    /**
     * Whether a widget is enabled in settings (defaults to true).
     *
     * @param string $key Settings element key.
     * @return bool
     */
    private function is_widget_enabled($key) {
        $elements = Settings::get('builder_support.elementor.elements', []);
        return (bool) ($elements[$key] ?? true);
    }

    /**
     * Register MarqueeX widgets
     */
    public function register_widgets($widgets_manager) {
        foreach ($this->get_widget_definitions() as $key => $def) {
            if (!$this->is_widget_enabled($key)) {
                continue;
            }
            require_once __DIR__ . '/Widgets/' . $def['class'] . '.php';
            $fqcn = __NAMESPACE__ . '\\Widgets\\' . $def['class'];
            $widgets_manager->register(new $fqcn());
        }
    }

    /**
     * Add custom widget categories
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'marqueex',
            [
                'title' => __('MarqueeX', 'marqueex'),
                'icon' => 'fa fa-sliders',
            ]
        );
    }

    /**
     * Enqueue editor scripts
     */
    public function enqueue_editor_scripts() {
        wp_enqueue_style(
            'marqueex-elementor-editor',
            plugins_url('/assets/css/elementor-editor.css', MARQUEEX_FILE),
            [],
            MARQUEEX_VERSION
        );
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_elementor_assets() {
        // Register (don't enqueue) the shared base styles. Each widget style
        // below depends on this handle, so common.css loads only on pages that
        // actually render a MarqueeX widget — not on every front-end page.
        wp_register_style(
            'marqueex-common',
            plugins_url('/assets/css/common.css', MARQUEEX_FILE),
            [],
            MARQUEEX_VERSION
        );
        wp_register_script(
            'marqueex-elementor-frontend',
            plugins_url('/assets/js/elementor-frontend.js', MARQUEEX_FILE),
            ['jquery'],
            MARQUEEX_VERSION,
            true
        );

        // Register each widget's style (chained to common.css) and script from
        // the canonical definition map. Elementor enqueues these on demand via
        // each widget's get_style_depends()/get_script_depends().
        foreach ($this->get_widget_definitions() as $def) {
            wp_register_style(
                $def['style_handle'],
                plugins_url('/assets/css/' . $def['style_css'], MARQUEEX_FILE),
                ['marqueex-common'],
                MARQUEEX_VERSION
            );
            wp_register_script(
                $def['script_handle'],
                plugins_url('/assets/js/' . $def['script_js'], MARQUEEX_FILE),
                $def['script_deps'],
                MARQUEEX_VERSION,
                true
            );
        }
    }

    /**
     * Get integration status
     */
    public function get_status() {
        $status = [
            'active' => false,
            'version' => null,
            'widgets_registered' => false,
            'enabled_widgets' => [],
        ];

        if ($this->is_elementor_active()) {
            $status['active'] = true;
            $status['version'] = \Elementor\Plugin::$instance->get_version();

            // Derive enabled widgets from the same canonical definition map so
            // status can never drift from what register_widgets() actually does.
            $enabled_widgets = [];
            foreach (array_keys($this->get_widget_definitions()) as $key) {
                if ($this->is_widget_enabled($key)) {
                    $enabled_widgets[] = $key;
                }
            }

            $status['enabled_widgets'] = $enabled_widgets;
            $status['widgets_registered'] = !empty($enabled_widgets);
        }

        return $status;
    }
}
