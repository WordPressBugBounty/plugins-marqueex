<?php

namespace Wpxero\Marqueex\Integrations\Gutenberg;

use Wpxero\Marqueex\Traits\Singleton;
use WP_Block_Type_Registry;
use Wpxero\Marqueex\Core\CSSBuilder;
use Wpxero\Marqueex\Core\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg Integration Class
 */
class GutenbergIntegration {
    use Singleton;

    /**
     * Whether the `MARQUEEX_VIEW` license flag has already been localized this
     * request. Guards the per-render fallback so it runs at most once.
     *
     * @var bool
     */
    private $view_localized = false;

    /**
     * blockId values whose Design-tab inline CSS has already been emitted this
     * request, keyed for O(1) lookup. Dedupes the page-level scan against the
     * per-render fallback (and nested-block recursion) so CSS prints once.
     *
     * @var array<string,bool>
     */
    private $styled_block_ids = array();

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize Shortcode integration
     */
    public function init() {
        // Check if integration is enabled
        $gutenberg_enabled = Settings::get('builder_support.gutenberg.enabled', true);

        if (!$gutenberg_enabled) {
            return;
        }

        // Register blocks
        $this->register_blocks();

        add_action('wp_enqueue_scripts', array($this, 'enqueue_front_scripts'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_filter('block_categories_all', array($this, 'add_custom_block_category'));
        // Fallback for blocks rendered outside their own page (e.g. injected via a
        // "page content renderer"): the wp_enqueue_scripts gate keys on the host
        // post_content, which won't contain the block marker. Localizing on render
        // guarantees the license flag is present wherever a MarqueeX block appears.
        add_filter('render_block', array($this, 'ensure_view_localized_on_render'), 10, 2);
    }

    public function enqueue_editor_assets() {
        $asset_path = MARQUEEX_DIR_PATH . '/dist/marqueex.asset.php';
        if (!file_exists($asset_path)) {
            return;
        }


        $args = require $asset_path;
        // Version the editor stylesheet by its own file mtime, not the JS asset
        // hash — otherwise CSS-only rebuilds (e.g. control styling) are not
        // cache-busted because the JS-derived $args['version'] is unchanged.
        $css_path = MARQUEEX_DIR_PATH . '/dist/marqueex.css';
        $css_ver  = file_exists($css_path) ? filemtime($css_path) : $args['version'];
        wp_enqueue_style('@marqueex/library', MARQUEEX_ADMIN_URL . 'dist/marqueex.css', array(), $css_ver);
        wp_register_script('@marqueex/library', MARQUEEX_ADMIN_URL . 'dist/marqueex.js', $args['dependencies'], $args['version'], true);
        // The shared control library carries its own translatable __() strings.
        // Block scripts get translations automatically via their block.json
        // `textdomain`; the library handle needs it set explicitly.
        wp_set_script_translations('@marqueex/library', 'marqueex', MARQUEEX_DIR_PATH . 'languages');
        wp_localize_script('@marqueex/library', 'MARQUEEX', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'home_url' => home_url('/'),
            'nonce' => wp_create_nonce('marqueex-nonce'),
            'rest_url' => rest_url('wpxero/marqueex/v1'),
            'assetsPath' =>  MARQUEEX_ADMIN_URL . 'assets/',
            'isPremium' => \Wpxero\Marqueex\MarqueeX::is_premium_active(),
            // Upgrade destination is resolved server-side so every CTA points at the
            // same place (Freemius in-admin pricing, or the trial when one is
            // available) and carries its own attribution source.
            'upgradeUrl' => \Wpxero\Marqueex\Core\Upsell::url('block-editor'),
            'upgradeLabel' => \Wpxero\Marqueex\Core\Upsell::cta_label(),
            'upgradeMeta' => \Wpxero\Marqueex\Core\Upsell::cta_meta(),
        ));
    }

    public function add_custom_block_category($categories) {
        $category = array(
            'slug' => 'marqueex',
            'title' => 'MarqueeX',
        );
        return array_merge(array($category), $categories);
    }

    public function register_blocks() {
        $dist_dir = MARQUEEX_DIR_PATH . 'dist/';
        if (!is_dir($dist_dir)) {
            return;
        }
        foreach ($this->get_registerable_blocks($dist_dir) as $name) {
            register_block_type($dist_dir . $name);
        }
    }

    /**
     * Return the list of block directories that should be registered, cached
     * per version + settings hash to avoid `scandir` on every request.
     */
    private function get_registerable_blocks($dist_dir) {
        $active_blocks = Settings::get('builder_support.gutenberg.elements', []);

        // Include the dist directory's mtime in the cache key so newly built
        // block directories invalidate the cache immediately. Without this, the
        // list stays cached for an hour even after a block is added to dist/,
        // because the settings hash alone does not change.
        $dist_signature = @filemtime($dist_dir) ?: 0;
        $cache_key = 'marqueex_registerable_blocks_' . MARQUEEX_VERSION . '_' . md5(wp_json_encode($active_blocks) . '|' . $dist_signature);
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $registerable = [];
        foreach (scandir($dist_dir) as $name) {
            if ($name === '.' || $name === '..' || str_contains($name, '.')) {
                continue;
            }
            $option_name = str_replace('-', '_', $name);
            $should_register = !empty($active_blocks[$option_name]);

            if (!$should_register && str_ends_with($name, '-item')) {
                $parent_option = str_replace('-', '_', substr($name, 0, -strlen('-item')));
                $should_register = !empty($active_blocks[$parent_option]);
            }

            if ($should_register) {
                $registerable[] = $name;
            }
        }

        set_transient($cache_key, $registerable, HOUR_IN_SECONDS);
        return $registerable;
    }


    /**
     * MarqueeX block names whose frontend `view` script should receive the
     * `MARQUEEX_VIEW` localization (license flag) so Pro-gated interactions
     * only activate when licensed.
     *
     * @return string[]
     */
    private function block_view_handles() {
        $blocks = [
            'infinite-slider',
            'text-marquee',
            'image-marquee',
            'news-ticker',
            'post-marquee',
        ];

        return array_map(static function ($name) {
            // Matches the handle WordPress derives from a block's `viewScript`.
            return 'marqueex-' . $name . '-view-script';
        }, $blocks);
    }

    public function enqueue_front_scripts() {
        $post = get_post();
        if (!$post || empty($post->post_content)) {
            return;
        }
        // Only do work when the post actually contains a MarqueeX block. A cheap
        // substring check avoids parsing blocks on pages that have none.
        if (strpos($post->post_content, '<!-- wp:marqueex/') === false) {
            return;
        }

        $this->localize_view_license();

        // Emit per-block inline CSS (Design-tab controls) for any MarqueeX block.
        // style_block keys on the `blockId` attribute, so it is block-agnostic.
        foreach (parse_blocks($post->post_content) as $block) {
            $this->style_block($block);
        }
    }

    /**
     * Enqueue the shared frontend stylesheet and surface the license flag
     * (`MARQUEEX_VIEW`) to every block's view script. Idempotent: safe to call
     * from both the page-level enqueue and the per-render fallback.
     */
    private function localize_view_license() {
        if ($this->view_localized) {
            return;
        }
        $this->view_localized = true;

        wp_enqueue_style('marqueex-frontend', MARQUEEX_ADMIN_URL . 'dist/style-marqueex.css', [], MARQUEEX_VERSION);

        // wp_localize_script is a no-op for handles that aren't enqueued on this page.
        $is_premium = \Wpxero\Marqueex\MarqueeX::is_premium_active();
        foreach ($this->block_view_handles() as $handle) {
            wp_localize_script($handle, 'MARQUEEX_VIEW', ['isPremium' => $is_premium]);
        }
    }

    /**
     * Localize the license flag and emit Design-tab inline CSS when a MarqueeX
     * block renders, regardless of which post owns it. Covers blocks injected
     * into a different page (e.g. a "page content renderer"), where
     * enqueue_front_scripts' host-content gate never fires. Returns content
     * unchanged. The license localization is guarded to run once; the inline
     * CSS is deduped per blockId, so re-styling already-handled blocks is a
     * no-op on normal pages.
     *
     * @param string $block_content
     * @param array  $block
     * @return string
     */
    public function ensure_view_localized_on_render($block_content, $block) {
        $name = $block['blockName'] ?? '';
        if ($name && strpos($name, 'marqueex/') === 0) {
            $this->localize_view_license();
            $this->style_block($block);
        }
        return $block_content;
    }

    public function style_block($block, $depth = 0) {
        // Guard against pathological / malformed block trees.
        if ($depth > 16) {
            return;
        }
        $attrs = $block['attrs'] ?? array();
        $block_id = $attrs['blockId'] ?? '';
        if ($block_id && !isset($this->styled_block_ids[$block_id])) {
            $this->styled_block_ids[$block_id] = true;
            $css = $this->block_css($block);
            if ($css) {
                $escaped_css = wp_strip_all_tags($css); // Escaping as late as possible
                $escaped_css = self::minify_css($escaped_css);

                wp_add_inline_style('marqueex-frontend', $escaped_css);
            }
        }

        foreach ($block['innerBlocks'] ?? array() as $inner_block) {
            $this->style_block($inner_block, $depth + 1);
        }
    }

    /**
     * Collapse whitespace out of generated CSS.
     *
     * Always applied. CSSBuilder already emits compact rulesets, so in practice
     * this only ever has work to do on a block's `customCSS`, which is passed
     * through as the user typed it.
     *
     * The ruleset string may also carry a block's `customCSS`, which is authored
     * by a user and passed through verbatim. Whitespace inside a quoted value is
     * significant (`content: "a ; b"`), and this is a whitespace collapser rather
     * than a real CSS parser — so when a quote is present anywhere in the string,
     * bail and emit it unchanged. Losing a few bytes is the right trade against
     * corrupting someone's declaration.
     *
     * @param string $css
     * @return string
     */
    private static function minify_css($css) {
        if (false !== strpos($css, '"') || false !== strpos($css, "'")) {
            return $css;
        }

        // Strip comments, then collapse runs of whitespace and the spaces that sit
        // either side of the structural characters.
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>~])\s*/', '$1', $css);
        $css = str_replace(';}', '}', $css);

