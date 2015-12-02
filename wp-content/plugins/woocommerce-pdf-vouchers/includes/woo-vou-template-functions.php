<?php
/**
 * Templates Functions
 *
 * Handles to manage templates of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 **/


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Returns the path to the pdf vouchers templates directory
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_get_templates_dir() {
	
	return WOO_VOU_DIR . '/includes/templates/';
	
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 * 
 */
function woo_vou_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	
	if ( ! $template_path ) $template_path = WOO_VOU_PLUGIN_BASENAME . '/';
	if ( ! $default_path ) $default_path = woo_vou_get_templates_dir();
	
	// Look within passed path within the theme - this is priority
	
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	
	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('woo_vou_locate_template', $template, $template_name, $template_path);
}

/**
 * Get other templates
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 * 
 */
function woo_vou_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	
	if ( $args && is_array($args) )
		extract( $args );

	$located = woo_vou_locate_template( $template_name, $template_path, $default_path );
		
	include( $located );
}

?>