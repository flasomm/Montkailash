<?php if (sizeof(WC()->cart->get_cart()) > 0): ?>
    <a href="<?php echo WC()->cart->get_cart_url(); ?>">
        <h4>
            <small><span class="glyphicon glyphicon-shopping-cart"></span></small>
            Panier: (<?php echo WC()->cart->cart_contents_count ?>)
        </h4>
    </a>
    <ul style="visibility: visible; display: none;" id="cart-details">
        <?php foreach (WC()->cart->cart_contents as $key => $item): ?>
            <li class="wps-clearfix" id="wps_min_cart_product_<?php echo $item['data']->id; ?>">
                <a href="<?php echo get_permalink($item['data']->id) ?>">
                    <?php echo $item['data']->post->post_title ?>
                </a>

                <div class="pull-right">
                    <?php echo $item['quantity'] ?> x <?php echo $item['data']->price; ?>€
                    <?php echo apply_filters('woocommerce_cart_item_remove_link', sprintf('<a href="%s" class="wps_mini_cart_delete_product" title="%s"><small><span class="glyphicon glyphicon-trash"></span></small></a>', esc_url(WC()->cart->get_remove_url($key)), __('Remove this item', 'woocommerce')), $key); ?>
                </div>
            </li>
        <?php endforeach; ?>
        <li class="clearfix">
            <div class="pull-left">
                Total:
            </div>
            <div class="pull-right">
                <strong><?php echo WC()->cart->get_cart_total() ?>
            </div>
        </li>
        <li class="clearfix">
            <a href="<?php echo WC()->cart->get_cart_url(); ?>" class="pull-right"><strong>Commander</strong></a>
        </li>
    </ul>
<?php endif; ?>