<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recommendations for other WPXERO plugins, shown inside the MarqueeX app.
 *
 * Rules this follows (WordPress.org guideline 11 and plain good manners):
 *  - Nothing installs or activates without the user clicking a button. The
 *    install itself goes through WordPress core (REST `wp/v2/plugins`), so the
 *    usual capability checks and file-mod restrictions apply.
 *  - Only on MarqueeX's own screen; never a site-wide notice.
 *  - Plugins already active are never suggested.
 *  - The catalogue ships with the plugin — no request to an external API from
 *    wp-admin. Icons load from WordPress.org's plugin CDN, as core's own
 *    plugin installer does.
 *
 * Keep the catalogue in step with what is actually published on
 * WordPress.org under the `wpxero` author: a slug that 404s there breaks the
 * install button.
 */
class Recommendations {
    use Singleton;

    private function __construct() {
    }

    /**
     * Curated catalogue. `pitch` is why it fits next to MarqueeX, not the
     * plugin's generic description.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function catalogue() {
        return [
            [
                'slug'     => 'ultimate-cursor',
                'name'     => 'Ultimate Cursor',
                'category' => __('Interaction', 'marqueex'),
                'pitch'    => __('Custom and animated cursors that match a site full of motion. Live preview, no code.', 'marqueex'),
                'icon'     => 'https://ps.w.org/ultimate-cursor/assets/icon.svg',
                'installs' => 3000,
                'score'    => 90,
            ],
            [
                'slug'     => 'slideverse',
                'name'     => 'SlideVerse',
                'category' => __('Sliders', 'marqueex'),
                'pitch'    => __('Hero, image, post, logo and before/after slider blocks for when content should stop and be read.', 'marqueex'),
                'icon'     => 'https://ps.w.org/slideverse/assets/icon.svg',
                'installs' => 100,
                'score'    => 80,
                'needs'    => 'gutenberg',
            ],
            [
                'slug'     => 'wpxero-animation-kit',
                'name'     => 'Animation Kit',
                'category' => __('Backgrounds', 'marqueex'),
                'pitch'    => __('Aurora, particles, silk and other animated backgrounds behind your marquees — site-wide or on one block.', 'marqueex'),
                'icon'     => 'https://ps.w.org/wpxero-animation-kit/assets/icon.svg',
                'installs' => 30,
                'score'    => 78,
            ],
            [
                'slug'     => 'gridify-plus',
                'name'     => 'Gridify Plus',
                'category' => __('Galleries', 'marqueex'),
                'pitch'    => __('Photo galleries, post grids and product grids with lightbox and masonry — the static partner to an image marquee.', 'marqueex'),
                'icon'     => 'https://ps.w.org/gridify-plus/assets/icon.svg',
                'installs' => 10,
                'score'    => 70,
            ],
            [
                'slug'     => 'photo-sphere-viewer',
                'name'     => 'Photo Sphere Viewer',
                'category' => __('360° & AR', 'marqueex'),
                'pitch'    => __('360° virtual tours, panoramas, 360° video and AR 3D models. Free, unlimited, no watermark.', 'marqueex'),
                'icon'     => 'https://ps.w.org/photo-sphere-viewer/assets/icon.svg',
                'installs' => 600,
                'score'    => 66,
            ],
            [
                'slug'     => 'wpxero-search-filter',
                'name'     => 'FalcoSearch',
                'category' => __('Search', 'marqueex'),
                'pitch'    => __('Live AJAX search and a visual product filter builder for WooCommerce and any post type.', 'marqueex'),
                'icon'     => 'https://ps.w.org/wpxero-search-filter/assets/icon.svg',
                'installs' => 0,
                'score'    => 45,
                'boost'    => ['woocommerce' => 45],
            ],
            [
                'slug'     => 'tryonx',
                'name'     => 'TryonX',
                'category' => __('WooCommerce', 'marqueex'),
                'pitch'    => __('AI virtual try-on: shoppers upload a photo and see your clothing on themselves before buying.', 'marqueex'),
                'icon'     => 'https://ps.w.org/tryonx/assets/icon.svg',
                'installs' => 10,
                'score'    => 60,
                'needs'    => 'woocommerce',
            ],
            [
                'slug'     => 'menuverse',
                'name'     => 'MenuVerse',
                'category' => __('Navigation', 'marqueex'),
                'pitch'    => __('A mobile menu and an app-style bottom navigation bar, with live preview.', 'marqueex'),
                'icon'     => 'https://ps.w.org/menuverse/assets/icon.svg',
                'installs' => 20,
                'score'    => 55,
            ],
            [
                'slug'     => 'extenderx',
                'name'     => 'ExtenderX',
                'category' => __('Accessibility', 'marqueex'),
                'pitch'    => __('An accessibility toolbar with text size, contrast and reading profiles. No cookies, no external service.', 'marqueex'),
                'icon'     => 'https://ps.w.org/extenderx/assets/icon.svg',
                'installs' => 100,
                'score'    => 54,
            ],
            [
                'slug'     => 'wpxero-text-to-speech',
                'name'     => 'VoiceX',
                'category' => __('Audio', 'marqueex'),
                'pitch'    => __('A Listen button that reads your posts aloud in natural voices. No API key, no account.', 'marqueex'),
                'icon'     => 'https://ps.w.org/wpxero-text-to-speech/assets/icon.svg',
                'installs' => 0,
                'score'    => 48,
            ],
            [
                'slug'     => 'advanced-google-map-block',
                'name'     => 'GeoVerse Maps',
                'category' => __('Maps', 'marqueex'),
                'pitch'    => __('A Google Maps block with custom markers, styles, directions and a store locator.', 'marqueex'),
                'icon'     => 'https://ps.w.org/advanced-google-map-block/assets/icon-256x256.png',
                'installs' => 40,
                'score'    => 40,
                'needs'    => 'gutenberg',
            ],
            [
                'slug'     => 'wpxero-iframe',
                'name'     => 'Iframe Block',
                'category' => __('Embeds', 'marqueex'),
                'pitch'    => __('Responsive YouTube, Vimeo and map embeds with captions, privacy mode and lazy-loading.', 'marqueex'),
                'icon'     => 'https://ps.w.org/wpxero-iframe/assets/icon-256x256.png',
                'installs' => 10,
                'score'    => 38,
                'needs'    => 'gutenberg',
            ],
        ];
    }

    /**
     * Data for the admin app.
     *
     * @return array{items: array, canInstall: bool, canActivate: bool}
     */
    public static function data() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Map directory slug => main plugin file, for everything installed.
        $installed = [];
        foreach (array_keys(get_plugins()) as $file) {
            $dir = strstr($file, '/', true);
            if ($dir !== false && !isset($installed[$dir])) {
                $installed[$dir] = $file;
            }
        }

