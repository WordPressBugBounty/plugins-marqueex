<?php

namespace Wpxero\Marqueex\Integrations\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Wpxero\Marqueex\Core\Upsell;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Wpxero\Marqueex\Traits\PostQuery\PostQueryControls;

if (!defined('ABSPATH')) {
    exit;
}

class PostMarquee extends Widget_Base {

    use PostQueryControls;

    public function get_name() {
        return 'marqueex-post-marquee';
    }

    public function get_title() {
        return __('Post Marquee', 'marqueex');
    }

    public function get_icon() {
        return 'eicon-posts-carousel eicon-marqueex';
    }

    public function get_categories() {
        return ['marqueex'];
    }

    public function get_style_depends() {
        return ['marqueex-post-marquee'];
    }

    public function get_script_depends() {
        return ['marqueex-post-marquee'];
    }

    public function get_keywords() {
        return ['post', 'grid', 'marquee', 'blog', 'posts', 'slider', 'scroll'];
    }

    protected function register_controls() {


        $this->start_controls_section(
            'post_content',
            ['label' => __('Post Content', 'marqueex')]
        );

        $this->add_control(
            'show_author',
            [
                'label' => __('Show Author', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_category',
            [
                'label' => __('Show Category', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        // $this->add_control(
        //     'show_excerpt',
        //     [
        //         'label' => __('Show Excerpt', 'marqueex'),
        //         'type' => Controls_Manager::SWITCHER,
        //         'default' => 'yes',
        //     ]
        // );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Word Limit', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 0,
                'max' => 100,
                'condition' => ['show_excerpt' => 'yes'],
            ]
        );

        $this->add_control(
            'meta_separator',
            [
                'label'       => __('Meta Separator', 'marqueex'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '-',
                'label_block' => false,
            ]
        );
        // ── Content layout (Pro) ──
        // Shown to everyone, labelled "(Pro)" without a license, so free users can
        // see the placements by name. render() clamps to the free default.
        $free_positions = ['bottom-left' => __('Bottom Left', 'marqueex')];
        $pro_positions  = [
            'top-left'      => __('Top Left', 'marqueex'),
            'top-right'     => __('Top Right', 'marqueex'),
            'center-center' => __('Center Center', 'marqueex'),
            'bottom-center' => __('Bottom Center', 'marqueex'),
            'bottom-right'  => __('Bottom Right', 'marqueex'),
        ];

        $free_styles = ['overlay' => __('Overlay', 'marqueex')];
        $pro_styles  = [
            'blury'   => __('Blury', 'marqueex'),
            'lighten' => __('Lighten', 'marqueex'),
        ];

        $this->add_control(
            'content_position',
            [
                'label'   => __('Content Position', 'marqueex'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'bottom-left',
                'options' => Upsell::merge_select_options($free_positions, $pro_positions),
            ]
        );

        $this->add_control(
            'content_style',
            [
                'label'   => __('Content Style', 'marqueex'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'overlay',
                'options' => Upsell::merge_select_options($free_styles, $pro_styles),
            ]
        );

        Upsell::add_elementor_notice($this, 'marqueex_post_layout_pro_notice', [
            'title'       => __('Card layout controls', 'marqueex'),
            'description' => __('Place the title, date and category anywhere on the card, and pick how the text sits over the image.', 'marqueex'),
            'items'       => [
                __('Six content placements', 'marqueex'),
                __('Overlay, Blury and Lighten text treatments', 'marqueex'),
            ],
            'cta'         => __('Unlock card layouts', 'marqueex'),
            'source'      => 'elementor-post-marquee-layout',
        ]);

        $this->add_control(
            'global_link',
            [
                'label'        => __('Item Wrapper Link', 'marqueex'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'marqueex-global-link-',
                'description'  => __('Be aware! When Item Wrapper Link activated then title link will not work', 'marqueex'),
            ]
        );


        $this->end_controls_section();

                // Query controls from PostQueryControls trait
        $this->register_query_controls();

        $this->start_controls_section(
            'marquee_settings',
            ['label' => __('Marquee Settings', 'marqueex')]
        );

        $this->add_control(
            'orientation',
            [
                'label' => __('Orientation', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'marqueex'),
                    'vertical' => __('Vertical', 'marqueex'),
                ],
            ]
        );

        $this->add_control(
            'vertical_height',
            [
                'label' => __('Height', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['vh', 'px'],
                'range' => [
                    'vh' => ['min' => 20, 'max' => 100],
                    'px' => ['min' => 200, 'max' => 1000],
                ],
                'default' => ['size' => 60, 'unit' => 'vh'],
                'condition' => ['orientation' => 'vertical'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-pgm-wrap' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'reverse',
            [
                'label' => __('Reverse Direction', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'pause',
            [
                'label' => __('Pause on Hover', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'speed',
            [
                'label' => __('Speed (px/s)', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 1, 'max' => 200]],
                'default' => ['size' => 20, 'unit' => 'px'],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label' => __('Gap', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    'em' => ['min' => 0, 'max' => 10, 'step' => 0.1],
                    'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
                ],
                'default' => ['size' => 24, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-track-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-marquee-track' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-marquee-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'mask_edges',
            [
                'label' => __('Soft Edge Mask', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_overlay',
            [
                'label' => __('Overlay & Effects', 'marqueex'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'overlay_background',
            [
                'label'     => __('Overlay Color', 'marqueex'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-post-marquee .marqueex-item-box:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'overlay_blur_effect',
            [
                'label'       => __('Glassmorphism', 'marqueex'),
                'type'        => Controls_Manager::SWITCHER,
                'description' => __('This feature will not work in the Firefox browser until you enable browser compatibility', 'marqueex'),
                'default'     => 'yes',
                'condition'   => [
                    'content_style' => 'blury',
                ]
            ]
        );

        $this->add_control(
            'overlay_blur_level',
            [
                'label'     => __('Blur Level', 'marqueex'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min'  => 0,
                        'step' => 1,
                        'max'  => 50,
                    ]
                ],
                'default'   => [
                    'size' => 10
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-post-marquee  .marqueex-content' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
                ],
                'condition' => [
                    'overlay_blur_effect' => 'yes',
                    'content_style'       => 'blury',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_category',
            [
                'label' => __('Category', 'marqueex'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'category_color',
            [
                'label'     => __('Text Color', 'marqueex'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .marqueex-category a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'category_background',
            [
                'label'     => __('Background Color', 'marqueex'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ef233c',
                'selectors' => [
                    '{{WRAPPER}} .marqueex-category a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'category_typography',
                'selector' => '{{WRAPPER}} .marqueex-category a',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_thumb',
            [
                'label' => __('Featured Image', 'marqueex'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'thumb_width',
            [
                'label' => __('Width', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 50, 'max' => 2000],
                    '%' => ['min' => 10, 'max' => 100],
                    'em' => ['min' => 1, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumb_height',
            [
                'label' => __('Height', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 50, 'max' => 2000],
                    '%' => ['min' => 10, 'max' => 100],
                    'em' => ['min' => 1, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumb_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'thumb_shadow',
                'selector' => '{{WRAPPER}} .marqueex-img',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_title',
            [
                'label' => __('Post Title', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Hover Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typo',
                'selector' => '{{WRAPPER}} .marqueex-title a',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_meta',
            [
                'label' => __('Meta (Author & Date)', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __('Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-meta, {{WRAPPER}} .marqueex-meta i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typo',
                'selector' => '{{WRAPPER}} .marqueex-meta',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_excerpt',
            [
                'label' => __('Excerpt', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typo',
                'selector' => '{{WRAPPER}} .marqueex-excerpt',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_button',
            [
                'label' => __('Read More Button', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'btn_color',
            [
                'label' => __('Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-readmore' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg',
            [
                'label' => __('Background Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-readmore' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_hover_color',
            [
                'label' => __('Hover Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-readmore:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_hover_bg',
            [
                'label' => __('Hover Background', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-readmore:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'btn_border',
                'selector' => '{{WRAPPER}} .marqueex-readmore',
            ]
        );

        $this->add_responsive_control(
            'btn_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-readmore' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typo',
                'selector' => '{{WRAPPER}} .marqueex-readmore',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Ensure minimum items for smooth marquee animation
     */
    private function ensure_minimum_posts($posts) {
        if (empty($posts)) {
            return [];
        }

        // Duplicate posts if we have less than 10 for smooth marquee
        while (count($posts) < 10) {
            $posts = array_merge($posts, $posts);
        }

        return $posts;
    }

    /**
     * Prepare widget configuration from settings
     *
     * @param array $settings Widget settings from Elementor
     * @return array Processed configuration array
     */
    private function prepare_widget_config($settings) {
        return [
            'unique_id' => 'marqueex-post-grid-' . $this->get_id(),
            'speed' => max(10, min(400, intval($settings['speed']['size'] ?? 60))),
        ];
    }

    /**
     * Render post grid content
     *
     * @param array $settings Widget settings
     */
    private function render_post_grid_content($settings) {
        $query_args = $this->get_query_args($settings);
        $posts = get_posts($query_args);

        if (empty($posts)) {
            return;
        }

        $posts = $this->ensure_minimum_posts($posts);

        echo '<div class="marqueex-marquee-wrapper">';
        foreach ($posts as $post) {
            $this->render_single_post_card($post, $settings);
        }
        echo '</div>';
    }

    /**
     * Render single post card
     *
     * @param object $post Post object
     * @param array $settings Widget settings
     */
    private function render_single_post_card($post, $settings) {
        if (!$post instanceof \WP_Post) {
            return;
        }

        setup_postdata($post);

        $use_global_link = !empty($settings['global_link']) && $settings['global_link'] === 'yes';
        $permalink = get_permalink($post->ID);

        $image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        $placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';
        $image_src = $image_src ? $image_src[0] : $placeholder;

        $categories = get_the_category($post->ID);
        $category_output = '';
        if (!empty($categories) && ($settings['show_category'] ?? '') === 'yes') {
            $category_links = [];
            foreach ($categories as $category) {
                $category_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
            }
            $category_output = implode('', $category_links);
        }

        $item_tag = $use_global_link ? 'a' : 'div';
?>
        <<?php echo tag_escape($item_tag); ?> class="marqueex-item"<?php echo $use_global_link ? ' href="' . esc_url($permalink) . '"' : ''; ?>>
            <div class="marqueex-item-box">
                <img class="marqueex-img" src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>">

                <div class="marqueex-content">
                    <?php if ($settings['show_category'] === 'yes' && !empty($category_output)) : ?>
                        <div class="marqueex-category">
                            <?php echo wp_kses_post($category_output); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($settings['show_title']) || $settings['show_title'] === 'yes') : ?>
                        <div class="marqueex-title-wrap">
                            <h3 class="marqueex-title">
                                <?php if ($use_global_link) : ?>
                                    <span><?php echo esc_html(get_the_title($post->ID)); ?></span>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                                        <?php echo esc_html(get_the_title($post->ID)); ?>
                                    </a>
                                <?php endif; ?>
                            </h3>
                        </div>
                    <?php endif; ?>

                    <?php if (
                        $settings['show_author'] === 'yes' ||
                        $settings['show_date'] === 'yes'
                    ) : ?>
                        <div class="marqueex-meta">
                            <?php if ($settings['show_author'] === 'yes') : ?>
                                <div class="marqueex-blog-author">
                                    <span class="by">
                                        <?php echo esc_html_x('by', 'Frontend', 'marqueex'); ?>
                                    </span>
                                    <span class="marqueex-post-grid-author">
                                        <a href="<?php echo esc_url(get_author_posts_url($post->post_author)); ?>">
                                            <?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($settings['show_date'] === 'yes') : ?>
                                <div data-separator="<?php echo esc_html($settings['meta_separator']); ?>">
                                    <div class="marqueex-date">
                                        <i class="far fa-calendar" aria-hidden="true"></i>
                                        <?php echo esc_html(get_the_date('', $post->ID)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (($settings['show_excerpt'] ?? '') === 'yes') :
                        $excerpt_length = intval($settings['excerpt_length'] ?? 15);
                        $excerpt = wp_trim_words(get_the_excerpt($post->ID), $excerpt_length, '...');
                        if (!empty($excerpt)) : ?>
                            <div class="marqueex-excerpt">
                                <?php echo esc_html($excerpt); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </<?php echo tag_escape($item_tag); ?>>
    <?php
        wp_reset_postdata();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $is_pro_active = class_exists('\\Wpxero\\Marqueex\\MarqueeX')
            && \Wpxero\Marqueex\MarqueeX::is_premium_active();
        $widget_config = $this->prepare_widget_config($settings);

        // Pro placements/styles are selectable without a license so free users can
        // see they exist; the front end falls back to the free value.
        $free_positions = ['bottom-left'];
        $free_styles    = ['overlay'];

        $content_position = $settings['content_position'] ?? 'bottom-left';
        if (!$is_pro_active && !in_array($content_position, $free_positions, true)) {
            $content_position = 'bottom-left';
        }

        $content_style = $settings['content_style'] ?? 'overlay';
        if (!$is_pro_active && !in_array($content_style, $free_styles, true)) {
            $content_style = 'overlay';
        }

        $this->add_render_attribute(
            [
                'marqueex-post-marquee' => [
                    'class' => array_values(array_filter([
                        'marqueex-marquee-block',
                        'marqueex-post-marquee',
                        'marqueex-text-position-' . sanitize_html_class($content_position),
                        'marqueex-content-style-' . sanitize_html_class($content_style),
                        ($settings['pause'] ?? '') === 'yes' ? 'is-paused' : '',
                        ($settings['reverse'] ?? '') === 'yes' ? 'is-reversed' : '',
                        ($settings['orientation'] ?? '') === 'vertical' ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
                        ($settings['mask_edges'] ?? '') === 'yes' ? 'marqueex-marquee-edge-shadow' : '',
                    ])),
                    'data-speed' => $widget_config['speed'],
                    'role' => 'region',
                    'aria-label' => __('Post Grid Marquee', 'marqueex'),
                ]
            ]
        );

        // Render the post grid once and reuse it for the aria-hidden clone,
        // avoiding a second identical post query per widget render.
        ob_start();
        $this->render_post_grid_content($settings);
        $track_html = ob_get_clean();

    ?>
        <?php // get_render_attribute_string() returns markup already escaped by Elementor. ?>
        <div <?php echo $this->get_render_attribute_string('marqueex-post-marquee'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="marqueex-marquee-track-wrapper">
                <div class="marqueex-marquee-track">
                    <?php echo $track_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <div aria-hidden="true" class="marqueex-marquee-track">
                    <?php echo $track_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
            </div>
        </div>

<?php
    }
}
