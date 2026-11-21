<?php

/**
 * Frontend render for the marqueex/news-ticker block.
 *
 * Dynamic block: server renders custom items or a live post query into the
 * shared marquee markup, with an optional fixed label badge at the left edge.
 *
 * @var array $attributes
 */

use Wpxero\Marqueex\Integrations\Gutenberg\Render\PostSource;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Block render templates are included in a function scope by WordPress; these are template locals, not globals.

$block_id     = $attributes['blockId'] ?? '';
$content_type = ($attributes['contentType'] ?? 'posts') === 'custom' ? 'custom' : 'posts';
$options      = $attributes['options'] ?? [];
$items        = $attributes['items'] ?? [];
$query        = $attributes['query'] ?? [];
$label_attr   = $attributes['label'] ?? [];
$separator    = $attributes['separator'] ?? [];

$speed        = max(10, min(100, intval($options['speed'] ?? 30)));
$item_spacing = isset($options['itemSpacing']) ? floatval($options['itemSpacing']) : 2;

// ── Separator markup ────────────────────────────────────────────────────────
$sep_type = $separator['type'] ?? 'text';
$sep_text = ($sep_type === 'text') ? ($separator['text'] ?? '•') : '';

$separator_html = '';
if ($sep_type === 'text' && $sep_text !== '') {
    $separator_html = '<span class="marqueex-separator" aria-hidden="true">'
        . esc_html($sep_text)
        . '</span>';
}

// ── Track content ────────────────────────────────────────────────────────────
$track_html = '';

if ($content_type === 'posts') {
    $open_in_new_tab = !empty($query['openInNewTab']);
    $posts = PostSource::get_instance()->get_posts(is_array($query) ? $query : []);
    if (!empty($posts)) {
        $track_html .= '<div class="marqueex-marquee-wrapper">';
        foreach ($posts as $index => $post) {
            $title = get_the_title($post);
            if ($title === '') {
                continue;
            }
            $url    = PostSource::get_instance()->url($post);
            $target = '';
            $rel    = $open_in_new_tab ? ' rel="noopener noreferrer"' : '';
            if ($open_in_new_tab) {
                $target = ' target="_blank"';
            }

            $track_html .= '<div class="marqueex-news-item"><span class="marqueex-news-text">';
            if (!empty($url)) {
                $track_html .= '<a href="' . esc_url($url) . '"' . $target . $rel
                    . ' class="marqueex-title-link" aria-label="' . esc_attr($title) . '">'
                    . esc_html($title) . '</a>';
            } else {
                $track_html .= esc_html($title);
            }
            $track_html .= '</span></div>';
            $track_html .= $separator_html;
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
                $rel_parts = [];
                $target    = '';
                if ($is_external) {
                    $target      = ' target="_blank"';
                    $rel_parts[] = 'noopener noreferrer';
                }
                if ($nofollow) {
                    $rel_parts[] = 'nofollow';
                }
                $rel_attr = !empty($rel_parts) ? ' rel="' . esc_attr(implode(' ', $rel_parts)) . '"' : '';
                $track_html .= '<a href="' . esc_url($url) . '"' . $target . $rel_attr
                    . ' class="marqueex-title-link" aria-label="' . esc_attr($text) . '">'
                    . esc_html($text) . '</a>';
            } else {
                $track_html .= esc_html($text);
            }
            $track_html .= '</span></div>';
            $track_html .= $separator_html;
        }
        $track_html .= '</div>';
    }
}

if ($track_html === '') {
    return '';
}

// ── Label ────────────────────────────────────────────────────────────────────
$show_label  = !empty($label_attr['show']);
$label_text  = $show_label ? sanitize_text_field($label_attr['text'] ?? 'BREAKING') : '';
$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span'];
$label_tag   = in_array($label_attr['tag'] ?? 'span', $allowed_tags, true)
    ? $label_attr['tag']
    : 'span';

// ── Wrapper classes / attributes ─────────────────────────────────────────────
$classes = array_filter([
    'marqueex-marquee-block',
    'marqueex-news-ticker',
    $show_label ? 'has-label' : '',
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

$wrapper_args = [
    'class'         => implode(' ', $classes),
    'style'         => '--marqueex-marquee-item-spacing:' . $item_spacing . 'rem;',
    'data-settings' => wp_json_encode($data_settings),
    'role'          => 'region',
    'aria-label'    => __('News Ticker', 'marqueex'),
];
if ($block_id) {
    $wrapper_args['id'] = 'marqueex-' . $block_id;
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

    <?php if ($show_label && $label_text) : ?>
        <?php if (!empty($options['reverse'])) : ?>
            <?php /* label rendered after track for reversed tickers */ ?>
        <?php endif; ?>
        <div class="marqueex-ticker-label">
            <<?php echo esc_attr($label_tag); ?> class="marqueex-label-heading">
                <?php echo esc_html($label_text); ?>
            </<?php echo esc_attr($label_tag); ?>>
        </div>
    <?php endif; ?>

    <div class="marqueex-marquee-track-wrapper">
        <div class="marqueex-marquee-track">
            <?php echo $track_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
        <div aria-hidden="true" class="marqueex-marquee-track">
            <?php echo \Wpxero\Marqueex\Utils\Markup::unfocusable($track_html); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
    </div>

    <?php if ($show_label && $label_text && !empty($options['reverse'])) : ?>
        <div class="marqueex-ticker-label">
            <<?php echo esc_attr($label_tag); ?> class="marqueex-label-heading">
                <?php echo esc_html($label_text); ?>
            </<?php echo esc_attr($label_tag); ?>>
        </div>
    <?php endif; ?>

</div>
<?php
