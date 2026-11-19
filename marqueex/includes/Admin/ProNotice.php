<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Core\Upsell;
use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A single, usage-triggered introduction to MarqueeX Pro.
 *
 * Built to the same rules as ReviewRequest, and to WordPress.org guideline 11
 * (no admin hijacking, upgrade prompts must be dismissible and sparing):
 *
 *  - Triggered by USE. The clock is ReviewRequest's first_use — the first time
 *    a marquee was saved into content — so it only reaches people who already
 *    have a marquee live and can picture what Pro would add to it.
 *  - Shown after two weeks of that, only on MarqueeX's own screens and the
 *    plugins list, only to administrators, never while Pro is unlocked.
 *  - Never on the same page load as the review request: that one goes first.
 *  - "Not now" snoozes for 60 days; "Don't show again" ends it for good, and
 *    after one snooze the next dismissal is permanent either way.
 */
class ProNotice {
    use Singleton;

    const OPTION = 'marqueex_pro_notice_state';

    /** Days after first use before introducing Pro. */
    const DELAY_DAYS = 14;

    /** Days to wait after "Not now". */
    const SNOOZE_DAYS = 60;

    private function __construct() {
        add_action('admin_notices', [$this, 'maybe_render_notice']);
        add_action('admin_init', [$this, 'handle_action']);
    }

    /**
     * Persist the notice's "Not now" / "Don't show again" choices.
     */
    public function handle_action() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked below.
        if (!isset($_GET['marqueex_pro_notice'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked below.
        $action = sanitize_key(wp_unslash($_GET['marqueex_pro_notice']));
        if (!in_array($action, ['later', 'dismiss'], true)) {
            return;
        }

        check_admin_referer('marqueex_pro_notice_' . $action);

        $state = $this->get_state();

        if ('later' === $action && empty($state['snoozed_once'])) {
            $state['snooze_until'] = time() + (self::SNOOZE_DAYS * DAY_IN_SECONDS);
            $state['snoozed_once'] = true;
        } else {
            $state['dismissed'] = true;
        }

        update_option(self::OPTION, $state, false);

        wp_safe_redirect(remove_query_arg(['marqueex_pro_notice', '_wpnonce']));
        exit;
    }

    /**
     * Render the notice when it is due.
     */
    public function maybe_render_notice() {
        if (!current_user_can('manage_options') || !$this->should_show()) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $is_own_screen = false !== strpos($screen->id, 'marqueex');
        if (!$is_own_screen && 'plugins' !== $screen->id) {
            return;
        }

        $has_license = Upsell::has_license();
        $cta_url     = Upsell::url('admin-notice');
        $compare_url = admin_url('admin.php?page=marqueex&sub_page=pro');
        // In-admin Freemius screens open in place; only an external fallback
        // gets a new tab.
        $external    = 0 !== strpos($cta_url, admin_url());
        ?>
        <?php // Not `is-dismissible`: core's X only hides it for one page load.
        // The links below are the real, persisted choices. ?>
        <div class="notice notice-info marqueex-pro-notice">
            <p>
                <?php if ($has_license) : ?>
                    <strong><?php esc_html_e('Your MarqueeX Pro license is not active yet.', 'marqueex'); ?></strong>
                    <?php esc_html_e('Install and activate MarqueeX Pro to switch on mouse wheel and drag-to-scroll, hand-picked posts and the extra animations on this site.', 'marqueex'); ?>
                <?php else : ?>
                    <strong><?php esc_html_e('Your marquees have been live for two weeks.', 'marqueex'); ?></strong>
                    <?php esc_html_e('Want visitors to play with them? MarqueeX Pro lets people spin the mouse wheel to speed up or reverse a marquee, or drag to scrub through it — plus hand-picked posts and extra heading animations.', 'marqueex'); ?>
                <?php endif; ?>
            </p>
            <p>
                <a href="<?php echo esc_url($cta_url); ?>" class="button button-primary"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html(Upsell::cta_label()); ?>
                </a>
                <?php if (!$has_license) : ?>
                    <a href="<?php echo esc_url($compare_url); ?>" class="button">
                        <?php esc_html_e('Compare Free vs Pro', 'marqueex'); ?>
                    </a>
                <?php endif; ?>
                <?php $state = $this->get_state(); ?>
                <?php if (empty($state['snoozed_once'])) : ?>
                    <a href="<?php echo esc_url($this->action_url('later')); ?>" class="button-link" style="margin-left:8px;">
                        <?php esc_html_e('Not now', 'marqueex'); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo esc_url($this->action_url('dismiss')); ?>" class="button-link" style="margin-left:8px;">
                    <?php esc_html_e('Don\'t show again', 'marqueex'); ?>
                </a>
                <span class="description" style="margin-left:8px;"><?php echo esc_html(Upsell::cta_meta()); ?></span>
            </p>
        </div>
        <?php
    }

    /**
     * @return bool
     */
    private function should_show() {
        if (!Upsell::should_show()) {
            return false;
        }

        $state = $this->get_state();
        if (!empty($state['dismissed'])) {
            return false;
        }

        if (!empty($state['snooze_until']) && time() < (int) $state['snooze_until']) {
            return false;
        }

        $review = ReviewRequest::get_instance();

        // One MarqueeX notice at a time; the review request goes first.
        if ($review->should_ask()) {
            return false;
        }

        $first_use = $review->first_use();
        if (!$first_use) {
            return false;
        }

        return (time() - $first_use) >= (self::DELAY_DAYS * DAY_IN_SECONDS);
    }

    /**
     * @param string $action
     * @return string
     */
    private function action_url($action) {
        return wp_nonce_url(
            add_query_arg('marqueex_pro_notice', $action),
            'marqueex_pro_notice_' . $action
        );
    }

    /**
     * @return array
     */
    private function get_state() {
        $state = get_option(self::OPTION, []);
        return is_array($state) ? $state : [];
    }
}
