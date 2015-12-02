<?php
/**
 * Used Voucher Code
 *
 * The html markup for the used voucher code popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model;

$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;
		
//Get Voucher Details by post id
$usedcodes = $model->woo_vou_get_used_codes_by_product_id( $postid );

$html = '';

$html .= '<div class="woo-vou-popup-content woo-vou-used-codes-popup">
	
		<div class="woo-vou-header">
			<div class="woo-vou-header-title">'.__( 'Used Voucher Codes', 'woovoucher' ).'</div>
			<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="' . WOO_VOU_URL .'includes/images/tb-close.png" alt="'.__( 'Close','woovoucher' ).'"></a></div>
		</div>';

$generatpdfurl = add_query_arg( array( 
									'woo-vou-used-gen-pdf'	=>	'1',
									'product_id'			=>	$postid,
									'woo_vou_action'		=>	'used'
								));
$exportcsvurl = add_query_arg( array( 
									'woo-vou-used-exp-csv'	=>	'1',
									'product_id'			=>	$postid,
									'woo_vou_action'		=>	'used'
								));
		
$html .= '		<div class="woo-vou-popup used-codes">
				
				<div>
					<a href="'.$exportcsvurl.'" id="woo-vou-export-csv-btn" class="button-secondary" title="'.__('Export CSV','woovoucher').'">'.__('Export CSV','woovoucher').'</a>
					<a href="'.$generatpdfurl.'" id="woo-vou-pdf-btn" class="button-secondary" title="'.__('Generate PDF','woovoucher').'">'.__('Generate PDF','woovoucher').'</a>
				</div>
				
				<table class="form-table" border="1">
					<tbody>
						<tr>
							<th scope="row">'.__( 'Voucher Code', 'woovoucher' ).'</th>
							<th scope="row">'.__( 'Buyer\'s Name', 'woovoucher' ).'</th>
							<th scope="row">'.__( 'Order Date', 'woovoucher' ).'</th>
							<th scope="row">'.__( 'Order ID', 'woovoucher' ).'</th>
							<th scope="row">'.__( 'Redeem By', 'woovoucher' ).'</th>
						</tr>';
							if( !empty( $usedcodes ) &&  count( $usedcodes ) > 0 ) { 
								
								foreach ( $usedcodes as $key => $voucodes_data ) { 
									
									//voucher order id
									$orderid 		= $voucodes_data['order_id'];
									
									//voucher order date
									$orderdate 		= $voucodes_data['order_date'];
									$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate ) : '';
									
									//buyer's name who has used voucher code				
									$buyername 		=  $voucodes_data['buyer_name'];
									
									//voucher code used
									$voucode 		= $voucodes_data['vou_codes'];
									$user_id 		= $voucodes_data['redeem_by'];
									$user_detail  	= get_userdata( $user_id );
									$user_profile 	= add_query_arg( array('user_id' => $user_id), admin_url('user-edit.php') );
									$display_name 	= isset( $user_detail->display_name ) ? $user_detail->display_name : '';
									
									if( !empty( $display_name ) ){
										$display_name = '<a href="'.$user_profile.'">'.$display_name.'</a>';
									} else {
										$display_name = __( 'N/A', 'woovoucher' );
									}
									
								$html .= '<tr>
										<td>'.$voucode.'</td>
										<td>'.$buyername.'</td>
										<td>'.$orderdate.'</td>
										<td>'.$orderid.'</td>
										<td>'.$display_name.'</td>
									</tr>';
									
								}
								
							} else { 
								$html .= '<tr>
										<td colspan="5">'.__( 'No voucher codes used yet.','woovoucher' ).'</td>
									</tr>';
							}	
$html .= '					</tbody>
				</table>
		</div><!--.woo-vou-popup-->

	</div><!--.woo-vou-used-codes-popup-->
	<div class="woo-vou-popup-overlay woo-vou-used-codes-popup-overlay"></div>';

echo $html;
?>