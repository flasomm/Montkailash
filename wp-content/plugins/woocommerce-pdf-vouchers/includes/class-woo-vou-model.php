<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Model Class
 * 
 * Handles generic plugin functionality.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Model {

	public function __construct() {

	}

	/**
	 * Escape Tags & Slashes
	 * 
	 * Handles escapping the slashes and tags
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_escape_attr( $data ) {
		return esc_attr( stripslashes( $data ) );
	}

	/**
	 * Strip Slashes From Array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_escape_slashes_deep( $data = array(), $flag = false, $limited = false ) {

		if( $flag != true ) {
			$data = $this->woo_vou_nohtml_kses( $data );
		} else {
			if( $limited == true ) {
				$data = wp_kses_post( $data );
			}
		}

		$data = stripslashes_deep( $data );

		return $data;
	}

	/**
	 * Strip Html Tags
	 * 
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_nohtml_kses( $data = array() ) {

		if ( is_array( $data ) ) {
			$data = array_map( array( $this, 'woo_vou_nohtml_kses' ), $data );
		} elseif ( is_string( $data ) ) {
			$data = wp_filter_nohtml_kses( $data );
		}

		return $data;
	}

	/**
	 * Convert Object To Array
	 * 
	 * Converting Object Type Data To Array Type
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_object_to_array( $result ) {

	    $array = array();
	    foreach ( $result as $key => $value ) {
	        
	    	if( is_object( $value ) ) {
	            $array[$key] = $this->woo_vou_object_to_array( $value );
	        } else {
	        	$array[$key]=$value;
	        }
	    }

	    return $array;
	}

	/**
	 * Get Date Format
	 * 
	 * Handles to return formatted date which format is set in backend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_date_format( $date, $time = false ) {

		$format	= $time ? get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) : get_option( 'date_format' );
		$date	= date_i18n( $format, strtotime( $date ) );
		return apply_filters( 'woo_vou_get_date_format', $date );
	}

	/**
	 * Generate Random Letter
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_random_letter( $len = 1 ) {

		$alphachar		= "abcdefghijklmnopqrstuvwxyz";
		$rand_string	= substr( str_shuffle( $alphachar ), 0, $len );
		
		return apply_filters( 'woo_vou_get_random_letter', $rand_string, $len );
	}
	
	/**
	 * Generate Capital Random Letter
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.7
	 */
	public function woo_vou_get_capital_random_letter( $len = 1 ) {

		$alphachar		= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$rand_string	= substr( str_shuffle( $alphachar ), 0, $len );
		
		return apply_filters( 'woo_vou_get_capital_random_letter', $rand_string, $len );
	}

	/**
	 * Generate Random Number
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_random_number( $len = 1 ) {

		$alphanum		= "0123456789";
		$rand_number	= substr( str_shuffle( $alphanum ), 0, $len );

		return apply_filters( 'woo_vou_get_random_number', $rand_number, $len );
	}

	/**
	 * Generate Random Pattern Code
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_pattern_string( $pattern ) {

		$pattern_string = '';
		$pattern_length = strlen( trim( $pattern, ' ' ) );

		for ( $i = 0; $i < $pattern_length; $i++ ) {

			$pattern_code	= substr( $pattern, $i, 1 );

			if( $pattern_code == 'l' ) {
				$pattern_string .= $this->woo_vou_get_random_letter();
			} else if ( $pattern_code == 'L' ) {
				$pattern_string .= $this->woo_vou_get_capital_random_letter();
			} else if( strtolower( $pattern_code ) == 'd' ) {
				$pattern_string .= $this->woo_vou_get_random_number();
			}
		}

		return apply_filters( 'woo_vou_get_pattern_string', $pattern_string, $pattern );
	}

	/**
	 * Get all vouchers templates
	 * 
	 * Handles to return all vouchers templates
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_vouchers( $args = array() ) {

		$vouargs = array( 'post_type' => WOO_VOU_POST_TYPE, 'post_status' => 'publish' );

		//return only id
		if(isset($args['fields']) && !empty($args['fields'])) {
			$vouargs['fields'] = $args['fields'];
		}

		//return based on meta query
		if(isset($args['meta_query']) && !empty($args['meta_query'])) {
			$vouargs['meta_query'] = $args['meta_query'];
		}

		//show how many per page records
		if(isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
			$vouargs['posts_per_page'] = $args['posts_per_page'];
		} else {
			$vouargs['posts_per_page'] = '-1';
		}

		//get by post parent records
		if(isset($args['post_parent']) && !empty($args['post_parent'])) {
			$vouargs['post_parent']	= $args['post_parent'];
		}

		//show per page records
		if(isset($args['paged']) && !empty($args['paged'])) {
			$vouargs['paged']	= $args['paged'];
		}

		//get order by records
		$vouargs['order']	= 'DESC';
		$vouargs['orderby']	= 'date';

		//fire query in to table for retriving data
		$result = new WP_Query( $vouargs );

		if(isset($args['getcount']) && $args['getcount'] == '1') {
			$postslist = $result->post_count;
		}  else {
			//retrived data is in object format so assign that data to array for listing
			$postslist = $this->woo_vou_object_to_array($result->posts);
		}

		return $postslist;
	}

	/**
	 * Get all voucher details
	 * 
	 * Handles to return all voucher details
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_voucher_details( $args = array() ) {

		$post_status	= isset( $args['post_status'] ) ? $args['post_status'] : 'publish';

		$vouargs = array( 'post_type' => WOO_VOU_CODE_POST_TYPE, 'post_status' => $post_status );

		$vouargs = wp_parse_args( $args, $vouargs );

		//return only id
		if(isset($args['fields']) && !empty($args['fields'])) {
			$vouargs['fields'] = $args['fields'];
		}

		//return based on post ids
		if(isset($args['post__in']) && !empty($args['post__in'])) {
			$vouargs['post__in'] = $args['post__in'];
		}

		//return based on author
		if(isset($args['author']) && !empty($args['author'])) {
			$vouargs['author'] = $args['author'];
		}

		//return based on meta query
		if(isset($args['meta_query']) && !empty($args['meta_query'])) {
			$vouargs['meta_query'] = $args['meta_query'];
		}

		//show how many per page records
		if(isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
			$vouargs['posts_per_page'] = $args['posts_per_page'];
		} else {
			$vouargs['posts_per_page'] = '-1';
		}

		//get by post parent records
		if(isset($args['post_parent']) && !empty($args['post_parent'])) {
			$vouargs['post_parent']	=	$args['post_parent'];
		}

		//show per page records
		if(isset($args['paged']) && !empty($args['paged'])) {
			$vouargs['paged']	=	$args['paged'];
		}

		//get order by records
		$vouargs['order']	= 'DESC';
		$vouargs['orderby']	= 'date';

		//show how many per page records
		if(isset($args['order']) && !empty($args['order'])) {
			$vouargs['order'] = $args['order'];
		}

		//show how many per page records
		if(isset($args['orderby']) && !empty($args['orderby'])) {
			$vouargs['orderby'] = $args['orderby'];
		}

		//fire query in to table for retriving data
		$result = new WP_Query( $vouargs );		
		
		if(isset($args['getcount']) && $args['getcount'] == '1') {
			$postslist = $result->post_count;	
		} else {
			//retrived data is in object format so assign that data to array for listing
			$postslist = $this->woo_vou_object_to_array($result->posts);

			// if get list for voucher list then return data with data and total array
			if( isset($args['woo_vou_list']) && $args['woo_vou_list'] ) {

				$data_res	= array();

				$data_res['data'] 	= $postslist;

				//To get total count of post using "found_posts" and for users "total_users" parameter
				$data_res['total']	= isset($result->found_posts) ? $result->found_posts : '';

				return $data_res;
			}
		}

		return apply_filters( 'woo_vou_get_voucher_details', $postslist, $args );
	}

	/**
	 * Get all products by vouchers
	 * 
	 * Handles to return all products by vouchers
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_products_by_voucher( $args = array() ) {

		$prefix = WOO_VOU_META_PREFIX;

		$args['fields'] = 'id=>parent';

		$voucodesdata = $this->woo_vou_get_voucher_details( $args );

		$product_ids =array();
		foreach ( $voucodesdata as $voucodes ) {

			if( !in_array( $voucodes['post_parent'], $product_ids ) ) {

				$product_ids[] = $voucodes['post_parent'];
			}
		}

		if( !empty( $product_ids ) ) { // Check products ids are not empty

			$vouargs = array( 'post_type' => WOO_VOU_MAIN_POST_TYPE, 'post_status' => 'publish', 'post__in' => $product_ids );

			//display based on per page
			if( isset( $args['posts_per_page'] ) && !empty( $args['posts_per_page'] ) ) {
				$vouargs['posts_per_page'] = $args['posts_per_page'];
			} else {
				$vouargs['posts_per_page'] = '-1';
			}

			//get order by records
			$vouargs['order']	= 'DESC';
			$vouargs['orderby']	= 'date';

			//fire query in to table for retriving data
			$result = new WP_Query( $vouargs );

			if( isset( $args['getcount'] ) && $args['getcount'] == '1' ) {
				$products = $result->post_count;
			}  else {
				//retrived data is in object format so assign that data to array for listing
				$products = $this->woo_vou_object_to_array( $result->posts );
			}
			return $products;
		} else {
			return array();
		}
	}

	/**
	 * Get purchased codes by product id
	 * 
	 * Handles to get purchased codes by product id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_purchased_codes_by_product_id( $product_id ) {

		global $woo_vou_vendor_role;

		//Check product id is empty
		if( empty( $product_id ) ) return array();

		global $current_user;

		$prefix = WOO_VOU_META_PREFIX;

		$args = array( 'post_parent' => $product_id, 'fields' => 'ids' );
		$args['meta_query'] = array(
										array(
													'key' 		=> $prefix . 'purchased_codes',
													'value' 	=> '',
													'compare' 	=> '!='
												),
										array(
													'key'     	=> $prefix . 'used_codes',
													'compare' 	=> 'NOT EXISTS'
												)
									);

		//Get User roles
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
			$args['author'] = $current_user->ID;
		}

		//add filter to group by order id
		add_filter( 'posts_groupby', array( $this, 'woo_vou_groupby_order_id' ) );

		$voucodesdata = $this->woo_vou_get_voucher_details( $args );

		//remove filter to group by order id
		remove_filter( 'posts_groupby', array( $this, 'woo_vou_groupby_order_id' ) );

		$vou_code_details = array();
		if( !empty( $voucodesdata ) && is_array( $voucodesdata ) ) {

			foreach ( $voucodesdata as $vou_codes_id ) {

				// get order id
				$order_id = get_post_meta( $vou_codes_id, $prefix.'order_id', true );

				// get order date
				$order_date = get_post_meta( $vou_codes_id, $prefix.'order_date', true );

				//buyer's first name who has purchased voucher code
				$first_name = get_post_meta( $vou_codes_id, $prefix . 'first_name', true );

				//buyer's last name who has purchased voucher code
				$last_name = get_post_meta( $vou_codes_id, $prefix . 'last_name', true );

				//buyer's name who has purchased voucher code
				$buyer_name =  $first_name. ' ' .$last_name;

				$args = array( 'post_parent' => $product_id, 'fields' => 'ids' );
				$args['meta_query'] = array(
												array(
															'key' 		=> $prefix . 'purchased_codes',
															'value' 	=> '',
															'compare' 	=> '!='
														),
												array(
															'key' 		=> $prefix . 'order_id',
															'value' 	=> $order_id
														),
												array(
															'key'     	=> $prefix . 'used_codes',
															'compare' 	=> 'NOT EXISTS'
												)
											);
				$vouorderdata = $this->woo_vou_get_voucher_details( $args );

				$purchased_codes = array();
				if( !empty( $vouorderdata ) && is_array( $vouorderdata ) ) {

					foreach ( $vouorderdata as $order_vou_id ) {

						// get purchased codes
						$purchased_codes[] = get_post_meta( $order_vou_id, $prefix.'purchased_codes', true );
					}
				}

				// Check purchased codes are not empty
				if( !empty( $purchased_codes ) ) {

					$vou_code_details[] = array(
														'order_id'			=> $order_id,
														'order_date' 		=> $order_date,
														'first_name' 		=> $first_name,
														'last_name' 		=> $last_name,
														'buyer_name' 		=> $buyer_name,
														'vou_codes'			=> implode( ', ', $purchased_codes )
													);
				}
			}
		}

		return apply_filters( 'woo_vou_get_purchased_codes_by_product_id', $vou_code_details, $product_id );
	}

	/**
	 * Get used codes by product id
	 * 
	 * Handles to get used codes by product id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_used_codes_by_product_id( $product_id ) {

		//Check product id is empty
		if( empty( $product_id ) ) return array();

		global $current_user, $woo_vou_vendor_role;

		$prefix = WOO_VOU_META_PREFIX;

		$args = array( 'post_parent' => $product_id, 'fields' => 'ids' );
		$args['meta_query'] = array(
										array(
													'key' 		=> $prefix . 'used_codes',
													'value' 	=> '',
													'compare' 	=> '!='
												)
									);

		//Get User roles
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
			$args['author'] = $current_user->ID;
		}

		//add filter to group by order id
		add_filter( 'posts_groupby', array( $this, 'woo_vou_groupby_order_id' ) );

		$voucodesdata = $this->woo_vou_get_voucher_details( $args );

		//remove filter to group by order id
		remove_filter( 'posts_groupby', array( $this, 'woo_vou_groupby_order_id' ) );

		$vou_code_details = array();
		if( !empty( $voucodesdata ) && is_array( $voucodesdata ) ) {

			foreach ( $voucodesdata as $vou_codes_id ) {

				// get order id
				$order_id = get_post_meta( $vou_codes_id, $prefix.'order_id', true );

				// get order date
				$order_date = get_post_meta( $vou_codes_id, $prefix.'order_date', true );

				//buyer's first name who has purchased voucher code
				$first_name = get_post_meta( $vou_codes_id, $prefix . 'first_name', true );

				//buyer's last name who has purchased voucher code
				$last_name = get_post_meta( $vou_codes_id, $prefix . 'last_name', true );

				//buyer's name who has purchased voucher code				
				$buyer_name =  $first_name. ' ' .$last_name;

				$args = array( 'post_parent' => $product_id, 'fields' => 'ids' );
				$args['meta_query'] = array(
												array(
															'key' 		=> $prefix . 'used_codes',
															'value' 	=> '',
															'compare' 	=> '!='
														),
												array(
															'key' 		=> $prefix . 'order_id',
															'value' 	=> $order_id
														)
											);
				$vouorderdata = $this->woo_vou_get_voucher_details( $args );

				$used_codes = $redeem_by = array();
				if( !empty( $vouorderdata ) && is_array( $vouorderdata ) ) {

					foreach ( $vouorderdata as $order_vou_id ) {

						// get purchased codes
						$used_codes[] = get_post_meta( $order_vou_id, $prefix.'used_codes', true );
						$redeem_by[]  = get_post_meta( $order_vou_id, $prefix.'redeem_by', true );
					}
				}

				// Check purchased codes are not empty
				if( !empty( $used_codes ) ) {

					$vou_code_details[] = array(
														'order_id'		=> $order_id,
														'order_date' 	=> $order_date,
														'first_name' 	=> $first_name,
														'last_name' 	=> $last_name,
														'buyer_name' 	=> $buyer_name,
														'vou_codes'		=> implode( ',', $used_codes ),
														'redeem_by'		=> implode( ',', $redeem_by )
													);
				}
			}
		}

		return apply_filters( 'woo_vou_get_used_codes_by_product_id', $vou_code_details, $product_id );
	}

	/**
	 * Group By Order ID
	 *
	 * Handles to group by order id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_groupby_order_id( $groupby ) {

		global $wpdb;

	    $groupby = "{$wpdb->posts}.post_title"; // post_title is used for order id

	    return $groupby;
	}

	/**
	 * Convert Color Hexa to RGB
	 *
	 * Handles to return RGB color from hexa color
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_hex_2_rgb( $hex ) {

		$rgb = array();
		if( !empty( $hex ) ) {

			$hex = str_replace("#", "", $hex);

			if(strlen($hex) == 3) {
				$r = hexdec(substr($hex,0,1).substr($hex,0,1));
				$g = hexdec(substr($hex,1,1).substr($hex,1,1));
				$b = hexdec(substr($hex,2,1).substr($hex,2,1));
			} else {
				$r = hexdec(substr($hex,0,2));
				$g = hexdec(substr($hex,2,2));
				$b = hexdec(substr($hex,4,2));
			}
			$rgb = array($r, $g, $b);
		}

		return apply_filters( 'woo_vou_hex_2_rgb', $rgb, $hex ); // returns an array with the rgb values
	}

	/**
	 * Get All voucher order details
	 * 
	 * Handles to return all voucher order details
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_all_ordered_data( $orderid ) {

		$prefix = WOO_VOU_META_PREFIX;

		$data = get_post_meta( $orderid, $prefix.'meta_order_details', true );
		return apply_filters( 'woo_vou_all_ordered_data', $data );
	}

	/**
	 * Update Duplicate Post Metas
	 * 
	 * Handles to update all old vous meta to 
	 * duplicate meta
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_dupd_post_meta( $old_id, $new_id ) {

		// set prefix for meta fields 
		$prefix = WOO_VOU_META_PREFIX;

		// get all post meta for vou
		$meta_fields = get_post_meta( $old_id );

		// take array to store metakeys of old vou
		$meta_keys = array();

		foreach ( $meta_fields as $metakey => $matavalues ) {
			// meta keys store in a array
			$meta_keys[] = $metakey;
		}

		foreach ( $meta_keys as $metakey ) {

			// get metavalue from metakey
			$meta_value = get_post_meta( $old_id, $metakey, true );

			// update meta values to new duplicate vou meta
			update_post_meta( $new_id, $metakey, $meta_value );
		}
	}

	/**
	 * Create Duplicate Voucher
	 * 
	 * Handles to create duplicate voucher
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_dupd_create_duplicate_vou( $vou_id ) {

			// get the vou data
			$vou = get_post( $vou_id );

			$prefix = WOO_VOU_META_PREFIX;

			// start process to create a new vou
			$suffix = __( '(Copy)', 'woovoucher' );

			// get post table data
			$post_author   			= $vou->post_author;
			$post_date      		= current_time('mysql');
			$post_date_gmt 			= get_gmt_from_date($post_date);
			$post_type				= $vou->post_type;
			$post_parent			= $vou->post_parent;
			$post_content    		= str_replace("'", "''", $vou->post_content);
			$post_content_filtered 	= str_replace("'", "''", $vou->post_content_filtered);
			$post_excerpt    		= str_replace("'", "''", $vou->post_excerpt);
			$post_title      		= str_replace("'", "''", $vou->post_title).' '.$suffix;
			$post_name       		= str_replace("'", "''", $vou->post_name);
			$post_comment_status  	= str_replace("'", "''", $vou->comment_status);
			$post_ping_status     	= str_replace("'", "''", $vou->ping_status);

			// get the column keys
		    $post_data = array(
					            'post_author'			=>	$post_author,
					            'post_date'				=>	$post_date,
					            'post_date_gmt'			=>	$post_date_gmt,
					            'post_content'			=>	$post_content,
					            'post_title'			=>	$post_title,
					            'post_excerpt'			=>	$post_excerpt,
					            'post_status'			=>	'draft',
					            'post_type'				=>	WOO_VOU_POST_TYPE,
					            'post_content_filtered'	=>	$post_content_filtered,
					            'comment_status'		=>	$post_comment_status,
					            'ping_status'			=> 	$post_ping_status,
					            'post_password'			=>	$vou->post_password,
					            'to_ping'				=>	$vou->to_ping,
					            'pinged'				=>	$vou->pinged,
					            'post_modified'			=>	$post_date,
					            'post_modified_gmt'		=>	$post_date_gmt,
					            'post_parent'			=>	$post_parent,
					            'menu_order'			=>	$vou->menu_order,
					            'post_mime_type'		=>	$vou->post_mime_type
				       		);

			// returns the vou id if we successfully created that vou
			$post_id = wp_insert_post( $post_data );

			//update vous meta values
			$this->woo_vou_dupd_post_meta( $vou->ID, $post_id );

			// if successfully created vou than redirect to main page
			wp_redirect( add_query_arg( array( 'post_type' => WOO_VOU_POST_TYPE, 'action' => 'edit', 'post' => $post_id ), admin_url( 'post.php' ) ) );

			// to avoid junk
			exit;
	}

	/**
	 * Check Enable Voucher
	 * 
	 * Handles to check enable voucher using product id
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_enable_voucher( $productid, $variation_id = false ) {

		$enable	= false;

		if( !empty( $productid ) ) { // Check product id is not empty

			$prefix = WOO_VOU_META_PREFIX;

			//enable voucher
			$enable_vou = get_post_meta( $productid, $prefix.'enable', true );

			// If variation id
			if(!empty($variation_id) ) {

				$is_downloadable = get_post_meta( $variation_id, '_downloadable', true );

			} else { // is downloadable

				$is_downloadable = get_post_meta( $productid, '_downloadable', true );
			}

			// Check enable voucher meta & product is downloadable
			// Check Voucher codes are not empty 
			if( $enable_vou == 'yes' && $is_downloadable == 'yes' ) { // Check enable voucher meta & product is downloadable

				$enable	= true;
			}
		}

		return apply_filters( 'woo_vou_check_enable_voucher', $enable, $productid, $variation_id );
	}

	/**
	 * Get User Details by order id
	 * 
	 * Handles to get user details by order id
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_payment_user_info( $order_id ) {

		$userdata = array();
		if( !empty( $order_id ) ) { // Check order id is not empty

			$order = new WC_Order( $order_id );

			$userdata['first_name'] = isset( $order->billing_first_name ) ? $order->billing_first_name : '';
			$userdata['last_name'] 	= isset( $order->billing_last_name ) ? $order->billing_last_name : '';
			$userdata['email'] 		= isset( $order->billing_email ) ? $order->billing_email : '';
		}

		return apply_filters( 'woo_vou_get_payment_user_info', $userdata, $order_id );
	}

	/**
	 * Get Voucher Keys
	 * 
	 * Handles to get voucher keys
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_multi_voucher_key( $order_id = '', $product_id = '', $item_id = '' ) {

		$voucher_keys	= array();
		$vouchers		= $this->woo_vou_get_multi_voucher( $order_id, $product_id, $item_id );

		if( !empty( $vouchers ) ) {

			$voucher_keys	= array_keys( $vouchers );
		}

		return apply_filters( 'woo_vou_get_multi_voucher_key', $voucher_keys, $order_id, $product_id, $item_id );
	}

	/**
	 * Get Vouchers
	 * 
	 * Handles to get vouchers
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_multi_voucher( $order_id = '', $product_id = '', $item_id = '' ) {

		$prefix = WOO_VOU_META_PREFIX;

		//Get voucher codes
		$codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );

		$codes			= !empty( $codes ) ? explode( ', ', $codes ) : array();
		$vouchers		= array();

		if( !empty( $codes ) ) {

			$key	= 1;
			foreach ( $codes as $code ) {

				$vouchers['woo_vou_pdf_'.$key]	= $code;
				$key++;
			}
		}

		return apply_filters( 'woo_vou_get_multi_voucher', $vouchers, $order_id, $product_id, $item_id );
	}

	/**
	 * Get the current date from timezone
	 * 
	 * Handles to get current date
	 * acording to timezone setting
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_current_date( $format = 'Y-m-d H:i:s' ) { 

		if( !empty( $format ) ) {
			$date_time = date( $format, current_time('timestamp') );
		} else {
			$date_time = date( 'Y-m-d H:i:s', current_time('timestamp') );
		}

		return apply_filters( 'woo_vou_current_date', $date_time, $format );
	}

	/**
	 * Get the product details from order id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_product_details( $orderid, $items = array() ) {

		// If order id is empty then return
		if( empty($orderid) ) return false;

		// Taking some defaults
		$result_item = array();

		//Get Order
		$woo_order	= new WC_Order( $orderid );

		// If item is empty or not passed then get from order.
		if( empty($items) || !is_array($items) ) {
			$woo_order_details 	= $woo_order;
			$items 				= $woo_order_details->get_items();
		}

		if( !empty( $items ) ) {

			foreach ( $items as $item_key => $item_val ) {

				if( isset( $item_val['product_id'] ) || $item_val['variation_id'] ) {

					if( !empty( $item_val['variation_id'] ) ) {
						$product_id = $item_val['variation_id'];
					} else {
						$product_id = $item_val['product_id'];
					}

					//Product name
					$result_item[$product_id]['product_name']	= !empty($item_val['name']) ? $item_val['name'] : '';
					$result_item[$product_id]['item_key'] 		= $item_key;

					//Product price
					if( !empty( $item_val['qty'] ) ) {
						$product_price = ( $item_val['line_total'] / $item_val['qty'] );
					} else {
						$product_price = '';
					}

					$result_item[$product_id]['product_price']	= $product_price;
					$result_item[$product_id]['product_formated_price']	= $this->woo_vou_get_formatted_product_price( $orderid, $item_val);

					// Total order price
					$result_item[$product_id]['product_price_total'] = isset($item_val['line_total']) ? $item_val['line_total'] : '';

					$result_item[$product_id]['recipient_name'] = isset($item_val['woo_vou_recipient_name']) ? $item_val['woo_vou_recipient_name'] : '';
					$result_item[$product_id]['recipient_email'] = isset($item_val['woo_vou_recipient_email']) ? $item_val['woo_vou_recipient_email'] : '';
					$result_item[$product_id]['recipient_message'] = isset($item_val['woo_vou_recipient_message']) ? $item_val['woo_vou_recipient_message'] : '';
				}
			}
		} // End of if 

		return apply_filters( 'woo_vou_get_product_details', $result_item, $orderid, $items );
	}

	/**
	 * Gets Order product Price in voucher code
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_get_formatted_product_price( $orderid, $item, $tax_display = '' ) {

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//Get Order
		$woo_order	= new WC_Order( $orderid );

		if ( ! $tax_display ) {
			$tax_display = $woo_order->tax_display_cart;
		}

		if ( ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal_tax'] ) ) {
			return '';
		}

		//get multipdf option in ordermeta
		$multiple_pdf	= get_post_meta( $orderid, $prefix . 'multiple_pdf', true );

		//Get Item quantity
		$item_qty	= isset( $item['qty'] ) ? $item['qty'] : '';

		if ( 'excl' == $tax_display ) {

			$ex_tax_label	= $woo_order->prices_include_tax ? 1 : 0;
			$line_subtotal	= $woo_order->get_line_subtotal( $item );
			if( $multiple_pdf == 'yes' && !empty( $item_qty ) ) {
				$line_subtotal	= $line_subtotal/$item_qty;
			}
			$subtotal		= wc_price( $line_subtotal, array( 'ex_tax_label' => $ex_tax_label, 'currency' => $woo_order->get_order_currency() ) );
		} else {

			$line_subtotal	= $woo_order->get_line_subtotal( $item, true );
			if( $multiple_pdf == 'yes' && !empty( $item_qty ) ) {
				$line_subtotal	= $line_subtotal/$item_qty;
			}
			$subtotal = wc_price( $line_subtotal, array( 'currency' => $woo_order->get_order_currency() ) );
		}

		return apply_filters( 'woo_vou_get_formatted_product_price', $subtotal, $orderid, $item, $tax_display );
	}

	/**
	 * Get the vendor detail to store in order meta
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.5
	 **/
	public function woo_vou_get_vendor_detail( $productid , $vendor_user ) {

		global $woo_vou_vendor_role;

		$prefix			= WOO_VOU_META_PREFIX;
		$vendor_detail	= array();

		$user_data		= get_userdata( $vendor_user );
		$user 			= get_user_by( 'id', $vendor_user );

		//Get User roles
		$user_roles	= isset( $user_data->roles ) ? $user_data->roles : array();
		$user_role	= array_shift( $user_roles );

		//Vendor Logo
		$vendor_logo 	 = get_post_meta( $productid, $prefix.'logo', true );
		//Vendor Address
		$vendor_address  = get_post_meta( $productid, $prefix.'address_phone', true );
		//Website URL
		$website_url 	 = get_post_meta( $productid, $prefix.'website', true );
		//Redeem Instructions
		$how_to_use 	 = get_post_meta( $productid, $prefix.'how_to_use', true );
		//Locations
		$avail_locations = get_post_meta( $productid, $prefix.'avail_locations', true );
		//Usability
		$using_type 	= get_post_meta( $productid, $prefix.'using_type', true );
		//PDF Template
		$pdf_template    = get_post_meta( $productid, $prefix.'pdf_template', true );

		// check if user id is not empty and user role is vendor
		if( !empty( $vendor_user ) && $user->has_cap('woo_vendor_options') ) {

			if( empty( $vendor_logo['src'] ) ) {
				$vendor_logo['src']	= get_user_meta( $vendor_user, $prefix.'logo', true );
			}

			if( empty( $vendor_address )  ){
				$vendor_address		= get_user_meta( $vendor_user, $prefix.'address_phone', true );			
			}

			if( empty( $website_url ) ){
				$website_url		= get_user_meta( $vendor_user, $prefix.'website', true );			
			}

			if( empty( $how_to_use ) ){
				$how_to_use			= get_user_meta( $vendor_user, $prefix.'how_to_use', true );			
			}

			if( empty( $avail_locations ) ) {
				$avail_locations	= get_user_meta( $vendor_user, $prefix.'avail_locations', true );			
			}

			if( $using_type == '' ){
				$using_type			= get_user_meta( $vendor_user, $prefix.'using_type', true );			
			}

			if( empty( $pdf_template ) ){
				$pdf_template		= get_user_meta( $vendor_user, $prefix.'pdf_template', true );
			}
		}

		// If using type is blank then take it from setting
		if( $using_type == '' ) {
			$using_type = get_option('vou_pdf_usability');
		}

		$vendor_detail = array(
								'vendor_logo'		=> $vendor_logo,
								'vendor_address'	=> $vendor_address,
								'vendor_website'	=> $website_url,
								'how_to_use'		=> $how_to_use,
								'avail_locations'	=> $avail_locations,
								'using_type'		=> $using_type,
								'pdf_template'		=> $pdf_template
							);

		return apply_filters( 'woo_vou_get_vendor_detail', $vendor_detail, $productid, $vendor_user );
	}

	/**
	 * Check to get voucher codes from variations or from product meta
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 **/
	public function woo_vou_get_voucher_code( $productid, $variation_id = false ) {

		$prefix = WOO_VOU_META_PREFIX;
		$vou_codes = '';
		
		//get voucher codes
		$vou_codes = get_post_meta( $productid, $prefix.'codes', true );

		// If variation id
		if( !empty( $variation_id ) ) {

			$vou_is_var = get_post_meta( $productid, $prefix.'is_variable_voucher', true );

			// if voucher codes set at variation level then get it from there
			if( $vou_is_var ) {
				$vou_codes = get_post_meta( $variation_id, $prefix.'codes', true );
			}
		}

		//trim voucher codes
		$vou_codes = trim( $vou_codes );
		
		return apply_filters( 'woo_vou_get_voucher_code', $vou_codes, $productid, $variation_id );
	}

	/**
	 * Check and Update voucher codes into variations or in product meta
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	public function woo_vou_update_voucher_code( $productid, $variation_id = false, $voucodes = '' ) {

		$prefix = WOO_VOU_META_PREFIX;
		$woo_vou_var_flag = false;

		// If variation id
		if( !empty( $variation_id ) ) {

			$vou_is_var = get_post_meta( $productid, $prefix.'is_variable_voucher', true );

			// if voucher codes set at variation level and get it from there
			if( $vou_is_var ) {
				$woo_vou_var_flag = true;
				update_post_meta( $variation_id, $prefix.'codes', trim( $voucodes ) );
			}
		}

		// if product is simple or variable but there is no voucher code set on variation level 
		if( $woo_vou_var_flag != true ) { 
			update_post_meta( $productid, $prefix.'codes', trim( $voucodes ) );
		}
	}

	/**
	 * Set the orderid as global
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 **/
	public function woo_vou_get_orderid_for_page() {

		global $vou_order;

		//Set OrderId Blank
		$order_id 	= '';

		// Order id get from order detail page
		$order_recieved_id = get_query_var( 'order-received' );

		// Order id get from view order page
		$order_view_id = get_query_var( 'view-order' );

		if( !empty( $order_recieved_id ) ) { 	// If on order detail page
			$order_id	= $order_recieved_id;
		} else if( !empty( $order_view_id ) ) { // If on view order page
			$order_id	= $order_view_id;
		} else if( !empty( $vou_order ) ) {		// If global order id is set
			$order_id	= $vou_order;
		}

		return apply_filters( 'woo_vou_get_orderid_for_page', $order_id );
	}

	/**
	 * Get variation detail from order and item id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.2.2
	 **/
	public function woo_vou_get_variation_data( $woo_order = array(), $item_key = '' ) {

		//Get product order meta using meta_key
		$order = new WC_Order($woo_order->id);
		$product_item_meta	= $order->get_item_meta( $item_key );
		
		$items	= $order->get_items();
		
		$item_array	= $this->woo_vou_get_item_data_using_item_key( $items, $item_key );
		
		$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
		$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();
		$_product 	= $order->get_product_from_item( $item );
		
		//Get variation data without recipient fields
		$variation_data = $this->woo_vou_display_product_item_name( $item, $_product, true );

		return apply_filters( 'woo_vou_get_variation_data', $variation_data, $order, $item_key );
	}

	/**
	 * Get variation Data From Item Key
	 * 
	 * Handle to get variation recipient data from order item key
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.2.2
	 **/
	public function woo_vou_get_recipient_data_using_item_key( $order, $item_key = '' ) {

		//Get product order meta using meta_key
		$product_item_meta	= $order->get_item_meta( $item_key );

		//Get variation data without recipient fields
		$variation_data = $this->woo_vou_get_recipient_data( $product_item_meta );

		return apply_filters( 'woo_vou_get_recipient_data_using_item_key', $variation_data, $item_key );
	}

	/**
	 * Get product recipient meta setting
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 **/
	public function woo_vou_get_product_recipient_meta( $product_id = '' ) {

		//Prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//default recipient data
		$recipient_data	= array(
								'enable_recipient_name'			=> '',
								'recipient_name_lable'			=> __( 'Recipient Name', 'woovoucher' ),
								'recipient_name_max_length'		=> '',
								'recipient_name_is_required'	=> '',

								'enable_recipient_email'		=> '',
								'recipient_email_label'			=> __( 'Recipient Email', 'woovoucher' ),
								'recipient_email_is_required'	=> '',

								'enable_recipient_message'		=> '',
								'recipient_message_label'		=> __( 'Recipient Message', 'woovoucher' ),
								'recipient_message_max_length'	=> '',
								'recipient_message_is_required'	=> ''
							);

		if( !empty( $product_id ) ) {

			//recipient name fields
			$recipient_data['enable_recipient_name']		= get_post_meta( $product_id, $prefix.'enable_recipient_name', true );

			$recipient_name_lable	= get_post_meta( $product_id, $prefix.'recipient_name_label', true );
			$recipient_name_lable	= !empty( $recipient_name_lable ) ? $recipient_name_lable : __( 'Recipient Name', 'woovoucher' );

			$recipient_data['recipient_name_lable']			= $recipient_name_lable;
			$recipient_data['recipient_name_max_length']	= get_post_meta( $product_id, $prefix.'recipient_name_max_length', true );
			$recipient_data['recipient_name_is_required']	= get_post_meta( $product_id, $prefix.'recipient_name_is_required', true );

			//recipient email fields
			$recipient_data['enable_recipient_email']		= get_post_meta( $product_id, $prefix.'enable_recipient_email', true );

			$recipient_email_label	= get_post_meta( $product_id, $prefix.'recipient_email_label', true );
			$recipient_email_label	= !empty( $recipient_email_label ) ? $recipient_email_label : __( 'Recipient Email', 'woovoucher' );

			$recipient_data['recipient_email_label']		= $recipient_email_label;
			$recipient_data['recipient_email_is_required']	= get_post_meta( $product_id, $prefix.'recipient_email_is_required', true );

			//recipient message fields
			$recipient_data['enable_recipient_message']		= get_post_meta( $product_id, $prefix.'enable_recipient_message', true );

			$recipient_message_label	= get_post_meta( $product_id, $prefix.'recipient_message_label', true );
			$recipient_message_label	= !empty( $recipient_message_label ) ? $recipient_message_label : __( 'Recipient Message', 'woovoucher' );

			$recipient_data['recipient_message_label']		= $recipient_message_label;
			$recipient_data['recipient_message_max_length']	= get_post_meta( $product_id, $prefix.'recipient_message_max_length', true );
			$recipient_data['recipient_message_is_required']= get_post_meta( $product_id, $prefix.'recipient_message_is_required', true );
		}

		return apply_filters( 'woo_vou_get_product_recipient_meta', $recipient_data, $product_id );
	}

	/**
	 * Check Enable Voucher
	 * 
	 * Handles to check enable voucher using product id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_check_enable_recipient( $productid ) {

		$enable	= false;

		if( !empty( $productid ) ) { // Check product id is not empty

			$prefix = WOO_VOU_META_PREFIX;

			$is_enable_rec_name		= get_post_meta( $productid, $prefix . 'enable_recipient_name', true );
			$is_enable_rec_email	= get_post_meta( $productid, $prefix . 'enable_recipient_email', true );
			$is_enable_rec_msg		= get_post_meta( $productid, $prefix . 'enable_recipient_message', true );

			if( $is_enable_rec_name == 'yes' || $is_enable_rec_email == 'yes' || $is_enable_rec_msg == 'yes' ) {
				$enable	= true;
			}
		}

		return apply_filters( 'woo_vou_check_enable_recipient', $enable, $productid );
	}

	/**
	 * Check item is already exist in order
	 * 
	 * Handles to check the item is already exist in order or not
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_order_item_exist( $order_items, $item_id, $data_id ) {

		if( !empty( $order_items ) ) {//If order itemd are empty

			foreach ( $order_items as $item_key => $order_item ) {

				if( $item_key == $item_id ) {
					continue;
				}

				//get product id
				$productid = isset( $order_item['product_id'] ) ? $order_item['product_id'] : '';

				//taking variation id
				$variation_id = !empty($order_item['variation_id']) ? $order_item['variation_id'] : '';

				//if product is variable product take variation id else product id
				$item_data_id = ( !empty($variation_id) ) ? $variation_id : $productid;

				if( $item_data_id == $data_id ) {//If item already exist
					return true;
				}
			}//end foreach
		}

		return false;
	}

	/**
	 * Check item is already exist in order
	 * 
	 * Handles to check the item is already exist in order or not
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_generate_pdf_voucher( $email = '', $product_id = '', $download_id = '', $order_id = '', $item_id = '' ) {

		$prefix	= WOO_VOU_META_PREFIX;

		$vou_codes_key	= $this->woo_vou_get_multi_voucher_key( $order_id, $product_id, $item_id );

		//Get mutiple pdf option from order meta
		$multiple_pdf = empty( $order_id ) ? '' : get_post_meta( $order_id, $prefix . 'multiple_pdf', true );

		$orderdvoucodes = array();

		if( !empty( $multiple_pdf ) ) {

			$orderdvoucodes = $this->woo_vou_get_multi_voucher( $order_id , $product_id, $item_id );
		}

		// Check out voucher download key
		if( in_array( $download_id, $vou_codes_key ) || $download_id == 'woo_vou_pdf_1' ) {

			//product voucher pdf
			woo_vou_process_product_pdf( $product_id, $order_id, $item_id, $orderdvoucodes );
		}
	}

	/**
	 * Get Recipient Data
	 * 
	 * Handles to replace recipient data in gift notification email and 
	 * in downloaded pdf for recipient name, email, message
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.2.2
	 */
	public function woo_vou_get_recipient_data( $product_item_meta = array() ) {

		$prefix	= WOO_VOU_META_PREFIX;

		//initilize recipient array
		$recipient_details	= array();

		if( !empty( $product_item_meta ) ) {

			//Get recipient name from orders
			if( !empty( $product_item_meta[$prefix.'recipient_name'] ) && !empty( $product_item_meta[$prefix.'recipient_name'][0] ) ) {

				if( is_serialized( $product_item_meta[$prefix.'recipient_name'][0] ) ) { // for new orders

					$recipient_name_field	= maybe_unserialize( $product_item_meta[$prefix.'recipient_name'][0] );
					$recipient_details['recipient_name'] = isset( $recipient_name_field['value'] ) ? $recipient_name_field['value'] : '';
				} else { // for old orders
					$recipient_details['recipient_name'] = $product_item_meta[$prefix.'recipient_name'][0];
				}
			}

			//Get recipient email from orders
			if( !empty( $product_item_meta[$prefix.'recipient_email'] ) && !empty( $product_item_meta[$prefix.'recipient_email'][0] ) ) {

				if( is_serialized( $product_item_meta[$prefix.'recipient_email'][0] ) ) { // for new orders

					$recipient_email_field	= maybe_unserialize( $product_item_meta[$prefix.'recipient_email'][0] );
					$recipient_details['recipient_email'] = isset( $recipient_email_field['value'] ) ? $recipient_email_field['value'] : '';
				} else { // for old orders
					$recipient_details['recipient_email'] = $product_item_meta[$prefix.'recipient_email'][0];
				}
			}

			//Get recipient message from orders
			if( !empty( $product_item_meta[$prefix.'recipient_message'] ) && !empty( $product_item_meta[$prefix.'recipient_message'][0] ) ) {

				if( is_serialized( $product_item_meta[$prefix.'recipient_message'][0] ) ) { // for new orders

					$recipient_msg_field	= maybe_unserialize( $product_item_meta[$prefix.'recipient_message'][0] );
					$recipient_details['recipient_message'] = isset( $recipient_msg_field['value'] ) ? $recipient_msg_field['value'] : '';
				} else { // for old orders
					$recipient_details['recipient_message'] = $product_item_meta[$prefix.'recipient_message'][0];
				}
			}
		}

		return apply_filters( 'woo_vou_get_recipient_data', $recipient_details, $product_item_meta );
	}

	/**
	 * Display Product Item Name
	 * 
	 * Handles to display product item name
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.2.2
	 */
	public function woo_vou_display_product_item_name( $item = array(), $product = array(), $filter_recipient = false ) {

		$prefix	= WOO_VOU_META_PREFIX;

		$product_item_meta	= isset( $item['item_meta'] ) ? $item['item_meta'] : array();
		$product_item_name	= '';
		
		$product_id					= isset( $product_item_meta['_product_id'] ) ? $product_item_meta['_product_id'] : '';
		$product_recipient_lables	= $this->woo_vou_get_product_recipient_meta( $product_id );

		if( !empty( $product_item_meta ) ) { // if not empty product meta

			// this is added due to skip depricted function get_formatted_legacy from woocommerce
			if( !defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}

			//Item meta object
			//$item_meta_object	= new WC_Order_Item_Meta( $item, $product );
			$item_meta_object	= new WC_Order_Item_Meta( $product_item_meta );

			//Get product variations
			$product_variations	= $item_meta_object->get_formatted();
			//$product_variations	= $this->woo_vou_get_formatted_legacy( '_', $item_meta_object );

			if( $filter_recipient ) { // If you want to hide some of variations using filter

				$product_variations = apply_filters( 'woo_vou_hide_recipient_variations', $product_variations, $product_item_meta );

			} else { // Displaying old order variations

				//Get recipient name from old orders
				if( !empty( $product_item_meta[$prefix.'recipient_name'] ) && !empty( $product_item_meta[$prefix.'recipient_name'][0] ) && !is_serialized( $product_item_meta[$prefix.'recipient_name'][0] ) ) {

					$recipient_name_lbl	= $product_recipient_lables['recipient_name_lable'];

					$product_variations[$recipient_name_lbl] = array(
																	'label'	=> $recipient_name_lbl,
																	'value'	=> $product_item_meta[$prefix.'recipient_name'][0]
																);
				}

				//Get recipient email from old orders
				if( !empty( $product_item_meta[$prefix.'recipient_email'] ) && !empty( $product_item_meta[$prefix.'recipient_email'][0] ) && !is_serialized( $product_item_meta[$prefix.'recipient_email'][0] ) ) {

					$recipient_email_lbl	= $product_recipient_lables['recipient_email_label'];

					$product_variations[$recipient_email_lbl] = array(
																	'label'	=> $recipient_email_lbl,
																	'value'	=> $product_item_meta[$prefix.'recipient_email'][0]
																);
				}

				//Get recipient message from old orders
				if( !empty( $product_item_meta[$prefix.'recipient_message'] ) && !empty( $product_item_meta[$prefix.'recipient_message'][0] ) && !is_serialized( $product_item_meta[$prefix.'recipient_message'][0] ) ) {

					$recipient_msg_lbl	= $product_recipient_lables['recipient_message_label'];

					$product_variations[$recipient_msg_lbl] = array(
																	'label'	=> $recipient_msg_lbl,
																	'value'	=> $product_item_meta[$prefix.'recipient_message'][0]
																);
				}
			}

			//Hide variation from item
			$product_variations = apply_filters( 'woo_vou_hide_item_variations', $product_variations );

			// Create variations Html
			if( !empty( $product_variations ) ) {

				//variation display format
				$variation_param_string = apply_filters( 'woo_vou_variation_name_string_format', '<br /><strong>%1$s</strong>: %2$s', $product_item_meta );

				foreach ( $product_variations as $product_variation ) {
					$product_item_name .= sprintf( $variation_param_string, $product_variation['label'], $product_variation['value'] );
				}
			}
		}

		return apply_filters( 'woo_vou_display_product_item_name', $product_item_name, $product_item_meta, $filter_recipient );
	}

	/**
	 * Get Product Meta Legacy Params
	 * 
	 * Handles to get product meta legacy params
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.4
	 */
	/*public function woo_vou_get_formatted_legacy( $hideprefix = '_', $item_meta_object = array() ) {

		$formatted_meta = array();

		foreach ( $item_meta_object->meta as $meta_key => $meta_values ) {
			if ( empty( $meta_values ) || ( ! empty( $hideprefix ) && substr( $meta_key, 0, 1 ) == $hideprefix ) ) {
				continue;
			}
			foreach ( (array) $meta_values as $meta_value ) {
				// Skip serialised meta
				if ( is_serialized( $meta_value ) ) {
					continue;
				}

				$attribute_key = urldecode( str_replace( 'attribute_', '', $meta_key ) );

				// If this is a term slug, get the term's nice name
				if ( taxonomy_exists( $attribute_key ) ) {
					$term = get_term_by( 'slug', $meta_value, $attribute_key );
					if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
						$meta_value = $term->name;
					}
				}

				// Unique key required
				$formatted_meta_key = $meta_key;
				$loop               = 0;
				while ( isset( $formatted_meta[ $formatted_meta_key ] ) ) {
					$loop ++;
					$formatted_meta_key = $meta_key . '-' . $loop;
				}

				$formatted_meta[ $formatted_meta_key ] = array(
					'key'   => $meta_key,
					'label' => wc_attribute_label( $attribute_key, $item_meta_object->product ),
					'value' => apply_filters( 'woocommerce_order_item_display_meta_value', $meta_value ),
				);
			}
		}

		return apply_filters( 'woo_vou_get_formatted_legacy', $formatted_meta, $hideprefix, $item_meta_object );
	}*/

	/**
	 * Vendor Sale Notification
	 * 
	 * Handles to send vendor sale notification
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.5
	 */
	public function woo_vou_vendor_sale_notification( $product_id = '', $variation_id = '', $item_key = '', $item_data = '', $order_id = '', $order = array() ) {

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		$data_id	= !empty( $variation_id ) ? $variation_id : $product_id;

		//vendor email notification code
		$vendor_user	= get_post_meta( $product_id, $prefix . 'vendor_user', true );

		if( !empty( $vendor_user ) ) { // Check vendor user is not empty

			// get user vendor sale email notification settings
			$vendor_sale_email_notification = get_user_meta( $vendor_user, $prefix.'enable_vendor_sale_email_notification', true );

			//Get vendor sale enabled option
			$vendor_sale_settings		= get_option( 'woocommerce_woo_vou_vendor_sale_notification_settings' );
			$global_vendor_sale_enabled	= isset( $vendor_sale_settings['enabled'] ) ? $vendor_sale_settings['enabled'] : '';

			$send_email = true;

			// if global settings is disable then check for user settings
			if( $global_vendor_sale_enabled != "yes" ) {

				if( empty( $vendor_sale_email_notification ) )
					$send_email = false;
			}

			if( $send_email ) {

				$product_title		= get_the_title( $product_id );
				$site_name			= get_bloginfo( 'name' );
				$vendor_user_data	= get_user_by( 'id', $vendor_user );
				$vendor_email		= isset( $vendor_user_data->user_email ) ? $vendor_user_data->user_email : '';
				$product_details	= $this->woo_vou_get_product_details( $order_id );
				$variation_data 	= $this->woo_vou_get_variation_data( $order, $item_key );
				$product_title 		= $product_title . $variation_data;
				$product_price		= !empty($product_details[$data_id]['product_formated_price']) ? $product_details[$data_id]['product_formated_price'] : '';

				//Get voucher code from item meta
				$allcodes			= wc_get_order_item_meta( $item_key, $prefix.'codes' );

				//Get All Data for vendor notify
				$vendor_data	= array(
										'site_name'		=> $site_name,
										'product_title'	=> $product_title,
										'voucher_code'	=> $allcodes,
										'product_price'	=> $product_price,
										'vendor_email'	=> $vendor_email,
										'order_id'		=> $order_id,
									);

				//Fires when sale notify to vendor.
				do_action( 'woo_vou_vendor_sale_email', $vendor_data );
			}
		}
	}

	/**
	 * Voucher Get Buyer Information
	 * 
	 * Handles to get buyer information
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_get_buyer_information( $order_id = '' ) {

		$buyer_details	= array();

		if( $order_id ) {

			// get order detail
			$order = new WC_Order( $order_id );			

			// buyer's details array
			$buyer_details = array(
				'first_name' => $order->billing_first_name,
				'last_name'  => $order->billing_last_name,					
				'address_1'  => $order->billing_address_1,
				'address_2'  => $order->billing_address_2,
				'city'       => $order->billing_city,
				'state'      => $order->billing_state,
				'postcode'   => $order->billing_postcode,
				'country'    => $order->billing_country,
				'email'      => $order->billing_email,
				'phone'      => $order->billing_phone
			);
		}

		return apply_filters( 'woo_vou_get_buyer_information', $buyer_details, $order );
	}

	/**
	 * Get Item Data From Voucher Code
	 * 
	 * Handles to get voucher data using
	 * voucher codes from order items
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_get_item_data_using_voucher_code( $order_items, $voucode ) {

		$prefix = WOO_VOU_META_PREFIX;

		//initilize item
		$return_item	= array( 'item_id' => '', 'item_data' => array() );

		if( !empty( $order_items ) ) {//if items are not empty

			
			foreach ( $order_items as $item_id => $item ) {

				$voucher_codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );

				//vouchers data of pdf
				$voucher_codes	= !empty( $voucher_codes ) ? explode( ',', $voucher_codes ) : array();
				$voucher_codes	= array_map( 'trim', $voucher_codes );
				$check_code		= trim( $voucode );

				if( in_array( $check_code, $voucher_codes ) ) { 

					$return_item['item_id']		= $item_id;
					$return_item['item_data']	= $item;
					break;
				}
			}
		}

		return apply_filters( 'woo_vou_get_item_data_using_voucher_code', $return_item, $order_items, $voucode );
	}

	/**
	 * Get Item Data From Item ID
	 * 
	 * Handles to get voucher data using
	 * voucher codes from order items
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_get_item_data_using_item_key( $order_items, $item_key ) {

		$prefix = WOO_VOU_META_PREFIX;

		//initilize item
		$return_item	= array( 'item_id' => '', 'item_data' => array() );

		if( !empty( $order_items ) ) {//if items are not empty

			
			foreach ( $order_items as $item_id => $item ) {

				if( $item_key == $item_id ) { 

					$return_item['item_id']		= $item_id;
					$return_item['item_data']	= $item;
					break;
				}
			}
		}

		return apply_filters( 'woo_vou_get_item_data_using_item_key', $return_item, $order_items, $item_key );
	}

	/**
	 * Display Buyer's information
	 * 
	 * Handles to display buyers information
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_display_buyer_info_html( $buyers_information = array(), $display = false ) {

		$buyer_info_columns	= apply_filters( 'woo_vou_buyer_info_columns', array(
																	'buyer_name'	=> __( 'Name:', 'woovoucher' ),
																	'buyer_email'	=> __( 'Email:', 'woovoucher' ),
																	'buyer_address'	=> __( 'Address:', 'woovoucher' ),
																	'buyer_phone'	=> __( 'Address:', 'woovoucher' ),
																) );

		$first_name	= isset( $buyers_information['first_name'] ) ? $buyers_information['first_name'] : '';
		$last_name	= isset( $buyers_information['last_name'] ) ? $buyers_information['last_name'] : '';
		$email		= isset( $buyers_information['email'] ) ? $buyers_information['email'] : '';
		$address_1	= isset( $buyers_information['address_1'] ) ? $buyers_information['address_1'] : '';
		$address_2	= isset( $buyers_information['address_2'] ) ? $buyers_information['address_2'] : '';
		$city		= isset( $buyers_information['city'] ) ? $buyers_information['city'] : '';
		$state		= isset( $buyers_information['state'] ) ? $buyers_information['state'] : '';
		$country	= isset( $buyers_information['country'] ) ? $buyers_information['country'] : '';
		$postcode	= isset( $buyers_information['postcode'] ) ? $buyers_information['postcode'] : '';
		$phone		= isset( $buyers_information['phone'] ) ? $buyers_information['phone'] : '';

		$buyer_details_html   = '<table class="woo-vou-buyer-info-table">';

		if( !empty( $buyer_info_columns ) ) {

			foreach ( $buyer_info_columns as $col_key => $column ) {

				switch ( $col_key ) {

					case 'buyer_name':
						$buyer_details_html  .= '<tr>';
						$buyer_details_html  .= 	'<td width="20%" style="font-weight:bold;">'.__('Name:', 'woovoucher').'</td>';
						$buyer_details_html	 .= 	'<td width="80%">'.$first_name.' '.$last_name.'</td>';
						$buyer_details_html  .= '</tr>';
						break;

					case 'buyer_email' : 
						$buyer_details_html  .= '<tr>';
						$buyer_details_html  .= 	'<td width="20%" style="font-weight:bold;">'.__('Email:', 'woovoucher').'</td>';
						$buyer_details_html	 .= 	'<td width="80%">'.$email.'</td>';
						$buyer_details_html  .= '</tr>';
						break;

					case 'buyer_address' : 
						$buyer_details_html  .= '<tr>';
						$buyer_details_html  .= 	'<td width="20%" style="font-weight:bold;">'.__('Address:', 'woovoucher').'</td>';
						$buyer_details_html	 .= 	'<td width="80%">'.$address_1.' '.$address_2.'<br />'.$city.' '.$state.' '.$country.' - '.$postcode.'</td>';
						$buyer_details_html  .= '</tr>';
						break;

					case 'buyer_phone' : 
						$buyer_details_html  .= '<tr>';
						$buyer_details_html  .= 	'<td width="20%" style="font-weight:bold;">'.__( 'Phone:', 'woovoucher').'</td>';
						$buyer_details_html	 .= 	'<td width="80%">'.$phone.'</td>';
						$buyer_details_html  .= '</tr>';
						break;

					default : 
						$buyer_details_html .= apply_filters( 'woo_vou_buyer_info_columns_value', '', $col_key, $buyers_information );
						break;
				}
			}
		}

		$buyer_details_html  .= '</table>';

		$html	= apply_filters( 'woo_vou_display_buyer_info_html', $buyer_details_html, $buyers_information );

		if( $display ) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Display product information
	 * 
	 * Handles to display buyers information
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_display_product_info_html( $order_id, $voucode = '', $type = 'html'  ) {
		
		$product_details_html	= '';
		
		if( !empty( $order_id ) && !empty( $voucode ) ) {//If not empty order id and voucher code
			
			//get order
			$order 			= new Wc_Order( $order_id );
			
			//get order items
			$order_items 	= $order->get_items();
			
			$check_code	= trim( $voucode );
			$item_array	= $this->woo_vou_get_item_data_using_voucher_code( $order_items, $check_code );
			
			$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
			$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();
			$_product 	= $order->get_product_from_item( $item );
			
			//initilize variables
			$product_name = $product_price = $product_sku ='';
			
			if ( $_product ) {
				if ( $_product && $_product->get_sku() ) {
					$product_sku = esc_html( $_product->get_sku() );
				}
				if( $type == 'html' ) {
					$product_name .= '<a target="_blank" href="'. esc_url( admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) ) . '">' . esc_html( $item['name'] ) . '</a>';
				} else {
					$product_name .=  esc_html( $item['name'] ) ."\n";	
				}
			} else {
				$product_name .= isset( $item['name'] ) ? esc_html( $item['name'] ) : '';
			}
	
			//Get product item meta
			$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();
	
			//Display product variations
			//$product_name .= $this->woo_vou_display_product_item_name( $product_item_meta, true );
			$product_name .= $this->woo_vou_display_product_item_name( $item, $_product, true );
			
			if ( isset( $item['line_total'] ) ) {
				$product_price = wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_order_currency() ) );
			}
			
			if( $type == 'csv' ) {
				
				$product_details_html  .= __( 'Name:', 'woovoucher' ).strip_tags($product_name)."\n";
				$product_details_html  .= __( 'Price:', 'woovoucher' ).strip_tags($product_price)."\n";
				if(!empty($product_sku))
					$product_details_html  .= __( 'SKU:', 'woovoucher' ).$product_sku;
				
			} else{
				
				$product_details_html  .= '<table>';
				$product_details_html  .= '<tr><td width="22%;" style="font-weight:bold;">'.__( 'Name:', 'woovoucher' ).'</td><td width="77%;">'.$product_name.'</td></tr>';
				$product_details_html  .= '<tr><td width="22%" style="font-weight:bold;">'.__( 'Price:', 'woovoucher' ).'</td><td width="77%;">'.$product_price.'</td></tr>';
				if(!empty($product_sku))
					$product_details_html  .= '<tr><td width="22%" style="font-weight:bold;">'.__( 'SKU:', 'woovoucher' ).'</td><td width="77%;">'.$product_sku.'</td></tr>';
				$product_details_html  .= '</table>';
			}
		}
		
		return apply_filters( 'woo_vou_display_product_info_html', $product_details_html, $order_id, $voucode, $type ) ;
	}
	
	/**
	 * Display Order information
	 * 
	 * Handles to display buyers information
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.6
	 */
	public function woo_vou_display_order_info_html( $order_id, $type='html' ){
		
		$order_details_html	= '';
		
		//get order
		$order 			= new Wc_Order( $order_id );
		
		//get order date
		$order_date		= $order->order_date;		
		
		//get payment method
		$payment_method	= $order->payment_method_title;
		
		//Order title
		$order_total = esc_html( strip_tags( $order->get_formatted_order_total() ) );
		
		//Order discount
		$order_discount = wc_price( $order->get_total_discount(), array( 'currency' => $order->get_order_currency() ) );
		
		if($type=='html')
			$order_id = '<a href="'.esc_url( admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) ).'">' . $order_id . '</a>';
		
		
		if( $type == 'csv' ) {
			
			$order_details_html  .= 'ID : '.$order_id."\n";
			$order_details_html  .= 'Order Date : '.$order_date."\n";
			$order_details_html  .= 'Payment Method : '.$payment_method."\n";
			$order_details_html  .= 'Order Total : 	'.$order_total."\n";
			$order_details_html  .= 'Order Discount :'.strip_tags($order_discount);
		} else {	
			
			$order_details_html  .= '<table>';
			$order_details_html  .= '<tr><td style="font-weight:bold;">'.__( 'ID:', 'woovoucher' ).'</td><td>'.$order_id.'</td></tr>';
			$order_details_html  .= '<tr><td style="font-weight:bold;">'.__( 'Order Date:', 'woovoucher' ).'</td><td>'.$order_date.'</td></tr>';
			$order_details_html  .= '<tr><td style="font-weight:bold;">'.__( 'Payment Method:', 'woovoucher' ).'</td><td>'.$payment_method.'</td></tr>';
			$order_details_html  .= '<tr><td style="font-weight:bold;">'.__( 'Order Total:', 'woovoucher' ).'</td><td>'.$order_total.'</td></tr>';
			$order_details_html  .= '<tr><td style="font-weight:bold;">'.__( 'Order Discount:', 'woovoucher' ).'</td><td>'.$order_discount.'</td></tr>';
			$order_details_html  .= '</table>';
		}
		
		return apply_filters( 'woo_vou_display_order_info_html', $order_details_html, $order_id, $type );
	}

	/**
	 * Enable/Disable Vendor To Check, Redeem and List voucher codes
	 * 
	 * Handles to enable/disable vendor to check, Redeem and List voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.7
	 */
	public function woo_vou_vendor_redeem_all_codes( $user_data = array() ) {
		
		if( empty( $user_data ) ) {//if user is empty
			
			global $current_user;
			$user_data	= $current_user;
		}
		
		return apply_filters( 'woo_vou_vendor_redeem_all_codes', false, $user_data );
	}

	/**
	 * Get a download from the database.
	 * 
	 * Handles to get a download from the database.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.7
	 */
	public function woo_vou_get_download_data( $args = array() ) {

		global $wpdb;

		$query = "SELECT * FROM " . $wpdb->prefix . "woocommerce_downloadable_product_permissions ";
		$query .= "WHERE user_email = %s ";
		$query .= "AND order_key = %s ";
		$query .= "AND product_id = %s ";

		if ( $args['download_id'] ) {
			$query .= "AND download_id = %s ";
		}

		return $wpdb->get_row( $wpdb->prepare( $query, array( $args['email'], $args['order_key'], $args['product_id'], $args['download_id'] ) ) );
	}

	/**
	 * Log the download + increase counts
	 * 
	 * Handles to Log the download + increase counts
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.7
	 */
	public function woo_vou_count_download( $download_data ) {

		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . "woocommerce_downloadable_product_permissions",
			array(
				'download_count'      => $download_data->download_count + 1,
				'downloads_remaining' => $download_data->downloads_remaining > 0 ? $download_data->downloads_remaining - 1 : $download_data->downloads_remaining,
			),
			array(
				'permission_id' => absint( $download_data->permission_id ),
			),
			array( '%d', '%s' ),
			array( '%d' )
		);
	}
	
	/**
	 * Restore voucher code to product
	 * 
	 * Handles to Restore voucher code to product again
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_restore_order_voucher_codes( $order_id = '' ) {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		if( !empty( $order_id ) ) {
			
			$args	= array( 
							'post_status'	=> array( 'pending' ),
							'meta_query'	=> array(
													array(
														'key'	=> $prefix . 'order_id',
														'value'	=> $order_id,
													)
												)
						);

			//Get vouchers code of this order
			$vochers	= $this->woo_vou_get_voucher_details( $args );

			if( !empty( $vochers ) ) {//If empty voucher codes

				//get order meta
				$meta_order_details	= get_post_meta( $order_id, $prefix.'meta_order_details', true );

				foreach ( $vochers as $vocher ) {

					//Initilize voucher codes array
					$salecode		= array();

					//Get voucher code ID
					$vou_codes_id	= isset( $vocher['ID'] ) ? $vocher['ID'] : '';

					//Get product ID
					$product_id		= isset( $vocher['post_parent'] ) ? $vocher['post_parent'] : '';

					//Get voucher codes
					$voucher_codes			= get_post_meta( $vou_codes_id, $prefix . 'purchased_codes', true );

					//meta detail of specific product
					$product_meta_detail	= isset( $meta_order_details[$product_id] ) ? $meta_order_details[$product_id] : array();

					//Voucher uses types
					$voucher_uses_type		= isset( $product_meta_detail['using_type'] ) ? $product_meta_detail['using_type'] : '';

					if( !empty( $voucher_codes ) && empty( $voucher_uses_type ) ) {//If voucher codes available and type is not unlimited

						$variation_id	= get_post_meta( $vou_codes_id, $prefix . 'vou_from_variation', true );

						if( !empty( $variation_id ) ) {

							//voucher codes
							$product_vou_codes = get_post_meta( $variation_id, $prefix . 'codes', true );

							//explode all voucher codes
							$salecode	= !empty( $product_vou_codes ) ? explode( ',', $product_vou_codes ) : array();

							//append sales code array
							$salecode[]	= $voucher_codes;

							//trim code
							foreach ( $salecode as $code_key => $code ) {

								$salecode[$code_key] = trim( $code );
							}

							//Total avialable voucher code
							$avail_total_codes	= count( $salecode );

							//update total voucher codes
							wc_update_product_stock( $variation_id,  $avail_total_codes );

							//after restore code in array update in code meta
							$lessvoucodes = implode( ',', $salecode );
							update_post_meta( $variation_id, $prefix.'codes', trim( $lessvoucodes ) );

						} else {

							//voucher codes
							$product_vou_codes = get_post_meta( $product_id, $prefix . 'codes', true );

							//explode all voucher codes
							$salecode	= !empty( $product_vou_codes ) ? explode( ',', $product_vou_codes ) : array();

							//append sales code array
							$salecode[]	= $voucher_codes;

							//trim code
							foreach ( $salecode as $code_key => $code ) {

								$salecode[$code_key] = trim( $code );
							}

							//Total avialable voucher code
							$avail_total_codes	= count( $salecode );

							//update total voucher codes
							update_post_meta( $product_id, $prefix.'avail_total', $avail_total_codes );

							//update total voucher codes
							wc_update_product_stock( $product_id,  $avail_total_codes );

							//after restore code in array update in code meta
							$lessvoucodes = implode( ',', $salecode );
							update_post_meta( $product_id, $prefix.'codes', trim( $lessvoucodes ) );
						}

						//delete voucher post
						wp_delete_post( $vou_codes_id, true );
					}
				}

				//delete voucher order details
				delete_post_meta( $order_id, $prefix.'order_details' );
				//delete voucher order details with all meta data
				delete_post_meta( $order_id, $prefix.'meta_order_details' );
			}
		}
	}
	
	/**
	 * Refund voucher code to product
	 * 
	 * Handles to Refund voucher code to product again
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_refund_order_voucher_codes( $order_id ) {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		if( !empty( $order_id ) ) {
			
			$args	= array( 
						'post_status'	=> array( 'pending', 'publish' ),
						'meta_query'	=> array(
												array(
													'key'	=> $prefix . 'order_id',
													'value'	=> $order_id,
												)
											)
						);

			//Get vouchers code of this order
			$vochers	= $this->woo_vou_get_voucher_details( $args );

			if( !empty( $vochers ) ) {//If empty voucher codes

				foreach ( $vochers as $vocher ) {

					$vou_codes_id	= isset( $vocher['ID'] ) ? $vocher['ID'] : '';

					if( !empty( $vou_codes_id ) ) {

						$update_refund	= array(
												'ID'			=> $vou_codes_id,
												'post_status'	=> WOO_VOU_REFUND_STATUS
											);

						//set status refunded of voucher post
						wp_update_post( $update_refund );
					}
				}
			}
		}
	}
	
	/**
	 * Update product stock
	 * 
	 * Handles to Update Product Stock
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_update_product_stock( $product_id = '', $variation_id = '', $voucher_codes = array() ) {
		
		//Total avialable voucher code
		$avail_total_codes	= count( $voucher_codes );
		
		if( !empty( $variation_id ) ) {
			wc_update_product_stock( $variation_id,  $avail_total_codes );
		} else {
			wc_update_product_stock( $product_id,  $avail_total_codes );
		}
	}
	
	/**
	 * Check product is expired/upcoming
	 * 
	 * Handles to check product is expired/upcoming based on start date and end date
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.2
	 */
	public function woo_vou_check_product_is_expired( $product ) {
		
		//Get Prefix
		$prefix		= WOO_VOU_META_PREFIX;
		
		// check voucher is enable
		$enabled = $this->woo_vou_check_enable_voucher( $product->id );	
		$expired = false;		
		// get expiration type
		$exp_type = get_post_meta( $product->id, $prefix.'exp_type', true );
		
		if( $enabled && $exp_type == 'specific_date' ) { // check expiration type is based on purchase
			
			// get start date
			$start_date = get_post_meta( $product->id, $prefix.'start_date', true );
			// get end date
		    $end_date  	= get_post_meta( $product->id, $prefix.'exp_date', true );
		    // get today date
		    $today_date	= date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ); 
		    
		    if( empty( $start_date) && empty( $end_date ) ) {
		    	$expired = false;
		    } elseif ( !empty( $start_date ) && $start_date > $today_date ) {
		    	$expired = 'upcoming';
		    } elseif ( !empty( $end_date ) && $end_date < $today_date ) {
		    	$expired = 'expired';
		    }
		}
		
		return apply_filters( 'woo_vou_check_product_is_expired', $expired, $product );
	}
}