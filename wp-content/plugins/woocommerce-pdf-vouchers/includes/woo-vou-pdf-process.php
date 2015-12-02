<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function woo_vou_generate_pdf_by_html($html = '', $pdf_args = array())
{

    if (!class_exists('TCPDF')) { //If class not exist

        //include tcpdf file
        require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
    }

    $prefix = WOO_VOU_META_PREFIX;

    $pdf_margin_top        = PDF_MARGIN_TOP;
    $pdf_margin_bottom     = PDF_MARGIN_BOTTOM;
    $pdf_margin_left       = PDF_MARGIN_LEFT;
    $pdf_margin_right      = PDF_MARGIN_RIGHT;
    $pdf_bg_image          = '';
    $vou_template_pdf_view = '';

    // Taking pdf arguments
    $pdf_font = 'helvetica'; // This is default font
    if (!empty($pdf_args['char_support'])) {    // if character support is checked

        if (defined('WOO_VOU_PF_DIR')) { // if pdf font plugin is active

            $vou_pdf_font = get_option('vou_pdf_font');
            if (!empty($vou_pdf_font)) {
                $pdf_font = $vou_pdf_font;
            } else {
                $pdf_font = 'freeserif';
            }

        } else {
            $pdf_font = 'freeserif';
        }
    }

    $pdf_save  = !empty($pdf_args['save_file']) ? true : false; // Pdf store in a folder or not
    $font_size = 12;

    if (isset($pdf_args['vou_template_id']) && !empty($pdf_args['vou_template_id'])) {

        global $woo_vou_template_id;

        //Voucher PDF ID
        $woo_vou_template_id = $pdf_args['vou_template_id'];

        //Get pdf size meta
        $woo_vou_template_size = get_post_meta($woo_vou_template_id, $prefix . 'pdf_size', true);
        $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

        //Get size array
        $woo_vou_allsize_array = woo_vou_get_pdf_sizes();

        $woo_vou_size_array = $woo_vou_allsize_array[$woo_vou_template_size];

        $pdf_width  = isset($woo_vou_size_array['width']) ? $woo_vou_size_array['width'] : '210';
        $pdf_height = isset($woo_vou_size_array['height']) ? $woo_vou_size_array['height'] : '297';
        $font_size  = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

        // Extend the TCPDF class to create custom Header and Footer
        if (!class_exists('VOUPDF')) {
            class VOUPDF extends TCPDF
            {

                function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
                {

                    // Call parent constructor
                    parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
                }

                //Page header
                public function Header()
                {

                    global $woo_vou_model, $woo_vou_template_id;

                    //model class
                    $model = $woo_vou_model;

                    $prefix = WOO_VOU_META_PREFIX;

                    $vou_template_bg_style   = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_style', true);
                    $vou_template_bg_pattern = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_pattern', true);
                    $vou_template_bg_img     = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_img', true);
                    $vou_template_bg_color   = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_color', true);
                    $vou_template_pdf_view   = get_post_meta($woo_vou_template_id, $prefix . 'pdf_view', true);

                    //Get pdf size meta
                    $woo_vou_template_size = get_post_meta($woo_vou_template_id, $prefix . 'pdf_size', true);
                    $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

                    //Get size array
                    $woo_vou_allsize_array = woo_vou_get_pdf_sizes();

                    $woo_vou_size_array = $woo_vou_allsize_array[$woo_vou_template_size];

                    $pdf_width  = isset($woo_vou_size_array['width']) ? $woo_vou_size_array['width'] : '210';
                    $pdf_height = isset($woo_vou_size_array['height']) ? $woo_vou_size_array['height'] : '297';
                    $font_size  = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

                    //Voucher PDF Background Color
                    if (!empty($vou_template_bg_color)) {

                        if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape

                            // Background color
                            $this->Rect(0, 0, $pdf_height, $pdf_width, 'F', '', $fill_color = $model->woo_vou_hex_2_rgb($vou_template_bg_color));

                        } else {

                            // Background color
                            $this->Rect(0, 0, $pdf_width, $pdf_height, 'F', '', $fill_color = $model->woo_vou_hex_2_rgb($vou_template_bg_color));
                        }
                    }

                    //Voucher PDF Background style is image & image is not empty
                    if (!empty($vou_template_bg_style) && $vou_template_bg_style == 'image'
                        && isset($vou_template_bg_img['src']) && !empty($vou_template_bg_img['src'])
                    ) {

                        $img_file = $vou_template_bg_img['src'];

                    } else {
                        if (!empty($vou_template_bg_style) && $vou_template_bg_style == 'pattern'
                            && !empty($vou_template_bg_pattern)
                        ) {//Voucher PDF Background style is pattern & Background Pattern is not selected

                            if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape

                                // Background Pattern Image
                                $img_file = WOO_VOU_IMG_URL . '/patterns/' . $vou_template_bg_pattern . '.png';

                            } else {

                                // Background Pattern Image
                                $img_file = WOO_VOU_IMG_URL . '/patterns/port_' . $vou_template_bg_pattern . '.png';
                            }

                        }
                    }

                    if (!empty($img_file)) { //Check image file

                        // get the current page break margin
                        $bMargin = $this->getBreakMargin();
                        // get current auto-page-break mode
                        $auto_page_break = $this->AutoPageBreak;
                        // disable auto-page-break
                        $this->SetAutoPageBreak(false, 0);

                        if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape

                            // Background image
                            $this->Image($img_file, 0, 0, $pdf_height, $pdf_width, '', '', '', false, 300, '', false, false, 0);

                        } else {

                            // Background image
                            $this->Image($img_file, 0, 0, $pdf_width, $pdf_height, '', '', '', false, 300, '', false, false, 0);
                        }
                        // restore auto-page-break status
                        $this->SetAutoPageBreak($auto_page_break, $bMargin);
                        // set the starting point for the page content
                        $this->setPageMark();

                    }
                }
            }
        } // class exist

        //Voucher PDF Margin Top
        $vou_template_pdf_view = get_post_meta($woo_vou_template_id, $prefix . 'pdf_view', true);

        //Voucher PDF Margin Top
        $vou_template_margin_top = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_top', true);
        if (!empty($vou_template_margin_top)) {
            $pdf_margin_top = $vou_template_margin_top;
        }
        //Voucher PDF Margin Top
        $vou_template_margin_bottom = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_bottom', true);
        if (!empty($vou_template_margin_bottom)) {
            $pdf_margin_bottom = $vou_template_margin_bottom;
        }

        //Voucher PDF Margin Left
        $vou_template_margin_left = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_left', true);
        if (!empty($vou_template_margin_left)) {
            $pdf_margin_left = $vou_template_margin_left;
        }

        //Voucher PDF Margin Right
        $vou_template_margin_right = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_right', true);
        if (!empty($vou_template_margin_right)) {
            $pdf_margin_right = $vou_template_margin_right;
        }

        // create new PDF document
        $pdf = new VOUPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $woo_vou_template_size, true, 'UTF-8', false);

    } else {

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // remove default header
        $pdf->setPrintHeader(false);

    }

    // remove default footer
    $pdf->setPrintFooter(false);

    // Auther name and Creater name
    $pdf->SetCreator(utf8_decode(__('WooCommerce', 'woovoucher')));
    $pdf->SetAuthor(utf8_decode(__('WooCommerce', 'woovoucher')));
    $pdf->SetTitle(utf8_decode(__('WooCommerce Voucher', 'woovoucher')));

    // set default header data
    //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 021', PDF_HEADER_STRING);

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins($pdf_margin_left, $pdf_margin_top, $pdf_margin_right);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(true, $pdf_margin_bottom);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont(apply_filters('woo_vou_pdaf_generate_fonts', $pdf_font), '', $font_size);

    // add a page
    if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape
        $pdf->AddPage('L');
    } else {
        $pdf->AddPage();
    }

    // set cell padding
    //$pdf->setCellPaddings(1, 1, 1, 1);

    // set cell margins
    $pdf->setCellMargins(0, 1, 0, 1);

    // set font color
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetFillColor(238, 238, 238);

    $pdf_vou_codes      = !empty($pdf_args['vou_codes']) ? $pdf_args['vou_codes'] : '';
    $pdf_args_vou_codes = !empty($pdf_vou_codes) ? explode(',', $pdf_vou_codes) : array();

    //Get QR code Dimantion
    $qrcode_dimention = apply_filters(
        'woo_vou_qrcode_dimention',
        array(
            'width'  => round($font_size * 1.5),
            'height' => round($font_size * 1.5),
        ), $font_size, $woo_vou_template_size
    );

    $qrcode_code_w = isset($qrcode_dimention['width']) ? $qrcode_dimention['width'] : '';
    $qrcode_code_h = isset($qrcode_dimention['height']) ? $qrcode_dimention['height'] : '';

    //Get Bar code Dimantion
    $barcode_dimention = apply_filters(
        'woo_vou_barcode_dimention',
        array(
            'width'  => round($font_size * 1.5) * 5,
            'height' => round($font_size * 1.5),
        ), $font_size, $woo_vou_template_size
    );

    $barcode_code_w = isset($barcode_dimention['width']) ? $barcode_dimention['width'] : '';
    $barcode_code_h = isset($barcode_dimention['height']) ? $barcode_dimention['height'] : '';

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcodes}') !== false) {// If qrcodes is there

        $vou_qr_msg = $vou_qrcode = '';
        if (!empty($pdf_args_vou_codes)) {

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                if (!empty($pdf_args_vou_code)) {

                    $vou_qr_msg = trim($pdf_args_vou_code);

                    // make qrcode url used at scanning time
                    $vou_qr_msg = site_url() . "?woo_vou_code=" . $vou_qr_msg;

                    $vou_qr_params = $pdf->serializeTCPDFtagParameters(
                        array(
                            $vou_qr_msg, 'QRCODE,H', '', '', $qrcode_code_w, $qrcode_code_h, array(
                            'border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100,
                        ), 'N',
                        )
                    );
                    $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';
                }
            }
        }

        $html = str_replace('{qrcodes}', $vou_qrcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcode}') !== false) {// If qrcode is there

        $vou_qr_msg = $vou_qrcode = '';

        $vou_qr_msg = trim($pdf_vou_codes);

        // make qrcode url used at scanning time
        $vou_qr_msg = site_url() . "?woo_vou_code=" . $vou_qr_msg;

        $vou_qr_params = $pdf->serializeTCPDFtagParameters(
            array(
                $vou_qr_msg, 'QRCODE,H', '', '', $qrcode_code_w, $qrcode_code_h, array(
                'border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100,
            ), 'N',
            )
        );
        $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

        $html = str_replace('{qrcode}', $vou_qrcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{barcode}') !== false) {// If barcode is there

        $vou_qr_msg = $vou_barcode = '';

        if (!empty($pdf_args_vou_codes)) {

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                $vou_bar_msg = trim($pdf_args_vou_code);

                // make qrcode url used at scanning time
                $vou_bar_msg = $vou_bar_msg;

                $vou_bar_params = $pdf->serializeTCPDFtagParameters(
                    array(
                        $vou_bar_msg, 'C128', '', '', $barcode_code_w, $barcode_code_h, 0.2, array(
                        'position' => 'S', 'border' => false, 'padding' => 'auto', 'fgcolor' => array(0, 0, 0),
                        'text'     => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10,
                    ), 'N',
                    )
                );
                $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';
            }
        }

        $html = str_replace('{barcode}', $vou_barcode, $html);
    }

    // output the HTML content
    $pdf->writeHTML($html, true, 0, true, 0);

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------
    $order_pdf_name = get_option('order_pdf_name');
    if (!empty($order_pdf_name)) {
        $pdf_file_name = str_replace("{current_date}", date('d-m-Y'), $order_pdf_name);
    } else {
        $pdf_file_name = 'woo-voucher-' . date('d-m-Y');
    }

    //Get pdf name
    $pdf_name = isset($pdf_args['pdf_name']) && !empty($pdf_args['pdf_name']) ? $pdf_args['pdf_name'] : $pdf_file_name;

    // Store pdf in a folder
    if ($pdf_save) {
        $pdf->Output($pdf_name . '.pdf', 'F');
    } else {
        //Close and output PDF document
        //Second Parameter I that means display direct and D that means ask product or open this file
        $pdf->Output($pdf_name . '.pdf', 'D');
    }
}

