<?php

namespace Wpxero\Marqueex\Traits;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared post-source helpers for the dynamic marquee widgets.
 *
 * These methods were previously copy-pasted byte-for-byte across the
 * TextMarquee, NewsTicker and ImageMarquee widgets. Consolidated here so a
 * single fix applies to all three. Behavior is unchanged from the originals.
 */
trait MarqueeSource {

    /**
     * Get available post types (public, navigable; sensitive types filtered out).
     */
    private function get_post_types() {
        $post_types = get_post_types(['public' => true, 'show_in_nav_menus' => true], 'objects');
        $post_types = wp_list_pluck($post_types, 'label', 'name');

        // Security: Filter out sensitive post types
        $excluded_types = ['elementor_library', 'attachment', 'revision', 'nav_menu_item'];
        return array_diff_key($post_types, array_flip($excluded_types));
    }

    /**
     * Get available post categories keyed by term id.
     */
    private function get_post_categories() {
        $categories = get_categories(['hide_empty' => false]);
        $options = [];

        foreach ($categories as $category) {
            if ($category instanceof \WP_Term) {
                $options[intval($category->term_id)] = sanitize_text_field($category->name);
            }
        }

        return $options;
    }

    /**
     * Build sanitized WP_Query args for the marquee post source.
     */
    private function get_query_args($settings = []) {
        $settings = wp_parse_args($settings, [
            'post_type' => 'post',
            'posts_per_page' => 6,
            'category_ids' => [],
            'orderby' => 'date',
            'order' => 'desc',
        ]);

        // Security: Sanitize and validate inputs
        $post_type = sanitize_text_field($settings['post_type']);
        $posts_per_page = min(50, max(1, intval($settings['posts_per_page']))); // Limit to prevent performance issues
        $orderby = in_array($settings['orderby'], ['date', 'title', 'rand', 'menu_order', 'comment_count'], true) ? $settings['orderby'] : 'date';
        $order = strtoupper($settings['order']) === 'ASC' ? 'ASC' : 'DESC';

        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'posts_per_page' => $posts_per_page,
            'no_found_rows' => true, // Performance: Skip pagination queries
            'update_post_meta_cache' => false, // Performance: Skip meta cache if not needed
            'update_post_term_cache' => false, // Performance: Skip term cache if not needed
        ];

        // Order by & order
        if ('rand' === $orderby) {
            $args['orderby'] = 'rand';
        } else {
            $args['orderby'] = $orderby;
            $args['order'] = $order;
        }

        // Category filter with security validation
        if (!empty($settings['category_ids']) && $post_type === 'post') {
            $category_ids = array_map('intval', (array) $settings['category_ids']);
            $category_ids = array_filter($category_ids, function ($id) {
                return $id > 0 && term_exists($id, 'category');
            });

            if (!empty($category_ids)) {
                $args['category__in'] = $category_ids;
            }
        }

        return $args;
    }

    /**
     * Duplicate the post list until there are enough items for a smooth,
     * seamless marquee loop.
     */
    private function ensure_minimum_posts($posts) {
        if (empty($posts)) {
            return [];
        }

        // Duplicate posts until we reach the minimum needed for a smooth marquee.
        while (count($posts) < 10) {
            $posts = array_merge($posts, $posts);
        }

        return $posts;
    }

    /**
     * Resolve the link target for a post, honoring custom URLs.
     */
    private function get_post_url($post) {
        if (!$post instanceof \WP_Post) {
            return '';
        }

        if (isset($post->is_custom) && $post->is_custom && !empty($post->custom_url)) {
            return esc_url_raw($post->custom_url);
        }

        return get_permalink($post) ?: '';
    }
}
