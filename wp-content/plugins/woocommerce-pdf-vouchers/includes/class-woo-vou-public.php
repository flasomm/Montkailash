<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public Pages Class
 *
 * Handles all the different features and functions
 * for the front end pages.
 *
 * @package WooCommerce - PDF Vouchers
 * @since   1.0.0
 */
class WOO_Vou_Public
{
    
    public $model;
    
    public function __construct()
    {
        
        global $woo_vou_model;
        
        $this->model = $woo_vou_model;
    }
    
    /**
     * Handles to update voucher details in order data
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_product_purchase($order_id)
    {
        
        //Get Prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        $changed     = false;
        $voucherdata = $vouchermetadata = $recipient_order_meta = array();
        
        //Get user data from order
        $userdata = $this->model->woo_vou_get_payment_user_info($order_id);
        
        //Get buyers information
        $userfirstname = isset($userdata['first_name']) ? trim($userdata['first_name']) : '';
        $userlastname  = isset($userdata['last_name']) ? trim($userdata['last_name']) : '';
        $useremail     = isset($userdata['email']) ? $userdata['email'] : '';
        $buyername     = $userfirstname;
        
        // Check woocommerce order class
        if (class_exists('WC_Order')) {
            
            $order       = new WC_Order($order_id);
            $order_items = $order->get_items();
            
            //Get Order Date
            $order_date = isset($order->order_date) ? $order->order_date : '';
            
            if (is_array($order_items)) {
                
                // Check cart details
                foreach ($order_items as $item_id => $item) {
                    
                    //get product id
                    $productid = $item['product_id'];
                    
                    //get product quantity
                    $productqty = $item['qty'];
                    
                    // Taking variation id
                    $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';
                    
                    // If product is variable product take variation id else product id
                    $data_id = (!empty($variation_id)) ? $variation_id : $productid;
                    
                    //Get voucher code from item meta "Now we store voucher codes in item meta fields"
                    $codes_item_meta = wc_get_order_item_meta($item_id, $prefix . 'codes');
                    
                    if (empty($codes_item_meta)) {// If voucher data are not empty so code get executed once only
                        
                        //voucher codes
                        $vou_codes = $this->model->woo_vou_get_voucher_code($productid, $variation_id);
                        
                        //vendor user
                        $vendor_user = get_post_meta($productid, $prefix . 'vendor_user', true);
                        
                        //get vendor detail
                        $vendor_detail = $this->model->woo_vou_get_vendor_detail($productid, $vendor_user);
                        
                        //using type of voucher
                        $using_type = isset($vendor_detail['using_type']) ? $vendor_detail['using_type'] : '';
                        
                        $allow_voucher_flag = true;
                        
                        // if using type is one time and voucher code is empty or quantity is zero
                        if (empty($using_type) && (empty($vou_codes))) { // || $avail_total_codes == '0'
                            $allow_voucher_flag = false;
                        }
                        
                        //check enable voucher & is downlable & total codes are not empty
                        if ($this->model->woo_vou_check_enable_voucher($productid, $variation_id) && $allow_voucher_flag == true) {
                            
                            // start date
                            $start_date = get_post_meta($productid, $prefix . 'start_date', true);
                            if (!empty($start_date)) {
                                //format start date
                                $start_date = date('Y-m-d H:i:s', strtotime($start_date));
                            } else {
                                $start_date = '';
                            }
                            
                            //manual expiration date
                            $manual_expire_date = get_post_meta($productid, $prefix . 'exp_date', true);
                            if (!empty($manual_expire_date)) {
                                //expiry data
                                $exp_date = date('Y-m-d H:i:s', strtotime($manual_expire_date));
                            } else {
                                $exp_date = '';
                            }
                            
                            //get expiration tpe
                            $exp_type = get_post_meta($productid, $prefix . 'exp_type', true);
                            
                            //custom days
                            $custom_days = '';
                            
                            if ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
                                
                                //get days difference
                                $days_diff = get_post_meta($productid, $prefix . 'days_diff', true);
                                
                                if ($days_diff == 'cust') {
                                    $custom_days = get_post_meta($productid, $prefix . 'custom_days', true);
                                    $custom_days = isset($custom_days) ? $custom_days : '';
                                    if (!empty($custom_days)) {
                                        $add_days = '+' . $custom_days . ' days';
                                        $exp_date = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
                                    } else {
                                        $exp_date = date('Y-m-d H:i:s', current_time('timestamp'));
                                    }
                                } else {
                                    $custom_days = $days_diff;
                                    $add_days    = '+' . $custom_days . ' days';
                                    $exp_date    = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
                                }
                            }
                            
                            //voucher code
                            $vouchercodes = $vou_codes;
                            $vouchercodes = trim($vouchercodes, ',');
                            
                            //explode all voucher codes
                            $salecode = !empty($vouchercodes) ? explode(',', $vouchercodes) : array();
                            
                            // trim code
                            foreach ($salecode as $code_key => $code) {
                                $salecode[$code_key] = trim($code);
                            }
                            
                            $allcodes = '';
                            
                            //if voucher useing type is more than one time then generate voucher codes
                            if (!empty($using_type)) {
                                
                                //if user buy more than 1 quantity of voucher
                                if (isset($productqty) && $productqty > 1) {
                                    for ($i = 1; $i <= $productqty; $i++) {
                                        
                                        $voucode = '';
                                        
                                        //make voucher code
                                        $randcode = array_rand($salecode);
                                        
                                        if (!empty($buyername)) {
                                            $voucode .= $buyername . '-';
                                        }
                                        if (!empty($salecode[$randcode]) && trim($salecode[$randcode]) != '') {
                                            $voucode .= trim($salecode[$randcode]) . '-';
                                        }
                                        
                                        $voucode .= $order_id . '-' . $data_id . '-' . $i;
                                        $allcodes .= $voucode . ', ';
                                    }
                                } else {
                                    
                                    $voucode = $code_prefix = '';
                                    
                                    //make voucher code when user buy single quantity
                                    $randcode = array_rand($salecode);
                                    
                                    if (!empty($salecode[$randcode]) && trim($salecode[$randcode]) != '') {
                                        $code_prefix = trim($salecode[$randcode]);
                                    }
                                    
                                    //voucher codes arguments for create unlinited voucher
                                    $voucode_args = apply_filters(
                                        'woo_vou_unlimited_code_params', array(
                                                                           'buyername'   => $buyername,
                                                                           'code_prefix' => $code_prefix,
                                                                           'order_id'    => $order_id,
                                                                           'data_id'     => $data_id,
                                                                       )
                                    );
                                    
                                    if (!empty($voucode_args)) { //arguments are not empty
                                        $length  = count($voucode_args);
                                        $counter = 1;
                                        foreach ($voucode_args as $key => $voucode_arg) {
                                            $voucode .= $voucode_arg;
                                            if ($counter != $length) {
                                                $voucode .= '-';
                                            }
                                            $counter++;
                                        }
                                    }
                                    
                                    $allcodes .= $voucode . ', ';
                                }
                            } else {
                                for ($i = 0; $i < $productqty; $i++) {
                                    
                                    //get first voucher code
                                    $voucode = $salecode[$i];
                                    
                                    //unset first voucher code to remove from all codes
                                    unset($salecode[$i]);
                                    $allcodes .= $voucode . ', ';
                                }
                                
                                //after unsetting first code make one string for other codes
                                $lessvoucodes = implode(',', $salecode);
                                $this->model->woo_vou_update_voucher_code($productid, $variation_id, $lessvoucodes);
                                
                                //Reduce stock quantity when order created and voucher deducted
                                $this->model->woo_vou_update_product_stock($productid, $variation_id, $salecode);
                            }
                            
                            $allcodes = trim($allcodes, ', ');
                            
                            //add voucher codes item meta "Now we store voucher codes in item meta fields"
                            //And Remove "order_details" array from here
                            wc_add_order_item_meta($item_id, $prefix . 'codes', $allcodes);
                            
                            //Append for voucher meta data into order
                            $productvoumetadata = array(
                                'user_email'      => $useremail,
                                'pdf_template'    => $vendor_detail['pdf_template'],
                                'vendor_logo'     => $vendor_detail['vendor_logo'],
                                'start_date'      => $start_date,
                                'exp_date'        => $exp_date,
                                'exp_type'        => $exp_type,
                                'custom_days'     => $custom_days,
                                'using_type'      => $using_type,
                                'vendor_address'  => $vendor_detail['vendor_address'],
                                'website_url'     => $vendor_detail['vendor_website'],
                                'redeem'          => $vendor_detail['how_to_use'],
                                'avail_locations' => $vendor_detail['avail_locations'],
                            );
                            
                            $vouchermetadata[$productid] = $productvoumetadata;
                            
                            $all_vou_codes = !empty($allcodes) ? explode(', ', $allcodes) : array();
                            
                            foreach ($all_vou_codes as $vou_code) {
                                
                                $vou_code = trim($vou_code, ',');
                                $vou_code = trim($vou_code);
                                
                                //Insert voucher details into custom post type with seperate voucher code
                                $vou_codes_args = array(
                                    'post_title'   => $order_id,
                                    'post_content' => '',
                                    'post_status'  => 'pending',
                                    'post_type'    => WOO_VOU_CODE_POST_TYPE,
                                    'post_parent'  => $productid,
                                );
                                
                                if (!empty($vendor_user)) { // Check vendor user is not empty
                                    $vou_codes_args['post_author'] = $vendor_user;
                                }
                                
                                $vou_codes_id = wp_insert_post($vou_codes_args);
                                
                                if ($vou_codes_id) { // Check voucher codes id is not empty
                                    
                                    // update buyer first name
                                    update_post_meta($vou_codes_id, $prefix . 'first_name', $userfirstname);
                                    // update buyer last name
                                    update_post_meta($vou_codes_id, $prefix . 'last_name', $userlastname);
                                    // update order id
                                    update_post_meta($vou_codes_id, $prefix . 'order_id', $order_id);
                                    // update order date
                                    update_post_meta($vou_codes_id, $prefix . 'order_date', $order_date);
                                    // update start date
                                    update_post_meta($vou_codes_id, $prefix . 'start_date', $start_date);
                                    // update expires date
                                    update_post_meta($vou_codes_id, $prefix . 'exp_date', $exp_date);
                                    // update purchased codes
                                    update_post_meta($vou_codes_id, $prefix . 'purchased_codes', $vou_code);
                                    
                                    $vou_from_variation = get_post_meta($productid, $prefix . 'is_variable_voucher', true);
                                    
                                    if (!empty($vou_from_variation)) {
                                        
                                        // update purchased codes
                                        update_post_meta($vou_codes_id, $prefix . 'vou_from_variation', $data_id);
                                    }
                                }
                            }
                        }
                    }
                }
                
                //Get custom meta data of order
                $custom_metadata = get_post_custom($order_id);
                
                if (!isset($custom_metadata['multiple_pdf'])) { // Multipdf is already updated
                    
                    //update If setting is set for multipdf or not
                    $multiple_pdf = get_option('multiple_pdf');
                    
                    //update multipdf option in ordermeta
                    update_post_meta($order_id, $prefix . 'multiple_pdf', $multiple_pdf);
                }
                
                if (!empty($vouchermetadata)) { // Check voucher meta data are not empty
                    //update voucher order details with all meta data
                    update_post_meta($order_id, $prefix . 'meta_order_details', $vouchermetadata);
                }
            }
        }
    }
    
    /**
     * Add custom email notification to woocommerce
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.3.4
     */
    public function woo_vou_add_email_notification($email_actions)
    {
        
        $email_actions[] = 'woo_vou_vendor_sale_email';
        $email_actions[] = 'woo_vou_gift_email';
        
        return $email_actions;
    }
    
