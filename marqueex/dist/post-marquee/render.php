<?php

/**
 * Frontend render for the marqueex/post-marquee block.
 *
 * Dynamic block: queries posts and renders post cards into the shared marquee
 * track structure (animated by the shared view.js engine).
 *
 * @var array $attributes
 */

use Wpxero\Marqueex\Integrations\Gutenberg\Render\PostSource;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Block render templates are included in a function scope by WordPress; these are template locals, not globals.

$block_id = $attributes['blockId'] ?? '';
$query    = $attributes['query'] ?? [];
$options  = $attributes['options'] ?? [];

$speed         = max(10, min(400, intval($options['speed'] ?? 60)));
$item_spacing  = isset($options['itemSpacing']) ? floatval($options['itemSpacing']) : 1.5;
$show_category = !empty($options['showCategory']);
$show_title    = !isset($options['showTitle']) || !empty($options['showTitle']);
$show_author   = !empty($options['showAuthor']);
$show_date     = !empty($options['showDate']);
$global_link   = !empty($options['globalLink']);
$separator     = sanitize_text_field($options['metaSeparator'] ?? '-');

$placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';

$posts = PostSource::get_instance()->get_posts(is_array($query) ? $query : []);

if (empty($posts)) {
    return '';
}

// ── Build one track's worth of cards ─────────────────────────────────────────
ob_start();
echo '<div class="marqueex-marquee-wrapper">';
foreach ($posts as $post) {
    if (!$post instanceof \WP_Post) {
        continue;
    }
    $post_id   = $post->ID;
    $title     = get_the_title($post_id);
    $permalink = get_permalink($post_id);

    $image     = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
    $image_src = $image ? $image[0] : $placeholder;
    // The placeholder is decorative; the card title already names the post.
    $image_alt = $image ? $title : '';

    $category_html = '';
    if ($show_category) {
        $categories = get_the_category($post_id);
        if (!empty($categories)) {
            $links = [];
            foreach ($categories as $cat) {
                $links[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
            }
            $category_html = implode('', $links);
        }
    }

    $item_tag  = $global_link ? 'a' : 'div';
    $item_open = $global_link ? ' href="' . esc_url($permalink) . '"' : '';
    ?>
    <<?php echo $item_tag; // phpcs:ignore ?> class="marqueex-item"<?php echo $item_open; // phpcs:ignore ?>>
        <div class="marqueex-item-box">
            <?php // The placeholder is a data: URI; plain esc_url() strips that protocol and empties src. ?>
            <img class="marqueex-img" src="<?php echo esc_url($image_src, ['http', 'https', 'data']); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy">
            <div class="marqueex-content">
                <?php if ($show_category && $category_html) : ?>
                    <div class="marqueex-category"><?php echo $category_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                <?php endif; ?>

                <?php if ($show_title) : ?>
                    <div class="marqueex-title-wrap">
                        <h3 class="marqueex-title">
                            <?php if ($global_link) : ?>
                                <span><?php echo esc_html($title); ?></span>
                            <?php else : ?>
                                <a href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></a>
                            <?php endif; ?>
                        </h3>
                    </div>
                <?php endif; ?>

                <?php if ($show_author || $show_date) : ?>
                    <div class="marqueex-meta">
                        <?php if ($show_author) : ?>
                            <div class="marqueex-blog-author">
                                <span class="by"><?php echo esc_html_x('by', 'Frontend', 'marqueex'); ?></span>
                                <span class="marqueex-post-grid-author">
                                    <a href="<?php echo esc_url(get_author_posts_url($post->post_author)); ?>">
                                        <?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($show_date) : ?>
                            <div data-separator="<?php echo esc_attr($separator); ?>">
                                <div class="marqueex-date">
                                    <?php echo esc_html(get_the_date('', $post_id)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </<?php echo $item_tag; // phpcs:ignore ?>>
    <?php
}
echo '</div>';
$track_html = ob_get_clean();

// ── Wrapper classes / attributes ─────────────────────────────────────────────
$classes = array_filter([
    'marqueex-marquee-block',
    'marqueex-post-marquee',
    !empty($options['pauseOnHover']) ? 'is-paused' : '',
    !empty($options['reverse']) ? 'is-reversed' : '',
    !empty($options['vertical']) ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
]);

$data_settings = ['marquee_speed' => $speed];
// Pro interactions: the shared view engine reads these and only activates them
// when a valid license is present (window.MARQUEEX_VIEW.isPremium).
if (!empty($options['wheelControl'])) {
    $data_settings['wheelControl'] = $options['wheelControl'];
}
if (!empty($options['dragControl'])) {
    $data_settings['dragControl'] = $options['dragControl'];
}

$wrapper_args = [
    'class'         => implode(' ', $classes),
    'style'         => '--marqueex-marquee-item-spacing:' . $item_spacing . 'rem;',
    'data-settings' => wp_json_encode($data_settings),
    'role'          => 'region',
    'aria-label'    => __('Post Marquee', 'marqueex'),
];
if ($block_id) {
    $wrapper_args['id'] = 'marqueex-' . $block_id;
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
    <div class="marqueex-marquee-track-wrapper">
        <div class="marqueex-marquee-track">
            <?php echo $track_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
        <div aria-hidden="true" class="marqueex-marquee-track">
            <?php echo \Wpxero\Marqueex\Utils\Markup::unfocusable($track_html); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
    </div>
</div>
<?php
