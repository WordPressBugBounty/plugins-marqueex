<?php

namespace Wpxero\Marqueex\Traits\PostQuery;

use Elementor\Controls_Manager;
use Wpxero\Marqueex\Core\Upsell;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reusable Post Query Controls Trait (Free Version)
 *
 * Provides basic query controls and query builder for any Elementor widget.
 * Premium features (Manual Selection, Include/Exclude) are shown as
 * disabled controls with PRO badges. All premium logic lives in marqueex-pro.
 *
 * Usage:
 *   use PostQueryControls;
 *   // Inside register_controls():
 *   $this->register_query_controls();
 *   // Inside render():
 *   $args = $this->get_query_args( $this->get_settings_for_display() );
 */
trait PostQueryControls {

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Get available public post types for the Source dropdown
     */
    protected function get_post_types_options() {
        $post_types = get_post_types(['public' => true], 'objects');
        $options    = [];
        $ignore     = ['elementor_library', 'attachment'];

        foreach ($post_types as $post_type) {
            if (!in_array($post_type->name, $ignore, true)) {
                $options[$post_type->name] = $post_type->label;
            }
        }

        return $options;
    }

    // ─── Controls Registration ───────────────────────────────────

    /**
     * Register query controls
     *
     * Call this inside your widget's register_controls() method.
     * Registers:
     *  - Basic controls (free)
     *  - Disabled PRO placeholders (when marqueex-pro is not active)
     *  - Hook for marqueex-pro to inject working premium controls
     */
    protected function register_query_controls() {
        $this->start_controls_section(
            'query_section',
            ['label' => __('Query', 'marqueex')]
        );

        // ── Posts per page ──
        $this->add_control(
            'posts_per_page',
            [
                'label'   => __('Number of Posts', 'marqueex'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        // ── Source (post types only in free) ──
        $this->add_control(
            'posts_source',
            [
                'label'   => __('Source', 'marqueex'),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_post_types_options(),
                'default' => 'post',
            ]
        );

        // ── Locked premium query controls ──
        Upsell::add_elementor_notice($this, 'pro_query_notice', [
            'title'       => __('Choose exactly which posts scroll', 'marqueex'),
            'description' => __('Hand-pick the entries yourself, or narrow an automatic query down to the authors and categories you want — and keep the ones you do not out of it.', 'marqueex'),
            'items'       => [
                __('Manual selection — pick posts one by one', 'marqueex'),
                __('Include by author or taxonomy term', 'marqueex'),
                __('Exclude specific posts, authors or terms', 'marqueex'),
            ],
            'cta'         => __('Unlock the query builder', 'marqueex'),
            'source'      => 'elementor-post-query',
        ]);

        /**
         * Hook for marqueex-pro to inject premium controls.
         *
         * When Pro is active the placeholders above are skipped and
         * this action registers the real working controls instead:
         *  - manual_selection option added to posts_source
         *  - posts_selected_ids  (post picker)
         *  - Include / Exclude tabs (authors, terms)
         *
         * @param \Elementor\Widget_Base $widget
         */
        do_action('marqueex/query/premium_controls', $this);

        // ── Basic Controls ──
        $this->add_control(
            'posts_divider',
            ['type' => Controls_Manager::DIVIDER]
        );

        $this->add_control(
            'posts_offset',
            [
                'label'     => __('Offset', 'marqueex'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 0,
                'condition' => ['posts_source!' => 'manual_selection'],
            ]
        );

        $this->add_control(
            'posts_select_date',
            [
                'label'     => __('Date', 'marqueex'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'anytime',
                'options'   => [
                    'anytime' => __('All', 'marqueex'),
                    'today'   => __('Past Day', 'marqueex'),
                    'week'    => __('Past Week', 'marqueex'),
                    'month'   => __('Past Month', 'marqueex'),
                    'quarter' => __('Past Quarter', 'marqueex'),
                    'year'    => __('Past Year', 'marqueex'),
                    'exact'   => __('Custom', 'marqueex'),
                ],
                'condition' => ['posts_source!' => 'manual_selection'],
            ]
        );

        $this->add_control(
            'posts_date_before',
            [
                'label'       => __('Before', 'marqueex'),
                'type'        => Controls_Manager::DATE_TIME,
                'description' => __('Setting a Before date will show all posts published until the chosen date (inclusive).', 'marqueex'),
                'condition'   => [
                    'posts_select_date' => 'exact',
                    'posts_source!'     => 'manual_selection',
                ],
            ]
        );

        $this->add_control(
            'posts_date_after',
            [
                'label'       => __('After', 'marqueex'),
                'type'        => Controls_Manager::DATE_TIME,
                'description' => __('Setting an After date will show all posts published since the chosen date (inclusive).', 'marqueex'),
                'condition'   => [
                    'posts_select_date' => 'exact',
                    'posts_source!'     => 'manual_selection',
                ],
            ]
        );

        $this->add_control(
            'posts_orderby',
            [
                'label'   => __('Order By', 'marqueex'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'title'         => __('Title', 'marqueex'),
                    'ID'            => __('ID', 'marqueex'),
                    'date'          => __('Date', 'marqueex'),
                    'author'        => __('Author', 'marqueex'),
                    'comment_count' => __('Comment Count', 'marqueex'),
                    'menu_order'    => __('Menu Order', 'marqueex'),
                    'modified'      => __('Last Modified', 'marqueex'),
                    'rand'          => __('Random', 'marqueex'),
                ],
            ]
        );

        $this->add_control(
            'posts_order',
            [
                'label'   => __('Order', 'marqueex'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc'  => __('ASC', 'marqueex'),
                    'desc' => __('DESC', 'marqueex'),
                ],
            ]
        );

        $this->add_control(
            'posts_ignore_sticky_posts',
            [
                'label'        => __('Ignore Sticky Posts', 'marqueex'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => ['posts_source' => 'post'],
            ]
        );

        $this->add_control(
            'posts_only_with_featured_image',
            [
                'label'        => __('Only Featured Image Post', 'marqueex'),
                'description'  => __('Enable to display posts only when featured image is present.', 'marqueex'),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'condition'    => ['posts_source!' => 'manual_selection'],
            ]
        );

        $this->end_controls_section();
    }

    // ─── Query Builder ───────────────────────────────────────────

    /**
     * Build WP_Query arguments from widget settings (free version)
     *
     * Handles basic parameters only. Premium logic (manual selection,
     * include/exclude) is applied by marqueex-pro via the
     * `marqueex/query/args` filter.
     *
     * @param array $settings Widget settings from get_settings_for_display()
     * @return array WP_Query compatible arguments
     */
    protected function get_query_args($settings = []) {
        $settings = wp_parse_args($settings, [
            'posts_per_page'                 => 6,
            'posts_source'                   => 'post',
            'posts_offset'                   => 0,
            'posts_select_date'              => 'anytime',
            'posts_date_before'              => '',
            'posts_date_after'               => '',
            'posts_orderby'                  => 'date',
            'posts_order'                    => 'desc',
            'posts_ignore_sticky_posts'      => 'yes',
            'posts_only_with_featured_image' => '',
            'post_categories'                => [],
        ]);

        $posts_per_page = min(50, max(1, intval($settings['posts_per_page'])));
        $post_source    = sanitize_text_field($settings['posts_source']);

        $args = [
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'no_found_rows'  => true,
            'post_type'      => $post_source ?: 'post',
        ];

        // Ignore sticky posts
        if ($post_source === 'post' && $settings['posts_ignore_sticky_posts'] === 'yes') {
            $args['ignore_sticky_posts'] = true;
        }

        // Backward compat: old post_categories setting
        if (!empty($settings['post_categories'])) {
            $category_ids = array_map('intval', (array) $settings['post_categories']);
            $category_ids = array_filter($category_ids, function ($id) {
                return $id > 0;
            });
            if (!empty($category_ids)) {
                $args['category__in'] = $category_ids;
            }
        }

        // Order
        $args['orderby'] = sanitize_text_field($settings['posts_orderby']);
        $args['order']   = strtoupper(sanitize_text_field($settings['posts_order']));

        // Offset
        if (!empty($settings['posts_offset']) && intval($settings['posts_offset']) > 0) {
            $args['offset'] = intval($settings['posts_offset']);
        }

        // Only with featured image. Filtering by the `_thumbnail_id` meta key is the
        // standard way to limit a query to posts that have a featured image; the
        // slow-query notice is acceptable for this opt-in, bounded marquee query.
        if ($settings['posts_only_with_featured_image'] === 'yes') {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $args['meta_key'] = '_thumbnail_id';
        }

        // Date filter
        $selected_date = $settings['posts_select_date'];
        if (!empty($selected_date) && $selected_date !== 'anytime') {
            $date_query = [];
            switch ($selected_date) {
                case 'today':
                    $date_query['after'] = '-1 day';
                    break;
                case 'week':
                    $date_query['after'] = '-1 week';
                    break;
                case 'month':
                    $date_query['after'] = '-1 month';
                    break;
                case 'quarter':
                    $date_query['after'] = '-3 month';
                    break;
                case 'year':
                    $date_query['after'] = '-1 year';
                    break;
                case 'exact':
                    if (!empty($settings['posts_date_after'])) {
                        $date_query['after'] = $settings['posts_date_after'];
                    }
                    if (!empty($settings['posts_date_before'])) {
                        $date_query['before'] = $settings['posts_date_before'];
                    }
                    $date_query['inclusive'] = true;
                    break;
            }
            if (!empty($date_query)) {
                $args['date_query'] = $date_query;
            }
        }

        /**
         * Filter query arguments.
         *
         * marqueex-pro hooks here to apply premium logic:
         *  - Manual selection (post__in, post_type = any)
         *  - Include by authors / terms
         *  - Exclude by posts / authors / terms / current post
         *
         * Also applied by the Gutenberg blocks (Render\PostSource::get_posts()),
         * which translate their `query` attribute into the same settings keys
         * and pass null for the widget.
         *
         * @param array                       $args     WP_Query arguments
         * @param array                       $settings Widget settings
         * @param \Elementor\Widget_Base|null $widget   Current widget instance, or null when a block is rendering
         */
        return apply_filters('marqueex/query/args', $args, $settings, $this);
    }
}
