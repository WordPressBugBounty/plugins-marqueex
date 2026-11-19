<?php

namespace Wpxero\Marqueex\Traits\AnimatedHeading;

use Elementor\Controls_Manager;
use Wpxero\Marqueex\Core\Upsell;

if (!defined('ABSPATH')) {
    exit;
}

trait AnimationControls {

    private function register_animation_section_controls() {
        $this->start_controls_section(
            'marqueex_animation_section',
            [
                'label' => __('Animation Effect', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );


        $free_animations = [
            'slide' => __('Slide', 'marqueex'),
            'slide-horizontal' => __('Slide Horizontal', 'marqueex'),
            'typing' => __('Typing', 'marqueex'),
        ];

        $pro_animations = [
            'rotation-3d' => __('3D Rotation', 'marqueex'),
            'construct' => __('Construct', 'marqueex'),
            'twist' => __('Twist', 'marqueex'),
            'line' => __('Line', 'marqueex'),
            'wave' => __('Wave', 'marqueex'),
            'swing' => __('Swing', 'marqueex'),
            'tilt' => __('Tilt', 'marqueex'),
            'lean' => __('Lean', 'marqueex'),
        ];

        // Pro animations stay visible to free users, labelled "(Pro)". Previously
        // they were omitted from the list entirely, so nobody knew they existed.
        // Selecting one without a license falls back to Slide on render (see
        // AnimatedHeading::render()), and the card below explains why.
        $this->add_control('marqueex_animation_type', [
            'label' => __('Type', 'marqueex'),
            'type' => Controls_Manager::SELECT,
            'default' => 'slide',
            'options' => Upsell::merge_select_options($free_animations, $pro_animations),
        ]);

        Upsell::add_elementor_notice($this, 'marqueex_animation_pro_notice', [
            'title'       => __('8 more heading animations', 'marqueex'),
            'description' => __('Give a headline motion people actually stop for, without writing a line of CSS.', 'marqueex'),
            'items'       => array_values($pro_animations),
            'cta'         => __('Unlock these animations', 'marqueex'),
            'source'      => 'elementor-animated-heading-animation',
        ]);

        $this->add_control('marqueex_animation_speed', [
            'label' => __('Speed', 'marqueex'),
            'type' => Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 1000,
            'step' => 1,
            'default' => 10,
        ]);

        $this->add_control('marqueex_pause_between_words', [
            'label' => __('Pause Between Words (sec)', 'marqueex'),
            'type' => Controls_Manager::NUMBER,
            'condition' => [
                'marqueex_animation_type' => 'construct'
            ],
            'min' => 0.1,
            'max' => 100,
            'step' => 0.1,
            'default' => 1,
        ]);

        $this->add_control('marqueex_pause_after_typed', [
            'label' => __('Pause After Typed (sec)', 'marqueex'),
            'type' => Controls_Manager::NUMBER,
            'condition' => [
                'marqueex_animation_type' => 'typing'
            ],
            'min' => 0.1,
            'max' => 100,
            'step' => 0.1,
            'default' => 1,
        ]);

        $this->add_control('marqueex_delay_per_word', [
            'label' => __('Delay Per Word (sec)', 'marqueex'),
            'type' => Controls_Manager::NUMBER,
            'condition' => [
                'marqueex_animation_type' => ['slide', 'slide-horizontal']
            ],
            'min' => 0.1,
            'max' => 100,
            'step' => 0.1,
            'default' => 1.5,
        ]);

        $this->add_control('marqueex_slide_vertical_direction', [
            'label' => __('Slide Direction', 'marqueex'),
            'type' => Controls_Manager::SELECT,
            'condition' => [
                'marqueex_animation_type' => 'slide',
            ],
            'default' => '1',
            'options' => [
                '1' => __('To Top', 'marqueex'),
                '-1' => __('To Bottom', 'marqueex'),
            ],
        ]);

        $this->add_control('marqueex_slide_horizontal_direction', [
            'label' => __('Slide Direction', 'marqueex'),
            'type' => Controls_Manager::SELECT,
            'condition' => [
                'marqueex_animation_type' => 'slide-horizontal',
            ],
            'default' => '1',
            'options' => [
                '1' => __('To Left', 'marqueex'),
                '-1' => __('To Right', 'marqueex'),
            ],
        ]);

        $this->add_control('marqueex_line_type', [
            'label' => __('Line Type', 'marqueex'),
            'type' => Controls_Manager::SELECT,
            'condition' => [
                'marqueex_animation_type' => 'line'
            ],
            'default' => 'singleUnderline',
            'options' => [
                'singleUnderline' => __('Single Underline', 'marqueex'),
                'doubleUnderline' => __('Double Underline', 'marqueex'),
                'wavyUnderline' => __('Wavy Underline', 'marqueex'),
                'underlineZigzag' => __('Zigzag Underline', 'marqueex'),
                'circle' => __('Circle', 'marqueex'),
            ],
        ]);

        $this->add_control('marqueex_delay_before_erase', [
            'label' => __('Delay Before Erase (sec)', 'marqueex'),
            'type' => Controls_Manager::NUMBER,
            'condition' => [
                'marqueex_animation_type' => 'line'
            ],
            'min' => 0.1,
            'max' => 100,
            'step' => 0.1,
            'default' => 1,
        ]);

        $this->add_control('marqueex_animation_pause_on_hover', [
            'label' => __('Pause on Hover', 'marqueex'),
            'type' => Controls_Manager::SWITCHER,
            'condition' => [
                'marqueex_animation_type!' => 'line'
            ],
            'label_on' => __('On', 'marqueex'),
            'label_off' => __('Off', 'marqueex'),
            'return_value' => 'yes',
            'default' => '',
        ]);

        $this->end_controls_section();
    }
}
