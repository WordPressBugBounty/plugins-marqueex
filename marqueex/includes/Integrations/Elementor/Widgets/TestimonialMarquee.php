<?php

namespace Wpxero\Marqueex\Integrations\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialMarquee extends Widget_Base {

    public function get_name() {
        return 'marqueex-testimonial-marquee';
    }

    public function get_title() {
        return __('Testimonial Marquee', 'marqueex');
    }

    public function get_icon() {
        return 'eicon-testimonial-carousel eicon-marqueex';
    }

    public function get_categories() {
        return ['marqueex'];
    }

    public function get_style_depends() {
        return ['marqueex-testimonial-marquee'];
    }

    public function get_script_depends() {
        return ['marqueex-testimonial-marquee'];
    }

    public function get_keywords() {
        return ['testimonial', 'marquee', 'review', 'feedback', 'slider', 'rating'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content',
            ['label' => __('Testimonials', 'marqueex')]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'content',
            [
                'label' => __('Content', 'marqueex'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('This is a testimonial text', 'marqueex'),
                'rows' => 5,
            ]
        );

        $repeater->add_control(
            'avatar',
            [
                'label' => __('Avatar', 'marqueex'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'name',
            [
                'label' => __('Name', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'default' => __('John Doe', 'marqueex'),
            ]
        );

        $repeater->add_control(
            'role',
            [
                'label' => __('Role/Designation', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Project Manager', 'marqueex'),
            ]
        );

        $repeater->add_control(
            'rating',
            [
                'label' => __('Rating', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'default' => 5,
            ]
        );

        $this->add_control(
            'items',
            [
                'label' => __('Testimonials List', 'marqueex'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'content' => __('MarqueeX is the perfect tool for anyone using Elementor.', 'marqueex'),
                        'name' => __('Wade Warren', 'marqueex'),
                        'role' => __('Project Manager', 'marqueex'),
                        'rating' => 5,
                    ],
                    [
                        'content' => __('Amazing features and great support. Highly recommended!', 'marqueex'),
                        'name' => __('Sarah Johnson', 'marqueex'),
                        'role' => __('Designer', 'marqueex'),
                        'rating' => 5,
                    ],
                ],
                'title_field' => '{{{ name }}}',
            ]
        );

        $this->add_control(
            'marqueex_testimonial_tip',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => '<strong>💡 Tip:</strong> For a smoother slider experience, add <strong>8–10 items</strong>.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'settings',
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
                    '{{WRAPPER}} .marqueex-marquee-block' => 'height: {{SIZE}}{{UNIT}};',
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

        $this->add_control(
            'gap',
            [
                'label' => __('Gap (px)', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'default' => 24,
                'min' => 0,
                'max' => 100,
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
            'style_section',
            [
                'label' => __('Card Style', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'label' => __('Card Background', 'marqueex'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .marqueex-testimonial .marqueex-card',
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label' => __('Content Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-testimonial .marqueex-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __('Name Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-testimonial .marqueex-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'role_color',
            [
                'label' => __('Role Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-testimonial .marqueex-role' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'stars_color',
            [
                'label' => __('Stars Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f1c40f',
                'selectors' => [
                    '{{WRAPPER}} .marqueex-testimonial .marqueex-star' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_typo',
                'selector' => '{{WRAPPER}} .marqueex-testimonial .marqueex-content',
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
     */
    private function prepare_widget_config($settings) {
        return [
            'unique_id' => 'marqueex-testimonial-' . $this->get_id(),
            'speed' => max(10, min(400, intval($settings['speed']['size'] ?? 60))),
            'orientation' => $settings['orientation'] ?? 'horizontal',
            'pause_on_hover' => $settings['pause'] === 'yes' ? 'true' : 'false',
            'reverse_direction' => $settings['reverse'] === 'yes',
        ];
    }

    /**
     * Render testimonials content
     */
    private function render_testimonials_content($settings) {
        if (empty($settings['items'])) {
            return;
        }

        $testimonials = $this->ensure_minimum_posts($settings['items']);

        echo '<div class="marqueex-marquee-wrapper">';
        foreach ($testimonials as $index => $item) {
            $this->render_single_testimonial($item, $settings, $index);
        }
        echo '</div>';
    }

    /**
     * Render single testimonial
     */
    private function render_single_testimonial($item, $settings, $index) {
        $rating = isset($item['rating']) ? intval($item['rating']) : 5;
        $rating = max(1, min(5, $rating));
        ?>
        <div class="marqueex-item marqueex-testimonial">
            <div class="marqueex-card">
                <?php if (!empty($item['content'])) : ?>
                    <div class="marqueex-content">
                        <?php echo wp_kses_post(nl2br($item['content'])); ?>
                    </div>
                <?php endif; ?>

                <div class="marqueex-author">
                    <?php if (!empty($item['avatar']['url'])) : ?>
                        <div class="marqueex-avatar">
                            <img src="<?php echo esc_url($item['avatar']['url']); ?>"
                                 alt="<?php echo esc_attr($item['name']); ?>"
                                 loading="lazy" />
                        </div>
                    <?php endif; ?>

                    <div class="marqueex-author-info">
                        <?php if (!empty($item['name'])) : ?>
                            <div class="marqueex-name">
                                <?php echo esc_html($item['name']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['role'])) : ?>
                            <div class="marqueex-role">
                                <?php echo esc_html($item['role']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="marqueex-rating">
                            <?php for ($i = 0; $i < 5; $i++) : ?>
                                <span class="marqueex-star<?php echo $i < $rating ? '' : ' inactive'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_config = $this->prepare_widget_config($settings);

        $this->add_render_attribute(
            [
                'marqueex-marquee-block' => [
                    'class' => [
                        'marqueex-marquee-block',
                        'marqueex-testimonial-marquee',
                        $settings['pause'] === 'yes' ? 'is-paused' : '',
                        $settings['reverse'] === 'yes' ? 'is-reversed' : '',
                        $settings['orientation'] === 'vertical' ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
                        $settings['mask_edges'] === 'yes' ? 'marqueex-mask-edges' : '',
                    ],
                    'data-speed' => $widget_config['speed'],
                    'role' => 'region',
                    'aria-label' => __('Testimonial Marquee', 'marqueex'),
                ]
            ]
        );

?>
        <?php // get_render_attribute_string() returns markup already escaped by Elementor. ?>
        <div <?php echo $this->get_render_attribute_string('marqueex-marquee-block'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="marqueex-marquee-track-wrapper">
                <div class="marqueex-marquee-track">
                    <?php $this->render_testimonials_content($settings); ?>
                </div>
                <div aria-hidden="true" class="marqueex-marquee-track">
                    <?php
                    ob_start();
                    $this->render_testimonials_content($settings);
                    echo \Wpxero\Marqueex\Utils\Markup::unfocusable(ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by render_testimonials_content()
                    ?>
                </div>
            </div>
        </div>

<?php
    }
}
