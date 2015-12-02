<?php
/**
 * Shop breadcrumb
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.3.0
 * @see           woocommerce_breadcrumb()
 */

if (!defined('ABSPATH')) {
    exit;
}

if ($breadcrumb) {

    echo $wrap_before;

    foreach ($breadcrumb as $key => $crumb) {

        echo $before;

        if (!empty($crumb[1]) && sizeof($breadcrumb) !== $key + 1) {
            if ($crumb[0] === 'Accueil') {
                $glyph = 'glyphicon-home';
            } else {
                $glyph = 'glyphicon-chevron-right';
            }
            echo '<a itemprop="url" title="' . esc_html($crumb[0]) . '" href="' . esc_url($crumb[1]) . '">';
            echo '<small><span class="glyphicon ' . $glyph . '"></span>';
            echo '</small><span itemprop="title"> ' . esc_html($crumb[0]) . '</span>';
            echo '</a>';

        } else {
            echo '<small><span class="glyphicon glyphicon-chevron-right"></span>';
            echo '</small><span itemprop="title"> ' . esc_html($crumb[0]) . '</span>';
        }

        echo $after;

        if (sizeof($breadcrumb) !== $key + 1) {
            echo $delimiter;
        }

    }

    echo $wrap_after;

}