<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	global $post;
	
	$prefix = WOO_VOU_META_PREFIX;
		
	$woo_vou_status = get_post_meta( $post->ID, $prefix . 'editor_status', true ); //getting metabox value for setting editor status
	$woo_vou_metacontent = get_post_meta( $post->ID, $prefix . 'meta_content',true );//getting the default metabox content
	
	if( $woo_vou_status == "" || !isset( $woo_vou_status ) ) { // setting editor's value if not set
	    $woo_vou_status = 'true'; // set default true when create new voucher
	}
	$metastring = '';
	$metastring .= '<input type="hidden" id="woo_vou_editor_status" name="'.$prefix.'editor_status" value="'.$woo_vou_status.'">
					<div id="woo_vou_main_editor" class="woo_vou_main_editor" style="display:block;">
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_site_logo_btn" name="woo_vou_site_logo_btn"><div class="woo_vou_site_logo_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Site Logo From Settings', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_logo_btn" name="woo_vou_logo_btn"><div class="woo_vou_vendor_logo_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Vendor\'s Logo', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_text_btn" name="woo_vou_text_btn"><div class="woo_vou_text_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Voucher Code', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_expire_btn" name="woo_vou_expire_btn"><div class="woo_vou_expire_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Expiration Date & Time', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_venaddr_btn" name="woo_vou_venaddr_btn"><div class="woo_vou_venaddr_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Vendor\'s Address', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_siteurl_btn" name="woo_vou_siteurl_btn"><div class="woo_vou_siteurl_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Website URL', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_message_btn" name="woo_vou_message_btn"><div class="woo_vou_message_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Redeem Instructions', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_loc_btn" name="woo_vou_loc_btn"><div class="woo_vou_loc_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Voucher Locations', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_blank_btn" name="woo_vou_blank_btn"><div class="woo_vou_blank_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Blank Block', 'woovoucher' ) . '</span></span>
						<span class="woo_vou_tooltip"><a href="javascript:void(0);" class="woo_vou_main_buttons" id="woo_vou_custom_btn" name="woo_vou_custom_btn"><div class="woo_vou_custom_btn"></div></a><span class="woo_vou_classic">' . __( 'Click to add a Custom Block', 'woovoucher' ) . '</span></span>';
	$metastring .=	'</div><!--main editor-->
					<div class="clear"></div>';
	
	if( empty( $woo_vou_metacontent ) ) {
		$metastring .= '<div class="woo_vou_builder_area">' . __( 'Voucher Builder Area', 'woovoucher' ).'</div><div id="columns"><div class="woo_vou_controls" id="woo_vou_controls"></div></div>';
	} else {
		$metastring .= '<div class="woo_vou_builder_area" style="display:none;">' . __( 'Voucher Builder Area', 'woovoucher' ).'</div><div id="columns"><div class="woo_vou_controls" id="woo_vou_controls">' . $woo_vou_metacontent . '</div></div>';
	}		
		
		$metastring .=	'<div style="display:none;"><textarea name="woo_vou_meta_content" id="woo_vou_meta_content" cols="60" rows="14">' . $woo_vou_metacontent . '</textarea></div><!--update meta content-->';
		$metastring .= '<div class="woo_vou_editor" id="woo_vou_edit_form" style="display:none;">
						</div>';
	echo $metastring;
?>