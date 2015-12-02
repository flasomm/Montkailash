<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Settings Page
 *
 * The code for the plugins main settings page
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */		
	global $current_user;
 ?>
<div class="wrap">

	<!-- plugin name -->
	<h2 class="woo-vou-settings-title"><?php _e( 'Voucher Codes', 'woovoucher' ); ?></h2><br />

		<!-- beginning of the left meta box section -->
		<div class="content woo-vou-content-section">
		
			<h2 class="nav-tab-wrapper woo-vou-h2">
				<?php
		
					$voucher_codes_page_url = add_query_arg( array( 'page' => 'woo-vou-codes' ), admin_url( 'admin.php' ) );

					$purchased_code_url 	= add_query_arg( array( 'vou-data' => 'purchased' ), $voucher_codes_page_url );
					$used_code_url 			= add_query_arg( array( 'vou-data' => 'used' ), $voucher_codes_page_url );
					$purchased_code_expire_url	= add_query_arg( array( 'vou-data' => 'expire' ), $voucher_codes_page_url );
					
					$tab_prchased = ' nav-tab-active';
					$tab_used = '';
					$tab_expire ='';
					
					if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'purchased' ) {
			
						$tab_prchased = ' nav-tab-active';
						
					} elseif ( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'used' ) {
						
						$tab_used = ' nav-tab-active';
						$tab_prchased = '';	
					} elseif( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expire' ) {
			
						$tab_expire = ' nav-tab-active';
						$tab_prchased = '';	
					
						
					}
					
				?>
		        <a class="nav-tab<?php echo $tab_prchased; ?>" href="<?php echo $purchased_code_url;  ?>"><?php _e('Purchased Voucher Codes','woovoucher');?></a>
		        <a class="nav-tab<?php echo $tab_used; ?>" href="<?php echo $used_code_url; ?>"><?php _e('Used Voucher Codes','woovoucher');?></a>
		        <a class="nav-tab<?php echo $tab_expire; ?>" href="<?php echo $purchased_code_expire_url; ?>"><?php _e('Unused Voucher Codes','woovoucher');?></a>
		    </h2><!--nav-tab-wrapper-->
		    <!--beginning of tabs panels-->
			 <div class="woo-voucher-code-content">
			 
			 	<?php
					if( !empty( $tab_prchased ) ) {
						
						include_once( WOO_VOU_ADMIN . '/forms/woo-vou-purchased-list.php');
						
					} elseif ( !empty( $tab_used ) ) {
						
						include_once( WOO_VOU_ADMIN . '/forms/woo-vou-used-list.php');
						
					} elseif ( !empty( $tab_expire ) ) {
						
						include_once( WOO_VOU_ADMIN . '/forms/woo-vou-purchased-list-expire.php');
					}
				?>
			 <!--end of tabs panels-->
			 </div>
		<!--end of the left meta box section -->
		</div><!--.content woo-vou-content-section-->
	
<!--end .wrap-->
</div>