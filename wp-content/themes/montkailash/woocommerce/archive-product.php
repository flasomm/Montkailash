<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); ?>


<?php get_template_part('breadcrumb', 'breadcrumb'); ?>

<div class="container pd-b">
    <?php do_action('woocommerce_archive_description'); ?>

    <?php if (have_posts()) : ?>

        <?php do_action('woocommerce_before_shop_loop'); ?>



        <div class="row" id="categories-list">
            <div class="col-md-12">
                <div class="row">
                    <?php woocommerce_product_subcategories(); ?>

                    <?php while (have_posts()) : the_post(); ?>

                        <?php wc_get_template_part('content', 'product_cat'); ?>

                    <?php endwhile; // end of the loop. ?>
                </div>
            </div>
        </div>

        <?php do_action('woocommerce_after_shop_loop'); ?>

    <?php elseif (!woocommerce_product_subcategories(
        array(
            'before' => woocommerce_product_loop_start(false), 'after' => woocommerce_product_loop_end(false),
        )
    )
    ) : ?>

        <?php wc_get_template('loop/no-products-found.php'); ?>

    <?php endif; ?>


</div>

<?php get_template_part('bandeor', 'bandeor'); ?>
<?php get_footer(); ?>
