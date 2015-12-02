<?php
/**
 * Handles the product variable meta HTML
 *
 * The html markup for the product variable
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

$prefix = WOO_VOU_META_PREFIX;

$variation_id 			= isset($variation->ID) ? $variation->ID : '';
$woo_vou_variable_codes = get_post_meta( $variation_id, $prefix . 'codes', true ); // Getting voucher code
?>

<div class="show_if_variation_downloadable" style="display:none;">
	<p>
		<label><?php _e('Voucher Codes', 'woovoucher'); ?>: <a data-tip="<?php _e( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated.', 'woovoucher' ); ?>" class="tips" href="#">[?]</a></label>
		<textarea style="width:100%" rows="2" placeholder="" id="woo-vou-variable-codes-<?php echo $loop; ?>" name="<?php echo $prefix; ?>variable_codes[<?php echo $loop; ?>]" class="short"><?php echo $woo_vou_variable_codes; ?></textarea>
	</p>
</div>