/**
 * View Preview for Voucher PDF
 *
 * Handles to view preview for voucher pdf
 *
 * @package WooCommerce - PDF Vouchers
 * @since   1.0.0
 */
function woo_vou_preview_pdf()
{

    global $woo_vou_model;

    $model = $woo_vou_model;

    $pdf_args = array();

    if (isset($_GET['voucher_id']) && !empty($_GET['voucher_id'])
        && isset($_GET['woo_vou_pdf_action']) && $_GET['woo_vou_pdf_action'] == 'preview'
    ) {

        // Getting voucher character support
        $voucher_char_support = get_option('vou_char_support');

        $voucher_template_id = $_GET['voucher_id'];

        $pdf_args['vou_template_id'] = $voucher_template_id;

        //site logo
        $vousitelogohtml = '';
        $vou_site_url    = get_option('vou_site_logo');
        if (!empty($vou_site_url)) {

            $vousitelogohtml = '<img src="' . $vou_site_url . '" alt="" />';
        }

        //vendor's logo
        $vou_url     = WOO_VOU_IMG_URL . '/vendor-logo.png';
        $voulogohtml = '<img src="' . $vou_url . '" alt="" />';

        $vendor_address = __('Infiniti Mall Malad', 'woovoucher') . "\n\r" . __('GF 9 & 10, Link Road, Mindspace, Malad West', 'woovoucher') . "\n\r" . __('Mumbai, Maharashtra 400064', 'woovoucher');
        $vendor_address = nl2br($vendor_address);

        $nextmonth     = mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d"), date("Y"));
        $previousmonth = mktime(date("H"), date("i"), date("s"), date("m") - 1, date("d"), date("Y"));

        $redeem_instruction = __('Redeem instructions :', 'woovoucher');
        $redeem_instruction .= __('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.', 'woovoucher');

        $locations = '<strong>' . __('DELHI:', 'woovoucher') . '</strong> ' . __('Dlf Promenade Mall & Pacific Mall', 'woovoucher');
        $locations .= ' <strong>' . __('MUMBAI:', 'woovoucher') . '</strong> ' . __('Infiniti Mall, Malad & Phoenix MarketCity', 'woovoucher');
        $locations .= ' <strong>' . __('BANGALORE:', 'woovoucher') . '</strong> ' . __('Phoenix MarketCity Mall', 'woovoucher');
        $locations .= ' <strong>' . __('PUNE:', 'woovoucher') . '</strong> ' . __('Phoenix MarketCity Mall', 'woovoucher');

        $buyer_fullname = __('WpWeb', 'woovoucher');
        $buyer_email    = 'web101@gmail.com';
        $orderid        = '101';
        $orderdate      = date("d-m-Y");
        $productname    = __('Test Product', 'woovoucher');
        $productprice   = '$' . number_format('10', 2);
        $codes          = __('[The voucher code will be inserted automatically here]', 'woovoucher');

        $voucher_rec_name    = 'Test Name';
        $voucher_rec_email   = 'recipient@example.com';
        $voucher_rec_message = 'Test message';
        $payment_method      = 'Test Payment Method';

        $content_post          = get_post($voucher_template_id);
        $content               = isset($content_post->post_content) ? $content_post->post_content : '';
        $post_title            = isset($content_post->post_title) ? $content_post->post_title : '';
        $voucher_template_html = do_shortcode($content);

        $voucher_template_html = str_replace('{redeem}', $redeem_instruction, $voucher_template_html);
        $voucher_template_html = str_replace('{vendorlogo}', $voulogohtml, $voucher_template_html);
        $voucher_template_html = str_replace('{sitelogo}', $vousitelogohtml, $voucher_template_html);
        $voucher_template_html = str_replace('{startdate}', $model->woo_vou_get_date_format(date('d-m-Y', $previousmonth)), $voucher_template_html);
        $voucher_template_html = str_replace('{startdatetime}', $model->woo_vou_get_date_format(date('d-m-Y H:i:s', $previousmonth), true), $voucher_template_html);
        $voucher_template_html = str_replace('{expiredate}', $model->woo_vou_get_date_format(date('d-m-Y', $nextmonth)), $voucher_template_html);
        $voucher_template_html = str_replace('{expiredatetime}', $model->woo_vou_get_date_format(date('d-m-Y H:i:s', $nextmonth), true), $voucher_template_html);
        $voucher_template_html = str_replace('{vendoraddress}', $vendor_address, $voucher_template_html);
        $voucher_template_html = str_replace('{siteurl}', 'www.bebe.com', $voucher_template_html);
        $voucher_template_html = str_replace('{location}', $locations, $voucher_template_html);
        $voucher_template_html = str_replace('{buyername}', $buyer_fullname, $voucher_template_html);
        $voucher_template_html = str_replace('{buyeremail}', $buyer_email, $voucher_template_html);
        $voucher_template_html = str_replace('{orderid}', $orderid, $voucher_template_html);
        $voucher_template_html = str_replace('{orderdate}', $model->woo_vou_get_date_format($orderdate), $voucher_template_html);
        $voucher_template_html = str_replace('{productname}', $productname, $voucher_template_html);
        $voucher_template_html = str_replace('{productprice}', $productprice, $voucher_template_html);
        $voucher_template_html = str_replace('{codes}', $codes, $voucher_template_html);

        $voucher_template_html = str_replace('{recipientname}', $voucher_rec_name, $voucher_template_html);
        $voucher_template_html = str_replace('{recipientemail}', $voucher_rec_email, $voucher_template_html);
        $voucher_template_html = str_replace('{recipientmessage}', $voucher_rec_message, $voucher_template_html);

        $voucher_template_html = str_replace('{payment_method}', $payment_method, $voucher_template_html);

        //Set pdf name
        $post_title               = str_replace(' ', '-', strtolower($post_title));
        $pdf_args['pdf_name']     = $post_title . __('-preview-', 'woovoucher') . $voucher_template_id;
        $pdf_args['vou_codes']    = $codes;
        $pdf_args['char_support'] = (!empty($voucher_char_support) && $voucher_char_support == 'yes') ? 1 : 0; // Character support

        woo_vou_generate_pdf_by_html($voucher_template_html, $pdf_args);
    }
}

