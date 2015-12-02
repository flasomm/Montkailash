<?php

/**
 * Plugin Name: WooCommerce Atos Gateway
 * Plugin URI: http://www.absoluteweb.net/prestations/wordpress-woocommerce-extensions-traductions/woocommerce-atos-worldline-sips/
 * Description: Passerelle de paiement Atos pour WooCommerce.
 * Version: 1.1.1
 * Author: Nicolas Maillard
 * Author URI: http://www.absoluteweb.net/
 * License: Copyright ABSOLUTE Web
 *
 *	Intellectual Property rights, and copyright, reserved by Nicolas Maillard, ABSOLUTE Web as allowed by law incude,
 *	but are not limited to, the working concept, function, and behavior of this plugin,
 *	the logical code structure and expression as written.
 *
 *
 * @package     WooCommerce Atos Gateway, WooCommerce API Manager
 * @author      Nicolas Maillard, ABSOLUTE Web
 * @category    Plugin
 * @copyright   Copyright (c) 2000-2014, Nicolas Maillard ABSOLUTE Web
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays an inactive message if the API License Key has not yet been activated
 */
if ( get_option( 'atos_sips_activated' ) != 'Activated' ) {
    add_action( 'admin_notices', 'Atos_Sips::am_atos_inactive_notice' );
}

class Atos_Sips {

	/**
	 * Self Upgrade Values
	 */
	// Base URL to the remote upgrade API server
	public $upgrade_url = 'http://www.absoluteweb.net/'; // URL to access the Update API Manager.

	/**
	 * @var string
	 */
	public $version = '1.1.1';

	/**
	 * @var string
	 * This version is saved after an upgrade to compare this db version to $version
	 */
	public $atos_sips_version_name = 'plugin_atos_sips_version';

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 * used to defined localization for translation, but a string literal is preferred
	 *
	 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/issues/59
	 * http://markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 */
	public $text_domain = 'atos_text_domain';

	/**
	 * Data defaults
	 * @var mixed
	 */
	private $asw_software_product_id;

	public $asw_data_key;
	public $asw_api_key;
	public $asw_activation_email;
	public $asw_product_id_key;
	public $asw_instance_key;
	public $asw_deactivate_checkbox_key;
	public $asw_activated_key;

	public $asw_deactivate_checkbox;
	public $asw_activation_tab_key;
	public $asw_deactivation_tab_key;
	public $asw_settings_menu_title;
	public $asw_settings_title;
	public $asw_menu_tab_activation_title;
	public $asw_menu_tab_deactivation_title;

	public $asw_options;
	public $asw_plugin_name;
	public $asw_product_id;
	public $asw_renew_license_url;
	public $asw_instance_id;
	public $asw_domain;
	public $asw_software_version;
	public $asw_plugin_or_theme;

	public $asw_update_version;

	public $asw_update_check = 'am_atos_plugin_update_check';

	/**
	 * Used to send any extra information.
	 * @var mixed array, object, string, etc.
	 */
	public $asw_extra;

    /**
     * @var The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

	public function __construct() {

		// Run the activation function
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// Ready for translation
		load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( __FILE__ ) ) ) . '/lang' );

		if ( is_admin() ) {

			/**
			 * Software Product ID is the product title string
			 * This value must be unique, and it must match the API tab for the product in WooCommerce
			 */
			$this->asw_software_product_id = __('WooCommerce Atos Gateway', 'atos_text_domain');

			/**
			 * Set all data defaults here
			 */
			$this->asw_data_key 				= 'atos_sips';
			$this->asw_api_key 					= 'api_key';
			$this->asw_activation_email 		= 'activation_email';
			$this->asw_product_id_key 			= 'atos_sips_product_id';
			$this->asw_instance_key 			= 'atos_sips_instance';
			$this->asw_deactivate_checkbox_key 	= 'atos_sips_deactivate_checkbox';
			$this->asw_activated_key 			= 'atos_sips_activated';

			/**
			 * Set all admin menu data
			 */
			$this->asw_deactivate_checkbox 			= 'am_deactivate_atos_checkbox';
			$this->asw_activation_tab_key 			= 'atos_sips_dashboard';
			$this->asw_deactivation_tab_key 		= 'atos_sips_deactivation';
			$this->asw_settings_menu_title 			= 'Licence Passerelle Atos Sips';
			$this->asw_settings_title 				= 'Licence Passerelle Atos Sips';
			$this->asw_menu_tab_activation_title 	= __('License Activation', 'atos_text_domain');
			$this->asw_menu_tab_deactivation_title 	= __('License Deactivation', 'atos_text_domain');

