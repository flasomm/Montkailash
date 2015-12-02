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

get_header();

global $post;
$terms        = get_the_terms($post->ID, 'product_cat');
$thumbnail_id = get_woocommerce_term_meta($terms[0]->term_id, 'thumbnail_id', true);

if ($thumbnail_id) {
    $image = wp_get_attachment_image_src($thumbnail_id, 'feature-image');
    $image = $image[0];
} else {
    $image = wc_placeholder_img_src();
}

if ($image) {
    // Prevent esc_url from breaking spaces in urls for image embeds
    // Ref: http://core.trac.wordpress.org/ticket/23605
    $image = str_replace(' ', '%20', $image);
}
?>

<?php get_template_part('breadcrumb', 'breadcrumb'); ?>

<div class="container pd-b">

    <div class="row">
        <div class="col-md-6">
            <h1><?php single_term_title(); ?></h1>

            <h2 class="p"><?php echo $terms[0]->description; ?></h2>

            <p>
                <strong>
                    <span class="glyphicon glyphicon-question-sign text-pink"></span> Commandez votre soin en ligne,
                    et gagnez du temps pour vous détendre :
                </strong> priorité sur les réservations, bons plans exclusifs, support spa manager.
            </p>
        </div>
        <div class="col-md-4">
            <img src="<?php echo esc_url($image); ?>" class="img-responsive img-rounded mg-t-b"
                 alt="<?php echo esc_attr($terms[0]->name); ?>" height="400" width="800">
        </div>
    </div>

    <?php if (have_posts()) : ?>

        <div class="row" id="categories-list">
            <div class="col-md-12">
                <div class="row">
                    <?php woocommerce_product_subcategories(); ?>

                    <div id="shop-list" class="row">
                        <div class="col-md-12">
                            <div class="row">

                            </div>
                        </div>
                    </div>


                    <?php while (have_posts()) : the_post(); ?>

                        <?php wc_get_template_part('content', 'product'); ?>

                    <?php endwhile; // end of the loop. ?>
                </div>
            </div>
        </div>

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
