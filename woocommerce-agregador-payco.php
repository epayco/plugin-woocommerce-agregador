<?php
/**
 * WooCommerce Epayco Agregador
 *
 * @package WooCommerce Epayco Agregador
 *
 * Plugin Name: WooCommerce Epayco Agregador
 * Description: Plugin ePayco Agregador for WooCommerce.
 * Version: 7.0.0
 * Author: ePayco
 * Author URI: http://epayco.co
 * Tested up to: 6.4
 * WC requires at least: 7.4
 * WC tested up to: 8.3
 * Text Domain: woo-epayco-agregador
 * Domain Path: /languages/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define( 'EPAYCO_AGREGADOR_WOOCOMMERCE_VERSION', '5.3.0' );
define( 'EPAYCO_AGREGADOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'EPAYCO_AGREGADOR_PLUGIN_PATH' ) ) {
    define( 'EPAYCO_AGREGADOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EPAYCO_AGREGADOR_PLUGIN_DATA_URL' ) ) {
    define( 'EPAYCO_AGREGADOR_PLUGIN_DATA_URL', EPAYCO_AGREGADOR_PLUGIN_URL . 'includes/data/' );
}
if ( ! defined( 'EPAYCO_AGREGADOR_PLUGIN_CLASS_PATH' ) ) {
    define( 'EPAYCO_AGREGADOR_PLUGIN_CLASS_PATH', EPAYCO_AGREGADOR_PLUGIN_PATH . 'classes/' );
}

add_action( 'plugins_loaded', 'woocommerce_agregador_epayco_init', 11 );

add_action( 'before_woocommerce_init',
    function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);

/**
 * epayco hook
 *
 * @param string $hook page hook.
 */
function epayco_agregador_styles_css( $hook ) {

    if ( 'woocommerce_page_wc-settings' == $hook ) {
        wp_register_style( 'aboutEpaycoAgregador', EPAYCO_AGREGADOR_PLUGIN_URL . 'assets/css/epayco-css.css', array(), '1.2.0' );
        wp_enqueue_style( 'aboutEpaycoAgregador' );
        wp_register_script('aboutEpaycoJqueryAgregador',  EPAYCO_AGREGADOR_PLUGIN_URL . 'assets/js/frontend/admin.js', array('jquery'), '7.0.0', null);
        wp_enqueue_script('aboutEpaycoJqueryAgregador');
    }
}
add_action( 'admin_enqueue_scripts', 'epayco_agregador_styles_css' );

/**
 * Epayco init.
 */
function woocommerce_agregador_epayco_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }
    /**
     * Localisation
     */
    load_plugin_textdomain( 'woo-epayco-agregador', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );


    /**
     * Epayco add method.
     *
     * @param array $methods all WooCommerce methods.
     */
    function woocommerce_add_agregador_epayco_agregador( $methods ) {
        $methods[] = 'WC_Agregador_Epayco';
        return $methods;
    }
    add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_agregador_epayco_agregador' );


    function plugin_abspath_epayco_agregador() {
        return trailingslashit( plugin_dir_path( __FILE__ ) );
    }

    function plugin_url_epayco_agregador() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    require_once EPAYCO_AGREGADOR_PLUGIN_CLASS_PATH . 'class-wc-agregador-epayco.php';

}

function woocommerce_agregador_epayco_block_support() {
    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        require_once 'includes/blocks/wc-agregador-epayco-support.php';
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new WC_Agregador_Epayco_Support );
            }
        );
    }
}
add_action( 'woocommerce_blocks_loaded', 'woocommerce_agregador_epayco_block_support' );

function epayco_agregador_woocommerce_addon_settings_link( $links ) {
    array_push( $links, '<a href="admin.php?page=wc-settings&tab=checkout&section=epayco_agregador">' . __( 'Configuración' ) . '</a>' );
    return $links;
}