        return trim($css);
    }

    public function block_css($block) {

        $css = '';
        $registry = WP_Block_Type_Registry::get_instance(); // Get the block type registry instance
        $attrs = $block['attrs'] ?? array();
        $block_id = $attrs['blockId'] ?? '';
        if ($block_id) {
            foreach ($attrs as $attr => $value) {
                if (is_array($value) && !empty($value)) {
                    $selector = $value['__selector__'] ?? null;
                    if (!$selector) {
                        $block_type = $registry->get_registered($block['blockName']);
                        if ($block_type) {
                            $selector = $block_type->attributes[$attr]['selector'] ?? null;
                        }
                    }
                    $css .= $this->build_css("marqueex-$block_id", $value, array('selector' => $selector));
                }
            }
        }
        return $css;
    }

    public function build_css($id, $attributes, $options = array()) {
        // Fresh builder per attribute group; CSSBuilder accumulates state and
        // must not be reused (previously a Singleton, which mixed declarations
        // across attributes and blocks).
        $builder = new CSSBuilder();
        $builder->add_attributes($attributes);
        return $builder->to_css($id, $options);
    }

    /**
     * Get integration status
     */
    public function get_status() {
        return [
            'active' => true,
            'blocks_registered' => true,
            'blocks' => [
                'marqueex_slider' => true,
                'marqueex_ticker' => true,
                'marqueex_infinite' => true,
            ],
        ];
    }
}
