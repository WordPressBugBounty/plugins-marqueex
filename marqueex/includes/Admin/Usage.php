<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * How much published content uses MarqueeX — for the dashboard's "Live on"
 * tile.
 *
 * The count is a LIKE scan over post_content and `_elementor_data`, which is
 * not something to run on every admin request on a large site. It is only
 * computed when the MarqueeX screen is opened, cached for 12 hours, and
 * invalidated whenever a post is saved or deleted so the number moves when
 * the user adds their first marquee.
 */
class Usage {
    use Singleton;

    const TRANSIENT = 'marqueex_usage_counts';

    private function __construct() {
        add_action('save_post', [$this, 'flush']);
        add_action('deleted_post', [$this, 'flush']);
    }

    /**
     * Drop the cached count.
     */
    public function flush() {
        delete_transient(self::TRANSIENT);
    }

    /**
     * @return array{pages:int}
     */
    public static function counts() {
        $cached = get_transient(self::TRANSIENT);
        if (is_array($cached) && isset($cached['pages'])) {
            return $cached;
        }

        global $wpdb;

        $block_like     = '%' . $wpdb->esc_like('<!-- wp:marqueex/') . '%';
        $shortcode_like = '%' . $wpdb->esc_like('[marqueex_') . '%';
        $widget_like    = '%' . $wpdb->esc_like('"widgetType":"marqueex-') . '%';

        // One query, DISTINCT across both sources, so a post that has blocks
        // and Elementor data is counted once. Published content only, and no
        // revisions / auto-drafts.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- cached in a transient above.
        $pages = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM {$wpdb->posts} p
                 LEFT JOIN {$wpdb->postmeta} m
                   ON m.post_id = p.ID AND m.meta_key = '_elementor_data'
                 WHERE p.post_status = 'publish'
                   AND p.post_type NOT IN ('revision', 'nav_menu_item')
                   AND (p.post_content LIKE %s OR p.post_content LIKE %s OR m.meta_value LIKE %s)",
                $block_like,
                $shortcode_like,
                $widget_like
            )
        );

        $counts = ['pages' => $pages];
        set_transient(self::TRANSIENT, $counts, 12 * HOUR_IN_SECONDS);

        return $counts;
    }
}
