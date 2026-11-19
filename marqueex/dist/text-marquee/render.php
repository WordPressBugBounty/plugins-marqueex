<?php

/**
 * Frontend render for the marqueex/text-marquee block.
 *
 * Dynamic block: renders custom items or a live post query into the shared
 * marquee markup. Variables in scope: $attributes, $content, $block.
 *
 * @var array $attributes
 */

use Wpxero\Marqueex\Integrations\Gutenberg\Render\PostSource;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Block render templates are included in a function scope by WordPress; these are template locals, not globals.

$block_id      = $attributes['blockId'] ?? '';
$content_type  = ($attributes['contentType'] ?? 'custom') === 'posts' ? 'posts' : 'custom';
$options       = $attributes['options'] ?? [];
$items         = $attributes['items'] ?? [];
$query         = $attributes['query'] ?? [];

$speed         = max(10, min(100, intval($options['speed'] ?? 30)));
$item_spacing  = isset($options['itemSpacing']) ? floatval($options['itemSpacing']) : 2;
$is_vertical   = !empty($options['vertical']);

// Build the track content once and reuse it for the aria-hidden clone.
$track_html = '';

if ($content_type === 'posts') {
    $posts = PostSource::get_instance()->get_posts(is_array($query) ? $query : []);
    if (!empty($posts)) {
        $track_html .= '<div class="marqueex-marquee-wrapper">';
        foreach ($posts as $post) {
            $title = get_the_title($post);
            if ($title === '') {
                continue;
            }
            $url = PostSource::get_instance()->url($post);
            $track_html .= '<div class="marqueex-news-item"><span class="marqueex-news-text">';
            if (!empty($url)) {
                $track_html .= '<a href="' . esc_url($url) . '" class="marqueex-title-link" aria-label="' . esc_attr($title) . '">' . esc_html($title) . '</a>';
            } else {
                $track_html .= esc_html($title);
            }
            $track_html .= '</span></div>';
        }
        $track_html .= '</div>';
    }
} else {
    $items = PostSource::get_instance()->pad_items(is_array($items) ? $items : []);
    if (!empty($items)) {
        $track_html .= '<div class="marqueex-marquee-wrapper">';
        foreach ($items as $item) {
            $text = $item['text'] ?? '';
            if ($text === '') {
                continue;
            }
            $url         = $item['url'] ?? '';
            $is_external = !empty($item['isExternal']);
            $nofollow    = !empty($item['nofollow']);

            $track_html .= '<div class="marqueex-news-item"><span class="marqueex-news-text">';
            if (!empty($url)) {
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
                $track_html .= '<a href="' . esc_url($url) . '"' . $target . $rel_attr . ' class="marqueex-title-link" aria-label="' . esc_attr($text) . '">' . esc_html($text) . '</a>';
            } else {
                $track_html .= esc_html($text);
            }
            $track_html .= '</span></div>';
        }
        $track_html .= '</div>';
    }
}

// Nothing to render — bail rather than emit an empty animated box.
if ($track_html === '') {
    return '';
}

$classes = array_filter([
    'marqueex-marquee-block',
    'marqueex-text-marquee',
    $is_vertical ? 'marqueex-marquee-vertical' : 'marqueex-marquee-horizontal',
    !empty($options['pauseOnHover']) ? 'is-paused' : '',
    !empty($options['reverse']) ? 'is-reversed' : '',
]);

$data_settings = ['marquee_speed' => $speed];
// Pro interaction settings — the view script only activates them when licensed.
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
    'aria-label'    => __('Text Marquee', 'marqueex'),
];
if ($block_id) {
    // Matches the id the Design-tab CSS targets (#marqueex-<blockId>).
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
