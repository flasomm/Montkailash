<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Used Voucher Code List Page
 * 
 * The html markup for the used voucher code list
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WOO_Vou_List extends WP_List_Table {

	public $model, $render, $per_page;

	function __construct() {

		global $woo_vou_model, $woo_vou_render;

		//Set parent defaults
		parent::__construct( array(
									'singular'  => 'usedvou',//singular name of the listed records
									'plural'    => 'usedvous',//plural name of the listed records
									'ajax'      => false//does this table support ajax?
								) );

		$this->model = $woo_vou_model;
		$this->render = $woo_vou_render;

		$this->per_page	= apply_filters( 'woo_vou_used_posts_per_page', 10 ); // Per page
	}

	/**
	 * Displaying Prodcuts
	 * 
	 * Does prepare the data for displaying the products in the table.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function display_used_vouchers() {

		global $current_user, $woo_vou_vendor_role;

		$prefix = WOO_VOU_META_PREFIX;

		$args = $data = array();

		// Taking parameter
		$orderby 	= isset( $_GET['orderby'] )	? urldecode( $_GET['orderby'] )		: 'ID';
		$order		= isset( $_GET['order'] )	? $_GET['order']                	: 'DESC';
		$search 	= isset( $_GET['s'] ) 		? sanitize_text_field( trim($_GET['s']) )	: null;

		$args = array(
						'posts_per_page'	=> $this->per_page,
						'page'				=> isset( $_GET['paged'] ) ? $_GET['paged'] : null,
						'orderby'			=> $orderby,
						'order'				=> $order,
						'offset'  			=> ( $this->get_pagenum() - 1 ) * $this->per_page,
						'woo_vou_list'		=> true
					);

		$args['meta_query'] = array(
										array(
													'key' 		=> $prefix . 'used_codes',
													'value' 	=> '',
													'compare' 	=> '!='
												)
									);
		//Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role

			$redeem_all	= $this->model->woo_vou_vendor_redeem_all_codes( $current_user );

			if( !$redeem_all ) {
				$args['author'] = $current_user->ID;
			}
		}

		if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
			$args['post_parent'] = $_GET['woo_vou_post_id'];
		}

		if( !empty( $search ) ) {

			$args['meta_query']	= array(
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

		//get used voucher codes data from database
		$woo_data 	= $this->model->woo_vou_get_voucher_details( $args );
		$data		= isset( $woo_data['data'] ) ? $woo_data['data'] : '';

		if( !empty( $data ) ) {

			foreach ( $data as $key => $value ) {

				$user_id 	  = get_post_meta( $value['ID'], $prefix.'redeem_by', true );
				$user_detail  = get_userdata( $user_id );
				$user_profile = add_query_arg( array('user_id' => $user_id), admin_url('user-edit.php') );
				$display_name = isset( $user_detail->display_name ) ? $user_detail->display_name : '';

				if( !empty( $display_name ) ) {
					$display_name = '<a href="'.$user_profile.'">'.$display_name.'</a>';
				} else {
					$display_name = __( 'N/A', 'woovoucher' );
				}

				$data[$key]['ID'] 			= $value['ID'];
				$data[$key]['post_parent'] 	= $value['post_parent'];
				$data[$key]['code'] 		= get_post_meta( $value['ID'], $prefix.'used_codes', true );
				$data[$key]['redeem_by'] 	= $display_name;
				$data[$key]['first_name'] 	= get_post_meta( $value['ID'], $prefix.'first_name', true );
				$data[$key]['last_name'] 	= get_post_meta( $value['ID'], $prefix.'last_name', true );
				$data[$key]['order_id'] 	= get_post_meta( $value['ID'], $prefix.'order_id', true );
				$data[$key]['order_date'] 	= get_post_meta( $value['ID'], $prefix.'order_date', true );
				$data[$key]['product_title']= get_the_title( $value['post_parent'] );

				$order_id = $data[$key]['order_id'];

				$data[$key]['buyers_info'] = $this->model->woo_vou_get_buyer_information( $order_id );
			}
		}

		$result_arr['data']		= !empty($data) ? $data : array();
		$result_arr['total'] 	= isset( $woo_data['total'] ) ? $woo_data['total'] 	: 0; // Total no of data

		return $result_arr;
	}

	/**
	 * Mange column data
	 * 
	 * Default Column for listing table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function column_default( $item, $column_name ) {

		global $current_user, $woo_vou_vendor_role;

		//Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		switch( $column_name ) {
			case 'code':
				return $item[ $column_name ];
			case 'buyers_info' :
				return $this->model->woo_vou_display_buyer_info_html( $item[ $column_name ] );
			case 'redeem_by' :
				return $item[ $column_name ];
			case 'product_info' :
				return $this->model->woo_vou_display_product_info_html( $item['order_id'], $item['code'] );				
			case 'order_info' :				
				return $this->model->woo_vou_display_order_info_html( $item['order_id'] );			
			default:
				$default_value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
				return apply_filters( 'woo_vou_used_column_value', $default_value, $item, $column_name );
		}
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item['ID']
		);
	}

	/**
	 * Display Columns
	 * 
	 * Handles which columns to show in table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function get_columns(){

		$columns = array(
							'code'			=>	__( 'Voucher Code', 'woovoucher' ),
							'product_info'	=>	__(	'Product Information', 'woovoucher' ),
							'buyers_info'	=>	__(	'Buyer\'s Information', 'woovoucher' ),
							'order_info'	=>	__(	'Order Information', 'woovoucher' ),							
							'redeem_by'		=>	__(	'Redeem By', 'woovoucher' ),
					);

		return apply_filters( 'woo_vou_used_add_column', $columns );
	}

	/**
	 * Sortable Columns
	 * 
	 * Handles soratable columns of the table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function get_sortable_columns() {

		$sortable_columns = array(
    								'code'			=>	array( 'code', true ),
						            //'product_title'	=>	array( 'product_title', true ),						            
						            'order_date'	=>	array( 'order_date', true ),
						            'order_id'		=>	array( 'order_id', true ),
						            'redeem_by'		=>	array( 'redeem_by', true ),
								);
		return apply_filters( 'woo_vou_used_add_sortable_column', $sortable_columns );
	}

	function no_items() {

		//message to show when no records in database table
		_e( 'No used voucher codes yet.', 'woovoucher' );
	}

	/**
     * Bulk actions field
     * 
     * Handles Bulk Action combo box values
     * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
     */
	function get_bulk_actions() {
		//bulk action combo box parameter
		//if you want to add some more value to bulk action parameter then push key value set in below array
		$actions = array();
		return $actions;
    }

	/**
	 * Add Filter for Sorting
	 * 
	 * Handles to add filter for sorting
	 * in listing
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function extra_tablenav( $which ) {

		if( $which == 'top' ) {

			global $current_user, $woo_vou_vendor_role;

			$prefix = WOO_VOU_META_PREFIX;
			$args	= array();

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

			if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
				$args['author']	= $current_user->ID;
			}

    		$products_data	= $this->model->woo_vou_get_products_by_voucher( $args );

    		echo '<div class="alignleft actions woo-vou-dropdown-wrapper">';?>
				<select id="woo_vou_post_id" name="woo_vou_post_id" class="chosen_select">
					<option value=""><?php _e( 'Show all products', 'woovoucher' ); ?></option><?php
					if( !empty( $products_data ) ) {
						foreach ( $products_data as $product_data ) {
							echo '<option value="' . $product_data['ID'] . '" ' . selected( isset( $_GET['woo_vou_post_id'] ) ? $_GET['woo_vou_post_id'] : '', $product_data['ID'], false ) . '>' . $product_data['post_title'] . '</option>';
						}
					}?>
				</select><?php
    		submit_button( __( 'Apply', 'woovoucher' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
			echo '</div>';
    	}
    }

	function prepare_items() {

		// Get how many records per page to show
		$per_page	= $this->per_page;

		// Get All, Hidden, Sortable columns
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		// Get final column header
		$this->_column_headers	= array( $columns, $hidden, $sortable );

		// Get Data of particular page
		$data_res 	= $this->display_used_vouchers();
		$data 		= $data_res['data'];

		// Get current page number
		$current_page = $this->get_pagenum();

		// Get total count
		$total_items  = $data_res['total'];

		// Get page items
		$this->items = $data;

		// We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
											'total_items' => $total_items,
											'per_page'    => $per_page,
											'total_pages' => ceil( $total_items/$per_page )
										) );
    }
}

global $current_user;

//Create an instance of our package class...
$WooUsedVouListTable = new WOO_Vou_List();

//Fetch, prepare, sort, and filter our data...
$WooUsedVouListTable->prepare_items();?>

<div class="wrap"><?php 

	//showing sorting links on the top of the list
	$WooUsedVouListTable->views();?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="product-filter" method="get" action="">

    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="vou-data" value="<?php echo $_REQUEST['vou-data'] ?>" />

        <!-- Search Title -->
        <?php $WooUsedVouListTable->search_box( __( 'Search', 'woovoucher' ), 'woovoucher' ); ?>

        <div class="alignright"><?php
				$generatpdfurl = add_query_arg( array( 
														'woo-vou-voucher-gen-pdf'	=> '1',
														'woo_vou_action'			=> 'used'
													));
				$exportcsvurl = add_query_arg( array( 
														'woo-vou-voucher-exp-csv'	=>	'1',
														'woo_vou_action'			=> 'used'
													));
			?>
			<a href="<?php echo $exportcsvurl; ?>" id="woo-vou-export-csv-btn" class="button-secondary woo-gen-pdf" title="<?php echo __('Export CSV','woovoucher'); ?>"><?php echo __("Export CSV",'woovoucher'); ?></a>
			<a href="<?php echo $generatpdfurl; ?>" id="woo-vou-pdf-btn" class="button-secondary" title="<?php echo __('Generate PDF','woovoucher'); ?>"><?php echo __("Generate PDF",'woovoucher'); ?></a>
		</div>
        <!-- Now we can render the completed list table -->
        <?php $WooUsedVouListTable->display(); ?>
    </form>
</div>