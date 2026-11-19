<?php
/**
 * MarqueeX uninstall — remove plugin-owned options and transients.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('marqueex_settings');
delete_option('marqueex_review_state');
delete_option('marqueex_pro_notice_state');
delete_metadata('user', 0, 'marqueex_recommendations_dismissed', '', true);

// Per-user onboarding dismissal flags.
delete_metadata('user', 0, 'marqueex_onboarding_dismissed', '', true);

delete_transient('marqueex_builder_cache');
delete_transient('marqueex_usage_counts');
delete_transient('_marqueex_welcome_screen_activation_redirect');

global $wpdb;
if (isset($wpdb)) {
    $marqueex_value_like   = $wpdb->esc_like('_transient_marqueex_registerable_blocks_') . '%';
    $marqueex_timeout_like = $wpdb->esc_like('_transient_timeout_marqueex_registerable_blocks_') . '%';
    // Direct, prepared bulk delete of our own versioned transients on uninstall —
    // there is no core API for a wildcard transient purge, and object caching is
    // irrelevant during uninstall.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $marqueex_value_like));
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $marqueex_timeout_like));
}
