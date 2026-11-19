<?php

namespace Wpxero\Marqueex\Utils;

use Wpxero\Marqueex\Traits\Singleton;
use Wpxero\Marqueex\Core\Settings;

/**
 * Optional meta description generation for pages using MarqueeX blocks.
 *
 * OFF by default. Rewriting a site's meta description is an SEO decision that
 * belongs to the site owner (or their SEO plugin), not to a marquee plugin —
 * doing it silently was surprising behaviour with no way to turn it off.
 *
 * Enable with marqueex_settings.compatibility.meta_description.
 *
 * @package marqueex
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Class MetaDescription
 */
class MetaDescription {
    use Singleton;
    /**
     * Constructor
     */
    public function __construct() {
        if (! Settings::get('compatibility.meta_description', false)) {
            return;
        }

        add_filter('wpseo_metadesc', array($this, 'modify_yoast_meta_description'), 10, 1);
        add_filter('rank_math/frontend/description', array($this, 'modify_rankmath_description'), 10, 1);
        add_action('wp_head', array($this, 'add_default_meta_description'), 1);
    }

    /**
     * Modify Yoast SEO meta description
     *
     * @param string $description The current meta description
     * @return string Modified description
     */
    public function modify_yoast_meta_description($description) {
        // Only modify if Yoast is active and we have an empty description
        if (empty($description) && $this->should_modify_description()) {
            return $this->get_marqueex_meta_description();
        }
        return $description;
    }

    /**
     * Modify Rank Math meta description
     *
     * @param string $description The current meta description
     * @return string Modified description
     */
    public function modify_rankmath_description($description) {
        // Only modify if Rank Math is active and we have an empty description
        if (empty($description) && $this->should_modify_description()) {
            return $this->get_marqueex_meta_description();
        }
        return $description;
    }

    /**
     * Add default meta description if no SEO plugin is active
     */
    public function add_default_meta_description() {
        // Only add if no SEO plugin is active
        if ($this->is_seo_plugin_active() || !$this->should_modify_description()) {
            return;
        }

        $description = $this->get_marqueex_meta_description();
        if ('' === $description) {
            return;
        }

        echo '<meta name="description" content="' . esc_attr($description) . '" />';
    }

    /**
     * Check if we should modify the description
     *
     * @return bool Whether description should be modified
     */
    private function should_modify_description() {
        global $post;

        // Only on singular pages with blocks
        if (!is_singular() || !is_object($post) || !has_blocks($post->post_content)) {
            return false;
        }

        // Only if page contains MarqueeX blocks. A substring test covers every
        // block in the namespace; the previous has_block() pair matched only the
        // legacy infinite-slider.
        return strpos($post->post_content, '<!-- wp:marqueex/') !== false;
    }

    /**
     * Build a meta description from the text a page's marquees actually carry.
     *
     * @return string Generated meta description, or '' when there is nothing to say.
     */
    private function get_marqueex_meta_description() {
        global $post;

        if (!is_object($post)) {
            return '';
        }

        $content = $this->extract_marqueex_content(parse_blocks($post->post_content));

        if (empty($content)) {
            // Nothing to describe. Return nothing rather than inventing a sentence
            // about the plugin — that is the site's meta description, not ours.
            return '';
        }

        // De-duplicate: a ticker and a text marquee on one page often repeat a
        // headline, and a description that stutters is worse than a short one.
        $content = array_values(array_unique($content));

        $description = trim(implode(' — ', $content));
        $description = preg_replace('/\s+/u', ' ', $description);

        // Search engines truncate around 155–160 characters.
        if (function_exists('mb_strlen') && mb_strlen($description) > 155) {
            $description = trim(mb_substr($description, 0, 152)) . '…';
        } elseif (!function_exists('mb_strlen') && strlen($description) > 155) {
            $description = trim(substr($description, 0, 152)) . '…';
        }

        return $description;
    }

    /**
     * Collect the human-readable text out of MarqueeX blocks.
     *
     * Four of the five blocks are dynamic — their `save()` returns null and they
     * render through render.php — so they have no `innerContent` at all and their
     * text lives in block attributes. Reading only `innerContent`, as this used
     * to, meant the description was never generated for anything except the
     * legacy Infinite Slider.
     *
     * @param array $blocks Parsed blocks.
     * @return string[] Text fragments, in document order.
     */
    private function extract_marqueex_content($blocks) {
        $content = array();

        foreach ($blocks as $block) {
            $name = $block['blockName'] ?? '';

            if ($name && strpos($name, 'marqueex/') === 0) {
                $content = array_merge($content, $this->extract_block_text($block));
            }

            // Inner blocks regardless of the parent — an Infinite Slider's items
            // are separate blocks, and a marquee can sit inside a group.
            if (!empty($block['innerBlocks'])) {
                $content = array_merge(
                    $content,
                    $this->extract_marqueex_content($block['innerBlocks'])
                );
            }
        }

        return $content;
    }

    /**
     * Text carried by a single MarqueeX block.
     *
     * @param array $block Parsed block.
     * @return string[]
     */
    private function extract_block_text($block) {
        $text = array();
        $attrs = $block['attrs'] ?? array();

        // Text Marquee and News Ticker keep their entries in `items`, each a
        // { text, url, … } record. When `contentType` is 'posts' the entries are
        // fetched at render time and whatever sits in `items` is stale sample
        // data, so it must not describe the page.
        //
        // Note these are the attributes as SAVED, not as rendered: parse_blocks()
        // does not merge block.json defaults, and deliberately we do not either.
        // A marquee the author never edited still carries placeholder copy
        // ("Breaking news: Your first headline here…"), and that must never end
        // up as a site's meta description. No authored text, no description.
        $source = $attrs['contentType'] ?? 'custom';

        if ('posts' !== $source && !empty($attrs['items']) && is_array($attrs['items'])) {
            foreach ($attrs['items'] as $item) {
                if (!is_array($item) || empty($item['text'])) {
                    continue;
                }
                $clean = $this->clean_text($item['text']);
                if ('' !== $clean) {
                    $text[] = $clean;
                }
            }
        }

        // Static blocks (the Infinite Slider and its items) do have innerContent.
        if (!empty($block['innerContent'])) {
            foreach ($block['innerContent'] as $inner) {
                if (!is_string($inner) || '' === trim($inner)) {
                    continue;
                }
                $clean = $this->clean_text($inner);
                if ('' !== $clean) {
                    $text[] = $clean;
                }
            }
        }

        return $text;
    }

    /**
     * Strip markup and collapse whitespace on one fragment.
     *
     * @param string $value
     * @return string
     */
    private function clean_text($value) {
        $value = wp_strip_all_tags((string) $value);
        $value = html_entity_decode($value, ENT_QUOTES, get_bloginfo('charset') ?: 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    /**
     * Check if a SEO plugin is active
     *
     * @return bool Whether a SEO plugin is active
     */
    private function is_seo_plugin_active() {
        return defined('WPSEO_VERSION') || class_exists('RankMath');
    }
}
