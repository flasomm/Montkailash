<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();
$product  = wc_get_product($post->ID);
$duree    = get_the_terms($product->id, 'pa_duree');
$discount = get_post_meta($product->id, "_bulkdiscount_discount_flat_1", true);

$image_title = esc_attr(get_the_title(get_post_thumbnail_id()));
$image_link  = wp_get_attachment_url(get_post_thumbnail_id());
$image       = wp_get_attachment_image(
    get_post_thumbnail_id(), 'full', false, array(
                               'alt'   => $image_title,
                               'class' => 'img-responsive img-rounded mg-t',
                           )
);

?>

<div class="container pd-b">

    <div class="row">
        <div class="col-md-6">
            <h1><?php the_title(); ?></h1>

            <h2 class="p"><?php echo $product->post->post_excerpt ?></h2>

            <p><?php echo $product->post->post_content ?>
                <br>
                <strong>Durée:</strong>
                <small> <?php echo $duree[0]->name; ?></small>
            </p>
            <p>
                <strong>
                    <span class="glyphicon glyphicon-info-sign text-pink"></span>
                    Commandez votre soin en ligne, et gagnez du temps pour vous détendre :
                </strong> priorité sur les réservations, bons plans exclusifs, support spa manager.
            </p>

            <div class="pull-right">
                <div class="btn-group mg-t-b">
                    <button type="button" class="btn btn-pink" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-calendar"></span> Réserver ce soin : <strong>à partir
                            de <?php echo $product->price; ?> €</strong></button>
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
        <div class="col-md-6">
            <?php echo $image; ?>
        </div>
    </div>
</div>
