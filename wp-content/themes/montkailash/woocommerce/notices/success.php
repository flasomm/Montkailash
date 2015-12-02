<?php
/**
 * Show messages
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!$messages) {
    return;
}

if (!is_shop() && !is_product()):

    foreach ($messages as $message) : ?>
        <div class="woocommerce-message"><?php echo wp_kses_post($message); ?></div>
    <?php endforeach;

endif;
?>


