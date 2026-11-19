<?php

namespace Wpxero\Marqueex\Integrations\Gutenberg\Render;

use Wpxero\Marqueex\Traits\MarqueeSource;
use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post-source helper for dynamic MarqueeX Gutenberg blocks.
 *
 * Wraps the shared {@see MarqueeSource} trait (the same query/duplication/url
 * logic the Elementor widgets use) and exposes a small, Elementor-free surface
 * the block `render.php` files can call. Keeping this in the Gutenberg namespace
 * means the dynamic blocks never depend on Elementor being installed.
 */
class PostSource {
    use Singleton;
    use MarqueeSource;

    /**
     * Most posts a manual selection may hold. Matches the `posts_per_page`
     * ceiling in {@see MarqueeSource::get_query_args()}.
     */
    const MAX_SELECTED_POSTS = 50;

    /**
     * Run the marquee post query for a block's `query` attribute and return a
     * list padded to the minimum needed for a seamless loop.
     *
     * The args pass through `marqueex/query/args` — the same seam the Elementor
     * widgets use — with the block's query translated into the widget settings
     * shape, so marqueex-pro applies manual selection to blocks with the code it
     * already has. Free itself never restricts the query to the selected posts:
     * with no extension answering, the block renders its ordinary query.
     *
     * @param array $query Block `query` attribute (postType, postsPerPage, orderby, order, categoryIds, source, selected).
     * @return \WP_Post[]
     */
    public function get_posts(array $query) {
        $post_type = $query['postType'] ?? 'post';

        $base_args = $this->get_query_args([
            'post_type'      => $post_type,
            'posts_per_page' => $query['postsPerPage'] ?? 6,
            'orderby'        => $query['orderby'] ?? 'date',
            'order'          => $query['order'] ?? 'desc',
            'category_ids'   => $query['categoryIds'] ?? [],
        ]);

        $selected_ids = $this->selected_ids($query);
        // A stored manual selection on an unlicensed site falls back to the
        // ordinary query rather than failing.
        $manual = !empty($selected_ids) && \Wpxero\Marqueex\MarqueeX::is_premium_active();

        $args     = $base_args;
        $settings = ['posts_source' => sanitize_key($post_type)];

        if ($manual) {
            $settings = [
                'posts_source'       => 'manual_selection',
                'posts_selected_ids' => $selected_ids,
            ];

            // Show every picked post, in the order it was picked.
            $args['posts_per_page'] = count($selected_ids);
            $args['orderby']        = 'post__in';
            unset($args['order'], $args['category__in']);
        }

        /** This filter is documented in includes/Traits/PostQuery/PostQueryControls.php */
        $args = apply_filters('marqueex/query/args', $args, $settings, null);

        if ($manual && empty($args['post__in'])) {
            $args = $base_args;
        }

        return $this->ensure_minimum_posts(get_posts($args));
    }

    /**
     * Post IDs of a block's manual selection, in picked order.
     *
     * @param array $query Block `query` attribute.
     * @return int[] Empty unless the block is in manual mode with a selection.
     */
    private function selected_ids(array $query) {
        if ('manual' !== ($query['source'] ?? '') || empty($query['selected']) || !is_array($query['selected'])) {
            return [];
        }

        $ids = [];
        foreach ($query['selected'] as $item) {
            $id = absint(is_array($item) ? ($item['id'] ?? 0) : $item);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_slice(array_values($ids), 0, self::MAX_SELECTED_POSTS);
    }

    /**
     * Pad a custom item list to the minimum needed for a seamless loop.
     *
     * @param array $items
     * @return array
     */
    public function pad_items(array $items) {
        return $this->ensure_minimum_posts($items);
    }

    /**
     * Resolve the public URL for a post, honoring custom URLs.
     *
     * @param \WP_Post $post
     * @return string
     */
    public function url($post) {
        return $this->get_post_url($post);
    }
}
