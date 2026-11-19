<?php

namespace Wpxero\Marqueex\Traits\AnimatedWordRoller;

use Elementor\Controls_Manager;
use Wpxero\Marqueex\Core\Upsell;

if (!defined('ABSPATH')) {
    exit;
}

trait AdditionalOptionsControls {

    private function register_additional_options_section_controls() {
        $this->start_controls_section(
            'marqueex_word_roller_additional_section',
            [
                'label' => __('Additional Options', 'marqueex'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'marqueex_word_roller_delay',
            [
                'label' => __('Active Word Duration', 'marqueex'),
                'description' => __('Time each word stays active (in seconds).', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 1,
            ]
        );

        $this->add_control(
            'marqueex_word_roller_visible_words',
            [
                'label' => __('Visible Words', 'marqueex'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'step' => 1,
                'default' => 5,
            ]
        );

        $free_animations = [
            'vertical' => __('Vertical Scroll', 'marqueex'),
        ];

        $pro_animations = [
            'horizontal' => __('Horizontal Scroll', 'marqueex'),
            'fade' => __('Fade', 'marqueex'),
            'slide' => __('Slide', 'marqueex'),
            'zoom' => __('Zoom', 'marqueex'),
        ];

        $this->add_control(
            'marqueex_word_roller_animation_type',
            [
                'label' => __('Animation Type', 'marqueex'),
                'type' => Controls_Manager::SELECT,
                'default' => 'vertical',
                'options' => Upsell::merge_select_options($free_animations, $pro_animations),
            ]
        );

        Upsell::add_elementor_notice($this, 'marqueex_word_roller_pro_notice', [
            'title'       => __('4 more word transitions', 'marqueex'),
            'description' => __('Roll words sideways, or swap them with a fade, slide or zoom instead of a vertical scroll.', 'marqueex'),
            'items'       => array_values($pro_animations),
            'cta'         => __('Unlock these transitions', 'marqueex'),
            'source'      => 'elementor-word-roller',
        ]);

        $this->add_control(
            'marqueex_word_roller_pause_on_hover',
            [
                'label' => __('Pause on Hover', 'marqueex'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'marqueex'),
                'label_off' => __('No', 'marqueex'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'marqueex_word_roller_notice',
            [
                'type' => Controls_Manager::ALERT,
                'alert_type' => 'warning',
                'heading' => __('Visible Words Notice', 'marqueex'),
                'content' => __('Please make sure to select an odd number that is less than or equal to the total number of words in your list.', 'marqueex'),
            ]
        );

        $this->end_controls_section();
    }
}
