<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Renderer Class
 *
 * To handles some small HTML content for front end and backend
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Renderer {
	
	public $mainmodel, $model;
	
	public function __construct() {
		
		global $woo_vou_model;
		
		$this->model = $woo_vou_model;
		
	}
	
	/**
	 * Add Popup For Purchased Codes 
	 * 
	 * Handels to show purchased voucher codes popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_purchased_codes_popup( $postid ) {
		
		ob_start();
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-purchased-codes-popup.php' ); // Including purchased voucher code file
		$html = ob_get_clean();
		
		return $html;
	}
	
	/**
	 * Add Popup For Used Codes
	 * 
	 * Handels to show used voucher codes popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_used_codes_popup( $postid ) {
		
		ob_start();
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-used-codes-popup.php' ); // Including used voucher code file
		$html = ob_get_clean();
		
		return $html;
	}
	
	/**
	 * Function For ajax edit of all controls
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_page_builder() {
		
		global $wp_version;
							
		$controltype = $_POST['type'];
		$bgcolor = isset( $_POST['bgcolor'] ) ? $_POST['bgcolor'] : '';
		$fontcolor = isset( $_POST['fontcolor'] ) ? $_POST['fontcolor'] : '';
		$fontsize = isset( $_POST['fontsize'] ) ? $_POST['fontsize'] : '';
		$textalign = isset( $_POST['textalign'] ) ? $_POST['textalign'] : '';
		$codetextalign = isset( $_POST['codetextalign'] ) ? $_POST['codetextalign'] : '';
		$codeborder = isset( $_POST['codeborder'] ) ? $_POST['codeborder'] : '';
		$codecolumn = isset( $_POST['codecolumn'] ) ? $_POST['codecolumn'] : '';
		$vouchercodes = isset( $_POST['vouchercodes'] ) ? $_POST['vouchercodes'] : '';
	
		$align_data = array(
								'left' 		=> __( 'Left', 'woovoucher' ),
								'center'	=> __( 'Center', 'woovoucher' ),
								'right' 	=> __( 'Right', 'woovoucher' ),
							);
	
		$border_data = array( '1', '2', '3' );
		
		$column_data = array(
								'1' 	=> __( '1 Column', 'woovoucher' ),
								'2'		=> __( '2 Column', 'woovoucher' ),
								'3' 	=> __( '3 Column', 'woovoucher' ),
							);
	
		if( $controltype == 'textblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
									
			/*echo '			<tr>
								<th scope="row">
									' . __( 'Title', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">
									<input type="text" name="' . $editorid . '" id="' . $editorid . '" value="" class="regular-text" />
									<br /><span class="description">' . __( 'Enter a voucher code title.', 'woovoucher' ) . '</span>
								</td>
							</tr>';*/
			
			echo '<tr>
								<th scope="row">
									' . __( 'Title', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid,
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid, $settings );	
			echo '					<span class="description">' . sprintf( __( 'Enter a voucher code title.', 'woovoucher' ), '<code>{codes}</code>' ) . '</span>
								</td>
							</tr>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
								
			/*echo '			<tr>
								<th scope="row">
									' . __( 'Title Font Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $fontcolor . '" id="woo_vou_edit_font_color" name="woo_vou_edit_font_color" class="woo_vou_font_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $fontcolor . '" id="woo_vou_edit_font_color" name="woo_vou_edit_font_color" class="woo_vou_edit_font_color" />
												<input type="button" class="woo_vou_font_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a font color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';*/
									
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Font Size', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $fontsize . '" id="woo_vou_edit_font_size" name="woo_vou_edit_font_size" class="woo_vou_font_size_box small-text" maxlength="2" />
									' . __( 'pt', 'woovoucher' ) . '<br /><span class="description">' . __( 'Enter a font size for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Alignment', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_text_align" name="woo_vou_edit_text_align" class="woo_vou_text_align_box">';
									foreach ( $align_data as $align_key => $align_value ) {
										echo '<option value="' . $align_key . '" ' . selected( $textalign, $align_key, false ) . '>' . $align_value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select text align for the voucher code title.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			/*echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">
									<code>{code}</code>
									<br /><span class="description">' . __( 'Above code is replaced with their voucher code(s).', 'woovoucher' ) . '</span>
								</td>
							</tr>';*/
					
			echo '<tr>
								<th scope="row">
									' . __( 'Voucher Code', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid . 'codes',
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid . 'codes', $settings );	
			echo '					<span class="description">' . __( 'Enter your voucher codes content. The available tags are:' , 'woovoucher').' <br /> <code>{codes}</code> - '.__( 'displays the voucher code(s)', 'woovoucher' ) . '</span>
								</td>
							</tr>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code Border', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_code_border" name="woo_vou_edit_code_border" class="woo_vou_code_border_box">
										<option value="">' . __( 'Select', 'woovoucher' ) . '</option>';
									foreach ( $border_data as $border ) {
										echo '<option value="' . $border . '" ' . selected( $codeborder, $border, false ) . '>' . $border . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select border for the voucher code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
								
			echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code Alignment', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_code_text_align" name="woo_vou_edit_code_text_align" class="woo_vou_code_text_align_box">';
									foreach ( $align_data as $align_key => $align_value ) {
										echo '<option value="' . $align_key . '" ' . selected( $codetextalign, $align_key, false ) . '>' . $align_value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select text align for the voucher code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
								
			/*echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code Column', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_code_column" name="woo_vou_edit_code_column" class="woo_vou_code_column_box">';
									foreach ( $column_data as $column_key => $column_value ) {
										echo '<option value="' . $column_key . '" ' . selected( $codecolumn, $column_key, false ) . '>' . $column_value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select column for the voucher code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';*/
								
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if($controltype == 'message') {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid,
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid, $settings );	
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher' ). ' <br /><code>{redeem}</code> -'. __( 'displays the voucher redeem instruction', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
				
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'expireblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /><code>{expiredatetime}</code> - '.__( 'displays the voucher expire date & time', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'venaddrblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /> <code>{vendoraddress}</code> - '. __( 'displays the vendor\' address', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'siteurlblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:', 'woovoucher').' <br /><code>{siteurl}</code> - '.__( 'displays the website url', 'woovoucher' ). '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'locblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /><code>{location}</code> - '.__( 'displays the voucher location', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'customblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your custom content. The available tags are:' , 'woovoucher')
										.'<br /><code>{redeem}</code> - '. __( 'displays the voucher redeem instruction' , 'woovoucher')
										.'<br /><code>{sitelogo}</code> - '.__( 'displays the voucher site logo' , 'woovoucher')
										.'<br /><code>{vendorlogo}</code> - '.__( 'displays the vendor logo' , 'woovoucher')
										.'<br /><code>{startdate}</code> - '.__( 'displays the voucher start date' , 'woovoucher')
										.'<br /><code>{startdatetime}</code> - '.__( 'displays the voucher start date & time' , 'woovoucher')
										.'<br /><code>{expiredate}</code> - '.__( 'displays the voucher expire date' , 'woovoucher')
										.'<br /><code>{expiredatetime}</code> - '.__( 'displays the voucher expire date & time' , 'woovoucher')
										.'<br /><code>{vendoraddress}</code> - '.__( 'displays the vendor address' , 'woovoucher')
										.'<br /><code>{siteurl}</code> - '.__( 'displays the site url' , 'woovoucher')
										.'<br /><code>{location}</code> - '.__( 'displays the location(s)', 'woovoucher' )
										.'<br /><code>{buyername}</code> - '.__( 'displays the buyer name', 'woovoucher' )
										.'<br /><code>{buyeremail}</code> - '.__( 'displays the buyer email', 'woovoucher' )
										.'<br /><code>{orderid}</code> - '.__( 'displays the order id', 'woovoucher' )
										.'<br /><code>{orderdate}</code> - '.__( 'displays the order date', 'woovoucher' )
										.'<br /><code>{productname}</code> - '.__( 'displays the product name', 'woovoucher' )
										.'<br /><code>{productprice}</code> - '.__( 'displays the product price', 'woovoucher' )
										.'<br /><code>{codes}</code> - '.__( 'displays the voucher code(s)', 'woovoucher' )
										.'<br /><code>{qrcode}</code> - '.__( 'displays one QR Code for multiple voucher code(s).', 'woovoucher' )
										.'<br /><code>{qrcodes}</code> - '.__( 'displays separate QR Codes for multiple voucher code(s).', 'woovoucher' )
										.'<br /><code>{barcode}</code> - '.__( 'displays the BAR Code for voucher code(s)', 'woovoucher' )
										.'<br /><code>{recipientname}</code> - '.__( 'displays the recipient name for voucher code(s)', 'woovoucher' )
										.'<br /><code>{recipientemail}</code> - '.__( 'displays the recipient email for voucher code(s)', 'woovoucher' )
										.'<br /><code>{recipientmessage}</code> - '.__( 'displays the recipient message for voucher code(s)', 'woovoucher' )
										.'<br /><code>{payment_method}</code> - '.__( 'displays the payment method of the order.', 'woovoucher' )
									. '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		}
		echo $html;
		exit;
	}
	
	/**
	 * Add Custom File Name settings
	 * 
	 * Handle to add custom file name settings
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_render_filename_callback( $field ) {
		
		global $woocommerce;
		
		if ( isset( $field['title'] ) && isset( $field['id'] ) ) :

			$filetype	= isset( $field['options'] ) ? $field['options'] : '';
			$file_val	= get_option( $field['id']);
			$file_val	= !empty($file_val) ? $file_val : '';
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<fieldset>
							<input name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $file_val ); ?>" style="min-width: 300px;"/><?php echo $filetype;?>
						</fieldset>
						<span class="description"><?php echo $field['desc'];?></span>
					</td>
				</tr>
			<?php

		endif;
	}
	
	/**
	 * Display Textarea/Editor HTML
	 * 
	 * Handle to add custom file name settings
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woocommerce_admin_field_vou_textarea( $field ) {
		
		global $woocommerce;

		if ( isset( $field['title'] ) && isset( $field['id'] ) ) :

			$file_val	= get_option( $field['id']);
			$file_val	= !empty($file_val) ? $file_val : '';
			$editor		= ( isset( $field['editor'] ) && $field['editor'] == true ) ? true : false;
			
			$editor_cofig = array(
									'media_buttons'	=> true,
									'textarea_rows'	=> 5,
									'editor_class'	=> 'woo-vou-wpeditor'
								);
				
			?>
			
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<fieldset><?php 
						if( $editor ) {
							
							wp_editor( $file_val, esc_attr( $field['id'] ), $editor_cofig );
							
						} else { ?>
							
							<textarea name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" style="width: 99%;height:200px;"/><?php echo esc_attr( $file_val ); ?></textarea>
						<?php } ?>
					</fieldset>
					<span class="description"><?php echo $field['desc'];?></span>
				</td>
			</tr><?php
		
		endif;
	}
	
	/**
	 * Upload Callback
	 *
	 * Renders upload fields.
	 *
	 * @since 1.0.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_render_upload_callback( $field ) {
		global $woocommerce;

		if ( isset( $field['title'] ) && isset( $field['id'] ) ) {

			$filetype	= isset( $field['options'] ) ? $field['options'] : '';
			$file_val	= get_option( $field['id'] );
			$file_val	= !empty($file_val) ? $file_val : '';
			
			?>
			<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<fieldset>
							<input name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $file_val ); ?>" style="min-width: 300px;"/><?php echo $filetype;?>
							<input type="button" class="woo-vou-upload-button button-secondary" value="<?php _e( 'Upload File', 'woovoucher' );?>"/>
						</fieldset>
						<span class="description"><?php echo $field['desc'];?></span>
					</td>
				</tr>
			<?php
		}
	}
}
?>