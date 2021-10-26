<?php
/**
 * @since             1.0.0
 * @package           ePayco_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       ePayco for WooCommerce
 * Description:       Plugin ePayco WooCommerce.
 * Version:           5.1.x
 * Author:            ePayco
 * Author URI:        http://epayco.co
 *Lice
 * Text Domain:       epayco-agregador-woocommerce
 * Domain Path:       /languages
 */


if (!defined('WPINC')) {
    die;
}


require_once(dirname(__FILE__) . '/lib/EpaycoAgregadorOrder.php');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('plugins_loaded', 'init_epayco_agregador_woocommerce', 0);
    function init_epayco_agregador_woocommerce()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }
        class WC_ePayco_agregador extends WC_Payment_Gateway
        {
            public $max_monto;
            public function __construct()
            {
                $this->id = 'epayco_agregador';
                $this->icon = plugin_dir_url(__FILE__).'lib/logo.png';
                $this->method_title = __('ePayco Checkout Agregador', 'epayco_agregador_woocommerce');
                $this->method_description = __('Acepta tarjetas de credito, depositos y transferencias.', 'epayco_agregador_woocommerce');
                $this->order_button_text = __('Pagar', 'epayco_agregador_woocommerce');
                $this->has_fields = false;
                $this->supports = array('products');
                $this->init_form_fields();
                $this->init_settings();
                $this->msg['message']   = "";
                $this->msg['class']     = "";
                $this->title = $this->get_option('epayco_agregador_title');
                $this->epayco_agregador_customerid = $this->get_option('epayco_agregador_customerid');
                $this->epayco_agregador_secretkey = $this->get_option('epayco_agregador_secretkey');
                $this->epayco_agregador_publickey = $this->get_option('epayco_agregador_publickey');
                $this->monto_maximo = $this->get_option('monto_maximo');
                $this->max_monto = $this->get_option('monto_maximo');
                $this->description = $this->get_option('description');
                $this->epayco_agregador_testmode = $this->get_option('epayco_agregador_testmode');
                if ($this->get_option('epayco_agregador_reduce_stock_pending') !== null ) {
                    $this->epayco_agregador_reduce_stock_pending = $this->get_option('epayco_agregador_reduce_stock_pending');
                }else{
                     $this->epayco_agregador_reduce_stock_pending = "yes";
                }
                $this->epayco_agregador_type_checkout=$this->get_option('epayco_agregador_type_checkout');
                $this->epayco_agregador_endorder_state=$this->get_option('epayco_agregador_endorder_state');
                $this->epayco_agregador_url_response=$this->get_option('epayco_agregador_url_response');
                $this->epayco_agregador_url_confirmation=$this->get_option('epayco_agregador_url_confirmation');
                $this->epayco_agregador_lang=$this->get_option('epayco_agregador_lang')?$this->get_option('epayco_agregador_lang'):'es';
                $this->response_data = $this->get_option('response_data');
                add_filter('woocommerce_thankyou_order_received_text', array(&$this, 'order_received_message'), 10, 2 );
                add_action('ePayco_agregador_init', array( $this, 'ePayco_agregador_successful_request'));
                add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
                add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_ePayco_agregador_response' ) );
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                add_action('wp_ajax_nopriv_returndata',array($this,'datareturnepayco_ajax'));
                if ($this->epayco_agregador_testmode == "yes") {
                    if (class_exists('WC_Logger')) {
                        $this->log = new WC_Logger();
                    } else {
                        $this->log = WC_ePayco_agregador::woocommerce_instance()->logger();
                    }
                }
            }

            function order_received_message( $text, $order ) {
                if(!empty($_GET['msg'])){
                    return $text .' '.$_GET['msg'];
                }
                return $text;
            }

            public function is_valid_for_use()
            {
                return in_array(get_woocommerce_currency(), array('COP', 'USD'));
            }

            public function admin_options()
            {
                ?>
                <style>
                    tbody{
                    }
                    .epayco-table tr:not(:first-child) {
                        border-top: 1px solid #ededed;
                    }
                    .epayco-table tr th{
                            padding-left: 15px;
                            text-align: -webkit-right;
                    }
                    .epayco-table input[type="text"]{
                            padding: 8px 13px!important;
                            border-radius: 3px;
                            width: 100%!important;
                    }
                    .epayco-table .description{
                        color: #afaeae;
                    }
                    .epayco-table select{
                            padding: 8px 13px!important;
                            border-radius: 3px;
                            width: 100%!important;
                            height: 37px!important;
                    }
                    .epayco-required::before{
                        content: '* ';
                        font-size: 16px;
                        color: #F00;
                        font-weight: bold;
                    }

                </style>
                <div class="container-fluid">
                    <div class="panel panel-default" style="">
                        <img  src="<?php echo plugin_dir_url(__FILE__).'lib/logo.png' ?>">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-pencil"></i>Configuración <?php _e('ePayco', 'epayco_agregador_woocommerce'); ?></h3>
                        </div>

                        <div style ="color: #31708f; background-color: #d9edf7; border-color: #bce8f1;padding: 10px;border-radius: 5px;">
                            <b>Este modulo le permite aceptar pagos seguros por la plataforma de pagos ePayco</b>
                            <br>Si el cliente decide pagar por ePayco, el estado del pedido cambiara a ePayco Esperando Pago
                            <br>Cuando el pago sea Aceptado o Rechazado ePayco envia una configuracion a la tienda para cambiar el estado del pedido.
                        </div>

                        <div class="panel-body" style="padding: 15px 0;background: #fff;margin-top: 15px;border-radius: 5px;border: 1px solid #dcdcdc;border-top: 1px solid #dcdcdc;">
                                <table class="form-table epayco-table">
                                <?php
                            if ($this->is_valid_for_use()) :
                                $this->generate_settings_html();
                            else :
                            if ( is_admin() && ! defined( 'DOING_AJAX')) {
                                echo '<div class="error"><p><strong>' . __( 'ePayco: Requiere que la moneda sea USD O COP', 'epayco-agregador-woocommerce' ) . '</strong>: ' . sprintf(__('%s', 'woocommerce-mercadopago' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __( 'Click aquí para configurar!', 'epayco_agregador_woocommerce') . '</a>' ) . '</p></div>';
                                        }
                                    endif;
                                ?>
                                </table>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                    'title' => __('Habilitar/Deshabilitar', 'epayco_agregador_woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Habilitar ePayco Checkout', 'epayco_agregador_woocommerce'),
                    'default' => 'yes'
                    ),
                    'epayco_agregador_title' => array(
                        'title' => __('<span class="epayco-required">Título</span>', 'epayco_agregador_woocommerce'),
                        'type' => 'text',
                        'description' => __('Corresponde al titulo que el usuario ve durante el checkout.', 'epayco_agregador_woocommerce'),
                        'default' => __('Checkout ePayco (Tarjetas de crédito,debito,efectivo)', 'epayco_agregador_woocommerce'),
                        //'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => __('<span class="epayco-required">Descripción</span>', 'epayco_agregador_woocommerce'),
                        'type' => 'textarea',
                        'description' => __('Corresponde a la descripción que verá el usuaro durante el checkout', 'epayco_agregador_woocommerce'),
                        'default' => __('Checkout ePayco (Tarjetas de crédito,debito,efectivo)', 'epayco_agregador_woocommerce'),
                        //'desc_tip' => true,
                    ),
                    'epayco_agregador_customerid' => array(
                        'title' => __('<span class="epayco-required">P_CUST_ID_CLIENTE</span>', 'epayco_agregador_woocommerce'),
                        'type' => 'text',
                        'description' => __('ID de cliente que lo identifica en ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.', 'epayco_agregador_woocommerce'),
                        'default' => '',
                        //'desc_tip' => true,
                        'placeholder' => '',
                    ),
                    'epayco_agregador_secretkey' => array(
                        'title' => __('<span class="epayco-required">P_KEY</span>', 'epayco_agregador_woocommerce'),
                        'type' => 'text',
                        'description' => __('LLave para firmar la información enviada y recibida de ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.', 'epayco_agregador_woocommerce'),
                        'default' => '',
                        'placeholder' => ''
                    ),
                    'epayco_agregador_publickey' => array(
                        'title' => __('<span class="epayco-required">PUBLIC_KEY</span>', 'epayco_agregador_woocommerce'),
                        'type' => 'text',
                        'description' => __('LLave para autenticar y consumir los servicios de ePayco, Proporcionado en su panel de clientes en la opción configuración.', 'epayco_agregador_woocommerce'),
                        'default' => '',
                        'placeholder' => ''
                    ),
                    'epayco_agregador_testmode' => array(
                        'title' => __('Sitio en pruebas', 'epayco_agregador_woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar el modo de pruebas', 'epayco_agregador_woocommerce'),
                        'description' => __('Habilite para realizar pruebas', 'epayco_agregador_woocommerce'),
                        'default' => 'no',
                    ),
                    'epayco_agregador_type_checkout' => array(
                        'title' => __('Tipo Checkout', 'epayco_agregador_woocommerce'),
                        'type' => 'select',
                        'css' =>'line-height: inherit',
                        'label' => __('Seleccione un tipo de Checkout:', 'epayco_agregador_woocommerce'),
                        'description' => __('(Onpage Checkout, el usuario al pagar permanece en el sitio) ó (Standart Checkout, el usario al pagar es redireccionado a la pasarela de ePayco)', 'epayco_agregador_woocommerce'),
                        'options' => array('false'=>"Onpage Checkout","true"=>"Standart Checkout"),
                    ),
                    'epayco_agregador_endorder_state' => array(
                        'title' => __('Estado Final del Pedido', 'epayco_agregador_woocommerce'),
                        'type' => 'select',
                        'css' =>'line-height: inherit',
                        'description' => __('Seleccione el estado del pedido que se aplicará a la hora de aceptar y confirmar el pago de la orden', 'epayco_agregador_woocommerce'),
                        'options' => array(
                            'epayco-processing'=>"ePayco Procesando Pago",
                            "epayco-completed"=>"ePayco Pago Completado",
                            'processing'=>"Procesando",
                            "completed"=>"Completado"
                        ),
                    ),
                    'epayco_agregador_url_response' => array(
                        'title' => __('Página de Respuesta', 'epayco_agregador_woocommerce'),
                        'type' => 'select',
                        'css' =>'line-height: inherit',
                        'description' => __('Url de la tienda donde se redirecciona al usuario luego de pagar el pedido', 'epayco_agregador_woocommerce'),
                        'options'       => $this->get_pages(__('Seleccionar pagina', 'epayco_agregador-woocommerce')),
                    ),
                    'epayco_agregador_url_confirmation' => array(
                        'title' => __('Página de Confirmación', 'epayco_agregador_woocommerce'),
                        'type' => 'select',
                        'css' =>'line-height: inherit',
                        'description' => __('Url de la tienda donde ePayco confirma el pago', 'epayco_agregador_woocommerce'),
                        'options'       => $this->get_pages(__('Seleccionar pagina', 'epayco_agregador-woocommerce')),
                    ),
                    'epayco_agregador_reduce_stock_pending' => array(
                        'title' => __('Reducir el stock en transacciones pendientes', 'epayco_agregador_woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar', 'epayco_woocommerce'),
                        'description' => __('Habilite para reducir el stock en transacciones pendientes', 'epayco_agregador_woocommerce'),
                        'default' => 'yes',
                    ),
                    'epayco_agregador_lang' => array(
                        'title' => __('Idioma del Checkout', 'epayco_agregador_woocommerce'),
                        'type' => 'select',
                        'css' =>'line-height: inherit',
                        'description' => __('Seleccione el idioma del checkout', 'epayco_agregador_woocommerce'),
                        'options' => array('es'=>"Español","en"=>"Inglés"),
                    ),
                    'response_data' => array(
                        'title' => __('Habilitar envió de atributos a través de la URL de respuesta', 'epayco_agregador_woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar el modo redireccion con data', 'epayco_agregador_woocommerce'),
                        'description' => __('Al habilitar esta opción puede exponer información sensible de sus clientes, el uso de esta opción es bajo su responsabilidad, conozca esta información en el siguiente  <a href="https://docs.epayco.co/payments/checkout#scroll-response-p" target="_blank">link.</a>', 'epayco_agregador_woocommerce'),
                        'default' => 'no',
                    ),
                        'monto_maximo' => array(
                        'title' => __('monto maximo', 'epayco_agregador_woocommerce'),
                        'type' => 'text',
                        'description' => __('ingresa el monto maximo permitido ha pagar por el metodo de pago', 'epayco_agregador_woocommerce'),
                        'default' => '3000000',
                        //'desc_tip' => true,
                        'placeholder' => '3000000',
                    ),
                );
            }


            /**
             * @param $order_id
             * @return array
             */
            public function process_payment($order_id)
            {
                $order = new WC_Order($order_id);
                $order->reduce_order_stock();
                if (version_compare( WOOCOMMERCE_VERSION, '2.1', '>=')) {
                    return array(
                        'result'    => 'success',
                        'redirect'  => add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
                    );
                } else {
                    return array(
                        'result'    => 'success',
                        'redirect'  => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
                    );
                }
            }


            function get_pages($title = false, $indent = true) {

                $wp_pages = get_pages('sort_column=menu_order');
                $page_list = array();
                if ($title) $page_list[] = $title;
                foreach ($wp_pages as $page) {
                    $prefix = '';
                    // show indented child pages?
                    if ($indent) {
                        $has_parent = $page->post_parent;
                        while($has_parent) {
                            $prefix .=  ' - ';
                            $next_page = get_page($has_parent);
                            $has_parent = $next_page->post_parent;
                        }
                    }

                    // add to page list array array
                    $page_list[$page->ID] = $prefix . $page->post_title;
                }
                return $page_list;
            }


            /**
             * @param $order_id
             */

            public function receipt_page($order_id)
            {
                global $woocommerce;
                $order = new WC_Order($order_id);
                $descripcionParts = array();
                
                foreach ($order->get_items() as $product) {
                    $clearData = str_replace('_', ' ', $this->string_sanitize($product['name']));
                    $descripcionParts[] = $clearData;
                }
                
                $descripcion = implode(' - ', $descripcionParts);
                $currency = strtolower(get_woocommerce_currency());
                $testMode = $this->epayco_agregador_testmode == "yes" ? "true" : "false";
                $basedCountry = WC()->countries->get_base_country();
                $external=$this->epayco_agregador_type_checkout;
                $redirect_url =get_site_url() . "/";
                $confirm_url=get_site_url() . "/";
                $redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );
                $redirect_url = add_query_arg( 'order_id', $order_id, $redirect_url );
                
                if ($this->get_option('epayco_agregador_url_confirmation' ) == 0) {
                        $confirm_url = add_query_arg( 'wc-api', get_class( $this ), $confirm_url );
                        $confirm_url = add_query_arg( 'order_id', $order_id, $confirm_url );
                        $confirm_url = $redirect_url.'&confirmation=1';
                    } else {
                        
                        $confirm_url = get_permalink($this->get_option('epayco_agregador_url_confirmation'));
                }
                
                $name_billing=$order->get_billing_first_name().' '.$order->get_billing_last_name();
                $address_billing=$order->get_billing_address_1();
                $phone_billing=@$order->billing_phone;
                $email_billing=@$order->billing_email;
                $order = new WC_Order($order_id);
                $tax=$order->get_total_tax();
                $tax=round($tax,2);
                
                if((int)$tax>0){
                    $base_tax=$order->get_total()-$tax;
                }else{
                    $base_tax=$order->get_total();
                    $tax=0;
                }
                
                $items = $woocommerce->cart->get_cart();
                 
                foreach($items as $item => $value) {
                    $_product =  wc_get_product( $value['data']->get_id());
                    $price = get_post_meta($value['product_id'] , '_price', true);
                    
                    if($value['line_subtotal_tax']>0){
                        $regulart_product_tax=$value['line_subtotal_tax'];
                    }else{
                         $regulart_product_tax=0;
                    }

                    $aomunt1=$price*$value['quantity'];
                    $total=floatval($aomunt1+$regulart_product_tax);
                    $product_ = $_product->get_data();
                    $productName = json_decode(json_encode((object)$product_), FALSE);
                }

                $product_price = $productName->price;
                $product_id = $productName->id;
                $product_sku = $productName->sku;
      
                //Busca si ya se restauro el stock
        
                if (!EpaycoAgregadorOrder::ifExist($order_id)) {
                    //si no se restauro el stock restaurarlo inmediatamente
                    EpaycoAgregadorOrder::create($order_id,1);
                }
            

               

                if ($this->epayco_agregador_lang !== "es") {

                    $msgEpaycoCheckout = '<span class="animated-points">Loading payment methods</span>
                               <br><small class="epayco-subtitle"> If they do not load automatically, click on the "Pay with ePayco" button</small>';

                    $epaycoButtonImage = plugin_dir_url(__FILE__).'lib/Boton-color-Ingles.png';

                }else{
                    $msgEpaycoCheckout = '<span class="animated-points">Cargando métodos de pago</span>
                    <br><small class="epayco-subtitle"> Si no se cargan automáticamente, de clic en el botón "Pagar con ePayco</small>';
                    $epaycoButtonImage = plugin_dir_url(__FILE__).'lib/Boton-color-espanol.png';
                }

                echo('
                    <style>
                        .epayco-title{
                            max-width: 900px;
                            display: block;
                            margin:auto;
                            color: #444;
                            font-weight: 700;
                            margin-bottom: 25px;
                        }
                        .loader-container{
                            position: relative;
                            padding: 20px;
                            color: #ff5700;
                        }
                        .epayco-subtitle{
                            font-size: 14px;
                        }
                        .epayco-button-render{
                            transition: all 500ms cubic-bezier(0.000, 0.445, 0.150, 1.025);
                            transform: scale(1.1);
                            box-shadow: 0 0 4px rgba(0,0,0,0);
                        }
                        .epayco-button-render:hover {
                            /*box-shadow: 0 0 4px rgba(0,0,0,.5);*
                            transform: scale(1.2);
                        }

                        .animated-points::after{
                            content: "";
                            animation-duration: 2s;
                            animation-fill-mode: forwards;
                            animation-iteration-count: infinite;
                            animation-name: animatedPoints;
                            animation-timing-function: linear;
                            position: absolute;
                        }
                        .animated-background {
                            animation-duration: 2s;
                            animation-fill-mode: forwards;
                            animation-iteration-count: infinite;
                            animation-name: placeHolderShimmer;
                            animation-timing-function: linear;
                            color: #f6f7f8;
                            background: linear-gradient(to right, #7b7b7b 8%, #999 18%, #7b7b7b 33%);
                            background-size: 800px 104px;
                            position: relative;
                            background-clip: text;
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                        }
                        .loading::before{
                            -webkit-background-clip: padding-box;
                            background-clip: padding-box;
                            box-sizing: border-box;
                            border-width: 2px;
                            border-color: currentColor currentColor currentColor transparent;
                            position: absolute;
                            margin: auto;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            content: " ";
                            display: inline-block;
                            background: center center no-repeat;
                            background-size: cover;
                            border-radius: 50%;
                            border-style: solid;
                            width: 30px;
                            height: 30px;
                            opacity: 1;
                            -webkit-animation: loaderAnimation 1s infinite linear,fadeIn 0.5s ease-in-out;
                            -moz-animation: loaderAnimation 1s infinite linear, fadeIn 0.5s ease-in-out;
                            animation: loaderAnimation 1s infinite linear, fadeIn 0.5s ease-in-out;
                        }
                        @keyframes animatedPoints{
                            33%{
                                content: "."
                            }

                            66%{
                                content: ".."
                            }

                            100%{
                                content: "..."
                            }
                        }

                        @keyframes placeHolderShimmer{
                            0%{
                                background-position: -800px 0
                            }
                            100%{
                                background-position: 800px 0
                            }
                        }
                        @keyframes loaderAnimation{
                            0%{
                                -webkit-transform:rotate(0);
                                transform:rotate(0);
                                animation-timing-function:cubic-bezier(.55,.055,.675,.19)
                            }

                            50%{
                                -webkit-transform:rotate(180deg);
                                transform:rotate(180deg);
                                animation-timing-function:cubic-bezier(.215,.61,.355,1)
                            }
                            100%{
                                -webkit-transform:rotate(360deg);
                                transform:rotate(360deg)
                            }
                        }
                    </style>
                    ');

                echo sprintf('
                        <div class="loader-container">
                            <div class="loading"></div>
                        </div>
                        <p style="text-align: center;" class="epayco-title">
                           '.$msgEpaycoCheckout.'
                        </p>                        
                        <script type="text/javascript" src="https://checkout.epayco.co/checkout.js">   </script>
                        <center>
                        <a href="#" onclick="return theFunction();">
                            <img src="'.$epaycoButtonImage.'" />
                        </a>
                        <script type="text/javascript">
                        var handler = ePayco.checkout.configure({
                                        key: "%s",
                                        test: "%s"
                                    })
                        var data={
                                  name: "%s",
                                  description: "%s",
                                  invoice: "%s",
                                  currency: "%s",
                                  amount: "%s",
                                  tax: "%s",
                                  tax_base: "%s",
                                  country: "%s",
                                  external: "%s",
                                  response: "%s",
                                  confirmation: "%s",
                                  email_billing: "%s",
                                  name_billing: "%s",
                                  address_billing: "%s",
                                  lang: "%s",
                                  mobilephone_billing: "%s",
                                  extra1: "WooCommerce",
                                  }
                                  handler.open(data)
                            function theFunction () {
                                    handler.open(data)
                            }
                        </script>
                        </center>
                ',trim($this->epayco_agregador_publickey),$testMode,$descripcion, $descripcion, $order->get_id(),$currency,$order->get_total(),$tax,$base_tax,$basedCountry, $external, $redirect_url,$confirm_url,$email_billing,$name_billing,$address_billing,$this->epayco_agregador_lang,$phone_billing);

                $messageload = __('Espere por favor..Cargando checkout.','payco-woocommerce');
                $js = "if(jQuery('button.epayco-button-render').length)    
                {
                jQuery('button.epayco-button-render').css('margin','auto');
                jQuery('button.epayco-button-render').css('display','block');
                }";

                if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')){
                    wc_enqueue_js($js);
                }else{
                    $woocommerce->add_inline_js($js);
                }
            }


            public function datareturnepayco_ajax()
            {
                die();
            }


            public function block($message)
            {
                return 'jQuery("body").block({
                        message: "' . esc_js($message) . '",
                        baseZ: 99999,
                        overlayCSS:
                        {
                            background: "#000",
                            opacity: "0.6",
                        },

                        css: {
                            padding:        "20px",
                            zindex:         "9999999",
                            textAlign:      "center",
                            color:          "#555",
                            border:         "1px solid #aaa",
                            backgroundColor:"#fff",
                            cursor:         "wait",
                            lineHeight:     "24px",
                        }
                    });';
            }

            public function authSignature($x_ref_payco,$x_transaction_id,$x_amount, $x_currency_code){
                    $signature = hash('sha256',
                                trim($this->epayco_agregador_customerid).'^'
                                .trim($this->epayco_agregador_secretkey).'^'
                                .$x_ref_payco.'^'
                                .$x_transaction_id.'^'
                                .$x_amount.'^'
                                .$x_currency_code
                            );
                return $signature;
            }

            function check_ePayco_agregador_response(){
                @ob_clean();
                if ( ! empty( $_REQUEST ) ) {
                    header( 'HTTP/1.1 200 OK' );
                    do_action( "ePayco_agregador_init", $_REQUEST );
                } else {
                    wp_die( __("ePayco Request Failure", 'epayco-agregador-woocommerce') );
                }
            }


            /**
             * @param $validationData
             */
            function ePayco_agregador_successful_request($validationData)
            {       
                    global $woocommerce;
                    $order_id="";
                    $ref_payco="";
                    $signature="";
                    $x_signature_p= wp_kses_post($_REQUEST['x_signature']);
                    $x_signature_e=esc_html($_REQUEST['x_signature']);
                    
                    if($x_signature_p || $x_signature_e ){
                       $order_id = trim(sanitize_text_field($_GET['order_id']));
                       $x_ref_payco = trim(sanitize_text_field($_REQUEST['x_ref_payco']));
                       $x_transaction_id = trim(sanitize_text_field($_REQUEST['x_transaction_id']));
                       $x_amount = trim(sanitize_text_field($_REQUEST['x_amount']));
                       $x_currency_code = trim(sanitize_text_field($_REQUEST['x_currency_code']));
                       $x_signature = trim(sanitize_text_field($_REQUEST['x_signature']));
                       $x_cod_transaction_state=(int)trim(sanitize_text_field($_REQUEST['x_cod_response']));

                        if ($order_id!="" && $x_ref_payco!="") {
                            $authSignature=$this->authSignature($x_ref_payco,$x_transaction_id,$x_amount, $x_currency_code);
                            $order = new WC_Order($order_id);
                        }
                       
                    }else{
                        
                        
                        $order_id_info = sanitize_text_field($_GET['order_id']);
                        $order_id_explode = explode('=',$order_id_info);
                        $order_id_rpl  = str_replace('?ref_payco','',$order_id_explode);
                        $order_id = $order_id_rpl[0];
                        $ref_payco = sanitize_text_field($_GET['ref_payco']);
                        $isConfirmation = sanitize_text_field($_GET['confirmation']) == 1;
                        if(empty($ref_payco)){
                            $ref_payco =$order_id_rpl[1];
                        }
                      
                        if (!$ref_payco) {
                            $explode=explode('=',$order_id);
                            $ref_payco=$explode[1];
                            $explode2 = explode('?', $order_id );
                            $order_id=$explode2[0];
                        }
                        
                        
                        $url = 'https://secure.epayco.co/validation/v1/reference/'.$ref_payco;
                        $response = wp_remote_get(  $url );
                        $body = wp_remote_retrieve_body( $response ); 
                        $jsonData = @json_decode($body, true);
                        $validationData = $jsonData['data'];
                        $x_signature=trim($validationData['x_signature']);
                        $x_cod_transaction_state=(int)trim($validationData['x_cod_response']);
                        $x_ref_payco = trim($validationData['x_ref_payco']);
                        $x_transaction_id = trim($validationData['x_transaction_id']);
                        $x_amount = trim($validationData['x_amount']);
                        $x_currency_code = trim($validationData['x_currency_code']);
                        
                        //Validamos la firma
                    if ($order_id!="" && $x_ref_payco!="") {
                        $authSignature=$this->authSignature($x_ref_payco,$x_transaction_id,$x_amount, $x_currency_code);
                        $order = new WC_Order($order_id);
                    }
                        
                }
                
                    if (!$x_ref_payco) {
                        $order = new WC_Order($order_id);
                        $message = 'Pago rechazado';
                        $messageClass = 'woocommerce-error';
                        $order->update_status('epayco-on-hold');
                        $order->add_order_note('Pago pendiente');
                        
                        if ($this->get_option('epayco_agregador_url_response' ) == 0) {
                            $redirect_url = $order->get_checkout_order_received_url();
                        }else{
                            $woocommerce->cart->empty_cart();
                            $redirect_url = get_permalink($this->get_option('epayco_agregador_url_response'));
                        }

                        wp_redirect($redirect_url);
                        die();
                    }


                        $message = '';
                        $messageClass = '';
                        $current_state = $order->get_status();
                        
                        if($authSignature == $x_signature){                
                        switch ($x_cod_transaction_state) {
                            case 1:{
                                //Busca si ya se descontó el stock
                           
                                if (!EpaycoAgregadorOrder::ifStockDiscount($order_id)) {
                                    //se descuenta el stock
                                    EpaycoAgregadorOrder::updateStockDiscount($order_id,1);
                                }      
                          
                                $message = 'Pago exitoso';
                                $messageClass = 'woocommerce-message';
                                $order->payment_complete($x_ref_payco);
                                $order->update_status($this->epayco_agregador_endorder_state);
                                $order->add_order_note('Pago exitoso');
                                echo "1";
                                }break;

                            case 2: {
                               
                                if($current_state =="epayco-failed" || 
                                $current_state =="epayco-cancelled" || 
                                $current_state =="failed" || 
                                $current_state == "epayco-processing" || 
                                $current_state == "epayco-completed" || 
                                $current_state == "processing" || 
                                $current_state == "completed" 
                            
                                ){
                                $order->update_status('epayco-cancelled');
                                $order->add_order_note('Pago fallido');
                                
                                }else{
                                $message = 'Pago rechazado' .$x_ref_payco;
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-cancelled');
                                $order->add_order_note('Pago fallido');
                                $this->restore_order_stock($order->id);
                                        
                                
                                }
                                echo "2";
                            }break;
                            case 3:{
                            //Busca si ya se restauro el stock y si se configuro reducir el stock en transacciones pendientes  
            
                             if (!EpaycoAgregadorOrder::ifStockDiscount($order_id) && $this->get_option('epayco_agregador_reduce_stock_pending') == 'yes') {
                                //reducir el stock   
                                EpaycoAgregadorOrder::updateStockDiscount($order_id,1);
                                }
                                $message = 'Pago pendiente de aprobación';
                                $messageClass = 'woocommerce-info';
                                $order->update_status('epayco-on-hold');
                                $order->add_order_note('Pago pendiente');
                                echo "3";
                            }break;
                            case 4:{
                              
                                if($current_state == "epayco-processing" || 
                                $current_state == "epayco-completed" || 
                                $current_state =="epayco-cancelled" ||
                                $current_state == "epayco-failed" ||
                                $current_state == "processing" || 
                                $current_state =="failed" ||
                                $current_state == "completed"){
                                $order->update_status('epayco-failed');
                                $order->add_order_note('Pago fallido');
                                }else{
                                $message = 'Pago fallido' .$x_ref_payco;
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-failed');
                                $order->add_order_note('Pago fallido');
                                $this->restore_order_stock($order->id);
                                }
                                echo "4";
                            }break;
                            case 6:{
                                $message = 'Pago Reversada' .$x_ref_payco;
                                $messageClass = 'woocommerce-error';
                                $order->update_status('refunded');
                                $order->add_order_note('Pago Reversado');
                                $this->restore_order_stock($order->id);
                                echo "6";
                            }break;
                            case 10: {
                               
                                if($current_state =="epayco-failed" || 
                                $current_state =="epayco-cancelled" || 
                                $current_state =="failed" || 
                                $current_state == "epayco-processing" || 
                                $current_state == "epayco-completed" || 
                                $current_state == "processing" || 
                                $current_state == "completed" 
                            
                                ){
                                $order->update_status('epayco-cancelled');
                                $order->add_order_note('Pago fallido');
                                }else{
                                $message = 'Pago rechazado' .$x_ref_payco;
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-failed');
                                $order->add_order_note('Pago fallido o Abandonado');
                                $this->restore_order_stock($order->id);
                                }
                                echo "2";
                            }break;
                            case 11: {
                               
                                if($current_state =="epayco-failed" || 
                                $current_state =="epayco-cancelled" || 
                                $current_state =="failed" || 
                                $current_state == "epayco-processing" || 
                                $current_state == "epayco-completed" || 
                                $current_state == "processing" || 
                                $current_state == "completed" 
                            
                                ){
                                $order->update_status('epayco-cancelled');
                                $order->add_order_note('Pago fallido');
                                }else{
                                $message = 'Pago rechazado' .$x_ref_payco;
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-failed');
                                $order->add_order_note('Pago Abandonado');
                                $this->restore_order_stock($order->id);
                                }
                                echo "2";
                            }break;
                            default:{
                                if(
                                    $current_state == "epayco-processing" || 
                                    $current_state == "epayco-completed" || 
                                    $current_state == "processing" || 
                                    $current_state == "completed"){

                                    }else{
                                    $message = 'Pago '.$_REQUEST['x_transaction_state'] . $x_ref_payco;
                                    $messageClass = 'woocommerce-error';
                                    $order->update_status('epayco-failed');
                                    $order->add_order_note('Pago fallido o abandonado');
                                    $this->restore_order_stock($order->id);
                                    }
                                    echo "default";
                            }break;

                        }

                    //validar si la transaccion esta pendiente y pasa a rechazada y ya habia descontado el stock
                    if($current_state == 'on-hold' && ((int)$x_cod_transaction_state == 2 || (int)$x_cod_transaction_state == 4) && EpaycoAgregadorOrder::ifStockDiscount($order_id)){
                        //si no se restauro el stock restaurarlo inmediatamente
                         $this->restore_order_stock($order_id);
                    };

                    }else {
                        $message = 'Firma no valida';
                        $messageClass = 'error';
                    }
                    
                    if (isset($_REQUEST['confirmation'])) {
                        $redirect_url = get_permalink($this->get_option('epayco_agregador_url_confirmation'));
                        if ($this->get_option('epayco_url_confirmation' ) == 0) {
                            echo "ok";
                            die();
                        }
                    }else{
                        if ($this->get_option('epayco_agregador_url_response' ) == 0) {
                            $redirect_url = $order->get_checkout_order_received_url();
                        }else{
                            $woocommerce->cart->empty_cart();
                            $redirect_url = get_permalink($this->get_option('epayco_agregador_url_response'));
                        }
                    }

                    $arguments=array();

                    foreach ($validationData as $key => $value) {
                        $arguments[$key]=$value;
                    }

                    unset($arguments["wc-api"]);
                    $arguments['msg']=urlencode($message);
                    $arguments['type']=$messageClass;

                    $response_data = $this->response_data == "yes" ? true : false;
                if ($response_data) {
                    $redirect_url = add_query_arg($arguments , $redirect_url );
                }
                    wp_redirect($redirect_url);
                    die();
                }


            /**
             * @param $order_id
             */
            public function restore_order_stock($order_id,$operation = 'increase')
            {
                //$order = new WC_Order($order_id);
                 $order = wc_get_order($order_id);
                if (!get_option('woocommerce_manage_stock') == 'yes' && !sizeof($order->get_items()) > 0) {
                    return;
                }

                foreach ($order->get_items() as $item) {
                    // Get an instance of corresponding the WC_Product object
                    $product = $item->get_product();
                    $qty = $item->get_quantity(); // Get the item quantity
                    wc_update_product_stock($product, $qty, $operation);
                }

            }


            public function string_sanitize($string, $force_lowercase = true, $anal = false) {

                $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]","}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;","â€”", "â€“", ",", "<", ".", ">", "/", "?");
                $clean = trim(str_replace($strip, "", strip_tags($string)));
                $clean = preg_replace('/\s+/', "_", $clean);
                $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
                return $clean;
            }


            public function getTaxesOrder($order){
                $taxes=($order->get_taxes());
                $tax=0;
                foreach($taxes as $tax){
                    $itemtax=$tax['item_meta']['tax_amount'][0];
                }
                return $itemtax;
            }
        }


            function is_product_in_cart_agregador( $prodids ){
             $product_in_cart = false;
             foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
             $product = $cart_item['data'];
                 if ( in_array( $product->id, $prodids ) ) {
                      $product_in_cart = true;
             }
                        
             }
             return $product_in_cart;
        }