			/**
			 * Set all software update data here
			 */
			$this->asw_options 				= get_option( $this->asw_data_key );
			$this->asw_plugin_name 			= untrailingslashit( plugin_basename( __FILE__ ) ); // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->asw_product_id 			= get_option( $this->asw_product_id_key ); // Software Title
			$this->asw_renew_license_url 	= 'http://www.absoluteweb.net/mon-compte'; // URL to renew a license
			$this->asw_instance_id 			= get_option( $this->asw_instance_key ); // Instance ID (unique to each blog activation)
			$this->asw_domain 				= site_url(); // blog domain name
			$this->asw_software_version 	= $this->version; // The software version
			$this->asw_plugin_or_theme 		= 'plugin'; // 'theme' or 'plugin'

			// Performs activations and deactivations of API License Keys
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-key-api.php' );
			$this->atos_sips_key = new Atos_Sips_Key();

			// Checks for software updatess
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-plugin-update.php' );

			// Admin menu with the license key and license email form
			require_once( plugin_dir_path( __FILE__ ) . 'am/admin/class-wc-api-manager-menu.php' );

			$options = get_option( $this->asw_data_key );

			/**
			 * Check for software updates
			 */
			if ( ! empty( $options ) && $options !== false ) {

				new Atos_Sips_Update_API_Check(
					$this->upgrade_url,
					$this->asw_plugin_name,
					$this->asw_product_id,
					$this->asw_options[$this->asw_api_key],
					$this->asw_options[$this->asw_activation_email],
					$this->asw_renew_license_url,
					$this->asw_instance_id,
					$this->asw_domain,
					$this->asw_software_version,
					$this->asw_plugin_or_theme,
					$this->text_domain
					);

			}

		}

		/**
		 * Deletes all data if plugin deactivated
		 */
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

	}

	public function plugin_url() {
		if ( isset( $this->plugin_url ) ) return $this->plugin_url;
		return $this->plugin_url = plugins_url( '/', __FILE__ );
	}

	/**
	 * Generate the default data arrays
	 */
	public function activation() {
		global $wpdb;

		$global_options = array(
			$this->asw_api_key 			=> '',
			$this->asw_activation_email 	=> '',
					);

		update_option( $this->asw_data_key, $global_options );

		require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-api-manager-passwords.php' );

		$Atos_Sips_Password_Management = new Atos_Sips_Password_Management();

		// Generate a unique installation $instance id
		$instance = $Atos_Sips_Password_Management->generate_password( 12, false );

		$single_options = array(
			$this->asw_product_id_key 			=> $this->asw_software_product_id,
			$this->asw_instance_key 			=> $instance,
			$this->asw_deactivate_checkbox_key 	=> 'on',
			$this->asw_activated_key 			=> 'Deactivated',
			);

		foreach ( $single_options as $key => $value ) {
			update_option( $key, $value );
		}

		$curr_ver = get_option( $this->atos_sips_version_name );

		// checks if the current plugin version is lower than the version being installed
		if ( version_compare( $this->version, $curr_ver, '>' ) ) {
			// update the version
			update_option( $this->atos_sips_version_name, $this->version );
		}

	}

	/**
	 * Deletes all data if plugin deactivated
	 * @return void
	 */
	public function uninstall() {
		global $wpdb, $blog_id;

		$this->license_key_deactivation();

		// Remove options
		if ( is_multisite() ) {

			switch_to_blog( $blog_id );

			foreach ( array(
					$this->asw_data_key,
					$this->asw_product_id_key,
					$this->asw_instance_key,
					$this->asw_deactivate_checkbox_key,
					$this->asw_activated_key,
					) as $option) {

					delete_option( $option );

					}

			restore_current_blog();

		} else {

			foreach ( array(
					$this->asw_data_key,
					$this->asw_product_id_key,
					$this->asw_instance_key,
					$this->asw_deactivate_checkbox_key,
					$this->asw_activated_key
					) as $option) {

					delete_option( $option );

					}

		}

	}

	/**
	 * Deactivates the license on the API server
	 * @return void
	 */
	public function license_key_deactivation() {

		$activation_status = get_option( $this->asw_activated_key );

		$api_email = $this->asw_options[$this->asw_activation_email];
		$api_key = $this->asw_options[$this->asw_api_key];

		$args = array(
			'email' => $api_email,
			'licence_key' => $api_key,
			);

		if ( $activation_status == 'Activated' && $api_key != '' && $api_email != '' ) {
			$this->atos_sips_key->deactivate( $args ); // reset license key activation
		}
	}

    /**
     * Displays an inactive notice when the software is inactive.
     */
	public static function am_atos_inactive_notice() { ?>
		<?php if ( ! current_user_can( 'manage_options' ) ) return; ?>
		<?php if ( isset( $_GET['page'] ) && 'atos_sips_dashboard' == $_GET['page'] ) return; ?>
		<div id="message" class="error">
			<p><?php printf( __( 'The API Manager Example API License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'atos_text_domain' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=atos_sips_dashboard' ) ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}

} // End of class

function ASW() {
    return Atos_Sips::instance();
}

// Initialize the class instance only once
ASW();

/*
*
*
*/

