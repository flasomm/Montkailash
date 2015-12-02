<?php

$prefix = WOO_VOU_META_PREFIX;

//get order
$order 			= new Wc_Order( $order_id );

//get order items
$order_items 	= $order->get_items();

//get order date
$order_date		= $order->order_date;

//get payment method
$payment_method	= $order->payment_method_title;

//get buyer details
$buyer_detail	= $this->model->woo_vou_get_buyer_information( $order_id );

//get buyer name
$buyername		= isset( $buyer_detail['first_name'] ) ? $buyer_detail['first_name'] : '';
$buyername		.= isset( $buyer_detail['last_name'] ) ? ' '.$buyer_detail['last_name'] : '';

//product info key parameter
$product_info_columns	= apply_filters( 'woo_vou_check_vou_productinfo_fields', array(
													'item_name'		=> __( 'Item Name', 'woovoucher' ),
													'item_price'	=> __( 'Price', 'woovoucher' )
												), $order_id, $voucode );

//product voucher information columns
$voucher_info_columns = apply_filters( 'woo_vou_check_vou_voucherinfo_fields', array(
													'logo' 			=> __( 'Logo', 'woovoucher' ),
													'voucher_data' 	=> __( 'Voucher Data', 'woovoucher' ),
													'expires' 		=> __( 'Expires', 'woovoucher' )
												), $order_id, $voucode );

//buyer info key parameter
$buyer_info_columns	= apply_filters( 'woo_vou_check_vou_buyerinfo_fields', array(
													'buyer_name'		=> __( 'Name', 'woovoucher' ),
													'buyer_email'		=> __( 'Email', 'woovoucher' ),
													'billing_address'	=> __( 'Billing Address', 'woovoucher' ),
													'shipping_address'	=> __( 'Shipping Address', 'woovoucher' ),
													'buyer_phone'		=> __( 'Phone', 'woovoucher' )
												), $order_id, $voucode );

//order info key parameter
$order_info_columns	= apply_filters( 'woo_vou_check_vou_orderinfo_fields', array(
													'order_id'			=> __( 'Order ID', 'woovoucher' ),
													'order_date'		=> __( 'Order Date', 'woovoucher' ),
													'payment_method'	=> __( 'Payment Method', 'woovoucher' ),
													'order_total'		=> __( 'Order Total', 'woovoucher' ),
													'order_discount'	=> __( 'Order Discount', 'woovoucher' ),
												), $order_id, $voucode );

$check_code	= trim( $voucode );
$item_array	= $this->model->woo_vou_get_item_data_using_voucher_code( $order_items, $check_code );

$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();
$_product 	= $order->get_product_from_item( $item );

$billing_address	= $order->get_formatted_billing_address();
$shipping_address	= $order->get_formatted_shipping_address();
?>