// Luego ya desactivamos la pasarela que queramos por ID de producto. Cambia los números de ID en el array $prodids
// function payment_gateway_disable_product( $available_gateways ) {
//  global $woocommerce;
//  $items = $woocommerce->cart->get_cart();
//  $suma=0;
//  foreach($items as $item => $value) {
//                 $_product =  wc_get_product( $value['data']->get_id());
//                 $price = get_post_meta($value['product_id'] , '_price', true);
//                    if($value['line_subtotal_tax']>0){
//                     $regulart_product_tax=$value['line_subtotal_tax'];
//                 }else{
//                      $regulart_product_tax=0;
//                 }
//                 $aomunt1=$price*$value['quantity'];
//                 $total=floatval($aomunt1+$regulart_product_tax);
//                 $product_ = $_product->get_data();
//                 $productName = json_decode(json_encode((object)$product_), FALSE);
//                 $suma += $total;
//                 }
//                 $product_price = $productName->price;
//                 $product_id = $productName->id;
//                 $epayco = new WC_ePayco();
//                 $monto = (int)$epayco->max_monto;
               
//                 if($suma>=$monto){
//                     $id_ = $product_id;
//                 }else{
//                     $id_ = null;
//                 }
              
//                  $prodids=array($id_);
//                  if ( isset( $available_gateways['epayco'] ) && is_product_in_cart( $prodids ) ) {
//                      unset(  $available_gateways['epayco'] );
//                  }
//                      return $available_gateways;
//                  }
//                 add_filter('woocommerce_available_payment_gateways','payment_gateway_disable_product' );


        /**
         * @param $methods
         * @return array
         */
        function woocommerce_epayco_agregador_add_gateway($methods)
        {
            $methods[] = 'WC_ePayco_agregador';
            return $methods;
        }
        add_filter('woocommerce_payment_gateways', 'woocommerce_epayco_agregador_add_gateway');

        function epayco_agregador_woocommerce_addon_settings_link( $links ) {
            array_push( $links, '<a href="admin.php?page=wc-settings&tab=checkout&section=epayco_agregador">' . __( 'Configuración' ) . '</a>' );
            return $links;
        }

        add_filter( "plugin_action_links_".plugin_basename( __FILE__ ),'epayco_agregador_woocommerce_addon_settings_link' );
        }


    //Actualización de versión
    global $epayco_agregador_db_version;
    $epayco_agregador_db_version = '1.0';
    //Verificar si la version de la base de datos esta actualizada 

    function epayco_agregador_update_db_check()
    {
        global $epayco_agregador_db_version;
        $installed_ver = get_option('epayco_agregador_db_version');
        if ($installed_ver == null || $installed_ver != $epayco_agregador_db_version) {
            EpaycoAgregadorOrder::setup();
            update_option('epayco_agregador_db_version', $epayco_agregador_db_version);
        }
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

        register_post_status( 'wc-epayco-cancelled', array(
            'label'                     => 'ePayco Pago Cancelado',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Cancelado <span class="count">(%s)</span>', 'ePayco Pago Cancelado <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-on-hold', array(
            'label'                     => 'ePayco Pago Pendiente',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Pendiente <span class="count">(%s)</span>', 'ePayco Pago Pendiente <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-processing', array(
            'label'                     => 'ePayco Procesando Pago',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Procesando Pago <span class="count">(%s)</span>', 'ePayco Procesando Pago <span class="count">(%s)</span>' )
        ));

         register_post_status( 'wc-processing', array(
            'label'                     => 'Procesando',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'Procesando<span class="count">(%s)</span>', 'Procesando<span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-completed', array(
            'label'                     => 'ePayco Pago Completado',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Completado <span class="count">(%s)</span>', 'ePayco Pago Completado <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-completed', array(
            'label'                     => 'Completado',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'Completado<span class="count">(%s)</span>', 'Completado<span class="count">(%s)</span>' )
        ));
    }

    add_action( 'plugins_loaded', 'register_epayco_agregador_order_status' );

    function add_epayco_agregador_to_order_statuses( $order_statuses ) {
        $new_order_statuses = array();
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-cancelled' === $key ) {
                $new_order_statuses['wc-epayco-cancelled'] = 'ePayco Pago Cancelado';
            }

            if ( 'wc-failed' === $key ) {
                $new_order_statuses['wc-epayco-failed'] = 'ePayco Pago Fallido';
            }

            if ( 'wc-on-hold' === $key ) {
                $new_order_statuses['wc-epayco-on-hold'] = 'ePayco Pago Pendiente';
            }

            if ( 'wc-processing' === $key ) {
               $new_order_statuses['wc-epayco-processing'] = 'ePayco Procesando Pago';
            }else {
                $new_order_statuses['wc-processing'] = 'Procesando';
            }

            if ( 'wc-completed' === $key ) {
                $new_order_statuses['wc-epayco-completed'] = 'ePayco Pago Completado';
            }else{
                $new_order_statuses['wc-completed'] = 'Completado';
            }
        }
        return $new_order_statuses;
    }

    add_filter( 'wc_order_statuses', 'add_epayco_agregador_to_order_statuses' );
    add_action('admin_head', 'styling_admin_order_list_epayco' );
    function styling_admin_order_list_epayco() {
        global $pagenow, $post;
        if( $pagenow != 'edit.php') return; // Exit
        if( get_post_type($post->ID) != 'shop_order' ) return; // Exit
        // HERE we set your custom status
        $order_status_failed = 'epayco-failed';
        $order_status_on_hold = 'epayco-on-hold';
        $order_status_processing = 'epayco-processing';
        $order_status_completed = 'epayco-completed';
        $order_status_cancelled = 'epayco-cancelled';
        ?>

        <style>
            .order-status.status-<?php echo sanitize_title( $order_status_failed); ?> {
                background: #eba3a3;
                color: #761919;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_on_hold); ?> {
                background: #f8dda7;
                color: #94660c;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_processing ); ?> {
                background: #c8d7e1;
                color: #2e4453;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_completed ); ?> {
                background: #d7f8a7;
                color: #0c942b;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_cancelled); ?> {
                background: #eba3a3;
                color: #761919;
            }
        </style>

        <?php
    }

}