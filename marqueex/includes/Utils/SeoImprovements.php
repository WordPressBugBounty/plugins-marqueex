<?php
namespace Wpxero\Marqueex\Utils;

use Wpxero\Marqueex\Traits\Singleton;
use Wpxero\Marqueex\Core\Settings;

/**
 * Plugin-row links and the optional front-end attribution comment.
 *
 * This class used to emit schema.org JSON-LD on any page containing a MarqueeX
 * block. That has been removed: it declared the marquee to be the page's
 * `mainContentOfPage` (a scrolling logo strip rarely is), and — worse — if the
 * scrolling text happened to contain "sale", "discount", "offer" or "deal" it
 * emitted a schema.org `Offer` describing a product that may not exist. Search
 * engines treat fabricated structured data as spam, and the consequences land on
 * the site owner, not on us. Marquee plugins should not be writing schema.
 *
 * @package marqueex
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SeoImprovements
 */
class SeoImprovements {
    use Singleton;

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('plugin_row_meta', array($this, 'add_plugin_meta_links'), 10, 2);
        add_action('wp_footer', array($this, 'add_seo_attribution'), 999);
    }

    /**
     * Check if post content contains MarqueeX blocks
     *
     * @param string $content Post content
     * @return bool Whether post has MarqueeX blocks
     */
    private function post_has_marqueex_blocks($content) {
        // Cheap substring test — covers every block in the namespace, including
        // the four added in 0.5.0. The previous has_block() pair only matched
        // `infinite-slider`, so newer blocks were never detected.
        return strpos($content, '<!-- wp:marqueex/') !== false;
    }

    /**
     * Add meta links to the plugin row in the plugins page
     *
     * @param array $links Plugin row meta links
     * @param string $file Plugin base file
     * @return array Modified meta links
     */
    public function add_plugin_meta_links($links, $file) {
        if (plugin_basename(MARQUEEX_FILE) === $file) {
            $new_links = array(
                'docs' => '<a href="https://wpxero.com/plugins/marqueex/docs" aria-label="' . esc_attr__('Documentation for MarqueeX', 'marqueex') . '">' . esc_html__('Documentation', 'marqueex') . '</a>',
                'support' => '<a href="https://wordpress.org/support/plugin/marqueex/" aria-label="' . esc_attr__('Support for MarqueeX', 'marqueex') . '">' . esc_html__('Support', 'marqueex') . '</a>'
            );

            $links = array_merge($links, $new_links);
        }

        return $links;
    }

    /**
     * Add an attribution comment to the footer.
     *
     * Opt-in via marqueex_settings.performance.show_attribution. Default off.
     */
    public function add_seo_attribution() {
        if (! Settings::get('performance.show_attribution', false)) {
            return;
        }

        global $post;
        if (!is_singular() || !is_object($post) || !has_blocks($post->post_content)) {
            return;
        }
        if (!$this->post_has_marqueex_blocks($post->post_content)) {
            return;
        }

        echo '<!-- This page uses MarqueeX - Smooth Marquee & Infinite Scroll for WordPress by WPXERO (https://wpxero.com/marqueex) -->';
    }
}