    /**
     * Display Download Voucher Link
     *
     * Handles to display product voucher link for user
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_downloadable_files($downloadable_files, $product)
    {
        
        global $post, $vou_order, $woo_vou_item_id;
        
        $prefix = WOO_VOU_META_PREFIX;
        
        $pdf_downloadable_files = array();
        
        // Taking variation id
        $variation_id = !empty($product->variation_id) ? $product->variation_id : $product->id;
        
        $order_id = $this->model->woo_vou_get_orderid_for_page(); // Getting order id
        
        //Get Order id on shop_order page
        // this is called when we make order complete from the backend
        if (is_admin() && !empty($post->post_type) && $post->post_type == 'shop_order') {
            
            $order_id = isset($post->ID) ? $post->ID : '';
        }
        
        if (empty($order_id)) { // Return download files if irder id not found
            return $downloadable_files;
        }
        
        //Get vouchers download files
        $pdf_downloadable_files = $this->woo_vou_get_vouchers_download_key($order_id, $variation_id, $woo_vou_item_id);
        
        //Mearge existing download files with vouchers file
        if (!empty($downloadable_files)) {
            $downloadable_files = array_merge($downloadable_files, $pdf_downloadable_files);
        } else {
            $downloadable_files = $pdf_downloadable_files;
        }
        
        return $downloadable_files;
    }
    
    /**
     * Download Process
     *
     * Handles to product process
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_download_process($email, $order_key, $product_id, $user_id, $download_id, $order_id)
    {
        
        if (!empty($_GET['item_id'])) {
            
            $item_id = $_GET['item_id'];
            
            //Generate PDF
            $this->model->woo_vou_generate_pdf_voucher($email, $product_id, $download_id, $order_id, $item_id);
            
            /*$downlod_data	= $this->model->woo_vou_get_download_data( array( 
														'product_id'  => $product_id,
														'order_key'   => wc_clean( $_GET['order'] ),
														'email'       => sanitize_email( str_replace( ' ', '+', $_GET['email'] ) ),
														'download_id' => wc_clean( isset( $_GET['key'] ) ? preg_replace( '/\s+/', ' ', $_GET['key'] ) : '' )
													));
			
			$this->model->woo_vou_count_download( $downlod_data );*/
            exit;
        }
    }
    
    /**
     * Insert pdf voucher files
     *
     * Handles to insert pdf voucher
     * files in database
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_insert_downloadable_files($order_id)
    {
        
        $prefix = WOO_VOU_META_PREFIX;
        
        $downloadable_files = array();
        
        //Get Order
        $order = new WC_Order($order_id);
        
        if (sizeof($order->get_items()) > 0) { //Get all items in order
            
            foreach ($order->get_items() as $item_id => $item) {
                
                //Product Data
                $_product = $order->get_product_from_item($item);
                
                // Taking variation id
                $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';
                
                if ($_product && $_product->exists()) { // && $_product->is_downloadable()
                    
                    //get product id from prduct data
                    $product_id = isset($_product->id) ? $_product->id : '';
                    
                    // If product is variable product take variation id else product id
                    $data_id = (!empty($variation_id)) ? $variation_id : $product_id;
                    
                    if ($this->model->woo_vou_check_enable_voucher($product_id, $variation_id)) {//Check voucher is enabled or not
                        
                        //Get vouchers downlodable pdf files
                        $downloadable_files = $this->woo_vou_get_vouchers_download_key($order_id, $data_id, $item_id);
                        
                        foreach (array_keys($downloadable_files) as $download_id) {
                            
                            //Insert pdf vouchers in downloadable table
                            wc_downloadable_file_permission($download_id, $data_id, $order);
                        }
                    }
                }
            }
        }
        
        // Status update from pending to publish when voucher is get completed or processing
        $args = array(
            'post_status' => array('pending'),
            'meta_query'  => array(
                array(
                    'key'   => $prefix . 'order_id',
                    'value' => $order_id,
                ),
            ),
        );
        
        // Get vouchers code of this order
        $purchased_vochers = $this->model->woo_vou_get_voucher_details($args);
        
        if (!empty($purchased_vochers)) { // If not empty voucher codes
            
            //For all possible vouchers
            foreach ($purchased_vochers as $vocher) {
                
                // Get voucher data
                $current_post = get_post($vocher['ID'], 'ARRAY_A');
                //Change voucher status
                $current_post['post_status'] = 'publish';
                //Update voucher post
                wp_update_post($current_post);
            }
        }
    }
    
    /**
     * Get downloadable vouchers files
     *
     * Handles to get downloadable vouchers files
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_get_vouchers_download_key($order_id = '', $product_id = '', $item_id = '')
    {
        
        $prefix             = WOO_VOU_META_PREFIX;
        $downloadable_files = array();
        
        //Get mutiple pdf option from order meta
        $multiple_pdf = empty($order_id) ? '' : get_post_meta($order_id, $prefix . 'multiple_pdf', true);
        
        if (!empty($order_id)) {
            
            if ($multiple_pdf == 'yes') { //If multiple pdf is set
                
                $vouchercodes = $this->model->woo_vou_get_multi_voucher_key($order_id, $product_id, $item_id);
                
                foreach ($vouchercodes as $codes) {
                    
                    $downloadable_files[$codes] = array(
                        'name' => woo_vou_voucher_download_text($product_id),
                        'file' => get_permalink($product_id),
                    );
                }
            } else {
                
                // Set our vocher download file in download files
                $downloadable_files['woo_vou_pdf_1'] = array(
                    'name' => woo_vou_voucher_download_text($product_id),
                    'file' => get_permalink($product_id),
                );
            }
        }
        
        return $downloadable_files;
    }
    
    /**
     * Set Order As Global Variable
     *
     * Handles to set order as global variable
     * when order links displayed in email
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.1.0
     */
    public function woo_vou_email_before_order_table($order)
    {
        
        global $vou_order;
        
        //Get Order_id from order data
        $order_id = isset($order->id) ? $order->id : '';
        //Create global varible for order
        $vou_order = $order_id;
    }
    
    /**
     * Allow admin access to vendor user
     *
     * Handles to allow admin access to vendor user
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.1.0
     */
    public function woo_vou_prevent_admin_access($prevent_access)
    {
        
        global $current_user, $woo_vou_vendor_role;
        
        //Get User roles
        $user_roles = isset($current_user->roles) ? $current_user->roles : array();
        $user_role  = array_shift($user_roles);
        
        if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
            
            return false;
        }
        
        return $prevent_access;
    }
    
    /**
     * Check Voucher Code
     *
     * Handles to check voucher code
     * is valid or invalid via ajax
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.1.0
     */
    public function woo_vou_check_voucher_code()
    {
        
        global $current_user, $woo_vou_vendor_role;
        
        $prefix             = WOO_VOU_META_PREFIX;
        $product_name       = '';
        $product_id         = '';
        $expiry_Date        = '';
        $response['expire'] = false;
        
        // Check voucher code is not empty
        if (!empty($_POST['voucode'])) {
            
            //Voucher Code
            $voucode = $_POST['voucode'];
            
            $args = array(
                'fields'     => 'ids',
                'meta_query' => array(
                    array(
                        'key'   => $prefix . 'purchased_codes',
                        'value' => $voucode,
                    ),
                    array(
                        'key'     => $prefix . 'used_codes',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
            
            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();
            $user_role  = array_shift($user_roles);
            
            if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
                
                //get redeem all voucher from vendor
                $redeem_all = $this->model->woo_vou_vendor_redeem_all_codes($current_user);
                
                if (!$redeem_all) { //If can't redeem all
                    $args['author'] = $current_user->ID;
                }
            }
            
            // this always return array
            $voucodedata = $this->model->woo_vou_get_voucher_details($args);
            
            $args = array(
                'fields'     => 'ids',
                'meta_query' => array(
                    array(
                        'key'   => $prefix . 'used_codes',
                        'value' => $voucode,
                    ),
                ),
            );
            
            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();
            $user_role  = array_shift($user_roles);
            
            if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
                $args['author'] = $current_user->ID;
            }
            
            $usedcodedata = $this->model->woo_vou_get_voucher_details($args);
            
            // Check voucher code ids are not empty
            if (!empty($voucodedata) && is_array($voucodedata)) {
                
                $voucodeid = isset($voucodedata[0]) ? $voucodedata[0] : '';
                
                if (!empty($voucodeid)) {
                    
                    //get vouchercodes data 
                    $voucher_data = get_post($voucodeid);
                    $order_id     = get_post_meta($voucodeid, $prefix . 'order_id', true);
                    $cart_details = new Wc_Order($order_id);
                    $order_items  = $cart_details->get_items();
                    
                    foreach ($order_items as $item_id => $download_data) {
                        
                        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                        $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                        $voucher_codes = array_map('trim', $voucher_codes);
                        
                        if (in_array($voucode, $voucher_codes)) {
                            
                            //get product data
                            $product_name = $download_data['name'];
                            $product_id   = $download_data['product_id'];
                        }
                    }
                }
                
                //voucher expired date
                $expiry_Date = get_post_meta($voucodeid, $prefix . 'exp_date', true);
                
                $response['success'] = sprintf(__('Voucher code is valid and this voucher code has been bought for %s. ' . "\n" . 'If you would like to redeem voucher code, Please click on the redeem button below:', 'woovoucher'), $product_name);
                
                if (isset($expiry_Date) && !empty($expiry_Date)) {
                    
                    if ($expiry_Date < $this->model->woo_vou_current_date()) {
                        $response['expire']  = true;
                        $response['expire']  = true;
                        $response['success'] = sprintf(__('Voucher code was expired on %s for %s. ' . "\n", 'woovoucher'), $this->model->woo_vou_get_date_format($expiry_Date, true), $product_name);
                    }
                }
                
                $response['product_detail'] = $this->woo_vou_get_product_detail($order_id, $_POST['voucode'], $voucodeid);
                
            } else {
                if (!empty($usedcodedata) && is_array($usedcodedata)) { // Check voucher code is used or not
                    
                    $voucodeid = isset($usedcodedata[0]) ? $usedcodedata[0] : '';
                    
                    if (!empty($voucodeid)) {
                        
                        $voucher_data = get_post($voucodeid);
                        $order_id     = get_post_meta($voucodeid, $prefix . 'order_id', true);
                        $cart_details = new Wc_Order($order_id);
                        $order_items  = $cart_details->get_items();
                        
                        foreach ($order_items as $item_id => $download_data) {
                            
                            $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                            $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                            $voucher_codes = array_map('trim', $voucher_codes);
                            
                            $check_code = trim($voucode);
                            
                            if (in_array($check_code, $voucher_codes)) {
                                
                                //get product data
                                $product_name = $download_data['name'];
                            }
                        }
                    }
                    
                    // get used code date
                    $used_code_date   = get_post_meta($voucodeid, $prefix . 'used_code_date', true);
                    $response['used'] = sprintf(__('Voucher code is invalid, was used on %s for %s.', 'woovoucher'), $this->model->woo_vou_get_date_format($used_code_date, true), $product_name);
                    
                } else {
                    $response['error'] = 'error';
                }
            }
            
            if (isset($_POST['ajax']) && $_POST['ajax'] == true) {  // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        }
    }
    
    /**
     * Get Product Detail From Order ID
     *
     * Handles to get product detail
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.6.2
     */
    public function woo_vou_get_product_detail($order_id, $voucode, $voucodeid = '')
    {
        
        ob_start();
        require_once(WOO_VOU_ADMIN . '/forms/woo-vou-check-code-product-info.php');
        $html = ob_get_clean();
        
        return apply_filters('woo_vou_get_product_detail', $html, $order_id, $voucode, $voucodeid);
    }
    
    /**
     * Save Voucher Code
     *
     * Handles to save voucher code
     * via ajax
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.1.0
     */
    public function woo_vou_save_voucher_code()
    {
        
        $prefix = WOO_VOU_META_PREFIX;
        
        global $woo_vou_vendor_role, $current_user;
        
        // Check voucher code is not empty
        if (!empty($_POST['voucode'])) {
            
            //Voucher Code
            $voucode = $_POST['voucode'];
            
            $args = array(
                'fields'     => 'ids',
                'meta_query' => array(
                    array(
                        'key'   => $prefix . 'purchased_codes',
                        'value' => $voucode,
                    ),
                    array(
                        'key'     => $prefix . 'used_codes',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
            
            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();
            $user_id    = isset($current_user->ID) ? $current_user->ID : '';
            $user_role  = array_shift($user_roles);
            
            if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
                
                $redeem_all = $this->model->woo_vou_vendor_redeem_all_codes($current_user);
                
                if (!$redeem_all) {
                    $args['author'] = $user_id;
                }
            }
            
            $voucodedata = $this->model->woo_vou_get_voucher_details($args);
            
            // Check voucher code ids are not empty
            if (!empty($voucodedata) && is_array($voucodedata)) {
                
                //current date
                $today = $this->model->woo_vou_current_date();
                
                foreach ($voucodedata as $voucodeid) {
                    
                    // update used codes
                    update_post_meta($voucodeid, $prefix . 'used_codes', $voucode);
                    
                    // update redeem by
                    update_post_meta($voucodeid, $prefix . 'redeem_by', $user_id);
                    
                    // update used code date
                    update_post_meta($voucodeid, $prefix . 'used_code_date', $today);
                    
                    // break is neccessary so if 2 code found then only 1 get marked as completed.
                    break;
                }
            }
            
            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                echo 'success';
                exit;
            } else {
                return 'success';
            }
        }
    }
    
    /**
     * Display Check Code Html
     *
     * Handles to display check code html for user and admin
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_check_code_content()
    { ?>
        
        <table class="form-table woo-vou-check-code">
        <tr>
            <th>
                <label for="woo_vou_voucher_code"><?php _e('Enter Voucher Code', 'woovoucher') ?></label>
            </th>
            <td>
                <input type="text" id="woo_vou_voucher_code" name="woo_vou_voucher_code" value=""/>
                <input type="button" id="woo_vou_check_voucher_code" name="woo_vou_check_voucher_code"
                       class="button-primary" value="<?php _e('Check It', 'woovoucher') ?>"/>
                
                <div class="woo-vou-loader woo-vou-check-voucher-code-loader"><img
                        src="<?php echo WOO_VOU_IMG_URL; ?>/ajax-loader.gif"/></div>
                <div class="woo-vou-voucher-code-msg"></div>
            </td>
        </tr>
        <tr class="woo-vou-voucher-code-submit-wrap">
            <th>
            </th>
            <td>
                <?php
                echo apply_filters(
                    'woo_vou_voucher_code_submit', '<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="' . __("Redeem", "woovoucher") . '"/>'
                );
                ?>
                <div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img
                        src="<?php echo WOO_VOU_IMG_URL; ?>/ajax-loader.gif"/></div>
            </td>
        </tr>
        </table><?php
    }
    
    /**
     * Add Capability to vendor role
     *
     * Handle to add capability to vendor role
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function woo_vou_initilize_role_capabilities()
    {
        
        global $woo_vou_vendor_role;
        
        $class_exist = apply_filters('woo_vou_initilize_role_capabilities', class_exists('WC_Vendors'));
        
        //Return if class not exist 
        if (!$class_exist) {
            return;
        }
        
        foreach ($woo_vou_vendor_role as $vendor_role) {
            
            //get vendor role
            $vendor_role_obj = get_role($vendor_role);
            
            if (!empty($vendor_role_obj)) { // If vendor role is exist 
                
                if (!$vendor_role_obj->has_cap(WOO_VOU_VENDOR_LEVEL)) { //If capabilty not exist
                    
                    //Add vucher level capability to vendor roles
                    $vendor_role_obj->add_cap(WOO_VOU_VENDOR_LEVEL);
                }
            }
        }
    }
    
    /**
     * Set Order Product As Global Variable
     *
     * Handles to set order product as global variable
     * when complete order mail fired or Order Details page is at front side
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.6
     */
    function woo_vou_order_item_product($product, $item)
    {
        
        global $woo_vou_order_item;
        
        $woo_vou_order_item = $item; // Making global of order product item
        
        return $product;
    }
    
    /**
     * Restore Voucher Code
     *
     * Handles to restore voucher codes
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.6.2
     */
    public function woo_vou_restore_voucher_codes($order_id, $old_status, $new_status)
    {
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        if ($new_status == 'cancelled') { //If status cancelled, failed
            $this->model->woo_vou_restore_order_voucher_codes($order_id);
        }
        
        if ($new_status == 'refunded') { //If status refunded
            $this->model->woo_vou_refund_order_voucher_codes($order_id);
        }
    }
    
    /**
     * Display Recipient HTML
     *
     * Handles to display the Recipient HTML for user
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_before_add_to_cart_button()
    {
        
        global $product;
        
        //Initilize products
        $products = array();
        
        if ($product->is_type('variable')) {//If variable product
            
            foreach ($product->get_children() as $variation_product_id) {
                
                $products[] = wc_get_product($variation_product_id);
            }
        } else {
            
            $products[] = $product;
        }
        
        foreach ($products as $product) {//For all products
            
            //Get prefix
            $prefix = WOO_VOU_META_PREFIX;
            
            //Get product ID
            $product_id = isset($product->id) ? $product->id : '';
            
            //Get variation ID
            $variation_id = isset($product->variation_id) ? $product->variation_id : $product->id;
            
            //voucher enable or not
            $voucher_enable = $this->model->woo_vou_check_enable_voucher($product_id, $variation_id);
            
            if ($voucher_enable) {
                
                //Get product recipient meta setting
                $recipient_data = $this->model->woo_vou_get_product_recipient_meta($product->id);
                
                //Recipient name fields
                $enable_recipient_name     = $recipient_data['enable_recipient_name'];
                $recipient_name_lable      = $recipient_data['recipient_name_lable'];
                $recipient_name_max_length = $recipient_data['recipient_name_max_length'];
                
                //Recipient email fields
                $enable_recipient_email = $recipient_data['enable_recipient_email'];
                $recipient_email_label  = $recipient_data['recipient_email_label'];
                
                //Recipient message fields
                $enable_recipient_message     = $recipient_data['enable_recipient_message'];
                $recipient_message_label      = $recipient_data['recipient_message_label'];
                $recipient_message_max_length = $recipient_data['recipient_message_max_length'];
                
                // check if enable Recipient Detail
                if ($enable_recipient_email == 'yes' || $enable_recipient_name == 'yes' || $enable_recipient_message == 'yes') {
                    
                    $recipient_name    = isset($_POST[$prefix . 'recipient_name'][$variation_id]) ? $this->model->woo_vou_escape_attr($_POST[$prefix . 'recipient_name'][$variation_id]) : '';
                    $recipient_email   = isset($_POST[$prefix . 'recipient_email'][$variation_id]) ? $this->model->woo_vou_escape_attr($_POST[$prefix . 'recipient_email'][$variation_id]) : '';
                    $recipient_message = isset($_POST[$prefix . 'recipient_message'][$variation_id]) ? $this->model->woo_vou_escape_attr($_POST[$prefix . 'recipient_message'][$variation_id]) : '';
                    
                    ?>
                <div class="woo-vou-fields-wrapper<?php echo $product->is_type('variation') ? '-variation' : ''; ?>"
                     id="woo-vou-fields-wrapper-<?php echo $variation_id; ?>">
                    <table cellspacing="0" class="woo-vou-recipient-fields">
                        <tbody><?php
                        
                        if ($enable_recipient_name == 'yes') {
                            
                            $recipient_name_lable = !empty($recipient_name_lable) ? $recipient_name_lable : __('Recipient Name', 'woovoucher');
                            $name_maxlength       = intval($recipient_name_max_length);
                            ?>
                            <tr>
                            <td class="label">
                                <label
                                    for="recipient_name-<?php echo $variation_id; ?>"><?php echo $recipient_name_lable; ?></label>
                            </td>
                            <td class="value">
                                <input type="text"
                                       class="woo-vou-recipient-details" <?php if (!empty($name_maxlength)) {
                                    echo 'maxlength="' . $name_maxlength . '"';
                                } ?> value="<?php echo $recipient_name; ?>"
                                       id="recipient_name-<?php echo $variation_id; ?>"
                                       name="<?php echo $prefix; ?>recipient_name[<?php echo $variation_id; ?>]">
                            </td>
                            </tr><?php
                        }
                        if ($enable_recipient_email == 'yes') {
                            
                            $recipient_email_label = !empty($recipient_email_label) ? $recipient_email_label : __('Recipient Email', 'woovoucher'); ?>
                            <tr>
                            <td class="label">
                                <label
                                    for="recipient_email-<?php echo $variation_id; ?>"><?php echo $recipient_email_label; ?></label>
                            </td>
                            <td class="value">
                                <input type="text" class="woo-vou-recipient-details"
                                       value="<?php echo $recipient_email; ?>"
                                       id="recipient_email-<?php echo $variation_id; ?>"
                                       name="<?php echo $prefix; ?>recipient_email[<?php echo $variation_id; ?>]">
                            </td>
                            </tr><?php
                        }
                        if ($enable_recipient_message == 'yes') {
                            
                            $recipient_message_label = !empty($recipient_message_label) ? $recipient_message_label : __('Message to Recipient', 'woovoucher');
                            $msg_maxlength           = intval($recipient_message_max_length);
                            ?>
                            <tr>
                            <td class="label">
                                <label
                                    for="recipient_message-<?php echo $variation_id; ?>"><?php echo $recipient_message_label; ?></label>
                            </td>
                            <td class="value">
                                <textarea <?php if (!empty($msg_maxlength)) {
                                    echo 'maxlength="' . $msg_maxlength . '"';
                                } ?> class="woo-vou-recipient-details"
                                     id="recipient_message-<?php echo $variation_id; ?>"
                                     name="<?php echo $prefix; ?>recipient_message[<?php echo $variation_id; ?>]"><?php echo $recipient_message; ?></textarea>
                            </td>
                            </tr><?php
                        } ?>
                        </tbody>
                    </table>
                    </div><?php
                }
            }
        }
    }
    

    /**
     * add to cart in item data
     *
     * Handles to add to cart in item data
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id)
    {
        
        $data_id = !empty($variation_id) ? $variation_id : $product_id;
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        if (isset($_POST[$prefix . 'recipient_name'])) {//If recipient name is set
            $cart_item_data[$prefix . 'recipient_name'] = $this->model->woo_vou_escape_slashes_deep($_POST[$prefix . 'recipient_name'][$data_id]);
        }
        
        if (isset($_POST[$prefix . 'recipient_email'])) {//If recipient email is set
            $cart_item_data[$prefix . 'recipient_email'] = $this->model->woo_vou_escape_slashes_deep($_POST[$prefix . 'recipient_email'][$data_id]);
        }
        
        if (isset($_POST[$prefix . 'recipient_message'])) {//If recipient message is set
            $cart_item_data[$prefix . 'recipient_message'] = $this->model->woo_vou_escape_slashes_deep($_POST[$prefix . 'recipient_message'][$data_id]);
        }
        
        return $cart_item_data;
    }
    
    /**
     * get to cart in item data to display in cart page
     *
     * Handles to get to cart in item data to display in cart page
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_woocommerce_get_item_data($data, $item)
    {
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        //Get Product ID
        $product_id = isset($item['product_id']) ? $item['product_id'] : '';
        
        //Get product recipient meta setting
        $recipient_data = $this->model->woo_vou_get_product_recipient_meta($product_id);
        
        //recipient name lable
        $recipient_name_lable = $recipient_data['recipient_name_lable'];
        
        //recipient email lable
        $recipient_email_label = $recipient_data['recipient_email_label'];
        
        //recipient message lable
        $recipient_message_label = $recipient_data['recipient_message_label'];
        
        if (!empty($item[$prefix . 'recipient_name'])) {
            
            $data[] = array(
                'name'    => $recipient_name_lable,
                'display' => $item[$prefix . 'recipient_name'],
                'hidden'  => false,
                'value'   => '',
            );
        }
        
        if (!empty($item[$prefix . 'recipient_email'])) {
            
            $data[] = array(
                'name'    => $recipient_email_label,
                'display' => $item[$prefix . 'recipient_email'],
                'hidden'  => false,
                'value'   => '',
            );
        }
        
        if (!empty($item[$prefix . 'recipient_message'])) {
            
            $data[] = array(
                'name'    => $recipient_message_label,
                'display' => $item[$prefix . 'recipient_message'],
                'hidden'  => false,
                'value'   => '',
            );
        }
        
        return $data;
    }
    
    /**
     * add to cart in item data from session
     *
     * Handles to add to cart in item data from session
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_get_cart_item_from_session($cart_item, $values)
    {
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        if (!empty($values[$prefix . 'recipient_name'])) {//Recipient Name
            $cart_item[$prefix . 'recipient_name'] = $values[$prefix . 'recipient_name'];
        }
        
        if (!empty($values[$prefix . 'recipient_email'])) {//Recipient Email
            $cart_item[$prefix . 'recipient_email'] = $values[$prefix . 'recipient_email'];
        }
        
        if (!empty($values[$prefix . 'recipient_message'])) {//Recipient Message
            $cart_item[$prefix . 'recipient_message'] = $values[$prefix . 'recipient_message'];
        }
        
        return $cart_item;
    }
    
    /**
     * add cart item to the order.
     *
     * Handles to add cart item to the order.
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_add_order_item_meta($item_id, $values)
    {
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        //Initilize recipients labels
        $woo_vou_recipient_labels = array();
        
        //Get product ID
        $_product_id = isset($values['product_id']) ? $values['product_id'] : '';
        
        $recipient_labels = $this->model->woo_vou_get_product_recipient_meta($_product_id);
        
        if (!empty($values[$prefix . 'recipient_name'])) {//Add recipient name field
            
            wc_add_order_item_meta(
                $item_id, $prefix . 'recipient_name', array(
                            'label' => $recipient_labels['recipient_name_lable'],
                            'value' => $values[$prefix . 'recipient_name'],
                        )
            );
            
            wc_add_order_item_meta($item_id, $recipient_labels['recipient_name_lable'], $values[$prefix . 'recipient_name']);
        }
        
        if (!empty($values[$prefix . 'recipient_email'])) {//Add recipient email field
            
            wc_add_order_item_meta(
                $item_id, $prefix . 'recipient_email', array(
                            'label' => $recipient_labels['recipient_email_label'],
                            'value' => $values[$prefix . 'recipient_email'],
                        )
            );
            
            wc_add_order_item_meta($item_id, $recipient_labels['recipient_email_label'], $values[$prefix . 'recipient_email']);
        }
        
        if (!empty($values[$prefix . 'recipient_message'])) {//Add recipient message field
            
            wc_add_order_item_meta(
                $item_id, $prefix . 'recipient_message', array(
                            'label' => $recipient_labels['recipient_message_label'],
                            'value' => $values[$prefix . 'recipient_message'],
                        )
            );
            
            wc_add_order_item_meta($item_id, $recipient_labels['recipient_message_label'], $values[$prefix . 'recipient_message']);
        }
    }
    
    /**
     * This is used to ensure any required user input fields are supplied
     *
     * Handles to This is used to ensure any required user input fields are supplied
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_add_to_cart_validation($valid, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array())
    {
        
        //Get prefix
        $prefix      = WOO_VOU_META_PREFIX;
        $_product_id = $variation_id ? $variation_id : $product_id;
        $product     = wc_get_product($_product_id);
        
        //voucher enable or not
        $voucher_enable = $this->model->woo_vou_check_enable_voucher($product_id, $variation_id);
        
        if ($voucher_enable) {//If voucher enable
            
            //Get product recipient meta setting
            $recipient_data = $this->model->woo_vou_get_product_recipient_meta($product_id);
            
            if (isset($_POST[$prefix . 'recipient_name'][$_product_id])) {//Strip recipient name
                $_POST[$prefix . 'recipient_name'][$_product_id] = $this->model->woo_vou_escape_slashes_deep(trim($_POST[$prefix . 'recipient_name'][$_product_id]));
            }
            if (isset($_POST[$prefix . 'recipient_email'][$_product_id])) {//Strip recipient email
                $_POST[$prefix . 'recipient_email'][$_product_id] = $this->model->woo_vou_escape_slashes_deep(trim($_POST[$prefix . 'recipient_email'][$_product_id]));
            }
            if (isset($_POST[$prefix . 'recipient_message'][$_product_id])) {//Strip recipient message
                $_POST[$prefix . 'recipient_message'][$_product_id] = $this->model->woo_vou_escape_slashes_deep(trim($_POST[$prefix . 'recipient_message'][$_product_id]));
            }
            
            //recipient name field validation
            if ($recipient_data['enable_recipient_name'] == 'yes' && $recipient_data['recipient_name_is_required'] == 'yes' && empty($_POST[$prefix . 'recipient_name'][$_product_id])) {
                wc_add_notice('<p class="woo-vou-recipient-error">' . __("Field " . $recipient_data['recipient_name_lable'] . " is required.", 'woovoucher') . '</p>', 'error');
                $valid = false;
            }
            
            //recipient email field validation
            if ($recipient_data['enable_recipient_email'] == 'yes' && $recipient_data['recipient_email_is_required'] == 'yes' && empty($_POST[$prefix . 'recipient_email'][$_product_id])) {
                wc_add_notice('<p class="woo-vou-recipient-error">' . __("Field " . $recipient_data['recipient_email_label'] . " is required.", 'woovoucher') . '</p>', 'error');
                $valid = false;
            }
            
            //recipient email valid email validation
            if (!empty($_POST[$prefix . 'recipient_email'][$_product_id]) && !is_email($_POST[$prefix . 'recipient_email'][$_product_id])) {
                wc_add_notice('<p class="woo-vou-recipient-error">' . __("Please Enter Valid " . $recipient_data['recipient_email_label'] . ".", 'woovoucher') . '</p>', 'error');
                $valid = false;
            }
            
            //recipient message validation
            if ($recipient_data['enable_recipient_message'] == 'yes' && $recipient_data['recipient_message_is_required'] == 'yes' && empty($_POST[$prefix . 'recipient_message'][$_product_id])) {
                wc_add_notice('<p class="woo-vou-recipient-error">' . __("Field " . $recipient_data['recipient_message_label'] . " is required.", 'woovoucher') . '</p>', 'error');
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    /**
     * This is used to send an email after order completed to recipient user
     *
     * Handles to send an email after order completed
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_payment_process_or_complete($order_id)
    {
        
        global $wpdb;
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        //Get order
        $cart_details = new Wc_Order($order_id);
        
        if ($cart_details->status == 'processing' && get_option('woocommerce_downloads_grant_access_after_payment') == 'no') {
            return;
        }
        
        // record the fact that the vouchers have been sent
        if (get_post_meta($order_id, $prefix . 'recipient_email_sent', true)) {
            return;
        }
        
        $order_items = $cart_details->get_items();
        $first_name  = isset($cart_details->billing_first_name) ? $cart_details->billing_first_name : '';
        $last_name   = isset($cart_details->billing_last_name) ? $cart_details->billing_last_name : '';
        
        if (!empty($order_items)) {//if item is empty
            
            foreach ($order_items as $product_item_key => $product_data) {
                
                $download_file_data = $cart_details->get_item_downloads($product_data);
                $product_id         = isset($product_data['product_id']) ? $product_data['product_id'] : '';
                $variation_id       = isset($product_data['variation_id']) ? $product_data['variation_id'] : '';
                
                //vendor sale notification
                $this->model->woo_vou_vendor_sale_notification($product_id, $variation_id, $product_item_key, $product_data, $order_id, $cart_details);
                
                //Initilize recipient detail
                $recipient_details = array();
                
                //Get product item meta
                $product_item_meta = isset($product_data['item_meta']) ? $product_data['item_meta'] : array();
                
                $recipient_details = $this->model->woo_vou_get_recipient_data($product_item_meta);
                
                $links      = array();
                $i          = 0;
                $attach_key = array();
                
                foreach ($download_file_data as $key => $download_file) {
                    
                    $check_key = strpos($key, 'woo_vou_pdf_');
                    
                    if (!empty($download_file) && $check_key !== false) {
                        
                        $attach_keys[] = $key;
                        $i++;
                        $links[] = '<small><a href="' . esc_url($download_file['download_url']) . '">' . sprintf(__('Download file%s', 'woovoucher'), (count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_html($download_file['name']) . '</a></small>';
                    }
                }
                
                $recipient_details['recipient_voucher'] = '<br/>' . implode('<br/>', $links);
                
                // added filter to send extra emails on diferent email ids by other extensions
                $woo_vou_extra_emails = false;
                $woo_vou_extra_emails = apply_filters('woo_vou_pdf_recipient_email', $woo_vou_extra_emails, $product_id);
                
                if ((isset($recipient_details['recipient_email']) && !empty($recipient_details['recipient_email'])) ||
                    (!empty($woo_vou_extra_emails))
                ) {
                    
                    $recipient_name    = isset($recipient_details['recipient_name']) ? $recipient_details['recipient_name'] : '';
                    $recipient_email   = isset($recipient_details['recipient_email']) ? $recipient_details['recipient_email'] : '';
                    $recipient_message = isset($recipient_details['recipient_message']) ? '"' . nl2br($recipient_details['recipient_message']) . '"' : '';
                    $recipient_voucher = isset($recipient_details['recipient_voucher']) ? $recipient_details['recipient_voucher'] : '';
                    
                    // Get Extra email if passed through filter
                    $woo_vou_extra_emails = !empty($woo_vou_extra_emails) ? $woo_vou_extra_emails : '';
                    
                    $attachments = array();
                    
                    if (get_option('vou_attach_mail') == 'yes') {//If attachment enable
                        
                        //Get product/variation ID
                        $product_id = !empty($product_data['variation_id']) ? $product_data['variation_id'] : $product_data['product_id'];
                        
                        if (!empty($attach_keys)) {//attachments keys not empty
                            
                            foreach ($attach_keys as $attach_key) {
                                
                                $attach_pdf_file_name = get_option('attach_pdf_name');
                                $attach_pdf_file_name = !empty($attach_pdf_file_name) ? $attach_pdf_file_name : 'woo-voucher-';
                                
                                // Replacing voucher pdf name with given value
                                $orderdvoucode_key = str_replace('woo_vou_pdf_', $attach_pdf_file_name, $attach_key);
                                
                                //Voucher attachment path
                                $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $orderdvoucode_key . '-' . $product_id . '-' . $product_item_key . '-' . $order_id; // Voucher pdf path
                                $vou_pdf_name = $vou_pdf_path . '.pdf';
                                
                                // If voucher pdf exist in folder
                                if (file_exists($vou_pdf_name)) {
                                    
                                    // Adding the voucher pdf in attachment array
                                    $attachments[] = $vou_pdf_name;
                                }
                            }
                        }
                    }
                    
                    //Get All Data for gift notify
                    $gift_data = array(
                        'first_name'           => $first_name,
                        'last_name'            => $last_name,
                        'recipient_name'       => $recipient_name,
                        'recipient_email'      => $recipient_email,
                        'recipient_message'    => $recipient_message,
                        'voucher_link'         => $recipient_voucher,
                        'attachments'          => $attachments,
                        'woo_vou_extra_emails' => $woo_vou_extra_emails,
                    );
                    
                    //Fires when gift notify.
                    do_action('woo_vou_gift_email', $gift_data);
                }
            } //end foreach
        }
        
        //Update post meta for email attachment issue
        update_post_meta($order_id, $prefix . 'recipient_email_sent', true);
    }
    
    /**
     * Hide Recipient Itemmeta
     *
     * Handle to hide recipient itemmeta
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_hide_recipient_itemmeta($item_meta = array())
    {
        
        $prefix = WOO_VOU_META_PREFIX;
        
        $item_meta[] = $prefix . 'recipient_name';
        $item_meta[] = $prefix . 'recipient_email';
        $item_meta[] = $prefix . 'recipient_message';
        $item_meta[] = $prefix . 'codes';
        
        return $item_meta;
    }
    
    /**
     * Handles the functionality to attach the voucher pdf in mail
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0
     */
    public function woo_vou_attach_voucher_to_email($attachments, $status, $order)
    {
        
        // Taking status array
        $vou_status = array('customer_processing_order', 'customer_completed_order', 'customer_invoice');
        
        // Taking order status array
        $vou_order_status = array('wc-completed');
        
        $order_status               = !empty($order->post_status) ? $order->post_status : ''; // Order status
        $vou_attach_mail            = get_option('vou_attach_mail'); // Getting voucher attach option
        $grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment'); // Woocommerce grant access after payment
        
        if ($vou_attach_mail == 'yes' && !empty($order) && ((in_array($status, $vou_status) && in_array($order_status, $vou_order_status)) || ($status == 'customer_processing_order' && $grant_access_after_payment == 'yes' && $order_status != 'wc-on-hold'))) {
            
            $prefix          = WOO_VOU_META_PREFIX;
            $vou_attachments = array();
            $order_id        = !empty($order->id) ? $order->id : ''; // Taking order id
            $cart_details    = new Wc_Order($order_id);
            $order_items     = $cart_details->get_items();
            
            if (!empty($order_items)) {//not empty items
                
                //foreach items
                foreach ($order_items as $item_id => $download_data) {
                    
                    $product_id   = !empty($download_data['product_id']) ? $download_data['product_id'] : '';
                    $variation_id = !empty($download_data['variation_id']) ? $download_data['variation_id'] : '';
                    
                    //Get data id vriation id or product id
                    $data_id = !empty($variation_id) ? $variation_id : $product_id;
                    
                    //Check voucher enable or not
                    $enable_voucher = $this->model->woo_vou_check_enable_voucher($product_id, $variation_id);
                    
                    if ($enable_voucher) {
                        
                        // Get mutiple pdf option from order meta
                        $multiple_pdf = !empty($order_id) ? get_post_meta($order_id, $prefix . 'multiple_pdf', true) : '';
                        
                        $orderdvoucodes = array();
                        
                        if ($multiple_pdf == 'yes') {
                            $orderdvoucodes = $this->model->woo_vou_get_multi_voucher($order_id, $data_id, $item_id);
                        } else {
                            $orderdvoucodes['woo_vou_pdf_1'] = '';
                        }
                        
                        // If order voucher codes are not empty
                        if (!empty($orderdvoucodes)) {
                            
                            foreach ($orderdvoucodes as $orderdvoucode_key => $orderdvoucode_val) {
                                
                                if (!empty($orderdvoucode_key)) {
                                    
                                    $attach_pdf_file_name = get_option('attach_pdf_name');
                                    $attach_pdf_file_name = isset($attach_pdf_file_name) ? $attach_pdf_file_name : 'woo-voucher-';
                                    
                                    //Get Pdf Key
                                    $pdf_vou_key = $orderdvoucode_key;
                                    
                                    // Replacing voucher pdf name with given value
                                    $orderdvoucode_key = str_replace('woo_vou_pdf_', $attach_pdf_file_name, $orderdvoucode_key);
                                    
                                    // Voucher pdf path and voucher name
                                    $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $orderdvoucode_key . '-' . $data_id . '-' . $item_id . '-' . $order_id; // Voucher pdf path
                                    $vou_pdf_name = $vou_pdf_path . '.pdf';
                                    
                                    // If voucher pdf does not exist in folder
                                    if (!file_exists($vou_pdf_name)) {
                                        
                                        $pdf_args = array(
                                            'pdf_vou_key' => $pdf_vou_key,
                                            'pdf_name'    => $vou_pdf_path,
                                            'save_file'   => true,
                                        );
                                        
                                        //Generatin pdf
                                        woo_vou_process_product_pdf($data_id, $order_id, $item_id, $orderdvoucodes, $pdf_args);
                                    }
                                    
                                    // If voucher pdf exist in folder
                                    if (file_exists($vou_pdf_name)) {
                                        $attachments[] = $vou_pdf_name; // Adding the voucher pdf in attachment array
                                    }
                                }
                            }
                        } // End of orderdvoucodes
                    }
                }
            } // End of order item
        }
        
        return $attachments;
    }
    
    /**
     * Update Cart for unique item
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0.0
     */
    public function woo_vou_add_to_cart_data($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        
        global $woocommerce;
        
        //Get voucher enable or not
        $enable_voucher = $this->model->woo_vou_check_enable_voucher($product_id, $variation_id);
        
        //enable voucher recipient
        $enable_recipient = $this->model->woo_vou_check_enable_recipient($product_id);
        
        if ($enable_voucher && $enable_recipient) {//If enable voucher
            
            //exist item key
            $exist_item_key = '';
            
            //get cart object
            $cart_object   = isset($woocommerce->cart) ? $woocommerce->cart : array();
            $cart_contents = isset($cart_object->cart_contents) ? $cart_object->cart_contents : array();
            
            //current Item ID
            $current_item_id = !empty($variation_id) ? $variation_id : $product_id;
            
            $sold_individually = get_post_meta($product_id, '_sold_individually', true);
            
            if ($sold_individually != 'yes') {// Sold Individually
                
                if (!empty($cart_contents)) {//if empty cart content
                    
                    foreach ($cart_contents as $item_key => $cart_content) {
                        
                        $exist_item_id = !empty($cart_content['variation_id']) ? $cart_content['variation_id'] : $cart_content['product_id'];
                        
                        if (($cart_item_key != $item_key) && ($current_item_id == $exist_item_id)) {
                            
                            //Assign existing item key
                            $exist_item_key = $item_key;
                            break;
                        }
                    }
                }
                
                //If product already add into cart
                if (!empty($exist_item_key) && !empty($cart_contents[$exist_item_key])) {
                    
                    //existing item data
                    $exist_item_data = $cart_contents[$exist_item_key];
                    
                    //get quantity
                    $exist_quantity = $exist_item_data['quantity'];
                    
                    //new quantity
                    $new_quantity = $quantity + $exist_quantity;
                    
                    //delete exist item
                    $cart_object->set_quantity($exist_item_key, 0);
                    
                    //add new item with quantity
                    $cart_object->set_quantity($cart_item_key, $new_quantity);
                }
            }
        }
    }
    
    /**
     * Check voucher code using qrcode and barcode
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.0.3
     */
    public function woo_vou_check_qrcode()
    {
        
        if (isset($_GET['woo_vou_code']) && !empty($_GET['woo_vou_code'])) {
            
            require_once(WOO_VOU_DIR . '/includes/woo-vou-check-qrcode.php');
        }
    }
    
    /**
     * Add Voucher When Add Order Manually
     *
     * Haldle to add voucher codes
     * when add order manually from backend
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.2.1
     */
    public function woo_vou_process_shop_order_manually($order_id)
    {
        
        if (!empty($_POST['order_item_id'])) {//If order item are not empty
            
            //Process voucher code functionality
            $this->woo_vou_product_purchase($order_id);
        }
    }
    
    /**
     * Hide recipient variation from product name field
     *
     * Handle to hide recipient variation from product name field
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.3.0
     */
    public function woo_vou_hide_recipients_item_variations($product_variations = array(), $product_item_meta = array())
    {
        
        $prefix = WOO_VOU_META_PREFIX;
        
        $recipient_string = '';
        
        //Get product ID
        $product_id = isset($product_item_meta['_product_id']) ? $product_item_meta['_product_id'] : '';
        
        //Get product recipient lables
        $product_recipient_lables = $this->model->woo_vou_get_product_recipient_meta($product_id);
        
        if (isset($product_item_meta[$prefix . 'recipient_name']) && !empty($product_item_meta[$prefix . 'recipient_name'][0])) {
            if (is_serialized($product_item_meta[$prefix . 'recipient_name'][0])) { // New recipient name field
                
                $recipient_name_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_name'][0]);
                $recipient_name_lable  = isset($recipient_name_fields['label']) ? $recipient_name_fields['label'] : $product_recipient_lables['recipient_name_lable'];
                
                if (isset($product_variations[$recipient_name_lable])) {
                    unset($product_variations[$recipient_name_lable]);
                }
            }
        }
        
        if (isset($product_item_meta[$prefix . 'recipient_email']) && !empty($product_item_meta[$prefix . 'recipient_email'][0])) {
            if (is_serialized($product_item_meta[$prefix . 'recipient_email'][0])) { // New recipient email field
                
                $recipient_email_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_email'][0]);
                $recipient_email_lable  = isset($recipient_email_fields['label']) ? $recipient_email_fields['label'] : $product_recipient_lables['recipient_email_label'];
                
                if (isset($product_variations[$recipient_email_lable])) {
                    unset($product_variations[$recipient_email_lable]);
                }
            }
        }
        
        if (isset($product_item_meta[$prefix . 'recipient_message']) && !empty($product_item_meta[$prefix . 'recipient_message'][0])) {
            if (is_serialized($product_item_meta[$prefix . 'recipient_message'][0])) { // New recipient message field
                
                $recipient_msg_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_message'][0]);
                $recipient_msg_lable  = isset($recipient_msg_fields['label']) ? $recipient_msg_fields['label'] : $product_recipient_lables['recipient_message_label'];
                
                if (isset($product_variations[$recipient_msg_lable])) {
                    unset($product_variations[$recipient_msg_lable]);
                }
            }
        }
        
        return $product_variations;
    }
    
    /**
     * Set Global Item ID For Voucher Key Generater
     *
     * Handle to Set global item id for voucher key generater
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.3.0
     */
    public function woo_vou_set_global_item_id($product, $item, $order)
    {
        
        global $woo_vou_item_id;
        
        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        $product_item_meta = isset($item['item_meta']) ? $item['item_meta'] : array();
        
        //Get voucher codes
        $voucher_codes = isset($product_item_meta[$prefix . 'codes'][0]) ? $product_item_meta[$prefix . 'codes'][0] : '';
        
        if (!empty($voucher_codes)) {
            
            //Get order items
            $order_items = $order->get_items();
            
            if (!empty($order_items)) { // If order not empty
                
                // Check cart details
                foreach ($order_items as $item_id => $item) {
                    
                    //Get voucher codes
                    $codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                    
                    if ($codes == $voucher_codes) {//If voucher code matches
                        $woo_vou_item_id = $item_id;
                        break;
                    }
                }
            }
        }
        
        return $product;
    }
    
    /**
     * Add Item Id In Download URL
     *
     * Handle to add item id in generate pdf download URL
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.3.0
     */
    public function woo_vou_add_item_id_in_download_pdf_url($files, $item, $abs_order)
    {
        
        global $woo_vou_item_id;
        
        if (!empty($files)) { //If files not empty
            
            foreach ($files as $file_key => $file_data) {
                
                //Check key is for pdf voucher
                $check_key = strpos($file_key, 'woo_vou_pdf_');
                
                if ($check_key !== false) {
                    
                    //Get download URL
                    $download_url = isset($files[$file_key]['download_url']) ? $files[$file_key]['download_url'] : '';
                    
                    //Add item id in download URL
                    $download_url = add_query_arg(array('item_id' => $woo_vou_item_id), $download_url);
                    
                    //Store download URL agaiin
                    $files[$file_key]['download_url'] = $download_url;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Adding Hooks
     *
     * Adding proper hoocks for the discount codes
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.3.1
     */
    public function woo_vou_my_pdf_vouchers_download_link($downloads = array())
    {
        //get prefix
        $prefix = WOO_VOU_META_PREFIX;
        
        if (is_user_logged_in()) {//If user is logged in

            //Get user ID
            $user_id = get_current_user_id();

            //Get User Order Arguments
            $args = array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => WOO_VOU_MAIN_SHOP_POST_TYPE,
                'post_status' => array('wc-completed'),
                'meta_query'  => array(
                    array(
                        'key'     => $prefix . 'meta_order_details',
                        'compare' => 'EXISTS',
                    ),
                ),
            );

            //user orders
            $user_orders = get_posts($args);
            
            if (!empty($user_orders)) {//If orders are not empty
                
                foreach ($user_orders as $user_order) {
                    
                    //Get order ID
                    $order_id = isset($user_order->ID) ? $user_order->ID : '';
                    
                    if (!empty($order_id)) {//Order it not empty
                        
                        global $vou_order;
                        
                        //Set global order ID
                        $vou_order = $order_id;
                        
                        //Get cart details
                        $cart_details = new Wc_Order($order_id);
                        $order_items  = $cart_details->get_items();
                        
                        $order_date = isset($cart_details->order_date) ? $cart_details->order_date : '';
                        $order_date = date('d-m-y', strtotime($order_date));

                        if (!empty($order_items)) {// Check cart details are not empty
                            
                            foreach ($order_items as $item_id => $product_data) {
                                
                                //Get product from Item ( It is required otherwise multipdf voucher link not work )
                                $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);
                                
                                if (!$_product) {//If product deleted
                                    $download_file_data = array();
                                } else {
                                    //Get download files
                                    $download_file_data = $cart_details->get_item_downloads($product_data);
                                }
                                
                                //Get voucher codes
                                $codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

                                if (!empty($download_file_data) && !empty($codes)) {//If download exist and code is not empty
                                    
                                    foreach ($download_file_data as $key => $download_file) {
                                        
                                        //check download key is voucher key or not
                                        $check_key = strpos($key, 'woo_vou_pdf_');
                                        
                                        //get voucher number
                                        $voucher_number = str_replace('woo_vou_pdf_', '', $key);
                                        
                                        if (empty($voucher_number)) {//If empty voucher number
                                            
                                            $voucher_number = 1;
                                        }

                                        if (!empty($download_file) && $check_key !== false) {
                                            
                                            //Get download URL
                                            $download_url = $download_file['download_url'];
                                            
                                            //add arguments array
                                            $add_arguments = array('item_id' => $item_id);
                                            
                                            //PDF Download URL
                                            $download_url = add_query_arg($add_arguments, $download_url);
                                            
                                            //get product name
                                            $product_name = isset($_product->post->post_title) ? $_product->post->post_title : '';

                                            //Download file arguments
                                            $download_args = array(
                                                'download_url'        => $download_url,
                                                'download_name'       => $product_name . ' - ' . $download_file['name'] . ' ' . $voucher_number . ' ( ' . $order_date . ' )',
                                                'downloads_remaining' => '',
                                            );
                                            
                                            //append voucher download to downloads array
                                            $downloads[] = $download_args;
                                        }
                                    }
                                }
                            }
                        }
                        
                        //reset global order ID
                        $vou_order = 0;
                    }
                }
            }
        }
        
        return $downloads;
    }
    
    /**
     * Restore Voucher When Resume Order
     *
     * Handle to restore old deduct voucher
     * when item overwite in meta field
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.0
     */
    public function woo_vou_resume_order_voucher_codes($order_id)
    {
        
        $this->model->woo_vou_restore_order_voucher_codes($order_id);
    }
    
    /**
     * Update product stock as per voucher codes when woocommerce deduct stock
     *
     * As woocommrece reduce stock quantity on product purchase and so we have to update stock
     * to no of voucher codes
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.0
     */
    public function woo_vou_update_order_stock($order)
    {
        
        $prefix = WOO_VOU_META_PREFIX;
        
        // loop for each item
        foreach ($order->get_items() as $item) {
            
            if ($item['product_id'] > 0) {
                $_product = $order->get_product_from_item($item);
                
                if ($_product && $_product->exists() && $_product->managing_stock()) {
                    
                    $product_id   = $item['product_id'];
                    $variation_id = isset($item['variation_id']) ? $item['variation_id'] : '';
                    
                    // check voucher is enabled for this product
                    if ($this->model->woo_vou_check_enable_voucher($product_id, $variation_id)) {
                        
                        //vendor user
                        $vendor_user = get_post_meta($product_id, $prefix . 'vendor_user', true);
                        
                        //get vendor detail
                        $vendor_detail = $this->model->woo_vou_get_vendor_detail($product_id, $vendor_user);
                        
                        //using type of voucher
                        $using_type = isset($vendor_detail['using_type']) ? $vendor_detail['using_type'] : '';
                        
                        // if using type is one time only
                        if (empty($using_type)) {
                            
                            //voucher codes
                            $vou_codes = $this->model->woo_vou_get_voucher_code($product_id, $variation_id);
                            
                            // convert voucher code comma seperate string into array
                            $vou_codes = !empty($vou_codes) ? explode(',', $vou_codes) : array();
                            
                            // update stock quanity
                            $this->model->woo_vou_update_product_stock($product_id, $variation_id, $vou_codes);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * expired/upcoming product
     *
     * Handles to Remove add to cart product button and display expired/upcoming product
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.2
     */
    public function woo_vou_display_expiry_product()
    {
        
        global $product;
        
        $expired = $this->model->woo_vou_check_product_is_expired($product);
        
        if ($expired == 'upcoming') {
            // remove add to cart button from single product page
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            // get expired/upcoming template
            woo_vou_get_template('expired/expired.php', array('expired' => $expired));
        } elseif ($expired == 'expired') {
            // remove add to cart button from single product page
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            // get expired/upcoming template
            woo_vou_get_template('expired/expired.php', array('expired' => $expired));
        }
    }
    
    /**
     * expired/upcoming product on shop page
     *
     * Handles to Remove add to cart product button on shop page when product is upcomming or expired
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.2
     */
    public function woo_vou_shop_add_to_cart($add_to_cart_html)
    {
        
        global $product;
        
        $expired = $this->model->woo_vou_check_product_is_expired($product);
        
        if ($expired == 'upcoming' || $expired == 'expired') {
            return ''; // do not display add to cart button
        }
        
        return $add_to_cart_html;
    }
    
    /**
     * Prevent product from being added to cart (free or priced) with ?add-to-cart=XXX
     * When product expired or upcoming
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.0
     */
    public function woo_vou_prevent_product_add_to_cart($passed, $product_id)
    {
        
        // Get complete product details from product id
        $product = wc_get_product($product_id);
        
        $expired = $this->model->woo_vou_check_product_is_expired($product);
        
        if ($expired == 'upcoming') {
            wc_add_notice(__('You can not add upcoming products to cart.', 'woovoucher'), 'error');
            $passed = false;
        } elseif ($expired == 'expired') {
            wc_add_notice(__('You can not add expired products to cart.', 'woovoucher'), 'error');
            $passed = false;
        }
        
        return $passed;
    }
    
    /**
     * Valiate product added in cart is expired/upcoming
     *
     * Handles to display error if proudct added in cart is expired/upcoming
     *
     * @package WooCommerce - PDF Vouchers
     * @since   2.4.0
     */
    /*public function woo_vou_woocommerce_checkout_process()
    {
        
        // get added products in cart
        $cart_details = WC()->session->cart;
        if (!empty($cart_details)) { // if cart is not empty
            
            foreach ($cart_details as $key => $product_data) {
                
                // get product id
                $product_id = $product_data['product_id'];
                
                // Get complete product details from product id
                $product = wc_get_product($product_id);
                
                // check product is expired/upcoming
                $expired = $this->model->woo_vou_check_product_is_expired($product);
                if ($expired == 'upcoming') {
                    wc_add_notice(sprintf(__('%s is no longer available.', 'woovoucher'), $product->post->post_title), 'error');
                    
                    return;
                } elseif ($expired == 'expired') {
                    wc_add_notice(sprintf(__('%s is no longer available.', 'woovoucher'), $product->post->post_title), 'error');
                    
                    return;
                }
            }
        }
    }*/

    public function woo_vou_woocommerce_checkout_process($order_id, $posted)
    {
        global $wpdb;

        if (empty($order_id)) {
            return;
        }

        $order         = wc_get_order($order_id);
        $order_address = array();

        // get added products in cart
        $cart_details = WC()->session->cart;

        if (!empty($cart_details)) { // if cart is not empty

            foreach ($cart_details as $key => $product_data) {

                // get product id
                $product_id       = $product_data['product_id'];
                $recipient_labels = $this->model->woo_vou_get_product_recipient_meta($product_id);

                if (isset($_POST['recipient_first_name']) && isset($_POST['recipient_last_name'])) {//If recipient name is set
                    $recipient_first_name = $this->model->woo_vou_escape_slashes_deep($_POST['recipient_first_name']);
                    $recipient_last_name  = $this->model->woo_vou_escape_slashes_deep($_POST['recipient_last_name']);
                    $recipent_name        = sprintf("%s %s", $recipient_first_name, $recipient_last_name);

                    wc_add_order_item_meta(
                        $product_id, 'recipient_name', array(
                                       'label' => $recipient_labels['recipient_name_lable'],
                                       'value' => $recipent_name,
                                   )
                    );

                    wc_add_order_item_meta($product_id, $recipient_labels['recipient_name_lable'], $recipent_name);

                }

                if (isset($_POST['recipient_email'])) {//If recipient email is set
                    $recipient_email = $this->model->woo_vou_escape_slashes_deep($_POST['recipient_email']);
                    wc_add_order_item_meta(
                        $product_id, 'recipient_email', array(
                                       'label' => $recipient_labels['recipient_email_label'],
                                       'value' => $recipient_email,
                                   )
                    );

                    wc_add_order_item_meta($product_id, $recipient_labels['recipient_email_label'], $recipient_email);
                }

                if (isset($_POST['recipient_message'])) {//If recipient message is set
                    $recipient_message = $this->model->woo_vou_escape_slashes_deep($_POST['recipient_message']);
                    wc_add_order_item_meta(
                        $product_id, 'recipient_message', array(
                                       'label' => $recipient_labels['recipient_message_label'],
                                       'value' => $recipient_message,
                                   )
                    );

                    wc_add_order_item_meta($product_id, $recipient_labels['recipient_message_label'], $recipient_message);
                }
            }

            // set recipient informations to shipping

            if (isset($_POST['recipient_first_name'])) {
                $order_address['first_name'] = $_POST['recipient_first_name'];
            }
            if (isset($_POST['recipient_last_name'])) {
                $order_address['last_name'] = $_POST['recipient_last_name'];
            }
            if (isset($_POST['recipient_email'])) {
                $order_address['email'] = $_POST['recipient_email'];
            }
            if (isset($_POST['recipient_company'])) {
                $order_address['company'] = $_POST['recipient_company'];
            }
            if (isset($_POST['recipient_address_1'])) {
                WC()->customer->set_shipping_address($_POST['recipient_address_1']);
                $order_address['address_1'] = $_POST['recipient_address_1'];
            }
            if (isset($_POST['recipient_address_2'])) {
                WC()->customer->set_shipping_address_2($_POST['recipient_address_2']);
                $order_address['address_2'] = $_POST['recipient_address_2'];
            }
            if (isset($_POST['recipient_postcode'])) {
                WC()->customer->set_shipping_postcode($_POST['recipient_postcode']);
                $order_address['postcode'] = $_POST['recipient_postcode'];
            }
            if (isset($_POST['recipient_city'])) {
                WC()->customer->set_shipping_city($_POST['recipient_city']);
                $order_address['city'] = $_POST['recipient_city'];
            }
            if (isset($_POST['recipient_country'])) {
                WC()->customer->set_shipping_country($_POST['recipient_country']);
                $order_address['country'] = $_POST['recipient_country'];
            }
            if (isset($_POST['recipient_state'])) {
                WC()->customer->set_shipping_state($_POST['recipient_state']);
                $order_address['state'] = $_POST['recipient_state'];
            }

            try {
                // Start transaction if available
                $wpdb->query('START TRANSACTION');

                $order->set_address($order_address, 'shipping');

                $order_data              = array();
                $order_data['ID']        = $order_id;
                $order_data['post_type'] = 'shop_order';

                if (isset($_POST['recipient_message'])) {
                    $order_data['post_excerpt'] = $_POST['recipient_message'];
                    //$order_data['customer_note'] = 'note:' . $_POST['recipient_message'];
                    //$order_data['customer_message'] = $_POST['recipient_message'];
                }

                $order_id = wp_update_post($order_data);

                // If we got here, the order was created without problems!
                $wpdb->query('COMMIT');

            } catch (Exception $e) {
                // There was an error adding order data!
                $wpdb->query('ROLLBACK');

                return new WP_Error('checkout-error', $e->getMessage());
            }

        }
    }

    
    /**
     * Adding Hooks
     *
     * Adding proper hoocks for the discount codes
     *
     * @package WooCommerce - PDF Vouchers
     * @since   1.0.0
     */
    public function add_hooks()
    {
        
        //add capabilities to user roles
        add_action('init', array($this, 'woo_vou_initilize_role_capabilities'), 100);
        
        //add action to save voucher in order
        add_action('woocommerce_checkout_update_order_meta', array($this, 'woo_vou_product_purchase'));
        
        //add action for add custom notifications
        add_filter('woocommerce_email_actions', array($this, 'woo_vou_add_email_notification'));
        
        //add filter to merge voucher pdf with product files
        add_filter('woocommerce_product_files', array($this, 'woo_vou_downloadable_files'), 10, 2);
        
        //insert pdf vouchers in woocommerce downloads fiels table
        add_action('woocommerce_grant_product_download_permissions', array($this, 'woo_vou_insert_downloadable_files'));
        
        //add action to product process
        add_action('woocommerce_download_product', array($this, 'woo_vou_download_process'), 10, 6);
        
        //add filter to add admin access for vendor role
        add_filter('woocommerce_prevent_admin_access', array($this, 'woo_vou_prevent_admin_access'));
        
        //ajax call to edit all controls
        add_action('wp_ajax_woo_vou_check_voucher_code', array($this, 'woo_vou_check_voucher_code'));
        add_action('wp_ajax_nopriv_woo_vou_check_voucher_code', array($this, 'woo_vou_check_voucher_code'));
        
        //ajax call to save voucher code
        add_action('wp_ajax_woo_vou_save_voucher_code', array($this, 'woo_vou_save_voucher_code'));
        add_action('wp_ajax_nopriv_woo_vou_save_voucher_code', array($this, 'woo_vou_save_voucher_code'));
        
        // add action to add html for check voucher code
        add_action('woo_vou_check_code_content', array($this, 'woo_vou_check_code_content'));
        
        // add action to set order as a global variable
        add_action('woocommerce_email_before_order_table', array($this, 'woo_vou_email_before_order_table'));
        
        //filter to set order product data as a global variable
        add_filter('woocommerce_order_item_product', array($this, 'woo_vou_order_item_product'), 10, 2);
        
        //restore voucher codes if order is failed or cancled
        add_action('woocommerce_order_status_changed', array($this, 'woo_vou_restore_voucher_codes'), 10, 3);
        
        //add custom html to single product page before add to cart button
        add_action('woocommerce_before_add_to_cart_button', array($this, 'woo_vou_before_add_to_cart_button'));

        //add to cart in item data
        add_filter('woocommerce_add_cart_item_data', array($this, 'woo_vou_woocommerce_add_cart_item_data'), 10, 3);
        
        //check if item already added in cart
        add_action('woocommerce_add_to_cart', array($this, 'woo_vou_add_to_cart_data'), 10, 6);
        
        // add to cart in item data from session
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'woo_vou_get_cart_item_from_session'), 10, 2);
        
        // get to cart in item data to display in cart page
        add_filter('woocommerce_get_item_data', array($this, 'woo_vou_woocommerce_get_item_data'), 10, 2);
        
        // add action to add cart item to the order.
        add_action('woocommerce_add_order_item_meta', array($this, 'woo_vou_add_order_item_meta'), 10, 2);
        
        //add filter to validate custom fields of product page
        add_filter('woocommerce_add_to_cart_validation', array($this, 'woo_vou_add_to_cart_validation'), 10, 6);
        
        // add action when order status goes to complete
        add_action(
            'woocommerce_order_status_completed_notification', array(
                $this, 'woo_vou_payment_process_or_complete',
            ), 100
        );
        add_action(
            'woocommerce_order_status_pending_to_processing_notification', array(
            $this, 'woo_vou_payment_process_or_complete',
        ), 100
        );
        
        //add action to hide recipient in order meta
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'woo_vou_hide_recipient_itemmeta'));
        
        //filter to attach the voucher pdf in mail
        add_filter('woocommerce_email_attachments', array($this, 'woo_vou_attach_voucher_to_email'), 10, 3);
        
        //add action to check qrcode
        add_action('init', array($this, 'woo_vou_check_qrcode'));
        
        //Add order manually from backend
        add_action('woocommerce_process_shop_order_meta', array($this, 'woo_vou_process_shop_order_manually'));
        
        //Hide recipient variation from product name field
        add_filter('woo_vou_hide_recipient_variations', array($this, 'woo_vou_hide_recipients_item_variations'), 10, 2);
        
        //Set global item id for voucher key generater
        add_filter('woocommerce_get_product_from_item', array($this, 'woo_vou_set_global_item_id'), 10, 3);
        
        //Add Item ID in generated pdf download URL
        add_filter('woocommerce_get_item_downloads', array($this, 'woo_vou_add_item_id_in_download_pdf_url'), 10, 3);
        
        //Add voucher download links to my account page
        add_action('woocommerce_customer_get_downloadable_products', array($this, 'woo_vou_my_pdf_vouchers_download_link'));
        
        //restore old voucher code again when resume old order due to overwrite item
        add_action('woocommerce_resume_order', array($this, 'woo_vou_resume_order_voucher_codes'));
        
        // add action to update stock as per no. of voucher codes
        add_action('woocommerce_reduce_order_stock', array($this, 'woo_vou_update_order_stock'));
        
        /************ Expired/Upcoming Product ***********/
        // add action to remove add to cart button on single product page if product is expire/upcoming
        add_action('woocommerce_single_product_summary', array($this, 'woo_vou_display_expiry_product'));
        
        // add filter to remove add to cart button on shop page for expire product
        add_action('woocommerce_loop_add_to_cart_link', array($this, 'woo_vou_shop_add_to_cart'), 10, 1);
        
        // prevent add to cart product if some one try directly using url	
        add_filter('woocommerce_add_to_cart_validation', array($this, 'woo_vou_prevent_product_add_to_cart'), 10, 2);
        
        // add action on place order check product is expired/upcoming in checkout page
        add_action('woocommerce_checkout_order_processed', array($this, 'woo_vou_woocommerce_checkout_process'), 10, 2);
    }
}