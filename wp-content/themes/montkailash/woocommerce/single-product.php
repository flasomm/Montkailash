<?php
/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/woocommerce/single-product.php
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('shop'); ?>


<?php get_template_part('breadcrumb', 'breadcrumb'); ?>

<div id="page-content" class="container pd-b">

    <?php while (have_posts()) : the_post(); ?>

        <?php wc_get_template_part('content', 'single-product'); ?>

    <?php endwhile; // end of the loop. ?>

</div>

<?php get_template_part('bandeor', 'bandeor'); ?>
<?php get_footer('shop'); ?>
