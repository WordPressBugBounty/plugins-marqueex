<?php

namespace Wpxero\Marqueex\Core;

use Wpxero\Marqueex\MarqueeX;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Upgrade links and locked-feature markup.
 *
 * Single source of truth for two things that were previously scattered across
 * seven files with three different visual styles and two different (one stale)
 * destination URLs:
 *
 *   1. Where an "upgrade" click goes — the public pricing page on wpxero.com,
 *      tagged with the control the click came from.
 *   2. What a locked Pro feature looks like.
 *
 * Free users must always be able to SEE what Pro adds. Hiding a Pro option
 * entirely (rather than showing it locked) means nobody can want it.
 */
class Upsell {

    /**
     * Public pricing page. Every upgrade CTA lands here — including Freemius'
     * own "Upgrade" admin submenu, which MarqueeX::init_freemius() points at
     * this URL through the SDK's pricing_url filter.
     */
    const PRICING_URL = 'https://wpxero.com/plugins/marqueex/pricing';

    /**
     * Upgrade destination for a given surface.
     *
     * @param string $source Short slug identifying the control/screen the click
     *                       came from, e.g. 'elementor-animated-heading'. Carried
     *                       through as a query arg so CTA performance can be
     *                       compared per surface.
     * @return string
     */
    public static function url($source = '') {
        $url = self::PRICING_URL;

        // Someone who has already paid doesn't need the pricing page — they need
        // to activate. Send them to their Freemius account screen instead.
        if (self::has_license() && function_exists('\\Wpxero\\Marqueex\\marqueex_fs')) {
            $fs = \Wpxero\Marqueex\marqueex_fs();
            if ($fs && method_exists($fs, 'get_account_url')) {
                try {
                    $url = $fs->get_account_url();
                } catch (\Exception $e) {
                    $url = self::PRICING_URL;
                }
            }
        }

        if ($source) {
            $url = add_query_arg('mqx_source', sanitize_key($source), $url);
        }

        return $url;
    }

    /**
     * Whether a free trial can still be started on this site.
     *
     * @return bool
     */
    public static function is_trial_available() {
        static $available = null;
        if ($available !== null) {
            return $available;
        }

        $available = false;

        if (function_exists('\\Wpxero\\Marqueex\\marqueex_fs')) {
            $fs = \Wpxero\Marqueex\marqueex_fs();
            if (
                $fs
                && method_exists($fs, 'has_trial_plan')
                && method_exists($fs, 'is_trial_utilized')
            ) {
                try {
                    $available = $fs->has_trial_plan()
                        && !$fs->is_trial_utilized()
                        && !self::has_license();
                } catch (\Exception $e) {
                    $available = false;
                }
            }
        }

        return $available;
    }

    /**
     * Label for the call to action.
     *
     * Deliberately not "Upgrade Now" — that names the transaction rather than
     * what the user gets. Callers pass the outcome; this only handles the
     * trial/no-trial split for the generic case.
     *
     * @return string
     */
    public static function cta_label() {
        if (self::has_license()) {
            return __('Activate your license', 'marqueex');
        }

        return self::is_trial_available()
            ? __('Try Pro free', 'marqueex')
            : __('See what Pro adds', 'marqueex');
    }

    /**
     * Reassurance line shown under the call to action.
     *
     * @return string
     */
    public static function cta_meta() {
        if (self::has_license()) {
            return __('You already have a license — activate MarqueeX Pro to switch this on.', 'marqueex');
        }

        return self::is_trial_available()
            ? __('Free trial — no card required', 'marqueex')
            : __('From $59.99 · lifetime · 30-day refund', 'marqueex');
    }

    /**
     * Whether upsell markup should be rendered at all.
     *
     * @return bool
     */
    public static function should_show() {
        // Shown whenever Pro features are locked — which covers two different
        // states. Someone with no license needs to be told what Pro adds;
        // someone who has already paid but not activated needs to be told to
        // activate, not sold to again. has_license() switches the copy; both
        // still need a card, or a locked control just fails silently.
        return !MarqueeX::is_premium_active();
    }

