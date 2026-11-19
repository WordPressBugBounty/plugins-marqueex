<?php

namespace Wpxero\Marqueex\Integrations\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use Wpxero\Marqueex\Traits\MarqueeSource;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * MarqueeX Text Marquee Widget for Elementor
 */
class TextMarquee extends Widget_Base {

    use MarqueeSource;

    /**
     * Get widget name
     */
    public function get_name() {
        return 'marqueex-text-marquee';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Text Marquee', 'marqueex');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-text-area eicon-marqueex';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['marqueex'];
    }

    /**
     * Enqueue widget scripts and styles
     */
    public function get_script_depends() {
        return ['marqueex-text-marquee'];
    }

    public function get_style_depends() {
        return ['marqueex-text-marquee'];
    }
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['marquee', 'ticker', 'marquee', 'posts', 'slider', 'scroll'];
    }


    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Type Section
        $this->start_controls_section(
            'content_type_section',
            [
                'label' => __('Content Type', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'content_type',
            [
                'label' => __('Content Type', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'posts',
                'options' => [
                    'posts' => __('Dynamic Posts', 'marqueex'),
                    'custom' => __('Custom Text', 'marqueex'),
                ],
            ]
        );

        $this->add_control(
            'ticker_content_custom',
            [
                'label' => __('Ticker Content', 'marqueex'),
                'type' => Controls_Manager::REPEATER,
                'condition' => [
                    'content_type' => 'custom',
                ],
                'fields' => [
                    [
                        'name' => 'icon',
                        'label' => __('Icon', 'marqueex'),
                        'type' => Controls_Manager::ICONS,
                        'label_block' => true,
                        'skin' => 'inline',
                        'default' => [
                            'value' => '',
                            'library' => '',
                        ],
                        'description' => __('Choose an icon to display before the text (optional).', 'marqueex'),
                    ],
                    [
                        'name' => 'text',
                        'label' => __('Text', 'marqueex'),
                        'type' => Controls_Manager::TEXT,
                        'default' => __('Breaking news: Your news content here...', 'marqueex'),
                        'description' => __('Enter the news content that will scroll in the ticker.', 'marqueex'),
                    ],
                    [
                        'name' => 'link',
                        'label' => __('Link', 'marqueex'),
                        'type' => Controls_Manager::URL,
                        'default' => [
                            'url' => '',
                            'is_external' => '',
                            'nofollow' => '',
                        ],
                        'description' => __('Enter the link for the news content.', 'marqueex'),
                    ],
                ],
                'condition' => [
                    'content_type' => 'custom',
                ],
                'title_field' => '{{{ text }}}',
                'default' => [
                    [
                        'text' => __('Breaking news: Your first news content here...', 'marqueex'),
                        'icon' => [
                            'value' => 'fas fa-circle',
                            'library' => 'fa-solid',
                        ],
                        'link' => [
                            'url' => '',
                            'is_external' => '',
                            'nofollow' => '',
                        ],
                    ],
                    [
                        'text' => __('Latest update: Your second news content here...', 'marqueex'),
                        'icon' => [
                            'value' => 'fas fa-circle',
                            'library' => 'fa-solid',
                        ],
                        'link' => [
                            'url' => '',
                            'is_external' => '',
                            'nofollow' => '',
                        ],
                    ],
                    [
                        'text' => __('Important announcement: Your third news content here...', 'marqueex'),
                        'icon' => [
                            'value' => 'fas fa-circle',
                            'library' => 'fa-solid',
                        ],
                        'link' => [
                            'url' => '',
                            'is_external' => '',
                            'nofollow' => '',
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label' => __('Item Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'rem',
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-track-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-marquee-track' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-marquee-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        $this->end_controls_section();

        // Query Section
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_type' => 'posts',
                ],
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => __('Post Type', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $this->get_post_types(),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Number of Posts', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 50,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date', 'marqueex'),
                    'title' => __('Title', 'marqueex'),
                    'rand' => __('Random', 'marqueex'),
                    'menu_order' => __('Menu Order', 'marqueex'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc' => __('ASC', 'marqueex'),
                    'desc' => __('DESC', 'marqueex'),
                ],
                'condition' => [
                    'orderby!' => 'rand',
                ],
            ]
        );

        $this->add_control(
            'category_ids',
            [
                'label' => __('Categories', 'marqueex'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_post_categories(),
                'condition' => [
                    'post_type' => 'post',
                ],
            ]
        );

        $this->end_controls_section();


        // Content Options Section
        $this->start_controls_section(
            'content_options_section',
            [
                'label' => __('Content Options', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_type' => 'posts',
                ],
            ]
        );

        $this->add_control(
            'title_trim_enable',
            [
                'label' => __('Trim Title', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'marqueex'),
                'label_off' => __('No', 'marqueex'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'title_trim_length',
            [
                'label' => __('Title Length', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 1,
                'max' => 200,
                'condition' => [
                    'title_trim_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'open_in_new_tab',
            [
                'label' => __('Open in New Tab', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'marqueex'),
                'label_off' => __('No', 'marqueex'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        // Separator Section
        $this->start_controls_section(
            'separator_section',
            [
                'label' => __('Separator', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_type' => 'posts',
                ],
            ]
        );

        $this->add_control(
            'separator_type',
            [
                'label' => __('Separator Type', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'none' => __('None', 'marqueex'),
                    'text' => __('Text', 'marqueex'),
                    'icon' => __('Icon', 'marqueex'),
                    // 'date' => __('Date', 'marqueex'),
                    // 'featured_image' => __('Featured Image', 'marqueex'),
                ],
            ]
        );

        // $this->add_control(
        //     'separator_style_preset',
        //     [
        //         'label' => __('Separator Style Preset', 'marqueex'),
        //         'type' => Controls_Manager::SELECT,
        //         'default' => 'modern',
        //         'options' => [
        //             'modern' => __('Modern Glass', 'marqueex'),
        //             'minimal' => __('Minimal Clean', 'marqueex'),
        //             'bold' => __('Bold Accent', 'marqueex'),
        //             'neon' => __('Neon Glow', 'marqueex'),
        //             'classic' => __('Classic Simple', 'marqueex'),
        //             'gradient' => __('Gradient Flow', 'marqueex'),
        //         ],
        //         'condition' => [
        //             'separator_type!' => 'none',
        //         ],
        //     ]
        // );

        $this->add_control(
            'separator_icon',
            [
                'label' => __('Separator Icon', 'marqueex'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-circle',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'separator_type' => 'icon',
                ],
            ]
        );

        $this->add_control(
            'separator_text',
            [
                'label' => __('Separator Text', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'default' => '•',
                'condition' => [
                    'separator_type' => 'text',
                ],
            ]
        );

        $this->end_controls_section();

        // Marquee Settings Section
        $this->start_controls_section(
            'marquee_settings_section',
            [
                'label' => __('Marquee Settings', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'speed',
            [
                'label' => __('Speed', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px/s'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
            ]
        );
        $this->add_control(
            'direction_vertical',
            [
                'label' => __('Direction Vertical', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'options' => [
                    'no' => __('Horizontal', 'marqueex'),
                    'yes' => __('Vertical', 'marqueex'),
                ],
            ]
        );
        $this->add_control(
            'pause_on_hover',
            [
                'label' => __('Pause on Hover', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'marqueex'),
                'label_off' => __('No', 'marqueex'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'reverse_direction',
            [
                'label' => __('Reverse Direction', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'marqueex'),
                'label_off' => __('No', 'marqueex'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        // Container Style Section
        $this->start_controls_section(
            'container_style_section',
            [
                'label' => __('Container', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'container_background',
                'label' => __('Background', 'marqueex'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .marqueex-marquee-block',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'label' => __('Border', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-marquee-block',
            ]
        );

        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-block' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'label' => __('Box Shadow', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-marquee-block',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-block' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_margin',
            [
                'label' => __('Margin', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-block' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_height',
            [
                'label' => __('Height', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', 'em'],
                'range' => [
                    'px' => [
                        'min' => 30,
                        'max' => 200,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-block' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_overflow',
            [
                'label' => __('Overflow', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'visible' => __('Visible', 'marqueex'),
                    'hidden' => __('Hidden', 'marqueex'),
                    'scroll' => __('Scroll', 'marqueex'),
                    'auto' => __('Auto', 'marqueex'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-marquee-block' => 'overflow: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Text Style Section
        $this->start_controls_section(
            'text_style_section',
            [
                'label' => __('Text', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->start_controls_tabs(
            'style_tabs'
        );

        $this->start_controls_tab(
            'style_normal_tab',
            [
                'label' => esc_html__('Normal', 'marqueex'),
            ]
        );
        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-news-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .marqueex-title-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .marqueex-news-text, {{WRAPPER}} .marqueex-title-link',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .marqueex-news-text, {{WRAPPER}} .marqueex-title-link',
            ]
        );

        $this->add_responsive_control(
            'text_spacing',
            [
                'label' => __('Letter Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -2,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-news-text' => 'letter-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'word_spacing',
            [
                'label' => __('Word Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-news-text' => 'word-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->start_controls_tab(
            'link_normal_tab',
            [
                'label' => __('Link', 'marqueex'),
            ]
        );

        $this->add_control(
            'link_color',
            [
                'label' => __('Link Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_decoration',
            [
                'label' => __('Text Decoration', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'marqueex'),
                    'underline' => __('Underline', 'marqueex'),
                    'overline' => __('Overline', 'marqueex'),
                    'line-through' => __('Line Through', 'marqueex'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title-link' => 'text-decoration: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'link_hover_heading',
            [
                'label' => __('H O V E R', 'marqueex'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'link_hover_color',
            [
                'label' => __('Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_hover_decoration',
            [
                'label' => __('Text Decoration', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'underline',
                'options' => [
                    'none' => __('None', 'marqueex'),
                    'underline' => __('Underline', 'marqueex'),
                    'overline' => __('Overline', 'marqueex'),
                    'line-through' => __('Line Through', 'marqueex'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title-link:hover' => 'text-decoration: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_hover_transition',
            [
                'label' => __('Transition Duration', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['ms', 's'],
                'range' => [
                    'ms' => [
                        'min' => 100,
                        'max' => 1000,
                        'step' => 50,
                    ],
                    's' => [
                        'min' => 0.1,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'ms',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-title-link' => 'transition: all {{SIZE}}{{UNIT}} ease;',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // // Link Style Section
        // $this->start_controls_section(
        //     'link_style_section',
        //     [
        //         'label' => __('Link', 'marqueex'),
        //         'tab' => Controls_Manager::TAB_STYLE,
        //     ]
        // );

        // $this->start_controls_tabs('link_style_tabs');



        // $this->end_controls_tab();

        // $this->start_controls_tab(
        //     'link_hover_tab',
        //     [
        //         'label' => __('Hover', 'marqueex'),
        //     ]
        // );



        // $this->end_controls_tab();

        // $this->end_controls_tabs();

        // $this->end_controls_section();

        // Separator Style Section
        $this->start_controls_section(
            'separator_style_section',
            [
                'label' => __('Separator', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'relation' => 'and',
                            'terms' => [
                                [
                                    'name' => 'content_type',
                                    'operator' => '===',
                                    'value' => 'posts',
                                ],
                                [
                                    'name' => 'separator_type',
                                    'operator' => '!==',
                                    'value' => 'none',
                                ],
                            ],
                        ],
                        [
                            'relation' => 'and',
                            'terms' => [
                                [
                                    'name' => 'content_type',
                                    'operator' => '===',
                                    'value' => 'custom',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'separator_color',
            [
                'label' => __('Separator Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .marqueex-separator svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'separator_typography',
                'selector' => '{{WRAPPER}} .marqueex-separator',
                'condition' => [
                    'separator_type' => ['text', 'date'],
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_size',
            [
                'label' => __('Icon Size', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-separator-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'separator_type' => 'icon',
                ],
            ]
        );
        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 6,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 6,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .marqueex-separator svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        // $this->add_responsive_control(
        //     'separator_spacing',
        //     [
        //         'label' => __('Separator Spacing', 'marqueex'),
        //         'type' => Controls_Manager::SLIDER,
        //         'size_units' => ['px', 'em'],
        //         'range' => [
        //             'px' => [
        //                 'min' => 0,
        //                 'max' => 100,
        //             ],
        //         ],
        //         'selectors' => [
        //             '{{WRAPPER}} .marqueex-separator' => 'margin: 0 {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'separator_background',
                'label' => __('Background', 'marqueex'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .marqueex-separator',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'separator_border',
                'label' => __('Border', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-separator',
            ]
        );

        $this->add_responsive_control(
            'separator_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'separator_box_shadow',
                'label' => __('Box Shadow', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-separator',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'separator_text_shadow',
                'label' => __('Text Shadow', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-separator',
                'condition' => [
                    'separator_type' => ['text', 'date'],
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_padding',
            [
                'label' => __('Padding', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_opacity',
            [
                'label' => __('Opacity', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'separator_backdrop_filter',
            [
                'label' => __('Backdrop Filter Blur', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->add_control(
            'separator_transform',
            [
                'label' => __('Transform', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('scale(1.1) rotate(5deg)', 'marqueex'),
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'transform: {{VALUE}};',
                ],
            ]
        );

        // Separator Hover Effects
        $this->add_control(
            'separator_hover_heading',
            [
                'label' => __('Hover Effects', 'marqueex'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'separator_hover_color',
            [
                'label' => __('Hover Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .marqueex-separator:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'separator_hover_background',
                'label' => __('Hover Background', 'marqueex'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .marqueex-separator:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'separator_hover_border',
                'label' => __('Hover Border', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-separator:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'separator_hover_box_shadow',
                'label' => __('Hover Box Shadow', 'marqueex'),
                'selector' => '{{WRAPPER}} .marqueex-separator:hover',
            ]
        );

        $this->add_control(
            'separator_hover_transform',
            [
                'label' => __('Hover Transform', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('translateY(-2px) scale(1.05)', 'marqueex'),
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator:hover' => 'transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_hover_opacity',
            [
                'label' => __('Hover Opacity', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator:hover' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'separator_transition_duration',
            [
                'label' => __('Transition Duration', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['ms'],
                'range' => [
                    'ms' => [
                        'min' => 100,
                        'max' => 2000,
                        'step' => 50,
                    ],
                ],
                'default' => [
                    'size' => 300,
                    'unit' => 'ms',
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator' => 'transition: all {{SIZE}}{{UNIT}} ease;',
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_image_size',
            [
                'label' => __('Image Size', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator-feature-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ],
                'condition' => [
                    'separator_type' => 'featured_image',
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_image_border_radius',
            [
                'label' => __('Image Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-separator-feature-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'separator_type' => 'featured_image',
                ],
            ]
        );

        $this->end_controls_section();

        // // Icon Style Section
        // $this->start_controls_section(
        //     'icon_style_section',
        //     [
        //         'label' => __('Icon', 'marqueex'),
        //         'tab' => Controls_Manager::TAB_STYLE,
        //         'condition' => [
        //             'content_type' => 'custom',
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'icon_color',
        //     [
        //         'label' => __('Icon Color', 'marqueex'),
        //         'type' => Controls_Manager::COLOR,
        //         'default' => '#333',
        //         'selectors' => [
        //             '{{WRAPPER}} .marqueex-news-icon' => 'color: {{VALUE}};',
        //             '{{WRAPPER}} .marqueex-news-icon svg' => 'fill: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->add_responsive_control(
        //     'icon_size',
        //     [
        //         'label' => __('Icon Size', 'marqueex'),
        //         'type' => Controls_Manager::SLIDER,
        //         'size_units' => ['px', 'em', 'rem'],
        //         'range' => [
        //             'px' => [
        //                 'min' => 8,
        //                 'max' => 100,
        //                 'step' => 1,
        //             ],
        //             'em' => [
        //                 'min' => 0.5,
        //                 'max' => 6,
        //                 'step' => 0.1,
        //             ],
        //             'rem' => [
        //                 'min' => 0.5,
        //                 'max' => 6,
        //                 'step' => 0.1,
        //             ],
        //         ],
        //         'default' => [
        //             'unit' => 'px',
        //             'size' => 16,
        //         ],
        //         'selectors' => [
        //             '{{WRAPPER}} .marqueex-news-icon' => 'font-size: {{SIZE}}{{UNIT}};',
        //             '{{WRAPPER}} .marqueex-news-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        // $this->add_responsive_control(
        //     'icon_spacing',
        //     [
        //         'label' => __('Icon Spacing', 'marqueex'),
        //         'type' => Controls_Manager::SLIDER,
        //         'size_units' => ['px', 'em', 'rem'],
        //         'range' => [
        //             'px' => [
        //                 'min' => 0,
        //                 'max' => 50,
        //                 'step' => 1,
        //             ],
        //             'em' => [
        //                 'min' => 0,
        //                 'max' => 3,
        //                 'step' => 0.1,
        //             ],
        //             'rem' => [
        //                 'min' => 0,
        //                 'max' => 3,
        //                 'step' => 0.1,
        //             ],
        //         ],
        //         'default' => [
        //             'unit' => 'px',
        //             'size' => 8,
        //         ],
        //         'selectors' => [
        //             '{{WRAPPER}} .marqueex-news-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'icon_vertical_align',
        //     [
        //         'label' => __('Vertical Alignment', 'marqueex'),
        //         'type' => Controls_Manager::SELECT,
        //         'default' => 'middle',
        //         'options' => [
        //             'top' => __('Top', 'marqueex'),
        //             'middle' => __('Middle', 'marqueex'),
        //             'bottom' => __('Bottom', 'marqueex'),
        //         ],
        //         'selectors' => [
        //             '{{WRAPPER}} .marqueex-news-icon' => 'vertical-align: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->add_group_control(
        //     Group_Control_Text_Shadow::get_type(),
        //     [
        //         'name' => 'icon_text_shadow',
        //         'selector' => '{{WRAPPER}} .marqueex-news-icon',
        //     ]
        // );

        // $this->end_controls_section();
    }

    /**
     * Prepare widget configuration from settings
     *
     * Extracts and processes all widget settings into a clean configuration array.
     *
     * @param array $settings Widget settings from Elementor
     * @return array Processed configuration array
     */
    private function prepare_widget_config($settings) {
        return [
            'unique_id' => 'marqueex-ticker-' . $this->get_id(),
            'speed' => max(10, min(100, intval($settings['speed']['size'] ?? 10))), // Security: Clamp speed values
            'marquee_direction' => $settings['marquee_direction'] ?? 'horizontal',
            'pause_on_hover' => $settings['pause_on_hover'] === 'yes' ? 'true' : 'false',
            'reverse_direction' => $settings['reverse_direction'] === 'yes',
            'content_type' => in_array($settings['content_type'] ?? 'posts', ['posts', 'custom']) ? $settings['content_type'] : 'posts',
        ];
    }

    /**
     * Render ticker content based on content type
     *
     * @param array $settings Widget settings
     * @param array $config Widget configuration
     */
    private function render_ticker_content_by_type($settings, $config) {
        if ($config['content_type'] === 'posts') {
            $this->render_dynamic_posts_content($settings);
        } else {
            $this->render_custom_text_content($settings);
        }
    }

    /**
     * Render dynamic posts content
     *
     * @param array $settings Widget settings
     */
    private function render_dynamic_posts_content($settings) {
        $query_args = $this->get_query_args($settings);
        $posts = get_posts($query_args);
        $this->render_ticker_content($settings, $posts);
    }

    /**
     * Render custom text content
     *
     * @param array $settings Widget settings
     */
    private function render_custom_text_content($settings) {
        $ticker_content = $settings['ticker_content_custom'] ?? [];

        if (empty($ticker_content)) {
            return;
        }

        echo '<div class="marqueex-marquee-wrapper">';
        $total_items = count($ticker_content);
        $ticker_content = $this->ensure_minimum_posts($ticker_content);

        foreach ($ticker_content as $index => $item) {
            $text = $item['text'] ?? '';
            $link = $item['link'] ?? [];
            $icon = $item['icon'] ?? [];

            if (empty($text)) {
                continue;
            }

            echo '<div class="marqueex-news-item">';
            // if ($settings['direction_vertical'] === 'yes') {
            //     $this->render_separator($settings, $item);
            // }
            $this->render_icons($settings, $icon);

            echo '<span class="marqueex-news-text">';

            // Render icon if available

            if (!empty($link['url'])) {
                $rel_attrs = [];

                if ($link['is_external']) {
                    $rel_attrs[] = 'noopener noreferrer';
                }
                if ($link['nofollow']) {
                    $rel_attrs[] = 'nofollow';
                }

                $rel_value = implode(' ', $rel_attrs);

                echo '<a href="' . esc_url($link['url']) . '"'
                    . ($link['is_external'] ? ' target="_blank"' : '')
                    . ($rel_value ? ' rel="' . esc_attr($rel_value) . '"' : '')
                    . ' class="marqueex-title-link" aria-label="' . esc_attr($text) . '">'
                    . esc_html($text) . '</a>';
            } else {
                echo esc_html($text);
            }

            echo '</span>';
            echo '</div>';

            // Only render separator if not the last item
            // if ($settings['direction_vertical'] !== 'yes') {
            //     // Render icon if available
            //     $this->render_icons($settings, $icon);
            // }
        }
        echo '</div>';
    }


    /**
     * Prepare link configuration
     *
     * @param array $settings Widget settings
     * @return array Link configuration
     */
    private function prepare_link_config($settings) {
        return [
            'enable_links' => $settings['enable_links'] ?? 'yes',
            'open_in_new_tab' => $settings['open_in_new_tab'] ?? 'no'
        ];
    }

    /**
     * Render single news item
     *
     * @param object $post Post object
     * @param array $settings Widget settings
     * @param array $link_config Link configuration
     */
    private function render_single_news_item($post, $settings, $link_config) {
        if (!$post instanceof \WP_Post) {
            return;
        }

        $title = get_the_title($post);

        // Trim title if enabled. Clamp the word count so adversarial /
        // unset settings can't produce surprising output.
        if (($settings['title_trim_enable'] ?? '') === 'yes') {
            $trim_length = max(1, min(200, intval($settings['title_trim_length'] ?? 50)));
            $title = wp_trim_words($title, $trim_length, '...');
        }

        $url = $this->get_post_url($post);

        echo '<span class="marqueex-news-text">';

        if ($link_config['enable_links'] === 'yes' && !empty($url)) {
            echo '<a href="' . esc_url($url) . '"'
                . ($link_config['open_in_new_tab'] === 'yes' ? ' target="_blank" rel="noopener noreferrer"' : '')
                . ' class="marqueex-title-link" aria-label="' . esc_attr($title) . '">'
                . esc_html($title) . '</a>';
        } else {
            echo esc_html($title);
        }

        echo '</span>';
    }

    /**
     * Render news ticker content with posts and separators
     *
     * Handles the rendering of news items with proper title trimming,
     * link generation, and separator insertion between items.
     *
     * @param array $settings Widget settings
     * @param array $posts Array of post objects
     */
    private function render_ticker_content($settings, $posts = []) {
        // Ensure we have posts to display
        $posts = $this->ensure_minimum_posts($posts);

        // Prepare link configuration
        $link_config = $this->prepare_link_config($settings);

        echo '<div class="marqueex-marquee-wrapper">';

        // Render each post with separators
        $total_posts = count($posts);
        foreach ($posts as $index => $post) {
            echo '<div class="marqueex-news-item">';
            if ($settings['direction_vertical'] === 'yes') {
                $this->render_separator($settings, $post);
            }
            $this->render_single_news_item($post, $settings, $link_config);
            echo '</div>';

            // Only render separator if not the last item
            if ($settings['direction_vertical'] !== 'yes') {
                if ($index < $total_posts - 1) {
                    $this->render_separator($settings, $post);
                }
            }
        }

        echo '</div>';
    }

    /**
     * Render separator based on type
     *
     * @param array $settings Widget settings
     * @param WP_Post $post Current post object
     */
    private function render_separator($settings, $post) {
        $separator_type = $settings['separator_type'] ?? 'none';

        if ($separator_type === 'none') {
            return;
        }

        $separator_preset = $settings['separator_style_preset'] ?? 'modern';
        $separator_classes = 'marqueex-separator marqueex-separator-' . esc_attr($separator_type) . ' marqueex-separator-preset-' . esc_attr($separator_preset);

        echo '<span class="' . esc_attr($separator_classes) . '">';

        switch ($separator_type) {
            case 'icon':
                $separator_icon = $settings['separator_icon'] ?? [];
                if (!empty($separator_icon['value'])) {
                    echo '<span class="marqueex-separator-icon">';
                    \Elementor\Icons_Manager::render_icon($separator_icon, ['aria-hidden' => 'true']);
                    echo '</span>';
                }
                break;

            case 'text':
                $separator_text = $settings['separator_text'] ?? '|';
                echo '<span class="marqueex-separator-text">' . esc_html($separator_text) . '</span>';
                break;

            case 'date':
                echo '<span class="marqueex-separator-date">' . esc_html(get_the_date('', $post)) . '</span>';
                break;

            case 'featured_image':
                if (has_post_thumbnail($post)) {
                    echo '<span class="marqueex-separator-feature-image">';
                    echo get_the_post_thumbnail($post, 'thumbnail');
                    echo '</span>';
                }
                break;
        }

        echo '</span>';
    }
    private function render_icons($settings, $icon) {
        $separator_type = $settings['separator_type'] ?? 'icon';

        if ($separator_type === 'none') {
            return;
        }
        $separator_preset = $settings['separator_style_preset'] ?? 'modern';
        $separator_classes = 'marqueex-separator marqueex-separator-icon marqueex-separator-preset-' . esc_attr($separator_preset);

        if (!empty($icon['value'])) {
            echo '<span class="' . esc_attr($separator_classes) . '">';
            echo '<span class="marqueex-separator-icon">';
            \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
            echo '</span>';
            echo '</span>';
        }
    }

    /**
     * Render separator for custom content
     *
     * @param array $settings Widget settings
     */
    private function render_separator_for_custom($settings) {
        $separator_type = $settings['separator_type'] ?? 'none';

        if ($separator_type === 'none') {
            return;
        }

        $separator_preset = $settings['separator_style_preset'] ?? 'modern';
        $separator_classes = 'marqueex-separator marqueex-separator-' . esc_attr($separator_type) . ' marqueex-separator-preset-' . esc_attr($separator_preset);

        echo '<span class="' . esc_attr($separator_classes) . '">';

        switch ($separator_type) {
            case 'icon':
                $separator_icon = $settings['separator_icon'] ?? [];
                if (!empty($separator_icon['value'])) {
                    echo '<span class="marqueex-separator-icon">';
                    \Elementor\Icons_Manager::render_icon($separator_icon, ['aria-hidden' => 'true']);
                    echo '</span>';
                }
                break;

            case 'text':
                $separator_text = $settings['separator_text'] ?? '|';
                echo '<span class="marqueex-separator-text">' . esc_html($separator_text) . '</span>';
                break;

            case 'date':
                echo '<span class="marqueex-separator-date">' . esc_html(current_time('F j, Y')) . '</span>';
                break;

            case 'featured_image':
                // For custom content, we can show a default image or skip
                // Since there's no post context, we'll show a placeholder or skip
                break;
        }

        echo '</span>';
    }

    /**
     * Render widget output on the frontend
     *
     * This method handles the complete rendering of the news ticker widget,
     * including label positioning, content generation, and marquee setup.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        // Extract and prepare widget configuration
        $widget_config = $this->prepare_widget_config($settings);

        $render_attrs = [
            'class' => array_values(array_filter([
                'marqueex-marquee-block',
                'marqueex-text-marquee',
                ($settings['pause_on_hover'] ?? '') === 'yes' ? 'is-paused' : '',
                ($settings['reverse_direction'] ?? '') === 'yes' ? 'is-reversed' : '',
                ($settings['direction_vertical'] ?? '') === 'yes' ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
            ])),
            'data-settings' => wp_json_encode(array_filter([
                'marquee_speed' => max(10, min(100, intval($settings['speed']['size'] ?? 30))),
            ])),
            'role' => 'region',
            'aria-label' => __('Text Marquee', 'marqueex'),
        ];

        /**
         * Filter the render attributes for marqueex widgets so extensions
         * (e.g. marqueex-pro) can inject extra classes or data-* attributes.
         *
         * @param array  $render_attrs Attribute map. `class` is an array of class names.
         * @param array  $settings     Widget settings from get_settings_for_display().
         * @param string $widget_name  Elementor widget name (e.g. marqueex-text-marquee).
         */
        $render_attrs = apply_filters('marqueex_widget_render_attributes', $render_attrs, $settings, $this->get_name());

        $this->add_render_attribute('marqueex-marquee-block', $render_attrs);

        // Render the track content once and reuse it for the aria-hidden clone,
        // avoiding a second identical post query per widget render.
        ob_start();
        $this->render_ticker_content_by_type($settings, $widget_config);
        $track_html = ob_get_clean();

?>
        <?php // get_render_attribute_string() returns markup already escaped by Elementor. ?>
        <div <?php echo $this->get_render_attribute_string('marqueex-marquee-block'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
