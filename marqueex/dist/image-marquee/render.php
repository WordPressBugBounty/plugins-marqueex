<?php

/**
 * Frontend render for the marqueex/image-marquee block.
 *
 * Dynamic block: renders custom images or featured images from a live post
 * query into the shared marquee markup. In scope: $attributes, $content, $block.
 *
 * @var array $attributes
 */

use Wpxero\Marqueex\Integrations\Gutenberg\Render\PostSource;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Block render templates are included in a function scope by WordPress; these are template locals, not globals.

$block_id     = $attributes['blockId'] ?? '';
$content_type = ($attributes['contentType'] ?? 'custom') === 'posts' ? 'posts' : 'custom';
$options      = $attributes['options'] ?? [];
$items        = $attributes['items'] ?? [];
$query        = $attributes['query'] ?? [];

$speed        = max(10, min(100, intval($options['speed'] ?? 30)));
$item_spacing = isset($options['itemSpacing']) ? floatval($options['itemSpacing']) : 2;
$img_width    = isset($options['imageWidth']) ? intval($options['imageWidth']) : 150;
$img_height   = isset($options['imageHeight']) ? intval($options['imageHeight']) : 150;
$object_fit   = in_array($options['objectFit'] ?? 'cover', ['fill', 'contain', 'cover', 'none', 'scale-down'], true)
    ? $options['objectFit']
    : 'cover';
$is_vertical  = !empty($options['vertical']);

/**
 * Render one image item, optionally wrapped in a link.
 */
$render_item = static function ($url, $alt, $link, $is_external = false, $nofollow = false) {
    if (empty($url)) {
        return '';
    }
    $img = '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="marqueex-marquee-image" loading="lazy" />';
    $html = '<div class="marqueex-image-item">';
    if (!empty($link)) {
        $rel = [];
        $target = '';
        if ($is_external) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener noreferrer';
        }
        if ($nofollow) {
            $rel[] = 'nofollow';
        }
        $rel_attr = !empty($rel) ? ' rel="' . esc_attr(implode(' ', $rel)) . '"' : '';
        $html .= '<a href="' . esc_url($link) . '"' . $target . $rel_attr . ' class="marqueex-image-link" aria-label="' . esc_attr($alt) . '">' . $img . '</a>';
    } else {
        $html .= $img;
    }
    $html .= '</div>';
    return $html;
};

$track_html = '';

if ($content_type === 'posts') {
    $posts = PostSource::get_instance()->get_posts(is_array($query) ? $query : []);
    if (!empty($posts)) {
        $track_html .= '<div class="marqueex-marquee-wrapper">';
        foreach ($posts as $post) {
            $url = get_the_post_thumbnail_url($post, 'medium_large');
            if (!$url) {
                continue; // Skip posts without a featured image.
            }
            $title = get_the_title($post);
            $link  = PostSource::get_instance()->url($post);
            $track_html .= $render_item($url, $title, $link);
        }
        $track_html .= '</div>';
    }
} else {
    $items = PostSource::get_instance()->pad_items(is_array($items) ? $items : []);
    if (!empty($items)) {
        $track_html .= '<div class="marqueex-marquee-wrapper">';
        foreach ($items as $item) {
            $url = $item['image']['url'] ?? '';
            if ($url === '') {
                continue;
            }
            $alt         = $item['alt'] ?? ($item['image']['alt'] ?? '');
            $link        = $item['url'] ?? '';
            $is_external = !empty($item['isExternal']);
            $nofollow    = !empty($item['nofollow']);
            $track_html .= $render_item($url, $alt, $link, $is_external, $nofollow);
        }
        $track_html .= '</div>';
    }
}

if ($track_html === '') {
    return '';
}

$classes = array_filter([
    'marqueex-marquee-block',
    'marqueex-image-marquee',
    $is_vertical ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
    !empty($options['pauseOnHover']) ? 'is-paused' : '',
    !empty($options['reverse']) ? 'is-reversed' : '',
]);

$data_settings = ['marquee_speed' => $speed];
if (!empty($options['wheelControl'])) {
    $data_settings['wheelControl'] = $options['wheelControl'];
}
if (!empty($options['dragControl'])) {
    $data_settings['dragControl'] = $options['dragControl'];
}

$inline_style = sprintf(
    '--marqueex-marquee-item-spacing:%srem;--marqueex-image-width:%dpx;--marqueex-image-height:%dpx;--marqueex-image-fit:%s;',
    $item_spacing,
    $img_width,
    $img_height,
    $object_fit
);

$wrapper_args = [
    'class'         => implode(' ', $classes),
    'style'         => $inline_style,
    'data-settings' => wp_json_encode($data_settings),
    'role'          => 'region',
    'aria-label'    => __('Image Marquee', 'marqueex'),
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
            <?php echo $track_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
    </div>
</div>
<?php
