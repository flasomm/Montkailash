<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

$duree       = get_the_terms($product->id, 'pa_duree');
$image_title = esc_attr(get_the_title(get_post_thumbnail_id()));
$image_link  = wp_get_attachment_url(get_post_thumbnail_id());
$image       = wp_get_attachment_image(
    get_post_thumbnail_id(), 'thumbnail', false, array(
                               'alt'   => $image_title,
                               'class' => 'img-responsive img-rounded',
                           )
);

$discount = get_post_meta($product->id, "_bulkdiscount_discount_flat_1", true);

// Store loop count we're currently on
if (empty($woocommerce_loop['loop'])) {
    $woocommerce_loop['loop'] = 0;
}

// Ensure visibility
if (!$product || !$product->is_visible()) {
    return;
}

// Increase loop count
$woocommerce_loop['loop']++;

?>

<div class="col-md-6 pd-r">
    <div class="row">
        <div class="col-md-12">
            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <h3><?php the_title(); ?></h3>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php echo $image; ?>
            </a>
        </div>
        <div class="col-md-8">
            <p class="resume"><?php echo $product->post->post_excerpt; ?><br><strong>Durée:</strong>
                <small><?php echo $duree[0]->name; ?></small>
            </p>
            <p>
                <a href="<?php the_permalink(); ?>" title="En savoir plus : <?php the_title(); ?>">
                    <strong>
                        <span class="glyphicon glyphicon-info-sign text-pink"></span> Détails :
                    </strong><?php the_title(); ?>
                </a>
            </p>

            <div class="pull-left">
                <div class="btn-group">
                    <button type="button" class="btn btn-pink" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-calendar"></span>
                        Réserver :<strong><?php echo $product->price; ?>€</strong>
                    </button>
                    <button type="button" class="btn btn-pink dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a rel="nofollow"
                               class="add_to_cart_button button product_type_simple product_type_<?php echo $product->product_type ?>"
                               data-product_id="<?php echo $product->id ?>" data-quantity="1"
                               href="#"><?php echo $product->price; ?> € pour 1 personne</a>
                        </li>
                        <li>

                            <a rel="nofollow"
                               class="add_to_cart_button button product_type_simple product_type_<?php echo $product->product_type ?>"
                               data-product_id="<?php echo $product->id ?>" data-quantity="2"
                               href="#"><?php echo ($product->price * 2) - $discount; ?> € pour 2 personnes</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>