jQuery( document ).ready( function( $ ) { 
	
	jQuery( document ).bind( 'show_variation', function( b, c ) {// on change variation event
		
		jQuery(".woo-vou-fields-wrapper-variation").hide();
		jQuery("#woo-vou-fields-wrapper-"+c.variation_id ).show();
	});
	
});