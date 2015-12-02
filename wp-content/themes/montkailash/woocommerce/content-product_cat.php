<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>
<div class="col-md-3">


    <a class="hover" href="<?php echo get_term_link($category->slug, 'product_cat'); ?>">

        <h3><?php echo $category->name; ?></h3>

        <?php
        do_action('woocommerce_after_subcategory_title', $category);
        ?>

        <?php
        $thumbnail_id = get_woocommerce_term_meta($category->term_id, 'thumbnail_id', true);

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
            echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($category->name) . '" width="800" height="400" class="img-responsive img-rounded mg-t-b"/>';
        }

        echo '<p>' . $category->description . '</p>';
        ?>

    </a>

</div>