<div class="woo_vou_product_details">
	<?php do_action( 'woo_vou_before_productinfo', $voucodeid, $item_id, $order_id );?>
	<h2><?php echo __( 'Product Information', 'woovoucher' );?></h2>
	<table style="width:100%;" cellpadding="0" cellspacing="0" class="woocommerce_order_items">
		<thead>
			<tr><?php
		
				if( !empty( $product_info_columns ) ) { //if product info is not empty
					foreach ( $product_info_columns as $col_key => $column ) { ?>
						
						<th><?php echo $column;?></th><?php
					}
				}?>
			</tr>
		</thead>
		<tbody id="order_items_list">
			<tr><?php
				if( !empty( $product_info_columns ) ) { //if product info is not empty

					foreach ( $product_info_columns as $col_key => $column ) {?>

						<td><?php

							$column_value = $sku_value	= '';

							switch ( $col_key ) {

								case 'item_name' : 
									
									if ( $_product && $_product->get_sku() ) {
										$sku_value	= esc_html( $_product->get_sku() ).' - ';
									}
									if ( $_product ) {
										$column_value .= $sku_value.'<a target="_blank" href="'. get_permalink( $_product->id ) . '">' . esc_html( $item['name'] ) . '</a>';
									} else {
										$column_value .= $sku_value.esc_html( $item['name'] );
									}

									//Get product item meta
									$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();

									//Display product variations
									//$column_value .= $this->model->woo_vou_display_product_item_name( $product_item_meta, true );
									$column_value .= $this->model->woo_vou_display_product_item_name( $item, $_product, true );
									break;

								case 'item_price' :
									if ( isset( $item['line_total'] ) ) {
										if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) echo '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ';
										$column_value .= wc_price( $item['line_total'] );
									}
									break;
									
								default:
									$column_value .= '';
							}

							echo apply_filters( 'woo_vou_check_voucher_column_vlaue', $column_value, $col_key, $voucodeid, $item_id, $order_id );

							?>
						</td><?php 
					}
				}?>
			</tr>
		</tbody>
	</table><?php 
	
	do_action( 'woo_vou_after_productinfo', $voucodeid, $item_id, $order_id );
	?>
	
	<h2><?php echo __( 'Voucher Information', 'woovoucher' ); ?></h2>
	<table style="width:100%;" cellpadding="0" cellspacing="0" class="woocommerce_order_items">
		<thead>
			<tr><?php
		
				if( !empty( $voucher_info_columns ) ) { //if voucher info column is not empty
					foreach ( $voucher_info_columns as $col_key => $column ) { ?>
						
						<th><?php echo $column;?></th><?php
					}
				}?>
			</tr>
		</thead>
		<tbody id="order_items_list">
			<tr><?php
				if( !empty( $voucher_info_columns ) ) { //if voucher info column is not empty
					
					// get orderdata
					$allorderdata	= $this->model->woo_vou_get_all_ordered_data( $order_id );						
					//get all voucher details from order meta
					$allvoucherdata = isset( $allorderdata[$_product->id] ) ? $allorderdata[$_product->id] : array();
					
					foreach ( $voucher_info_columns as $col_key => $column ) { ?>

						<td><?php

							$column_value = '';

							switch ( $col_key ) {

								case 'logo' :									
									if( !empty(  $allvoucherdata['vendor_logo']['src'] ) )
										$column_value .= '<img src="' . $allvoucherdata['vendor_logo']['src'] . '" alt="" width="70" height="70" />';
									break;
								case 'voucher_data' : 
									ob_start(); ?>									
									<span><strong><?php _e( 'Vendor\'s Address', 'woovoucher' ); ?></strong></span><br />
									<span><?php echo !empty( $allvoucherdata['vendor_address'] ) ? nl2br( $allvoucherdata['vendor_address'] ) : __( 'N/A', 'woovoucher' ); ?></span><br />
									<span><strong><?php _e( 'Site URL', 'woovoucher' ); ?></strong></span><br />
									<span><?php echo !empty( $allvoucherdata['website_url'] ) ? $allvoucherdata['website_url'] : __( 'N/A', 'woovoucher' ); ?></span><br />
									<span><strong><?php _e( 'Redeem Instructions', 'woovoucher' ); ?></strong></span><br />
									<span><?php echo !empty( $allvoucherdata['redeem'] ) ? nl2br( $allvoucherdata['redeem'] ) : __( 'N/A', 'woovoucher' ); ?></span><br /><?php
									
									if( !empty( $allvoucherdata['avail_locations'] ) ) {
										
										echo '<span><strong>' . __( 'Locations', 'woovoucher' ) . '</strong></span><br />';
										
										foreach ( $allvoucherdata['avail_locations'] as $location ) {
											
											if( !empty( $location[$prefix.'locations'] ) ) {
												
												if( !empty( $location[$prefix.'map_link'] ) ) {
													echo '<span><a target="_blank" style="text-decoration: none;" href="' . $location[$prefix.'map_link'] . '">' . $location[$prefix.'locations'] . '</a></span><br />';
												} else {
													echo '<span>' . $location[$prefix.'locations'] . '</span><br />';
												}
											}
										}
									}
									$column_value = ob_get_clean();
									break;
								case 'expires' : 
									$column_value = !empty( $allvoucherdata['exp_date'] ) ? $this->model->woo_vou_get_date_format( $allvoucherdata['exp_date'], true ) : __( 'N/A', 'woovoucher' );	
								default:
									$column_value .= '';
							}

							echo apply_filters( 'woo_vou_check_voucher_column_vlaue', $column_value, $col_key, $voucodeid, $item_id, $order_id );
							?>
						</td><?php
					}
				}?>
			</tr>
		</tbody>
	</table><?php 
	
	do_action( 'woo_vou_after_voucherinfo', $voucodeid, $item_id, $order_id );
	?>
	
	<h2><?php echo __( 'Buyer Information', 'woovoucher' ); ?></h2>
	<table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
		<thead>
			<tr><?php
		
				if( !empty( $buyer_info_columns ) ) { //if product info is not empty
					foreach ( $buyer_info_columns as $col_key => $column ) { ?>
						
						<th><?php echo $column;?></th><?php
					}
				}?>
			</tr>
		</thead>
		<tbody id="order_items_list">
			<tr><?php
				if( !empty( $buyer_info_columns ) ) { //if buyer info is not empty
					foreach ( $buyer_info_columns as $col_key => $column ) {?>
						
						<td><?php
						
							$column_value = '';
							
							switch ( $col_key ) { 
								
								case 'buyer_name' : 
									$column_value .= $order->billing_first_name;
									
									if( !empty( $order->billing_last_name ) ) {
										$column_value .= ' ' . $order->billing_last_name;
									}
									break;
								
								case 'buyer_email' : 
									$column_value .= $order->billing_email;
									break;
								
								case 'billing_address' : 
									$column_value .= $billing_address;
									break;
								
								case 'shipping_address' : 
									$column_value .= $shipping_address;
									break;
								
								case 'buyer_phone' : 
									$column_value .= $order->billing_phone;
									break;
								
								default:
									$column_value .= '';
							}
							
							echo apply_filters( 'woo_vou_check_voucher_column_vlaue', $column_value, $col_key, $voucodeid, $item_id, $order_id );
							?>
						</td><?php 
					}
				}?>
			</tr>
		</tbody>
	</table><?php 
	
	do_action( 'woo_vou_after_buyerinfo', $voucodeid, $item_id, $order_id );
	?>
	
	<h2><?php echo __( 'Order Information', 'woovoucher' );?></h2>
	<table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
		<thead>
			<tr><?php
		
				if( !empty( $order_info_columns ) ) { //if product info is not empty
					foreach ( $order_info_columns as $col_key => $column ) { ?>
						
						<th><?php echo $column;?></th><?php
					}
				}?>
			</tr>
		</thead>
		<tbody id="order_items_list">
			<tr><?php
				if( !empty( $order_info_columns ) ) { //if order info is not empty
					foreach ( $order_info_columns as $col_key => $column ) {?>
						
						<td><?php
						
							$column_value = '';
							
							switch ( $col_key ) { 
								
								case 'order_id' :
									$column_value .= $order_id;
									break;
								
								case 'order_date' :
									$column_value .= $this->model->woo_vou_get_date_format( $order->post->post_date, true );
									break;
								
								case 'payment_method' : 
									$column_value .= $payment_method;
									break;
								
								case 'order_total':
									$column_value .= esc_html( strip_tags( $order->get_formatted_order_total() ) );
									break;
								
								case 'order_discount' : 
									$column_value .= wc_price( $order->get_total_discount(), array( 'currency' => $order->get_order_currency() ) );
									break;
								
								default:
									$column_value .= '';
							}
							
							echo apply_filters( 'woo_vou_check_voucher_column_vlaue', $column_value, $col_key, $voucodeid, $item_id, $order_id ); ?>
						</td><?php 
					}
				}?>
			</tr>
		</tbody>
	</table><?php
	
	do_action( 'woo_vou_after_orderinfo', $voucodeid, $item_id, $order_id );
	?>
</div>