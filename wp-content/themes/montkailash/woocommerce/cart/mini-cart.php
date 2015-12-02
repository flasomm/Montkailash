<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>

<section id="shopping-cart" class="col-md-3 wps-mini-cart-body cart-contents">
    <?php get_template_part('woocommerce/cart/cart', 'content'); ?>
</section>