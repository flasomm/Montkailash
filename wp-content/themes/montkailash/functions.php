<?php


if (!function_exists('montkailash_setup')) :

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     *
     * @since Twenty Fifteen 1.0
     */
    function montkailash_setup()
    {

    }

endif; // montkailash_setup
add_action('after_setup_theme', 'montkailash_setup');

/**
 * Register widget area.
 *
 * @since Twenty Fifteen 1.0
 *
 * @link  https://codex.wordpress.org/Function_Reference/register_sidebar
 */
function montkailash_widgets_init()
{
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Archives');
    unregister_widget('WP_Widget_Links');
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Search');
    unregister_widget('WP_Widget_Text');
    unregister_widget('WP_Widget_Categories');
    unregister_widget('WP_Widget_Recent_Posts');
    unregister_widget('WP_Widget_Recent_Comments');
    unregister_widget('WP_Widget_RSS');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Nav_Menu_Widget');

    register_sidebar(
        array(
            'name'          => __('Widget Area', 'montkailash'),
            'id'            => 'sidebar-1',
            'description'   => __('Add widgets here to appear in your sidebar.', 'montkailash'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        )
    );

}

add_action('widgets_init', 'montkailash_widgets_init');


/**
 * Enqueue scripts and styles.
 *
 * @since Twenty Fifteen 1.0
 */
function montkailash_scripts()
{
    // Load our main stylesheet.
    wp_enqueue_style('bootstrap-style', get_template_directory_uri() . '/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-style', get_template_directory_uri() . '/css/bootstrap-theme.min.css');
    wp_enqueue_style('lightbox-style', get_template_directory_uri() . '/css/lightbox.css');
    wp_enqueue_style('montkailash-style', get_stylesheet_uri());

    if (is_page('Contact')) {
        wp_register_script('gmap', ("http://maps.google.com/maps/api/js?sensor=true"), '', '', true);
        wp_enqueue_script('gmap');
        wp_enqueue_script('gmaps', get_template_directory_uri() . '/js/gmaps.js', array('jquery'), '', true);
        wp_enqueue_script('function-map', get_template_directory_uri() . '/js/function_map.js', array('jquery'), '0.1', true);
    }

    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"), '', '1.8.3', true);
        wp_enqueue_script('jquery');

        wp_deregister_script('wonderplugin-slider-skins-script');
        wp_deregister_script('wonderplugin-slider-script');
        wp_deregister_script('wonderplugin-slider-creator-script');
        wp_deregister_script('woo-vou-public-script');

        wp_register_script('wonderplugin-slider-skins-script', plugins_url() . '/wonderplugin-slider/engine/wonderpluginsliderskins.js', array('jquery'), '4.9', true);
        wp_register_script('wonderplugin-slider-script', plugins_url() . '/wonderplugin-slider/engine/wonderpluginslider.js', array('jquery'), '4.9', true);
        wp_register_script('wonderplugin-slider-creator-script', plugins_url() . '/wonderplugin-slider/app/wonderplugin-slider-creator.js', array('jquery'), '4.9', true);
        wp_register_script('woo-vou-public-script', plugins_url() . '/woocommerce-pdf-vouchers/includes/js/woo-vou-public.js', array('jquery'), '2.4.4', true);

        wp_enqueue_script('bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '20150330', true);
        wp_enqueue_script('lightbox', get_template_directory_uri() . '/js/lightbox.js', array('jquery'), '2.8.1', true);
        wp_enqueue_script('montkailash-script', get_template_directory_uri() . '/js/functions.js', array('jquery'), '0.1', true);
    }

}

add_action('wp_enqueue_scripts', 'montkailash_scripts');


add_filter('woocommerce_breadcrumb_defaults', 'jk_woocommerce_breadcrumbs');
function jk_woocommerce_breadcrumbs()
{
    return array(
        'delimiter'   => '',
        'wrap_before' => '<nav id="breadcrumbs"><ul>',
        'wrap_after'  => '</ul></nav>',
        'before'      => '<li><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb">',
        'after'       => '</span></li>',
        'home'        => _x('Home', 'breadcrumb', 'woocommerce'),
    );
}

/**
 * Ajax functions to retrieve cart
 */
add_action('wp_ajax_nopriv_load_woo_cart', 'load_woo_cart');
add_action('wp_ajax_load_woo_cart', 'load_woo_cart');

function load_woo_cart()
{
    echo get_template_part('woocommerce/cart/cart', 'content');
    die();
}

/**
 * woocommerce_package_rates is a 2.1+ hook
 */
add_filter('woocommerce_package_rates', 'hide_shipping_when_free_is_available', 10, 2);

/**
 * Hide shipping rates when free shipping is available
 *
 * @param array $rates   Array of rates found for the package
 * @param array $package The package array/object being shipped
 *
 * @return array of modified rates
 */
function hide_shipping_when_free_is_available($rates, $package)
{

    // Only modify rates if free_shipping is present
    if (isset($rates['free_shipping'])) {

        // To unset a single rate/method, do the following. This example unsets flat_rate shipping
        unset($rates['flat_rate']);

        // To unset all methods except for free_shipping, do the following
        $free_shipping          = $rates['free_shipping'];
        $rates                  = array();
        $rates['free_shipping'] = $free_shipping;
    }

    return $rates;
}

function my_login_logo()
{ ?>
    <style type="text/css">
        .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/spa-logo.png);
            background-size: 100% auto;
            width: 100%;
        }
    </style>
<?php }

add_action('login_enqueue_scripts', 'my_login_logo');


function woo_vou_assign_vendor_cap_to_shopmanager() {

    $role = get_role( 'shop_manager' );
    $role->add_cap( 'woo_vendor_options' );

    $role = get_role( 'administrator' );
    $role->add_cap( 'woo_vendor_options' );
}

add_action( 'init', 'woo_vou_assign_vendor_cap_to_shopmanager' );