    /**
     * Whether Freemius reports that this site already has a paid license.
     *
     * Distinct from MarqueeX::is_premium_active(), which additionally requires
     * the Pro plugin to be loaded. A site can have paid and not yet activated.
     *
     * @return bool
     */
    public static function has_license() {
        static $has = null;
        if ($has !== null) {
            return $has;
        }

        $has = false;

        if (function_exists('\\Wpxero\\Marqueex\\marqueex_fs')) {
            $fs = \Wpxero\Marqueex\marqueex_fs();
            if ($fs) {
                try {
                    if (method_exists($fs, 'has_active_valid_license') && $fs->has_active_valid_license()) {
                        $has = true;
                    } elseif (method_exists($fs, 'is_paying') && $fs->is_paying()) {
                        $has = true;
                    }
                } catch (\Exception $e) {
                    $has = false;
                }
            }
        }

        return $has;
    }

    /**
     * Markup for a locked Pro feature.
     *
     * One card style used everywhere — Elementor control panels and anywhere
     * else a locked feature needs describing.
     *
     * @param array $args {
     *     @type string   $title       Feature name.
     *     @type string   $description What the user gets, phrased as an outcome.
     *     @type string[] $items       Optional list of the specific options unlocked.
     *     @type string   $cta         Optional CTA label override.
     *     @type string   $source      Surface slug for attribution.
     * }
     * @return string
     */
    public static function locked_card(array $args) {
        $args = wp_parse_args($args, [
            'title'       => '',
            'description' => '',
            'items'       => [],
            'cta'         => '',
            'source'      => '',
        ]);

        // A caller's outcome-named CTA ("Unlock these animations") only makes
        // sense as a sales line. Once a license exists the instruction is
        // "activate", so the shared label wins.
        $cta = (!self::has_license() && $args['cta'] !== '') ? $args['cta'] : self::cta_label();

        ob_start();
        ?>
        <div class="marqueex-upsell">
            <div class="marqueex-upsell__head">
                <i class="eicon-lock" aria-hidden="true"></i>
                <span class="marqueex-upsell__title"><?php echo esc_html($args['title']); ?></span>
                <span class="marqueex-upsell__badge"><?php esc_html_e('PRO', 'marqueex'); ?></span>
            </div>

            <?php if ($args['description']) : ?>
                <p class="marqueex-upsell__desc"><?php echo esc_html($args['description']); ?></p>
            <?php endif; ?>

            <?php if (!empty($args['items'])) : ?>
                <ul class="marqueex-upsell__items">
                    <?php foreach ($args['items'] as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="marqueex-upsell__cta"
               href="<?php echo esc_url(self::url($args['source'])); ?>"
               target="_blank"
               rel="noopener noreferrer"><?php echo esc_html($cta); ?></a>

            <span class="marqueex-upsell__meta"><?php echo esc_html(self::cta_meta()); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add a locked-feature card to an Elementor widget as a RAW_HTML control.
     *
     * @param \Elementor\Widget_Base|\Elementor\Controls_Stack $element
     * @param string $control_id
     * @param array  $args See locked_card().
     * @return void
     */
    public static function add_elementor_notice($element, $control_id, array $args) {
        if (!self::should_show()) {
            return;
        }

        $element->add_control(
            $control_id,
            [
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => self::locked_card($args),
                'content_classes' => 'marqueex-upsell-wrap',
            ]
        );
    }

    /**
     * Merge Pro options into a select control's option list, marking the locked
     * ones so free users can see they exist.
     *
     * Elementor has no per-option disabled state, so a locked option is labelled
     * with a padlock and reverts on select (handled in elementor-editor.js). The
     * render side already falls back to the free default, so a stored Pro value
     * on an unlicensed site is harmless.
     *
     * @param array $free_options Options available to everyone.
     * @param array $pro_options  Options that require a license.
     * @return array
     */
    public static function merge_select_options(array $free_options, array $pro_options) {
        if (!self::should_show()) {
            return array_merge($free_options, $pro_options);
        }

        $locked = [];
        foreach ($pro_options as $key => $label) {
            /* translators: %s: name of a Pro-only option in a dropdown. */
            $locked[$key] = sprintf(__('%s (Pro)', 'marqueex'), $label);
        }

        return array_merge($free_options, $locked);
    }

    /**
     * Option keys that require a license, for the editor script to mark up.
     *
     * @param array $pro_options
     * @return string[]
     */
    public static function locked_option_keys(array $pro_options) {
        return self::should_show() ? array_keys($pro_options) : [];
    }
}
