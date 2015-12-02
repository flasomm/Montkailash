<?php
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	header('Pragma: no-cache');
?>
<!doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
	<title><?php echo __('Redeem voucher code', 'woovoucher'); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo WOO_VOU_URL; ?>includes/css/woo-vou-check-qrcode.css">
</head>
<body><?php

	$redeem_response	= '';
	$redeem				= false;
	if( !empty( $_POST['woo_vou_voucher_code_submit'] ) ) { // if form is submited

		// save voucher code
		$redeem_response = $this->woo_vou_save_voucher_code();
	}

	if( !empty( $redeem_response ) && $redeem_response == 'success' ) {

		echo "<div class='woo-vou-voucher-code-msg success'>" . __( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ) . "</div>";
		unset( $_GET['woo_vou_code'] );
		unset( $_POST['voucode'] );
		$redeem = true;
	}

	//Check if the user is logged in.  If not, show the login form.
	if ( !is_user_logged_in() ) {

		$args = array(
		        'echo'		=> true,
		        'redirect'	=> add_query_arg( get_site_url(), $_SERVER["QUERY_STRING"] )
		);

		wp_login_form( $args );
	} else {

		if( !$redeem ) {

			// if multiple voucher codes exist then split it
			$voucodes = explode( ",", $_GET['woo_vou_code'] );

			foreach ( $voucodes as $voucode ) {

				$voucode = trim( $voucode ); // remove spaces from voucher code

				// assign voucher code to $_POST variable.
				// Needed because $_POST['voucode'] used in function woo_vou_check_voucher_code()
				$_POST['voucode'] = $voucode;

				// Check voucher code and get result
				$voucher_data = $this->woo_vou_check_voucher_code();

				if( !empty( $voucher_data ) ) {

					if( empty( $voucode ) ) {

						echo "<div class='woo-vou-voucher-code-msg error'>" . __('Please enter voucher code.', 'woovoucher') . "</div>";
					} else if( !empty( $voucher_data['success'] ) ) { ?>
						<form id="woo-vou-check-vou-code-form" method="post" action="">
							<input type="hidden" name="voucode" value="<?php echo $voucode; ?>" />
							<table class="form-table woo-vou-check-code">
								<tr>
									<td>
										<div class="woo-vou-voucher-code-msg success">
											<span><?php echo $voucher_data['success']; ?></span>
										</div>
										<?php echo $voucher_data['product_detail']; ?>
									</td>
								</tr>
								<tr class="woo-vou-voucher-code-submit-wrap">
									<td>
										<?php 
											echo apply_filters('woo_vou_voucher_code_submit',
												'<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="'.__( "Redeem", "woovoucher" ).'"/>'
											);
										?>
										<div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img src="<?php echo WOO_VOU_IMG_URL;?>/ajax-loader.gif"/></div>
									</td>
								</tr>
							</table>
						</form><?php

					} else if( !empty( $voucher_data['error']) ) {
						echo "<div class='woo-vou-voucher-code-msg error'>" . __('Voucher code doest not exist.', 'woovoucher') . "</div>";
					} else if( !empty( $voucher_data['used']) ) {
						echo "<div class='woo-vou-voucher-code-msg error'>" . $voucher_data['used'] . "</div>";
					}
				}
			} // End of foreach
		} // End of if $redeem
	}?>
</body>
</html><?php
exit();