add_filter( "plugin_action_links_".plugin_basename( __FILE__ ),'epayco_agregador_woocommerce_addon_settings_link' );
function epayco_agregador_update_db_check()
{
    require_once(dirname(__FILE__) . '/includes/blocks/EpaycoOrderAgregador.php');
    EpaycoOrderAgregador::setup();
}
add_action('plugins_loaded', 'epayco_agregador_update_db_check');
function register_epayco_agregador_order_status() {
    register_post_status( 'wc-epayco-failed', array(
        'label'                     => 'ePayco Pago Fallido',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Fallido <span class="count">(%s)</span>', 'ePayco Pago Fallido <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco_agregador_failed', array(
        'label'                     => 'ePayco Pago Fallido Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Fallido Prueba <span class="count">(%s)</span>', 'ePayco Pago Fallido Prueba <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco-cancelled', array(
        'label'                     => 'ePayco Pago Cancelado',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Cancelado <span class="count">(%s)</span>', 'ePayco Pago Cancelado <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco_agregador_cancelled', array(
        'label'                     => 'ePayco Pago Cancelado Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Cancelado Prueba <span class="count">(%s)</span>', 'ePayco Pago Cancelado Prueba <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco-on-hold', array(
        'label'                     => 'ePayco Pago Pendiente',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Pendiente <span class="count">(%s)</span>', 'ePayco Pago Pendiente <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco_agregador_on_hold', array(
        'label'                     => 'ePayco Pago Pendiente Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Pendiente Prueba <span class="count">(%s)</span>', 'ePayco Pago Pendiente Prueba <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco-processing', array(
        'label'                     => 'ePayco Procesando Pago',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Procesando Pago <span class="count">(%s)</span>', 'ePayco Procesando Pago <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco_agregador_processing', array(
        'label'                     => 'ePayco Procesando Pago Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Procesando Pago Prueba<span class="count">(%s)</span>', 'ePayco Procesando Pago Prueba<span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-processing', array(
        'label'                     => 'Procesando',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Procesando<span class="count">(%s)</span>', 'Procesando<span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-processing_test', array(
        'label'                     => 'Procesando Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Procesando Prueba<span class="count">(%s)</span>', 'Procesando Prueba<span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco-completed', array(
        'label'                     => 'ePayco Pago Completado',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Completado <span class="count">(%s)</span>', 'ePayco Pago Completado <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-epayco_agregador_completed', array(
        'label'                     => 'ePayco Pago Completado Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'ePayco Pago Completado Prueba <span class="count">(%s)</span>', 'ePayco Pago Completado Prueba <span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-completed', array(
        'label'                     => 'Completado',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Completado<span class="count">(%s)</span>', 'Completado<span class="count">(%s)</span>' )
    ));

    register_post_status( 'wc-completed_test', array(
        'label'                     => 'Completado Prueba',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Completado Prueba<span class="count">(%s)</span>', 'Completado Prueba<span class="count">(%s)</span>' )
    ));
}
add_action( 'plugins_loaded', 'register_epayco_agregador_order_status' );

function add_epayco_agregador_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    $epayco_agregador_order = get_option('epayco_agregador_order_status');
    $testMode = $epayco_agregador_order == "yes" ? "true" : "false";
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-cancelled' === $key ) {
            if($testMode=="true"){
                $new_order_statuses['wc-epayco_agregador_cancelled'] = 'ePayco Pago Cancelado Prueba';
            }else{
                $new_order_statuses['wc-epayco-cancelled'] = 'ePayco Pago Cancelado';
            }
        }

        if ( 'wc-failed' === $key ) {
            if($testMode=="true"){
                $new_order_statuses['wc-epayco_agregador_failed'] = 'ePayco Pago Fallido Prueba';
            }else{
                $new_order_statuses['wc-epayco-failed'] = 'ePayco Pago Fallido';
            }
        }

        if ( 'wc-on-hold' === $key ) {
            if($testMode=="true"){
                $new_order_statuses['wc-epayco_agregador_on_hold'] = 'ePayco Pago Pendiente Prueba';
            }else{
                $new_order_statuses['wc-epayco-on-hold'] = 'ePayco Pago Pendiente';
            }
        }

        if ( 'wc-processing' === $key ) {
            if($testMode=="true"){
                $new_order_statuses['wc-epayco_agregador_processing'] = 'ePayco Pago Procesando Prueba';
            }else{
                $new_order_statuses['wc-epayco-processing'] = 'ePayco Pago Procesando';
            }
        }else {
            if($testMode=="true"){
                $new_order_statuses['wc-processing_test'] = 'Procesando Prueba';
            }else{
                $new_order_statuses['wc-processing'] = 'Procesando';
            }
        }

        if ( 'wc-completed' === $key ) {
            if($testMode=="true"){
                $new_order_statuses['wc-epayco_agregador_completed'] = 'ePayco Pago Completado Prueba';
            }else{
                $new_order_statuses['wc-epayco-completed'] = 'ePayco Pago Completado';
            }
        }else{
            if($testMode=="true"){
                $new_order_statuses['wc-completed_test'] = 'Completado Prueba';
            }else{
                $new_order_statuses['wc-completed'] = 'Completado';
            }
        }
    }
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_epayco_agregador_to_order_statuses' );

function styling_admin_order_list_agregador() {
    global $pagenow, $post;
    if( $pagenow != 'edit.php') return; // Exit
    if( get_post_type($post->ID) != 'shop_order' ) return; // Exit
    // HERE we set your custom status
    $epayco_agregador_order = get_option('epayco_agregador_order_status');
    $testMode = $epayco_agregador_order == "yes" ? "true" : "false";
    if($testMode=="true"){
        $order_status_failed = 'epayco_agregador_failed';
        $order_status_on_hold = 'epayco_agregador_on_hold';
        $order_status_processing = 'epayco_agregador_processing';
        $order_status_processing_ = 'processing_test';
        $order_status_completed = 'epayco_agregador_completed';
        $order_status_cancelled = 'epayco_agregador_cancelled';
        $order_status_completed_ = 'completed_test';

    }else{
        $order_status_failed = 'epayco-failed';
        $order_status_on_hold = 'epayco-on-hold';
        $order_status_processing = 'epayco-processing';
        $order_status_processing_ = 'processing';
        $order_status_completed = 'epayco-completed';
        $order_status_cancelled = 'epayco-cancelled';
        $order_status_completed_ = 'completed';
    }
    ?>

    <style>
        .order-status.status-<?php esc_html_e( $order_status_failed, 'text_domain' );  ?> {
            background: #eba3a3;
            color: #761919;
        }
        .order-status.status-<?php esc_html_e( $order_status_on_hold, 'text_domain' ); ?> {
            background: #f8dda7;
            color: #94660c;
        }
        .order-status.status-<?php esc_html_e( $order_status_processing, 'text_domain' ); ?> {
            background: #c8d7e1;
            color: #2e4453;
        }
        .order-status.status-<?php esc_html_e( $order_status_processing_, 'text_domain' ); ?> {
            background: #c8d7e1;
            color: #2e4453;
        }
        .order-status.status-<?php esc_html_e( $order_status_completed, 'text_domain' ); ?> {
            background: #d7f8a7;
            color: #0c942b;
        }
        .order-status.status-<?php esc_html_e( $order_status_completed_, 'text_domain' ); ?> {
            background: #d7f8a7;
            color: #0c942b;
        }
        .order-status.status-<?php esc_html_e( $order_status_cancelled, 'text_domain' ); ?> {
            background: #eba3a3;
            color: #761919;
        }
    </style>

    <?php
}
add_action('admin_head', 'styling_admin_order_list_agregador' );