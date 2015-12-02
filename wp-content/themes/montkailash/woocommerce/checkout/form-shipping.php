<?php
/**
 * Checkout shipping information form
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.2.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>
<div class="woocommerce-shipping-fields">
    <?php if (WC()->cart->needs_shipping_address() === true) : ?>

        <?php
        if (empty($_POST)) {

            $ship_to_different_address = get_option('woocommerce_ship_to_destination') === 'shipping' ? 1 : 0;
            $ship_to_different_address = apply_filters('woocommerce_ship_to_different_address_checked', $ship_to_different_address);

        } else {

            $ship_to_different_address = $checkout->get_value('ship_to_different_address');
        }
        ?>

        <div id="ship-to-different-address">
            <div>
                <h3><?php _e('Ship to a different address?', 'woocommerce'); ?></h3>
            </div>

            <div class="ship-checkbox">
                <input id="ship-to-different-address-checkbox"
                       class="input-checkbox" <?php checked($ship_to_different_address, 1); ?> type="checkbox"
                       name="ship_to_different_address" value="1"/>
            </div>
        </div>

        <div class="shipping_address">

            <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

            <?php foreach ($checkout->checkout_fields['shipping'] as $key => $field) : ?>

                <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>

            <?php endforeach; ?>

            <?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>


            <?php if (apply_filters('woocommerce_enable_order_notes_field', get_option('woocommerce_enable_order_comments', 'yes') === 'yes')) : ?>

                <?php do_action('woocommerce_before_order_notes', $checkout); ?>

                <?php if (!WC()->cart->needs_shipping() || WC()->cart->ship_to_billing_address_only()) : ?>

                    <h3><?php _e('Additional Information', 'woocommerce'); ?></h3>

                <?php endif; ?>

                <?php foreach ($checkout->checkout_fields['order'] as $key => $field) : ?>

                    <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>

                <?php endforeach; ?>

                <?php do_action('woocommerce_after_order_notes', $checkout); ?>

            <?php endif; ?>

        </div>


    <?php endif; ?>

</div>


<?php

$virtual = false;
foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    $res = false;
    if ($cart_item['data']->virtual == 'yes') {
        $res = true;
    }

    $virtual = $res;
}

if ($virtual === true) :

    $recipient_fields = array();

    $recipient_fields["recipient_first_name"] = array(
        'label'    => "Prénom du destinataire",
        'required' => true,
        'class'    => array("form-row-first"),
    );

    $recipient_fields["recipient_last_name"] = array(
        'label'    => "Nom du destinataire",
        'required' => true,
        'class'    => array("form-row-last"),
        'clear'    => true,
    );

    $recipient_fields["recipient_company"] = array(
        'label' => "Nom de l'entreprise",
        'class' => array("form-row-wide"),
    );

    $recipient_fields["recipient_email"] = array(
        'label'    => "Adresse email du destinataire",
        'required' => true,
        'type'     => 'email',
        'class'    => array('form-row-wide'),
        'validate' => array('email'),
    );

    $recipient_fields["recipient_address_1"] = array(
        'label'       => "Adresse",
        'required'    => true,
        'placeholder' => 'Adresse',
        'class'       => array('form-row-wide', 'address-field'),
    );

    $recipient_fields["recipient_address_2"] = array(
        'required'    => false,
        'placeholder' => 'Appartement, bureau, etc. (optionnel)',
        'class'       => array('form-row-wide', 'address-field'),
    );

    $recipient_fields["recipient_country"] = array(
        'label'    => "Pays",
        'required' => true,
        'type'     => 'country',
        'class'    => array('form-row-wide', 'address-field', 'update_totals_on_change'),
    );

    $recipient_fields["recipient_postcode"] = array(
        'label'       => "Code Postal",
        'required'    => true,
        'placeholder' => 'Code postal',
        'class'       => array('form-row-first', 'address-field'),
        'validate'    => array("postcode"),
    );

    $recipient_fields["recipient_city"] = array(
        'label'       => "Ville",
        'required'    => true,
        'placeholder' => 'Ville',
        'clear'       => true,
        'class'       => array('form-row-last', 'address-field'),
    );

    $recipient_fields["recipient_message"] = array(
        'label'       => "Message au destinataire",
        'required'    => false,
        'type'        => 'textarea',
        'placeholder' => 'Ajoutez un message au destinataire',
        'class'       => array('form-row-wide', 'address-field'),
    );

    ?>

    <div class="woocommerce_recipient_fields">

        <div id="destinataire-des-soins">
            <div>
                <h3>
                    <?php _e('Destinataire des soins ?', 'woocommerce'); ?>
                    <small><i>(bon cadeau)</i></small>
                </h3>
            </div>

            <div class="ship-checkbox">
                <input class="input-checkbox" type="checkbox" name="destinataire_soins" value="0"/>
            </div>
        </div>

        <div class="recipient_fields">
            <?php foreach ($recipient_fields as $key => $field) : ?>
                <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
            <?php endforeach; ?>
        </div>

    </div>

<?php endif; ?>


<div class="woocommerce-order-review">

    <?php do_action('woocommerce_checkout_before_order_review'); ?>

    <h3 id="order_review_heading"><?php _e('Your order', 'woocommerce'); ?></h3>

    <div id="order_review" class="woocommerce-checkout-review-order">
        <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>

    <?php do_action('woocommerce_checkout_after_order_review'); ?>

</div>