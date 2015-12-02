<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Scripts {

	public function __construct() {
		
	}
	
	/**
	 * Enqueue Scrips
	 * 
	 * Handles to enqueue script on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_popup_scripts( $hook_suffix ) {
				
		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		
		global $post, $wp_version;
		$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
		
		$pages_hook_suffix = array( 'post.php', 'post-new.php', 'user-edit.php' );
		
		//Check pages when you needed 
		if( in_array( $hook_suffix, array( $wc_screen_id.'_page_wc-settings', $wc_screen_id.'_page_woo-vou-check-voucher-code', $woo_vou_screen_id.'_page_woo-vou-check-voucher-code', 'toplevel_page_woo-vou-check-voucher-code', 'user-edit.php' , 'user-new.php' ) ) ) {

			wp_register_script( 'woo-vou-admin-script', WOO_VOU_URL . 'includes/js/woo-vou-admin.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-admin-script' );
			
			// check if pdf fonts plugin is active or not
			$is_pdf_fonts_plugin_active = false;
			if( defined( 'WOO_VOU_PF_DIR') ) {
				$is_pdf_fonts_plugin_active = true;
			}
			
			wp_localize_script( 'woo-vou-admin-script' , 'WooVouAdminSettings' , array( 'new_media_ui' => $newui, 'is_pdf_fonts_plugin_active' => $is_pdf_fonts_plugin_active ) );
			
		    wp_enqueue_media();
		}
		
		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
					
			//Check vouchers & product post type
			if( in_array( $hook_suffix, array( 'user-edit.php' ) )
				|| ( isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE ) ) {
				
				wp_register_script( 'woo-vou-script-metabox', WOO_VOU_URL.'includes/js/woo-vou-metabox.js', array( 'jquery', 'jquery-form' ), WOO_VOU_PLUGIN_VERSION, true ); 
				wp_enqueue_script( 'woo-vou-script-metabox' );
				wp_localize_script( 'woo-vou-script-metabox', 'WooVouMeta', array(	
																					'invalid_url' 			=> __( 'Please enter valid url.', 'woovoucher' ),
																					'noofvouchererror' 		=> '<div>' . __( 'Please enter Number of Voucher Codes.', 'woovoucher' ) . '</div>',
																					'patternemptyerror' 	=> '<div>' . __( 'Please enter Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'generateerror' 		=> '<div>' . __( 'Please enter Valid Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'filetypeerror'			=> '<div>' . __( 'Please upload csv file.', 'woovoucher' ) . '</div>',
																					'fileerror'				=> '<div>' . __( 'File can not be empty, please upload valid file.', 'woovoucher' ) . '</div>',
																					'new_media_ui' 			=> $newui
																				) );
		
			}
			
			//Check vouchers post type
			if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
						
				//If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
			    if ( $wp_version >= 3.5 ) {
			        //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_script( 'wp-color-picker' );
			    }
			    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
			    else {
			        //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_script( 'farbtastic' );
			    }
				wp_enqueue_script( array( 'jquery', 'jquery-ui-tabs', 'media-upload', 'thickbox', 'tinymce','jquery-ui-accordion' ) );
				
				wp_register_script( 'woo-vou-admin-voucher-script', WOO_VOU_URL . 'includes/js/woo-vou-admin-voucher.js', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_script( 'woo-vou-admin-voucher-script' );
				//wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouAjax' , array( 'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) ) );
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSettings' , array( 'new_media_ui' => $newui ) );
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouTranObj' , array( 
																									'onbuttontxt' => __('Voucher Builder is On','woovoucher'),
																									'offbuttontxt' => __('Voucher Builder is Off','woovoucher'),
																									'switchanswer' => __('Default WordPress editor has some content, switching to the Voucher will remove it.','woovoucher'),
																									'btnsave' => __('Save','woovoucher'),
																									'btncancel' => __('Cancel','woovoucher'),
																									'btndelete' => __('Delete','woovoucher'),
																									'btnaddmore' => __('Add More','woovoucher')
																								));
				/* this is used for text block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouTextBlock' , array( 
																									'textblocktitle' => __('Voucher Code','woovoucher'),
																									'textblockdesc' => __('Voucher Code','woovoucher'),
																									'textblockdesccodes' => '{codes}'
																								));
				/* this is used for message box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouMsgBox' , array( 
																									'msgboxtitle' => __('Redeem Instruction','woovoucher'),
																									'msgboxdesc' => '<p>' . '{redeem}' . '</p>'
																								));
				/* this is used for logo box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSiteLogoBox' , array( 
																									'sitelogoboxtitle' => __('Voucher Site Logo','woovoucher'),
																									'sitelogoboxdesc'  => '{sitelogo}'
																								));
				/* this is used for logo box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouLogoBox' , array( 
																									'logoboxtitle' => __('Voucher Logo','woovoucher'),
																									'logoboxdesc' => '{vendorlogo}'
																								));
				/* this is used for expire date block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouExpireBlock' , array( 
																									'expireblocktitle' => __('Expire Date','woovoucher'),
																									'expireblockdesc' => __('Expire :','woovoucher') . ' {expiredatetime}'
																								));
				/* this is used for vendor's address block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouVenAddrBlock' , array( 
																									'venaddrblocktitle' => __('Vendor\'s Address','woovoucher'),
																									'venaddrblockdesc' => '{vendoraddress}'
																								));
				/* this is used for website URL block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSiteURLBlock' , array( 
																									'siteurlblocktitle' => __('Website URL','woovoucher'),
																									'siteurlblockdesc' => '{siteurl}'
																								));
				/* this is used for voucher location block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouLocBlock' , array( 
																									'locblocktitle' => __('Voucher Locations','woovoucher'),
																									'locblockdesc' => '<p><span style="font-size: 9pt;">{location}</span></p>'
																								));
				/* this is used for blank box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouBlankBox' , array( 
																									'blankboxtitle' => __('Blank Block','woovoucher'),
																									'blankboxdesc' => __('Blank Block','woovoucher')
																								));
				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouCustomBlock' , array( 
																									'customblocktitle' => __('Custom Block','woovoucher'),
																									'customblockdesc' => __('Custom Block','woovoucher')
																								));
				/* this is used for Messages section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouMessage' , array( 
																									'invalid_number' => __('Please enter valid number.','woovoucher'),
																								));
			}
		}
	}
	
	/**
	 * Enqueue Styles
	 * 
	 * Handles to enqueue styles on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_popup_styles( $hook_suffix ) {

		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		
		$pages_hook_suffix = array( 'post.php', 'post-new.php', $wc_screen_id.'_page_woo-vou-codes', 'toplevel_page_woo-vou-codes' );
		
		//Check pages when you needed
		if( in_array( $hook_suffix, array( $wc_screen_id . '_page_woo-vou-check-voucher-code', $woo_vou_screen_id . '_page_woo-vou-check-voucher-code', 'toplevel_page_woo-vou-check-voucher-code', $wc_screen_id.'_page_woo-vou-codes', 'toplevel_page_woo-vou-codes' ) ) ) {

			wp_register_style( 'woo-vou-admin-style', WOO_VOU_URL.'includes/css/woo-vou-admin.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-admin-style' );
		}
		
		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
			
			global $post, $wp_version;
			
			//Check vouchers & product post type
			if( in_array( $hook_suffix, array( $wc_screen_id . '_page_woo-vou-codes', 'toplevel_page_woo-vou-codes' ) )
				|| ( isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE ) ) {
				
				wp_register_style( 'woo-vou-style-metabox', WOO_VOU_URL.'includes/css/woo-vou-metabox.css', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_style( 'woo-vou-style-metabox' );
			}
			
			//Check vouchers post type
			if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
				
				//for color picker
				
				//If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
			    if ( $wp_version >= 3.5 ){
			        //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_style( 'wp-color-picker' );
			    }
			    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
			    else {
			        //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_style( 'farbtastic' );
			    }
			    
				wp_register_style( 'woo-vou-admin-style',  WOO_VOU_URL . 'includes/css/woo-vou-admin-voucher.css', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_style( 'woo-vou-admin-style' );
			}
		}
	}
		
	/**
	 * Enqueue Scripts
	 * 
	 * Handles to enqueue scripts on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_admin_drag_drop_head() {
	
		global $post;
		
		//Check vouchers post type
		if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
		
			echo '	<script type="text/javascript">			
						var settings 	= {};
						var options 	= { portal 			: "columns",
											editorEnabled 	: true};
						var data 		= {};

						var portal;

						Event.observe(window, "load", function() {
							portal = new Portal(settings, options, data);
						});
					</script>';
		}
	}
	
	/**
	 * Enqueue Scripts
	 * 
	 * Handles to enqueue scripts on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_admin_drag_drop_scripts( $hook_suffix ) {
		
		global $post;
			
		//Check vouchers post type
		if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
			
			wp_register_script( 'woo-vou-drag-script', WOO_VOU_URL . 'includes/js/dragdrop/portal.js', array( 'scriptaculous' ), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-drag-script' );
						
		}
	}
	
	/**
	 * Enqueue style for meta box page
	 * 
	 * Handles style which is enqueue in products meta box page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_metabox_styles() {
		
		global $woocommerce;
		
		// Enqueue Meta Box Style
		wp_enqueue_style( 'woo-vou-meta-box', WOO_VOU_META_URL . '/css/meta-box.css', array(), WOO_VOU_PLUGIN_VERSION );
		  
		//css directory url
		$css_dir = $woocommerce->plugin_url() . '/assets/css/';
		
		// Admin styles for WC pages only
		wp_enqueue_style( 'woo_vou_admin_styles', $css_dir . 'admin.css', array(), WOOCOMMERCE_VERSION );
			
		wp_register_style( 'select2', $css_dir . 'select2.css', array(), WOOCOMMERCE_VERSION );
		wp_enqueue_style( 'select2' );
			
		// Enqueue for datepicker
		wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
		
		// Enqueu built-in style for color picker.
		if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
			wp_enqueue_style( 'wp-color-picker' );
		} else {
			wp_enqueue_style( 'farbtastic' );
		}
		
	}
	
	/**
	 * Enqueue script for meta box page
	 * 
	 * Handles script which is enqueue in products meta box page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_metabox_scripts() {
		
		global $wp_version, $woocommerce;;
		
		// Enqueue Meta Box Scripts
		wp_enqueue_script( 'woo-vou-meta-box', WOO_VOU_META_URL . '/js/meta-box.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION, true );
		
		//localize script
		$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
		wp_localize_script( 'woo-vou-meta-box','WooVou',array(		'new_media_ui'	=>	$newui,
																	'one_file_min'	=>  __('You must have at least one file.','woovoucher' )));

		// Enqueue for  image or file uploader
		wp_enqueue_script( 'media-upload' );
		add_thickbox();
		wp_enqueue_script( 'jquery-ui-sortable' );
								
		//js directory url
		$js_dir = $woocommerce->plugin_url() . '/assets/js/';
		
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Select2
		wp_register_script( 'select2', $js_dir . 'select2/select2'.$suffix . '.js', array( 'jquery' ), '3.5.2' );
		wp_register_script( 'wc-enhanced-select', $woocommerce->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
		wp_enqueue_script( 'wc-enhanced-select' );	
		
		// Enqueue for datepicker
		wp_enqueue_script(array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider'));
		
		wp_deregister_script( 'datepicker-slider' );
		wp_register_script('datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array(), WOO_VOU_PLUGIN_VERSION );
		wp_enqueue_script('datepicker-slider');
		
		wp_deregister_script( 'timepicker-addon' );
		wp_register_script('timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true);
		wp_enqueue_script('timepicker-addon');
							
		// Enqueu built-in script for color picker.
		if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
			wp_enqueue_script( 'wp-color-picker' );
		} else {
			wp_enqueue_script( 'farbtastic' );
		}
		
	}
	
	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for check code public
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_code_public_scripts(){
		
		global $post;
		
		$post_content = isset($post->post_content) ? $post->post_content : '';
		
		// add css for check code in public
		if(  has_shortcode( $post_content, 'woo_vou_check_code' ) ) {
			
			// add css for check code in public
			wp_register_style( 'woo-vou-public-check-code-style', WOO_VOU_URL . 'includes/css/woo-vou-check-code.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-public-check-code-style' );
			
			// add js for check code in public
			wp_register_script( 'woo-vou-check-code-script', WOO_VOU_URL . 'includes/js/woo-vou-check-code.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-check-code-script' );
			
			wp_localize_script( 'woo-vou-check-code-script' , 'WooVouCheck' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																						'check_code_error' 	=> __( 'Please enter voucher code.', 'woovoucher' ),
																						'code_invalid' 		=> __( 'Voucher code doest not exist.', 'woovoucher' ),
																						'code_used_success'	=> __( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' )
																					) );
		}
		
		// add js on front side
		wp_register_script( 'woo-vou-public-script', WOO_VOU_URL . 'includes/js/woo-vou-public.js', array(), WOO_VOU_PLUGIN_VERSION );
		wp_enqueue_script( 'woo-vou-public-script' );
		
		// add css on front side
		wp_register_style( 'woo-vou-public-style', WOO_VOU_URL . 'includes/css/woo-vou-public.css', array(), WOO_VOU_PLUGIN_VERSION );
		wp_enqueue_style( 'woo-vou-public-style' );
	}
	
	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for check code in admin
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_code_scripts( $hook_suffix ){
		
		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		
		if( $hook_suffix == $wc_screen_id . '_page_woo-vou-check-voucher-code' || $hook_suffix == $woo_vou_screen_id . '_page_woo-vou-check-voucher-code' ) {
			
			// add css for check code in admin
			wp_register_style( 'woo-vou-check-code-style', WOO_VOU_URL . 'includes/css/woo-vou-check-code.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-check-code-style' );
			
			// add js for check code in admin
			wp_register_script( 'woo-vou-check-code-script', WOO_VOU_URL . 'includes/js/woo-vou-check-code.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-check-code-script' );
			
			wp_localize_script( 'woo-vou-check-code-script' , 'WooVouCheck' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																						'check_code_error' 	=> __( 'Please enter voucher code.', 'woovoucher' ),
																						'code_used_success'	=> __( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ),
																						'code_invalid' 		=> __( 'Voucher code doest not exist.', 'woovoucher' )
																					) );
		}
	}

	/**
	 * style on head of page
	 * 
	 * Handles style code display when wp head initialize 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_custom_styles() {
		
		//Get custom css code
		$custom_css	= get_option( 'vou_custom_css' );
		
		if( !empty( $custom_css ) )	{//if custom css code not available
			
			echo '<style type="text/css">' . $custom_css . '</style>';
		}
	}
	
	/**
	 * Adding Hooks
	 *
	 * Adding proper hoocks for the scripts.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//add styles for new and edit post and purchased voucher code
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_popup_styles' ) );
		
		//add script for new and edit post and purchased voucher code
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_popup_scripts' ) );
		
		//add scripts for check code admin side
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_check_code_scripts' ) );
		
		//drag & drop scripts on admin head for new and edit post
		add_action( 'admin_head-post.php', array( $this, 'woo_vou_admin_drag_drop_head' ) );
		
		add_action( 'admin_head-post-new.php', array( $this, 'woo_vou_admin_drag_drop_head' ) );
		
		//drag & drop scripts for new and edit post
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_admin_drag_drop_scripts' ) );	

		if( woo_vou_is_edit_page() ) { // check metabox page
				
			//add styles for metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_metabox_styles' ) );
			
			//add styles for metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_metabox_scripts' ) );
			
		}
		
		//add scripts for check code front side
		add_action( 'wp_enqueue_scripts', array( $this, 'woo_vou_check_code_public_scripts' ) );

		//style code on wp head
		add_action( 'wp_head', array( $this, 'woo_vou_custom_styles' ) );		
	}
}