load_plugin_textdomain('atos', false, dirname(plugin_basename(__FILE__)).'/lang');

function woocommerce_gateway_atos_activation() {
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		deactivate_plugins(plugin_basename(__FILE__)); 		
		$message = sprintf(__("Désolé ! Pour utiliser l'extension de passerelle WooCommerce %s, vous devez installer et activer l'extension WooCommerce.", 'atos'), 'Atos Sips');
		wp_die($message, __('Extension Passerelle de Paiement Atos Sips', 'atos'), array('back_link' => true));
	}
}
register_activation_hook(__FILE__, 'woocommerce_gateway_atos_activation');

add_action('plugins_loaded', 'init_gateway_atos', 0);

function init_gateway_atos() {
	
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	
	define('__WPRootAtos__',dirname(dirname(dirname(dirname(__FILE__)))));
	define('__ServerRootAtos__',dirname(dirname(dirname(dirname(dirname(__FILE__))))));	
 
 class WC_Gateway_Atos extends WC_Payment_Gateway {
			
		public function __construct() { 
        	$this->id = 'atos';
			$this->method_title = 'Atos Sips';
			$this->logo = plugins_url('woocommerce-gateway-atos/logo/Atos-Worldline-Sips.png');
        	$this->has_fields = false;	
			$this->init_form_fields();
			$this->init_settings();
			$this->icon = apply_filters('woocommerce_atos_icon', $this->get_option('gateway_image'));
			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');
			add_action( 'woocommerce_api_'.strtolower(get_class($this)), array( $this, 'check_atos_response' ) );
			add_action('woocommerce_receipt_atos', array($this, 'receipt_page'));
			add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    		add_action('woocommerce_thankyou_atos', array($this, 'thankyou_page'));
    	} 
		function init_form_fields() {
		
			$this->form_fields = array(
				'enabled' => array(
								'title' => __( "Activer/Désactiver", 'atos' ), 
								'type' => 'checkbox', 
								'label' => __( "Activer le paiement Atos Sips", 'atos' ), 
								'default' => 'yes'
							), 
				'title' => array(
								'title' => __( "Titre", 'atos' ), 
								'type' => 'text', 
								'description' => __( "Correspond au titre que le client verra pendant la commande.", 'atos' ), 
								'default' => __( "Paiement Carte Bancaire", 'atos' )
							),
				'description' => array(
								'title' => __( "Message au client", 'atos' ), 
								'type' => 'textarea', 
								'description' => __( "Informez le client du mode de paiement par carte bancaire.", 'atos' ), 
								'default' => __( "En choisissant ce mode de paiement vous pourrez effectuer votre règlement sur le serveur sécurisé de notre banque.", 'atos' )
							), 
				'gateway_image' => array(
								'title' => __( "Icône de paiement", 'atos' ), 
								'type' => 'text', 
								'description' => __( "Url de l'image affichée lors du choix du mode de paiement.", 'atos' ),
								'default' => plugins_url('woocommerce-gateway-atos/logo/LogoMercanetBnpParibas.gif'),
								'css' => 'width:750px'
							), 
				'merchantid' => array(
								'title' => 'merchant_id', 
								'type' => 'text', 
								'description' => __( "Identifiant commerçant fourni par votre banque.", 'atos' ), 
								'default' => '014213245611111',
								'css' => 'width:140px'
							), 
				'exec_mode' => array(
								'title' => 'Fonction exec() active ?', 
								'type' => 'select', 
								'description' => __( "Certains hébergeurs (Infomaniak, WPEngine, ...) bloquent la fonction PHP exec() nécessaire au bon fonctionnement de votre kit bancaire Atos Sips. Nous avons développé une alternative pour contourner ce bloquage. N'utilisez cette alternative que si votre hébergeur bloque la fonction exec().<br/><strong style='color:red'>IMPORTANT :</strong> Le cas échéant, vous devez copier le contenu du dossier <strong>perl</strong>, présent dans le dossier de la passerelle de paiement, dans un dossier <strong>cgi-bin</strong>, à la racine de votre site (http://www.votre-site.fr/cgi-bin/). Ce dossier ainsi que les deux fichiers perl que vous aurez copié doivent être exécutables (CHMOD 0755). Vous devez également ajouter la ligne suivante dans le fichier <strong>.htaccess</strong> se trouvant à la racine de votre hébergement : php_flag 'allow_url_fopen' 'On'", 'atos' ),
								'options' => array(
									"on" => "Mon hébergeur autorise exec() (valeur recommandée pour la majorité des hébergeurs)",
									"off" => "Mon hébergeur bloque exec() (Infomaniak, WPEngine, ...)"
								),
								'default' => 'on',
								'css' => 'width:600px'
							),
				'currency_code' => array(
								'title' => 'currency_code', 
								'type' => 'text', 
								'description' => __( "Devise utilisée sur la boutique. 978 -> €. Voir le dictionnaire des données de votre banque.", 'atos' ), 
								'default' => '978',
								'css' => 'width:50px'
							), 
				'merchant_country' => array(
								'title' => 'merchant_country', 
								'type' => 'text', 
								'description' => __( "Pays du commerçant. fr -> France. Voir le dictionnaire des données de votre banque.", 'atos' ), 
								'default' => 'fr',
								'css' => 'width:50px'
							), 
				'language' => array(
								'title' => 'language', 
								'type' => 'text', 
								'description' => __( "Langue utilisée sur la boutique. fr -> Français. Voir le dictionnaire des données de votre banque.", 'atos' ), 
								'default' => 'fr',
								'css' => 'width:50px'
							), 
				'pathfile' => array(
								'title' => 'pathfile', 
								'type' => 'text', 
								'description' => __( "Emplacement du fichier pathfile de votre kit. Voir la documentation de votre banque.", 'atos' ), 
								'default' => __ServerRootAtos__.'/param/pathfile',
								'css' => 'width:430px'
							), 
				'path_bin_request' => array(
								'title' => 'request', 
								'type' => 'text', 
								'description' => __( "Emplacement de l'exécutable request du kit. Voir la documentation de votre banque.", 'atos' ),
								'default' => __ServerRootAtos__.'/cgi-bin/request',
								'css' => 'width:430px'
							), 
				'path_bin_response' => array(
								'title' => 'response', 
								'type' => 'text', 
								'description' => __( "Emplacement de l'exécutable response du kit. Voir la documentation de votre banque.", 'atos' ),
								'default' => __ServerRootAtos__.'/cgi-bin/response',
								'css' => 'width:430px'
							), 
				'capture_mode' => array(
								'title' => 'capture_mode', 
								'type' => 'select', 
								'description' => __( "Mode d'envoi en banque. AUTHOR_CAPTURE (encaissement automatique après x jours) ou VALIDATION (encaissement manuel, annulation après x jours si non encaissé). Voir la documentation de votre banque.", 'atos' ),
								'options' => array(
									'AUTHOR_CAPTURE' => "AUTHOR_CAPTURE",
									'VALIDATION' => "VALIDATION"
								),
								'default' => 'AUTHOR_CAPTURE',
								'css' => 'width:160px'
							),
				'capture_day' => array(
								'title' => 'capture_day', 
								'type' => 'text',
								'description' => __( "Délai en jours avant l'envoi en banque (AUTHOR_CAPTURE) ou l'expiration (VALIDATION). Voir la documentation de votre banque. La valeur peut être plafonnée par votre banque.", 'atos' ),
								'default' => '0',
								'css' => 'width:50px'
							),
				'logfile' => array(
								'title' => 'logfile', 
								'type' => 'text', 
								'description' => __( "Laisser vide pour ne pas enregister de log. Le dossier de destination doit être accessible en écriture. Si le fichier n'existe pas il sera créé.", 'atos' ),
								'default' => __ServerRootAtos__.'/log/logfile.txt',
								'css' => 'width:430px'
							), 
				'advert' => array(
								'title' => 'advert', 
								'type' => 'text', 
								'description' => __( "Nom de fichier d'une bannière affichée au centre en haut des pages de paiement. Voir le GUIDE DE PERSONNALISATION DES PAGES Atos. Laisser vide si vous ne souhaitez pas afficher cette bannière.", 'atos' ),
								'default' => 'advert.jpg',
								'css' => 'width:100px'
							), 
				'logo_id2' => array(
								'title' => 'logo_id2', 
								'type' => 'text', 
								'description' => __( "Nom du fichier du logo de la boutique affiché en haut à droite des pages de paiement. Voir le GUIDE DE PERSONNALISATION DES PAGES Atos. Laisser vide si vous ne souhaitez pas afficher ce logo.", 'atos' ),
								'default' => 'logo_id2.jpg',
								'css' => 'width:100px'
							), 
				'payment_means' => array(
								'title' => 'payment_means', 
								'type' => 'text', 
								'description' => __( "Contient la liste des moyens de paiement et le numéro des phrases de commentaires affichés par l'API en fonction du moyen de paiement. Voir le dictionnaire des données.", 'atos' ),
								'default' => 'CB,2,VISA,2,MASTERCARD,2'
							), 
				'debug' => array(
								'title' => __( 'Debug', 'atos' ), 
								'type' => 'checkbox', 
								'label' => __( "Afficher les informations de débogage.", 'atos' ),
								'description' => __("Ne pas activer en production.", 'atos'),
								'default' => 'no'
							)						
				);
		
		}		
		public function admin_options() {
			?>
            <p><img src="<?php echo $this->logo; ?>" /></p>
			<h3><?php _e("Paiement Atos Sips", 'atos'); ?></h3>
			<p><?php _e("Autorise les paiements par carte bancaire avec la solution <a href=\"http://www.sips.atosorigin.com\" target=\"_blank\">Atos Sips</a>. Cela nécessite la signature d'un contrat de vente à distance auprès d'une banque compatible avec la solution de paiement <a href=\"http://www.sips.atosorigin.com\" target=\"_blank\">Atos Sips</a>. Une fois le kit de paiement reçu, vous devrez l'installer sur votre serveur avant d'utiliser cette passerelle de paiement WooCommerce.", 'atos'); ?></p>
			<table class="form-table">
			<?php
				$this->generate_settings_html();
			?>
			<?php
			echo '<tr><td colspan="2">'.__("Informations sur votre installation :",'atos').'</td></tr>';
			echo '<tr><td>'.__("Racine Wordpress",'atos').'</td><td><pre>'.__WPRootAtos__.'</pre></td></tr>';
			echo '<tr><td>'.__("Racine de l'hébergement",'atos').'</td><td><pre>'.__ServerRootAtos__.'</pre></td></tr>';
			?>
			</table><!--/.form-table-->
			<?php
		} 		
		function payment_fields() {
			if ($this->description) echo wpautop(wptexturize($this->description));
		}
		public function generate_atos_form( $order_id ) {
			global $woocommerce;
			
			$atos_settings = get_option('woocommerce_atos_settings');
			$order = new WC_Order( (int) $order_id );

			if($atos_settings['exec_mode']!='off'):
				$sep = " ";
			else:
				$sep = "&";
			endif;
			$parm="merchant_id=".$atos_settings['merchantid']; // 011223344551112 (3D) 014213245611111 (no3D)
			$parm.=$sep."merchant_country=".$atos_settings['merchant_country'];
			$amount=number_format($order->order_total,2,'.','')*100;
			$parm.=$sep."amount=".str_pad($amount,3,"0",STR_PAD_LEFT);
			$parm.=$sep."currency_code=".$atos_settings['currency_code'];
			$parm.=$sep."pathfile=".$atos_settings['pathfile'];
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.20', '>' ) ): /* WC 2.1 */
				$normal_cancel_url = $order->get_checkout_order_received_url();
				if($atos_settings['exec_mode']=='off')
					$normal_cancel_url = urlencode($normal_cancel_url);
				$parm.=$sep."normal_return_url=".$normal_cancel_url; 
				$parm.=$sep."cancel_return_url=".$normal_cancel_url;
			else:
				$normal_cancel_url = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))));
				if($atos_settings['exec_mode']=='off')
					$normal_cancel_url = urlencode($normal_cancel_url);
				$parm.=$sep."normal_return_url=".$normal_cancel_url;
				$parm.=$sep."cancel_return_url=".$normal_cancel_url;
			endif;
			$automatic_url = trailingslashit(str_replace('https', 'http', get_bloginfo('wpurl')))."?wc-api=WC_Gateway_Atos";
			if($atos_settings['exec_mode']=='off')
					$automatic_url = urlencode($automatic_url);
			$parm.=$sep."automatic_response_url=".$automatic_url;
			$parm.=$sep."language=".$atos_settings['language'];
			$parm.=$sep."payment_means=".$atos_settings['payment_means'];
			$parm.=$sep."header_flag=no";
			$parm.=$sep."order_id=".$order_id;
			$parm.=$sep."logo_id2=".$atos_settings['logo_id2'];
			$parm.=$sep."advert=".$atos_settings['advert'];
			$parm.=$sep."customer_email=".$order->billing_email;
			$parm.=$sep."customer_ip_address=".substr($_SERVER['REMOTE_ADDR'], 0, 19);
			$parm.=$sep."capture_day=".$atos_settings['capture_day'];
			$parm.=$sep."capture_mode=".$atos_settings['capture_mode'];
			$path_bin = $atos_settings['path_bin_request'];
			if($atos_settings['exec_mode']!='off'):
				$parm = escapeshellcmd($parm);
				$result = exec("$path_bin $parm");
			else:
				$result = file_get_contents( "http://".$_SERVER['SERVER_NAME']."/cgi-bin/atos_request.pl?".$parm."&bindir=".$path_bin );
			endif;
		
			$tableau = explode ("!", "$result");

			$code = $tableau[1];
			$error = $tableau[2];
			$message = $tableau[3];
		
		  	if (( $code == "" ) && ( $error == "" ) )
			{
			print ("<BR><CENTER>".__('erreur appel request','atos')."</CENTER><BR>");
			print (__("executable request non trouv&eacute;","atos")." $path_bin");
			}
		
			else if ($code != 0){
				print ("<center><b><h2>".__("Erreur appel API de paiement.","atos")."</h2></b></center>");
				print ("<br><br><br>");
				print (" ".__("message d'erreur","atos")." : $error<br>");
			}
		
			else {
				print ("<br><br>");
				
				if($atos_settings['debug']=='yes')
					print (" $error <br>");
				
				print ("  $message <br>");
			}
			

		}		
		function process_payment( $order_id ) {
			$order = new WC_Order( $order_id );
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.14', '>=' ) ): /* WC 2.1 */
				$redirect = $order->get_checkout_payment_url( true ); /* WC 2.1 */
			else:
				$redirect = add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('woocommerce_pay_page_id'))));
			endif;
			return array(
				'result' 	=> 'success',
				'redirect'	=> $redirect
			);
		}
		function receipt_page( $order ) {
			
			echo '<p>'.__("Merci pour votre commande, veuillez cliquer sur l'icône de votre carte bancaire pour réaliser le paiement auprès de notre banque.", "atos").'</p>';
			echo $this->generate_atos_form( $order );
			
		}
		function check_atos_response() {
			
			global $woocommerce;
			
			if (isset($_GET['wc-api']) && $_GET['wc-api'] == 'WC_Gateway_Atos'): 

				$atos_settings = get_option('woocommerce_atos_settings');
				$message="message=".$_POST["DATA"];
				$pathfile="pathfile=".$atos_settings['pathfile'];
				$path_bin = $atos_settings['path_bin_response'];
				$message = escapeshellcmd($message);
				if($atos_settings['exec_mode']!='off'):
					$result = exec("$path_bin $pathfile $message");
				else:
					$result = file_get_contents("http://".$_SERVER['SERVER_NAME']."/cgi-bin/atos_response.pl?".$pathfile."&".$message."&bindir=".$path_bin);
				endif;
				$tableau = explode ("!", $result);
				
				$code = $tableau[1];
				$error = $tableau[2];
				$merchant_id = $tableau[3];
				$merchant_country = $tableau[4];
				$amount = $tableau[5];
				$transaction_id = $tableau[6];
				$payment_means = $tableau[7];
				$transmission_date= $tableau[8];
				$payment_time = $tableau[9];
				$payment_date = $tableau[10];
				$response_code = $tableau[11];
				$payment_certificate = $tableau[12];
				$authorisation_id = $tableau[13];
				$currency_code = $tableau[14];
				$card_number = $tableau[15];
				$cvv_flag = $tableau[16];
				$cvv_response_code = $tableau[17];
				$bank_response_code = $tableau[18];
				$complementary_code = $tableau[19];
				$complementary_info= $tableau[20];
				$return_context = $tableau[21];
				$caddie = $tableau[22];
				$receipt_complement = $tableau[23];
				$merchant_language = $tableau[24];
				$language = $tableau[25];
				$customer_id = $tableau[26];
				$order_id = $tableau[27];
				$customer_email = $tableau[28];
				$customer_ip_address = $tableau[29];
				$capture_day = $tableau[30];
				$capture_mode = $tableau[31];
				$data = $tableau[32];
				$order_validity = $tableau[33];
				$transaction_condition = $tableau[34];
				$statement_reference = $tableau[35];
				$card_validity = $tableau[36];
				$score_value = $tableau[37];
				$score_color = $tableau[38];
				$score_info = $tableau[39];
				$score_threshold = $tableau[40];
				$score_profile = $tableau[41];
			
				$logfile=$atos_settings['logfile'];
			
				if($logfile!="")
					$fp=fopen($logfile, "a");
			
			  	if(($code=="")&&($error=="")) {
					if($logfile!="")
						fwrite($fp, __("erreur appel response","atos")."\n");
					$erreur = __("executable response non trouvé","atos")." $path_bin\n";
					print ($erreur);
				}
				else if ( $code != 0 ){
					if($logfile!="") {
						fwrite($fp, " API call error.\n");
						fwrite($fp, "Error message :  $error\n");
					}
				}
				else {
					$order = new WC_Order( (int) $order_id );
					if($response_code==""||$response_code=="00") { 
						if ($order->status !== 'completed') {
							if ($order->status == 'processing') {
							} else {
								$order->payment_complete();
								$order->add_order_note(__("Paiement CB confirmé.",'atos'),1);
								$woocommerce->cart->empty_cart();
							}
						}
					} else { 
						$order->update_status('failed');
						switch($response_code) {
							case "02" : $msg_err = __("Demande d'autorisation par téléphone à la banque à cause d'un dépassement du plafond d'autorisation sur la carte, si vous êtes autorisé à forcer les transactions. Dans le cas contraire, vous obtiendrez un code 05.","atos"); break;
							case "03" : $msg_err = __("Champ merchant_id invalide, vérifier la valeur renseignée dans la requête Contrat de vente à distance inexistant, contacter votre banque.","atos"); break;
							case "05" : $msg_err = __("Autorisation refusée.","atos"); break;
							case "12" : $msg_err = __("Transaction invalide, vérifier les paramètres transférés dans la requête.","atos"); break; 
							case "17" : $msg_err = __("Annulation de l'internaute.","atos"); break;
							case "30" : $msg_err = __("Erreur de format.","atos"); break;
							case "34" : $msg_err = __("Suspicion de fraude.","atos"); break;
							case "75" : $msg_err = __("Nombre de tentatives de saisie du numéro de carte dépassé.","atos"); break;
							case "90" : $msg_err = __("Service temporairement indisponible.","atos"); break;
							default : $msg_err = __("Erreur inconnue.","atos");
						}
						switch($transaction_condition) {
							case "3D_FAILURE" : $msg_err =  __("L'acheteur n'a pas réussi à s'authentifier à 3D Secure.","atos"); break;
							case "3D_ERROR" : $msg_err =  __("Problème technique durant le processus d'authentification 3D Secure.","atos"); break;
							case "3D_NOTENROLLED" : $msg_err =  __("La carte du porteur n’est pas enrôlée à 3D Secure.","atos"); break;
						}
						if($bank_response_code!=""&&$bank_response_code!="00") {
							$msg_err .= "<br/>".__("Code réponse du serveur d'autorisation bancaire :","atos")." ";
							switch($bank_response_code) {
								case "02" : $msg_err .= __("Contacter l'émetteur de carte.","atos"); break;
								case "03" : $msg_err .= __("Accepteur invalide.","atos"); break;
								case "04" : $msg_err .= __("Conserver la carte.","atos"); break;
								case "05" : $msg_err .= __("Ne pas honorer.","atos"); break;
								case "07" : $msg_err .= __("Conserver la carte, conditions spéciales.","atos"); break;
								case "08" : $msg_err .= __("Approuver après identification.","atos"); break;
								case "12" : $msg_err .= __("Transaction invalide.","atos"); break;
								case "13" : $msg_err .= __("Montant invalide.","atos"); break;
								case "14" : $msg_err .= __("Numéro de porteur invalide.","atos"); break;
								case "15" : $msg_err .= __("Emetteur de carte inconnu.","atos"); break;
								case "30" : $msg_err .= __("Erreur de format.","atos"); break;
								case "31" : $msg_err .= __("Identifiant de l'organisme acquéreur inconnu.","atos"); break;
								case "33" : $msg_err .= __("Date de validité de la carte dépassée.","atos"); break;
								case "34" : $msg_err .= __("Suspicion de fraude.","atos"); break;
								case "41" : $msg_err .= __("Carte perdue.","atos"); break;
								case "43" : $msg_err .= __("Carte volée.","atos"); break;
								case "51" : $msg_err .= __("Provision insuffisante ou crédit dépassé.","atos"); break;
								case "54" : $msg_err .= __("Date de validité de la carte dépassée.","atos"); break;
								case "56" : $msg_err .= __("Carte absente du fichier.","atos"); break;
								case "57" : $msg_err .= __("Transaction non permise à ce porteur.","atos"); break;
								case "58" : $msg_err .= __("Transaction interdite au terminal.","atos"); break;
								case "59" : $msg_err .= __("Suspicion de fraude.","atos"); break;
								case "60" : $msg_err .= __("L'accepteur de carte doit contacter l'acquéreur.","atos"); break;
								case "61" : $msg_err .= __("Dépasse la limite du montant de retrait.","atos"); break;
								case "63" : $msg_err .= __("Règles de sécurité non respectées.","atos"); break;
								case "68" : $msg_err .= __("Réponse non parvenue ou reçue trop tard.","atos"); break;
								case "90" : $msg_err .= __("Arrêt momentané du système.","atos"); break;
								case "91" : $msg_err .= __("Emetteur de cartes inaccessible.","atos"); break;
								case "96" : $msg_err .= __("Mauvais fonctionnement du système.","atos"); break;
								case "97" : $msg_err .= __("Échéance de la temporisation de surveillance globale.","atos"); break;
								case "98" : $msg_err .= __("Serveur indisponible routage réseau demandé à nouveau.","atos"); break;
								case "99" : $msg_err .= __("Incident domaine initiateur","atos"); break;
								default : $msg_err .= __("Erreur inconnue","atos");
							}
						}
						
						$order->add_order_note(__("Paiement CB : ECHEC<br/>Erreur :",'atos').' '.$msg_err);
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0.14', '>=' ) ): /* WC 2.1 */
							$payer_url = $order->get_checkout_payment_url();
						else:
							$payer_url = add_query_arg('order_id', $order->id, add_query_arg('order', $order->order_key, add_query_arg('pay_for_order', 'true', get_permalink(get_option('woocommerce_pay_page_id')))));
						endif;
						$order->add_order_note(sprintf(__("Échec du règlement par carte bancaire de votre commande, <a href=\"%s\">cliquez ici</a> pour effectuer une nouvelle tentative de paiement.", "atos"), $payer_url),1); /* WC 2.1 */
					}
					if($logfile!="") {
						fwrite( $fp, "merchant_id : $merchant_id\n");
						fwrite( $fp, "merchant_country : $merchant_country\n");
						fwrite( $fp, "amount : $amount\n");
						fwrite( $fp, "transaction_id : $transaction_id\n");
						fwrite( $fp, "transmission_date: $transmission_date\n");
						fwrite( $fp, "payment_means: $payment_means\n");
						fwrite( $fp, "payment_time : $payment_time\n");
						fwrite( $fp, "payment_date : $payment_date\n");
						fwrite( $fp, "response_code : $response_code\n");
						fwrite( $fp, "payment_certificate : $payment_certificate\n");
						fwrite( $fp, "authorisation_id : $authorisation_id\n");
						fwrite( $fp, "currency_code : $currency_code\n");
						fwrite( $fp, "card_number : $card_number\n");
						fwrite( $fp, "cvv_flag: $cvv_flag\n");
						fwrite( $fp, "cvv_response_code: $cvv_response_code\n");
						fwrite( $fp, "bank_response_code: $bank_response_code\n");
						fwrite( $fp, "complementary_code: $complementary_code\n");
						fwrite( $fp, "complementary_info: $complementary_info\n");
						fwrite( $fp, "return_context: $return_context\n");
						fwrite( $fp, "caddie : $caddie\n");
						fwrite( $fp, "receipt_complement: $receipt_complement\n");
						fwrite( $fp, "merchant_language: $merchant_language\n");
						fwrite( $fp, "language: $language\n");
						fwrite( $fp, "customer_id: $customer_id\n");
						fwrite( $fp, "order_id: $order_id\n");
						fwrite( $fp, "customer_email: $customer_email\n");
						fwrite( $fp, "customer_ip_address: $customer_ip_address\n");
						fwrite( $fp, "capture_day: $capture_day\n");
						fwrite( $fp, "capture_mode: $capture_mode\n");
						fwrite( $fp, "data: $data\n");
						fwrite( $fp, "order_validity: $order_validity\n");
						fwrite( $fp, "transaction_condition: $transaction_condition\n");
						fwrite( $fp, "statement_reference: $statement_reference\n");
						fwrite( $fp, "card_validity: $card_validity\n");
						fwrite( $fp, "card_validity: $score_value\n");
						fwrite( $fp, "card_validity: $score_color\n");
						fwrite( $fp, "card_validity: $score_info\n");
						fwrite( $fp, "card_validity: $score_threshold\n");
						fwrite( $fp, "card_validity: $score_profile\n");
						fwrite( $fp, "-------------------------------------------\n");
					}
				}
				if($logfile!="")
					fclose ($fp);
				
				die(); 			
				
			endif; 
		}
		function thankyou_page() {
			global $woocommerce;
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.20', '>' ) ): /* WC 2.1 */
				global $wp;
				$order_id = (int) $wp->query_vars['order-received'];
			else:
				$order_id = (int) $_GET['order'];
			endif;
			$order = new WC_Order( $order_id );
			if ($order->status == 'processing'||$order->status == 'completed') {
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.20', '>' ) ): /* WC 2.1 */
					$url_commande = $order->get_view_order_url();
					$montant_commande = wc_price($order->order_total);
				else:
					$url_commande = add_query_arg('order', $order->id, get_permalink(get_option('woocommerce_view_order_page_id')));
					$montant_commande = woocommerce_price($order->order_total);
				endif;
				$compte_client = get_post_meta( $order->id, '_customer_user', true );
				printf("<p>".__("Votre règlement par carte bancaire de %s a bien été finalisé auprès de notre banque"), $montant_commande);
				if($compte_client>0):
					printf(__(", <a href=\"%s\">cliquez ici</a> pour consulter votre commande.", "atos")."</p>", $url_commande);
				else:
					echo ".</p>";
				endif;
			} elseif($order->status != 'failed') {
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.14', '>=' ) ): /* WC 2.1 */
					$payer_url = $order->get_checkout_payment_url();
				else:
					$payer_url = add_query_arg('order_id', $order->id, add_query_arg('order', $order->order_key, add_query_arg('pay_for_order', 'true', get_permalink(get_option('woocommerce_pay_page_id')))));
				endif;
				printf("<p>".__("Échec du règlement par carte bancaire de votre commande, <a href=\"%s\">cliquez ici</a> pour effectuer une nouvelle tentative de paiement.", "atos")."</p>", $payer_url); /* WC 2.1 */
			}
		}
	}
	function add_atos_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Atos'; return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'add_atos_gateway' );

}

function woocommerce_gateway_atos_add_link($links, $file) {
	if ( version_compare( WOOCOMMERCE_VERSION, '2.0.20', '>' ) ): /* WC 2.1 */
		$reglages_url = 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_atos';
	else:
		$reglages_url = 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Gateway_Atos';
	endif;
	$links[] = '<a href="'.admin_url($reglages_url).'">' . __('Réglages','atos') .'</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'woocommerce_gateway_atos_add_link',  10, 2);
?>