        $signals = [
            'woocommerce' => class_exists('WooCommerce'),
            'gutenberg'   => (bool) \Wpxero\Marqueex\Core\Settings::get('builder_support.gutenberg.enabled', true),
            'elementor'   => class_exists('\Elementor\Plugin'),
        ];

        $requirement_labels = [
            'woocommerce' => __('WooCommerce', 'marqueex'),
            'gutenberg'   => __('the block editor', 'marqueex'),
        ];

        $items = [];
        foreach (self::catalogue() as $plugin) {
            // A plugin whose requirement this site lacks still appears in the
            // full catalogue (flagged), but is never pushed on the dashboard.
            $needs = $plugin['needs'] ?? '';
            $fits  = !$needs || !empty($signals[$needs]);

            $file = $installed[$plugin['slug']] ?? null;
            if ($file && is_plugin_active($file)) {
                continue; // Already in use — nothing to suggest.
            }

            $score = (int) $plugin['score'];
            foreach ($plugin['boost'] ?? [] as $signal => $bonus) {
                if (!empty($signals[$signal])) {
                    $score += (int) $bonus;
                }
            }

            $items[] = [
                'slug'     => $plugin['slug'],
                'name'     => $plugin['name'],
                'category' => $plugin['category'],
                'pitch'    => $plugin['pitch'],
                'icon'     => $plugin['icon'],
                'installs' => (int) $plugin['installs'],
                'status'   => $file ? 'inactive' : 'not-installed',
                // REST id for wp/v2/plugins/<plugin>: the file path without ".php".
                'plugin'   => $file ? substr($file, 0, -4) : null,
                'url'      => 'https://wordpress.org/plugins/' . $plugin['slug'] . '/',
                'score'    => $fits ? $score : $score - 100,
                'fits'     => $fits,
                'requires' => $fits ? '' : ($requirement_labels[$needs] ?? $needs),
            ];
        }

        usort($items, static function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return [
            'items'       => $items,
            // wp_is_file_mod_allowed covers DISALLOW_FILE_MODS and multisite.
            'canInstall'  => current_user_can('install_plugins') && wp_is_file_mod_allowed('marqueex_recommendations'),
            'canActivate' => current_user_can('activate_plugins'),
        ];
    }
}
