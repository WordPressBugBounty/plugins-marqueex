<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Menu {
    use Singleton;

    /** Screen id of the MarqueeX app page. */
    const SCREEN_ID = 'toplevel_page_marqueex';

    private function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        // in_admin_header fires after the screen is set up and before any
        // *_admin_notices hook runs, so callbacks removed here never print.
        add_action('in_admin_header', [$this, 'suppress_notices'], PHP_INT_MAX);
    }

    /**
     * Keep the MarqueeX app screen free of admin notices — ours, core's
     * (update nags) and every other plugin's, Freemius included.
     *
     * Scoped to the app screen only. Freemius' own account/pricing screens
     * keep their notices, so license activation messages still have a home,
     * and the app itself surfaces "Activate your license" when one is pending.
     */
    public function suppress_notices() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || self::SCREEN_ID !== $screen->id) {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
    }

    public function register_menu() {
        // Add main menu
        add_menu_page(
            __('MarqueeX', 'marqueex'),
            __('MarqueeX', 'marqueex'),
            'manage_options',
            'marqueex',
            [$this, 'render_menu_page'],
            // Strokes sit inside a mask: wp-admin's svg-painter rewrites every fill="…"
            // to the menu colour, which would flood a stroke-only icon.
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><mask id="marqueex-mark"><g stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" fill-opacity="0"><path d="M9 17.5V6.5l5.75 7 5.75-7v11"/><path d="M2 9.5h3.5M3.5 14.5h2"/></g></mask><rect width="24" height="24" fill="#a7aaad" mask="url(#marqueex-mark)"/></svg>'),
            30
        );
        // One screen, so one submenu item — registered against the parent slug so
        // it renames WordPress's auto-generated duplicate rather than adding a
        // second registered page. (A second page slug was why "Settings" used to
        // open the app with no sub_page and land on the wrong tab.)
        add_submenu_page(
            'marqueex',
            esc_html__('Dashboard', 'marqueex'),
            esc_html__('Dashboard', 'marqueex'),
            'manage_options',
            'marqueex',
            [$this, 'render_menu_page']
        );

        // "Our Plugins" opens the same app on its catalogue screen. A URL as the
        // menu slug makes WordPress print it as a plain link instead of
        // registering a second page (which would break routing, see above).
        add_submenu_page(
            'marqueex',
            esc_html__('Our Plugins', 'marqueex'),
            esc_html__('Our Plugins', 'marqueex'),
            'manage_options',
            'admin.php?page=marqueex&sub_page=plugins'
        );
    }

    public function render_menu_page() {
        echo '<div class="wrap">';
        echo '<div id="marqueex-admin-page"></div>';
        echo '</div>';
    }
}
