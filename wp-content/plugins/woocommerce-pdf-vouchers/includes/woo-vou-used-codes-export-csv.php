<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Export to CSV for Voucher
 * 
 * Handles to Export to CSV on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

function woo_vou_code_export_to_csv(){
	
	
	$prefix = WOO_VOU_META_PREFIX;	
	
	if( isset( $_GET['woo-vou-used-exp-csv'] ) && !empty( $_GET['woo-vou-used-exp-csv'] ) 
		&& $_GET['woo-vou-used-exp-csv'] == '1'
		&& isset($_GET['product_id']) && !empty($_GET['product_id'] ) ) {
		
		global $current_user,$woo_vou_model, $post;
		
		//model class
		$model = $woo_vou_model;
	
		$postid = $_GET['product_id']; 
		
		$exports = '';
		
		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
		
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_used_codes_by_product_id( $postid );
		 	
			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
			
		} else{
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_purchased_codes_by_product_id( $postid );
		 	
			$vou_csv_name = get_option( 'vou_csv_name' );
			$vou_file_name = !empty( $vou_csv_name )? $vou_csv_name : 'woo-purchased-voucher-codes-{current_date}';
		}
		$columns = array(	
							__( 'Voucher Code', 'woovoucher' ),
							__( 'Buyer\'s Name', 'woovoucher' ),
							__( 'Order Date', 'woovoucher' ),
							__( 'Order ID', 'woovoucher' ),							
					     );
					     
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
			
			$new_columns	= array( __('Redeem By', 'woovoucher' ) );
			$columns 		= array_merge ( $columns , $new_columns );
			
		}
		
		
        // Put the name of all fields
		foreach ($columns as $column) {
			
			$exports .= '"'.$column.'",';
		}
		$exports .="\n";
		
		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) { 
												
			foreach ( $voucodes as $key => $voucodes_data ) { 
			
				//voucher order id
				$orderid 		= $voucodes_data['order_id'];
				
				//voucher order date
				$orderdate 		= $voucodes_data['order_date'];
				$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate ) : '';
				
				//buyer's name who has purchased/used voucher code				
				$buyername 		=  $voucodes_data['buyer_name'];
				
				//voucher code purchased/used
				$voucode 		= $voucodes_data['vou_codes'];
				
				//this line should be on start of loop
				$exports .= '"'.$voucode.'",';
				$exports .= '"'.$buyername.'",';
				$exports .= '"'.$orderdate.'",';
				$exports .= '"'.$orderid.'",';
				
				if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
					
					$user_id 	 	= $voucodes_data['redeem_by'];
					$user_detail 	= get_userdata( $user_id );
					$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';
					
					$exports .= '"'.$redeem_by.'",';
				}
				ob_start();
								
				$added_column = ob_get_clean();

				$exports .= $added_column;
				
				$exports .="\n";
			}
		} 
		
		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );
		
		// Output to browser with appropriate mime type, you choose ;)
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=".$vou_file_name.".csv");
		echo $exports;
		exit;
		
	}
	
	// generate csv for voucher code
	if( isset( $_GET['woo-vou-voucher-exp-csv'] ) && !empty( $_GET['woo-vou-voucher-exp-csv'] ) 
		&& $_GET['woo-vou-voucher-exp-csv'] == '1' ) 
	{	
		global $current_user,$woo_vou_model, $post, $woo_vou_vendor_role;
		
		//model class
		$model = $woo_vou_model;
	
		$exports = '';
		
		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
		
			$args = array();
		
			$args['meta_query'] = array(
											array(
														'key'		=> $prefix.'used_codes',
														'value'		=> '',
														'compare'	=> '!=',
													)
										);
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );
			
			if( in_array( $user_role, $woo_vou_vendor_role  ) ) { // Check vendor user role
				
				$redeem_all	= $model->woo_vou_vendor_redeem_all_codes( $current_user );
				
				if( !$redeem_all ) {
					$args['author'] = $current_user->ID;
				}				
			}
			
			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				
				//$args['s'] = $_GET['s'];
				$args['meta_query'] = array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'used_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											);
			}
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_voucher_details( $args );
		 	
		 	$vou_file_name = 'woo-used-voucher-codes-{current_date}';
			
		} else{
			
		 	$args = array();	 			 	
		 	
		 	if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expire'){
		 		
		 			$args['meta_query'] = array(
											array(
													'key' 		=> $prefix . 'purchased_codes',
													'value'		=> '',
													'compare' 	=> '!='
												),
											array(
														'key'     	=> $prefix . 'used_codes',
														'compare' 	=> 'NOT EXISTS'
												 ),
											array(
													'key' =>  $prefix .'exp_date',
													'compare' => '<=',
		                  							'type'    => 'DATE',
		                  							'value' => $model->woo_vou_current_date()
												)										    
										);
		 		
		 		
		 	}
		 	else{
				$args['meta_query'] = array(
											array(
													'key' 		=> $prefix . 'purchased_codes',
													'value'		=> '',
													'compare' 	=> '!='
												),
											array(
														'key'     	=> $prefix . 'used_codes',
														'compare' 	=> 'NOT EXISTS'
												 ),
											array(
												'relation' => 'OR', // Optional, defaults to "AND"
												array(
													'key'     => $prefix .'exp_date',
													'value'   => '',
													'compare' => '='
												),
												array(
													'key' =>  $prefix .'exp_date',
													'compare' => '>=',
		                  							'type'    => 'DATE',
		                  							'value' => $model->woo_vou_current_date()
												)
										   )	 
										);
		 	}
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );
			
			if( in_array( $user_role, $woo_vou_vendor_role  ) ) { // Check vendor user role
				
				$redeem_all	= $model->woo_vou_vendor_redeem_all_codes( $current_user );
				
				if( !$redeem_all ) {
					$args['author'] = $current_user->ID;
				}
			}
			
			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				
				//$args['s'] = $_GET['s'];
				$args['meta_query'] = array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'purchased_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											);
			}
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_voucher_details( $args );
		 	
		 	$vou_csv_name = get_option( 'vou_csv_name' );
			$vou_file_name = !empty( $vou_csv_name )? $vou_csv_name : 'woo-purchased-voucher-codes-{current_date}';			
		}
		$columns = array(	
							__( 'Voucher Code', 'woovoucher' ),
							__( 'Product Information', 'woovoucher' ),
							__( 'Buyer\'s Information', 'woovoucher' ),
							__( 'Order Information', 'woovoucher' ),							
					     );
					     
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
			
			$new_columns	= array( __('Redeem By', 'woovoucher' ) );
			$columns 		= array_merge ( $columns , $new_columns );
			
		}	
		
		$csv_type	= isset( $_GET['woo_vou_action'] ) ? $_GET['woo_vou_action'] : 'purchased';		
		
		$columns	= apply_filters( 'woo_vou_generate_csv_columns', $columns, $csv_type );	
		
        // Put the name of all fields
		foreach ($columns as $column) {
			
			$exports .= '"'.$column.'",';
		}
		$exports .="\n";
		
		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) { 
												
			foreach ( $voucodes as $key => $voucodes_data ) { 
			
				//voucher order id
				$orderid 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_id', true );
				
				//voucher order date
				$orderdate 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_date', true );
				$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate ) : '';
				
								
				// get order detail
				$order = new WC_Order( $orderid );
				// get Buyer id, if buyer is guest then user id will be zero
				$user_id = $order->user_id;
				
				// buyer's details array who has purchased/used voucher code
				$buyer_details = array(
					'first_name' => get_user_meta( $user_id, 'billing_first_name', true ),
					'last_name'  => get_user_meta( $user_id, 'billing_last_name', true ),					
					'address_1'  => get_user_meta( $user_id, 'billing_address_1', true ),
					'address_2'  => get_user_meta( $user_id, 'billing_address_2', true ),
					'city'       => get_user_meta( $user_id, 'billing_city', true ),
					'state'      => get_user_meta( $user_id, 'billing_state', true ),
					'postcode'   => get_user_meta( $user_id, 'billing_postcode', true ),
					'country'    => get_user_meta( $user_id, 'billing_country', true ),
					'email'      => get_user_meta( $user_id, 'billing_email', true ),
					'phone'      => get_user_meta( $user_id, 'billing_phone', true )
				);
				
				
				$buyer_details_html  = 'Name: '.$buyer_details['first_name'].' '.$buyer_details['last_name']."\n";
				$buyer_details_html .= 'Email: '.$buyer_details['email']."\n";
				$buyer_details_html .= 'Address: '.$buyer_details['address_1'].' '.$buyer_details['address_2']."\n";
				$buyer_details_html .= $buyer_details['city'].' '.$buyer_details['state'].' '.$buyer_details['country'].' - '.$buyer_details['postcode']."\n";
				$buyer_details_html .= 'Phone: '.$buyer_details['phone'];
				
				$buyerinfo = $buyer_details_html;				
				
				//voucher code purchased/used
				$voucode 		= get_post_meta( $voucodes_data['ID'], $prefix.'purchased_codes', true );
				
				$user_id 	 	= get_post_meta( $voucodes_data['ID'], $prefix.'redeem_by', true );
				$user_detail 	= get_userdata( $user_id );
				$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';
				
			//	$product_title = get_the_title( $voucodes_data['post_parent'] );
			
				$product_info = $woo_vou_model->woo_vou_display_product_info_html( $orderid, $voucode, 'csv' );
				
				$order_info = $woo_vou_model->woo_vou_display_order_info_html( $orderid, 'csv' );
				
				//this line should be on start of loop
				$exports .= '"'.$voucode.'",';
				$exports .= '"'.$product_info.'",';
				$exports .= '"'.$buyerinfo.'",';
				$exports .= '"'.$order_info.'",';
				

				if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
					$exports .= '"'.$redeem_by.'",';
				}
				do_action( 'woo_vou_generate_csv_add_column_after', $orderid, $voucode );
				
				$exports .="\n";
			}
		} 
		
		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );

		
		// Output to browser with appropriate mime type, you choose ;)
		
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=".$vou_file_name.".csv");
		echo $exports;
		exit;
		
	}
}
add_action( 'admin_init', 'woo_vou_code_export_to_csv' );