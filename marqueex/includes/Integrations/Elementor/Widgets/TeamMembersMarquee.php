<?php

namespace Wpxero\Marqueex\Integrations\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;

if (!defined('ABSPATH')) {
    exit;
}

class TeamMembersMarquee extends Widget_Base {

    public function get_name() {
        return 'marqueex-team-members-marquee';
    }

    public function get_title() {
        return __('Team Members Marquee', 'marqueex');
    }

    public function get_icon() {
        return 'eicon-person eicon-marqueex';
    }

    public function get_categories() {
        return ['marqueex'];
    }

    public function get_style_depends() {
        return ['marqueex-team-members-marquee'];
    }

    public function get_script_depends() {
        return ['marqueex-team-members-marquee'];
    }

    public function get_keywords() {
        return ['team', 'members', 'marquee', 'staff', 'people', 'slider'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Team Members', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'member_image',
            [
                'label' => __('Choose Image', 'marqueex'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'member_name',
            [
                'label' => __('Name', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'default' => __('John Doe', 'marqueex'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'member_role',
            [
                'label' => __('Job Role', 'marqueex'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Team Member', 'marqueex'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'team_members',
            [
                'label' => __('Team Members', 'marqueex'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'member_name' => __('John Doe', 'marqueex'),
                        'member_role' => __('CEO & Founder', 'marqueex'),
                    ],
                    [
                        'member_name' => __('Jane Smith', 'marqueex'),
                        'member_role' => __('Lead Developer', 'marqueex'),
                    ],
                    [
                        'member_name' => __('Mike Johnson', 'marqueex'),
                        'member_role' => __('Designer', 'marqueex'),
                    ],
                ],
                'title_field' => '{{{ member_name }}}',
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'default' => 'medium_large',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

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
                'range' => ['px' => ['min' => 10, 'max' => 400]],
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

        $this->add_control(
            'image_effect',
            [
                'label' => __('Image Effect', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'marqueex'),
                    'grayscale' => __('Grayscale', 'marqueex'),
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Team Member Box', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_background',
            [
                'label' => __('Background Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'selector' => '{{WRAPPER}} .marqueex-team-member',
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .marqueex-team-member',
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => __('Padding', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_alignment',
            [
                'label' => __('Content Alignment', 'marqueex'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'marqueex'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'marqueex'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'marqueex'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Image', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Width', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 50, 'max' => 500],
                    '%' => ['min' => 10, 'max' => 100],
                ],
                'default' => ['unit' => 'px', 'size' => 200],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-image img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_spacing',
            [
                'label' => __('Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 100]],
                'default' => ['size' => 20, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-image' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .marqueex-team-member-image img',
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'marqueex'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-image, {{WRAPPER}} .marqueex-team-member-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .marqueex-team-member-image img',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'name_style_section',
            [
                'label' => __('Name', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __('Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .marqueex-team-member-name',
            ]
        );

        $this->add_responsive_control(
            'name_spacing',
            [
                'label' => __('Spacing', 'marqueex'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 50]],
                'default' => ['size' => 10, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'role_style_section',
            [
                'label' => __('Job Role', 'marqueex'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'role_color',
            [
                'label' => __('Color', 'marqueex'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .marqueex-team-member-role' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'role_typography',
                'selector' => '{{WRAPPER}} .marqueex-team-member-role',
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
            'unique_id' => 'marqueex-team-' . $this->get_id(),
            'speed' => max(10, min(400, intval($settings['speed']['size'] ?? 20))),
            'orientation' => $settings['orientation'] ?? 'horizontal',
            'pause_on_hover' => $settings['pause'] === 'yes' ? 'true' : 'false',
            'reverse_direction' => $settings['reverse'] === 'yes',
        ];
    }

    /**
     * Render team members content
     */
    private function render_team_members_content($settings) {
        if (empty($settings['team_members'])) {
            return;
        }

        $team_members = $this->ensure_minimum_posts($settings['team_members']);

        echo '<div class="marqueex-marquee-wrapper">';
        foreach ($team_members as $index => $member) {
            $this->render_single_team_member($member, $settings, $index);
        }
        echo '</div>';
    }

    /**
     * Render single team member
     */
    private function render_single_team_member($member, $settings, $index) {
        $member_classes = 'marqueex-team-member';
        if ('grayscale' === $settings['image_effect']) {
            $member_classes .= ' marqueex-grayscale';
        }
        ?>
        <div class="<?php echo esc_attr($member_classes); ?>">
            <div class="marqueex-team-member-content">
                <?php if (!empty($member['member_image']['url'])) : ?>
                    <div class="marqueex-team-member-image">
                        <?php
                        // Returns a complete, safe <img> built by Elementor's image-size group control.
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo Group_Control_Image_Size::get_attachment_image_html(
                            $member,
                            'thumbnail',
                            'member_image'
                        );
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($member['member_name'])) : ?>
                    <h3 class="marqueex-team-member-name">
                        <?php echo esc_html($member['member_name']); ?>
                    </h3>
                <?php endif; ?>

                <?php if (!empty($member['member_role'])) : ?>
                    <div class="marqueex-team-member-role">
                        <?php echo esc_html($member['member_role']); ?>
                    </div>
                <?php endif; ?>
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
                        'marqueex-team-members-marquee',
                        $settings['pause'] === 'yes' ? 'is-paused' : '',
                        $settings['reverse'] === 'yes' ? 'is-reversed' : '',
                        $settings['orientation'] === 'vertical' ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
                        $settings['mask_edges'] === 'yes' ? 'marqueex-mask-edges' : '',
                    ],
                    'data-speed' => $widget_config['speed'],
                    'role' => 'region',
                    'aria-label' => __('Team Members Marquee', 'marqueex'),
                ]
            ]
        );

?>
        <?php // get_render_attribute_string() returns markup already escaped by Elementor. ?>
        <div <?php echo $this->get_render_attribute_string('marqueex-marquee-block'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="marqueex-marquee-track-wrapper">
                <div class="marqueex-marquee-track">
                    <?php $this->render_team_members_content($settings); ?>
                </div>
                <div aria-hidden="true" class="marqueex-marquee-track">
                    <?php
                    ob_start();
                    $this->render_team_members_content($settings);
                    echo \Wpxero\Marqueex\Utils\Markup::unfocusable(ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by render_team_members_content()
                    ?>
                </div>
            </div>
        </div>

<?php
    }
}
