<?php

namespace Wpxero\Marqueex\Admin;

use Wpxero\Marqueex\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A single, usage-triggered request for a WordPress.org review.
 *
 * Deliberate constraints, because review prompts are the easiest thing in a
 * plugin to get wrong:
 *
 *  - Triggered by USE, not by install date. The clock starts the first time a
 *    marquee is actually saved into content, so nobody is asked to vouch for
 *    something they have not used.
 *  - Shown only after a week of that, and only on MarqueeX's own screens and
 *    the plugins list — never across the whole admin.
 *  - Three honest choices, one of which ends it permanently. "Maybe later"
 *    means a month, not a day.
 *  - No second prompt, ever, once dismissed or marked done.
 */
class ReviewRequest {
    use Singleton;

    const OPTION = 'marqueex_review_state';

    /** Days of use before asking. */
    const DELAY_DAYS = 7;

    /** Days to wait after "Maybe later". */
    const SNOOZE_DAYS = 30;

    const REVIEW_URL = 'https://wordpress.org/support/plugin/marqueex/reviews/#new-post';

    private function __construct() {
        add_action('save_post', [$this, 'record_first_use'], 10, 2);
        add_action('admin_notices', [$this, 'maybe_render_notice']);
        add_action('admin_init', [$this, 'handle_action']);
    }

    /**
     * Note the first time a marquee is saved into a post.
     *
     * @param int      $post_id
     * @param \WP_Post $post
     */
    public function record_first_use($post_id, $post) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $state = $this->get_state();
        if (!empty($state['first_use'])) {
            return; // Already recorded — nothing to do on every later save.
        }

        if (!$this->post_uses_marqueex($post)) {
            return;
        }

        $state['first_use'] = time();
        $this->save_state($state);
    }

    /**
     * Whether a post contains a MarqueeX block or Elementor widget.
     *
     * @param \WP_Post $post
     * @return bool
     */
    private function post_uses_marqueex($post) {
        if (!is_object($post) || empty($post->post_content)) {
            // Elementor keeps its layout in post meta rather than post_content.
            return $this->elementor_data_uses_marqueex($post);
        }

        if (false !== strpos($post->post_content, '<!-- wp:marqueex/')) {
            return true;
        }

        if (false !== strpos($post->post_content, '[marqueex_')) {
            return true;
        }

        return $this->elementor_data_uses_marqueex($post);
    }

    /**
     * @param \WP_Post $post
     * @return bool
     */
    private function elementor_data_uses_marqueex($post) {
        if (!is_object($post) || empty($post->ID)) {
            return false;
        }

        $data = get_post_meta($post->ID, '_elementor_data', true);
        if (!is_string($data) || '' === $data) {
            return false;
        }

        return false !== strpos($data, 'marqueex-');
    }

    /**
     * Handle the notice's three buttons.
     */
    public function handle_action() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['marqueex_review'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = sanitize_key(wp_unslash($_GET['marqueex_review']));

        check_admin_referer('marqueex_review_' . $action);

        $state = $this->get_state();

        if ('later' === $action) {
            $state['snooze_until'] = time() + (self::SNOOZE_DAYS * DAY_IN_SECONDS);
        } else {
            // "done" and "dismiss" both end it for good.
            $state['dismissed'] = true;
        }

        $this->save_state($state);

        wp_safe_redirect(remove_query_arg(['marqueex_review', '_wpnonce']));
        exit;
    }

    /**
     * Render the notice when it has been earned.
     */
    public function maybe_render_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!$this->should_ask()) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // MarqueeX's own screens and the plugins list only.
        $is_own_screen = false !== strpos($screen->id, 'marqueex');
        if (!$is_own_screen && 'plugins' !== $screen->id) {
            return;
        }
        ?>
        <?php // Not `is-dismissible`: core's X only hides the notice for the current
        // page load, which would look like a choice that did not stick. The three
        // links below are the real, persisted choices. ?>
        <div class="notice notice-info marqueex-review-notice">
            <p>
                <strong><?php esc_html_e('Enjoying MarqueeX?', 'marqueex'); ?></strong>
                <?php esc_html_e('You have had a marquee running for a week now. A short review on WordPress.org helps other people find the plugin — and tells us what to build next.', 'marqueex'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url(self::REVIEW_URL); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Leave a review', 'marqueex'); ?>
                </a>
                <a href="<?php echo esc_url($this->action_url('later')); ?>" class="button">
                    <?php esc_html_e('Maybe later', 'marqueex'); ?>
                </a>
                <a href="<?php echo esc_url($this->action_url('done')); ?>" class="button-link" style="margin-left:8px;">
                    <?php esc_html_e('Don\'t ask again', 'marqueex'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Whether the review notice is due right now. Public so other notices
     * (Admin\ProNotice) can stand aside instead of stacking on the same screen.
     *
     * @return bool
     */
    public function should_ask() {
        $state = $this->get_state();

        if (!empty($state['dismissed'])) {
            return false;
        }

        if (empty($state['first_use'])) {
            return false;
        }

        if (!empty($state['snooze_until']) && time() < $state['snooze_until']) {
            return false;
        }

        return (time() - (int) $state['first_use']) >= (self::DELAY_DAYS * DAY_IN_SECONDS);
    }

    /**
     * Unix time a marquee was first saved into content, or 0 if never.
     *
     * @return int
     */
    public function first_use() {
        $state = $this->get_state();
        return empty($state['first_use']) ? 0 : (int) $state['first_use'];
    }

    /**
     * @param string $action
     * @return string
     */
    private function action_url($action) {
        return wp_nonce_url(
            add_query_arg('marqueex_review', $action),
            'marqueex_review_' . $action
        );
    }

    /**
     * @return array
     */
    private function get_state() {
        $state = get_option(self::OPTION, []);
        return is_array($state) ? $state : [];
    }

    /**
     * @param array $state
     */
    private function save_state(array $state) {
        update_option(self::OPTION, $state, false);
    }
}