add_action('init', 'woo_vou_preview_pdf', 9);


/**
 * Generate PDF for Voucher
 *
 * Handles to Generate PDF on run time when
 * user will execute the url which is sent to
 * user email with purchase receipt
 *
 * @package WooCommerce - PDF Vouchers
 * @since   1.0.0
 */
function woo_vou_process_product_pdf($productid, $orderid, $item_id = '', $orderdvoucodes = array(), $pdf_args = array())
{

    $prefix = WOO_VOU_META_PREFIX;

    global $current_user, $woo_vou_model;

    //model class
    $model = $woo_vou_model;

    // If pdf argument is not array then make it array
    $pdf_args = !is_array($pdf_args) ? (array)$pdf_args : $pdf_args;

    // Taking voucher key
    if (!empty($pdf_args['pdf_vou_key'])) {
        $pdf_vou_key = $pdf_args['pdf_vou_key'];
    } else {
        $pdf_vou_key = isset($_GET['key']) ? $_GET['key'] : '';
    }

    if (!empty($productid) && !empty($orderid)) { // Check product id & order id are not empty

        //get all voucher details from order meta
        $allorderdata = $model->woo_vou_get_all_ordered_data($orderid);

        // Creating order object for order details
        $woo_order         = new WC_Order($orderid);
        $woo_order_details = $woo_order;
        $items             = $woo_order_details->get_items();

        foreach($items as $key => $item) {
            $qty = $items[$key]['qty'];
        }

        // Getting product name
        $woo_product_details = $model->woo_vou_get_product_details($orderid, $items);

        // get product
        $product = wc_get_product($productid);
        // if product type is variable then $productid will contain variation id
        $variation_id = $productid;
        // check if product type is variable then we need to take parent id of variation id
        if ($product->is_type('variation') || $product->is_type('variable')) {

            // productid is variation id in case of variable product so we need to take actual product id
            $woo_variation = new WC_Product_Variation($productid);
            $productid     = (!empty($woo_variation->id)) ? $woo_variation->id : $variation_id;
        }

        $product = wc_get_product($productid);
        $duree   = get_the_terms($productid, 'pa_duree');
        $args    = array(
            'post_type'   => 'attachment',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => $productid,
        );

        $attachments = get_posts($args);
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $image = wp_get_attachment_image($attachment->ID, 'full');
            }
        }

        // Getting order details
        $buyer_email    = $woo_order_details->billing_email;
        $buyer_fname    = $woo_order_details->billing_first_name;
        $buyer_lname    = $woo_order_details->billing_last_name;
        $buyer_fullname = $buyer_fname . ' ' . $buyer_lname;
        $productname    = isset($woo_product_details[$variation_id]) ? $woo_product_details[$variation_id]['product_name'] : '';

        //Added in version 2.3.7 for sold indivdual bug
        $item_key = $item_id;

        if (empty($item_key)) {
            $item_key = isset($woo_product_details[$variation_id]) ? $woo_product_details[$variation_id]['item_key'] : '';
        }

        $variation_data = $model->woo_vou_get_variation_data($woo_order, $item_key);
        $productname    = $productname . $variation_data;

        //Get recipient fields
        $recipient_data = $model->woo_vou_get_recipient_data_using_item_key($woo_order, $item_key);
        $productprice   = !empty($woo_product_details[$variation_id]['product_formated_price']) ? $woo_product_details[$variation_id]['product_formated_price'] : '';

        //Get voucher codes
        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

        //get all voucher details from order meta
        $allvoucherdata = isset($allorderdata[$productid]) ? $allorderdata[$productid] : array();


        //how to use the voucher details
        $howtouse = isset($allvoucherdata['redeem']) ? $allvoucherdata['redeem'] : '';

        //start date
        $start_date = isset($allvoucherdata['start_date']) ? $allvoucherdata['start_date'] : '';

        //expiry data
        $exp_date = isset($allvoucherdata['exp_date']) ? $allvoucherdata['exp_date'] : '';

        //vou order date
        $orderdate = get_the_time('Y-m-d', $orderid);
        if (!empty($orderdate)) {
            $orderdate = $model->woo_vou_get_date_format($orderdate);
        }

        //vou logo
        $voulogo = isset($allvoucherdata['vendor_logo']) ? $allvoucherdata['vendor_logo'] : '';
        $voulogo = isset($voulogo['src']) && !empty($voulogo['src']) ? $voulogo['src'] : '';

        //vendor logo
        $voulogohtml = '';
        if (!empty($voulogo)) {

            $voulogohtml = '<img src="' . $voulogo . '" alt="" />';
        }

        //site logo
        $vousitelogohtml = '';
        $vou_site_url    = get_option('vou_site_logo');
        if (!empty($vou_site_url)) {

            $vousitelogohtml = '<img src="' . $vou_site_url . '" alt="" />';
        }

        //start date
        if (!empty($start_date)) {
            $start_date_time = $model->woo_vou_get_date_format($start_date, true);
            $start_date      = $model->woo_vou_get_date_format($start_date);
        } else {
            $start_date = $start_date_time = __('No Start Date', 'woovoucher');
        }

        //expiration date
        if (!empty($exp_date)) {
            $expiry_date      = $model->woo_vou_get_date_format($exp_date);
            $expiry_date_time = $model->woo_vou_get_date_format($exp_date, true);
        } else {
            $expiry_date = $expiry_date_time = __('No Expiration', 'woovoucher');
        }

        //website url
        $website = isset($allvoucherdata['website_url']) ? $allvoucherdata['website_url'] : '';

        //vendor address
        $addressphone = isset($allvoucherdata['vendor_address']) ? $allvoucherdata['vendor_address'] : '';

        //location where voucher is availble
        $locations = isset($allvoucherdata['avail_locations']) ? $allvoucherdata['avail_locations'] : '';

        //vendor user
        $vendor_user = get_post_meta($productid, $prefix . 'vendor_user', true);

        //get vendor detail
        $vendor_detail = $model->woo_vou_get_vendor_detail($productid, $vendor_user);

        //pdf template
        $pdf_template_meta = $vendor_detail['pdf_template'];

        $voucodes = '';

        //Get mutiple pdf option from order meta
        $multiple_pdf = empty($orderid) ? '' : get_post_meta($orderid, $prefix . 'multiple_pdf', true);

        if ($multiple_pdf == 'yes' && !empty($orderdvoucodes)) { //check is enable multiple pdf

            $key      = $pdf_vou_key;
            $voucodes = $orderdvoucodes[$key];

        } elseif (!empty($voucher_codes)) {

            $voucodes = trim($voucher_codes);
        }

        $recipientname    = isset($recipient_data['recipient_name']) ? $recipient_data['recipient_name'] : '';
        $recipientemail   = isset($recipient_data['recipient_email']) ? $recipient_data['recipient_email'] : '';
        $recipientmessage = isset($recipient_data['recipient_message']) ? $recipient_data['recipient_message'] : '';
        $payment_method   = isset($woo_order_details->payment_method_title) ? $woo_order_details->payment_method_title : '';


        include(WOO_VOU_DIR . '/includes/woo-vou-generate-order-pdf.php');
    }
}