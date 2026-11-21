<?php
namespace Wpxero\Marqueex\Utils;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Small HTML helpers shared by blocks and Elementor widgets.
 *
 * @package marqueex
 */
class Markup {

    /**
     * Take every focusable element in $html out of the tab order.
     *
     * A seamless marquee renders its content twice; the second copy is
     * aria-hidden so screen readers announce each item once. Its links must
     * also be skipped by the keyboard — a focusable element inside
     * aria-hidden is announced as nothing (WCAG 4.1.2). `inert` would do both,
     * but the copy is visible and mouse users must still be able to click it.
     *
     * @param string $html Already-escaped markup for the duplicate track.
     * @return string
     */
    public static function unfocusable($html) {
        return (string) preg_replace_callback(
            '/<(a|button|input|select|textarea|iframe|summary)\b([^>]*)>/i',
            static function ($m) {
                $attrs = preg_replace('/\stabindex\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $m[2]);
                return '<' . $m[1] . ' tabindex="-1"' . $attrs . '>';
            },
            (string) $html
        );
    }
}
