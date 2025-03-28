<?php
/*
Plugin Name: WooCommerce Zain Payment Gateway
Plugin URI: https://aioneum.com
Description: Custom WooCommerce payment gateway for balance transfer via Zain's "عبّيلهم" service.
Version: 1.0
Author: Aioneum
Author URI: https://aioneum.com
License: GPL2
Text Domain: wc-zain-gateway
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure WooCommerce is loaded before initializing the plugin
add_action('plugins_loaded', 'wc_zain_gateway_init', 11);
function wc_zain_gateway_init() {
    
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }
    
    class WC_Gateway_Zain extends WC_Payment_Gateway {

        public function __construct(){
            $this->id                 = 'zain';
            $this->icon               = ''; // Add an icon URL if needed
            $this->has_fields         = true;
            $this->method_title       = __( 'Pay via Zain', 'wc-zain-gateway' );
            $this->method_description = __( 'Custom payment gateway for balance transfer via Zain\'s عبّيلهم service.', 'wc-zain-gateway' );
            
            // Load settings
            $this->init_form_fields();
            $this->init_settings();

            // Assign settings
            $this->title                = $this->get_option( 'title' );
            $this->description          = $this->get_option( 'description' );
            $this->admin_phone          = $this->get_option( 'admin_phone' );
            $this->payment_method       = $this->get_option( 'payment_method' );
            $this->fixed_categories     = $this->get_option( 'fixed_categories' );
            $this->password_instruction = $this->get_option( 'password_instruction' );

            // Hooks
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        }

        /**
         * Setup admin panel form fields
         */
        public function init_form_fields(){
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __( 'Enable/Disable', 'wc-zain-gateway' ),
                    'type'        => 'checkbox',
                    'label'       => __( 'Enable Zain Payment', 'wc-zain-gateway' ),
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => __( 'Payment Method Title', 'wc-zain-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Title shown to customers at checkout.', 'wc-zain-gateway' ),
                    'default'     => __( 'Pay via Zain', 'wc-zain-gateway' )
                ),
                'description' => array(
                    'title'       => __( 'Payment Method Description', 'wc-zain-gateway' ),
                    'type'        => 'textarea',
                    'description' => __( 'Description shown to customers at checkout.', 'wc-zain-gateway' ),
                    'default'     => __( 'Choose this option to pay via balance transfer through Zain\'s عبّيلهم service.', 'wc-zain-gateway' )
                ),
                'admin_phone' => array(
                    'title'       => __( 'Admin Phone Number', 'wc-zain-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Phone number to receive the balance transfer.', 'wc-zain-gateway' ),
                    'default'     => ''
                ),
                'payment_method' => array(
                    'title'       => __( 'Payment Method', 'wc-zain-gateway' ),
                    'type'        => 'select',
                    'options'     => array(
                        'cart_value'       => __( 'Pay based on cart total', 'wc-zain-gateway' ),
                        'fixed_categories' => __( 'Pay using predefined categories', 'wc-zain-gateway' )
                    ),
                    'default'     => 'cart_value'
                ),
                'fixed_categories' => array(
                    'title'       => __( 'Available Categories', 'wc-zain-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter categories separated by commas (e.g., 5000,10000,25000,50000)', 'wc-zain-gateway' ),
                    'default'     => '5000,10000,25000,50000'
                ),
                'password_instruction' => array(
                    'title'       => __( 'Password Instruction', 'wc-zain-gateway' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions for entering the recharge code.', 'wc-zain-gateway' ),
                    'default'     => __( 'To recharge, enter *123* followed by the recharge amount and the recipient\'s phone number, then press call.', 'wc-zain-gateway' )
                )
            );
        }

        /**
         * Render payment button via shortcode
         */
        public function render_payment_button() {
            ob_start();
            ?>
            <button type="button" id="zain_pay_button">
                <?php _e('Pay via Zain', 'wc-zain-gateway'); ?>
            </button>
            <script type="text/javascript">
                document.getElementById('zain_pay_button').addEventListener('click', function(){
                    var amount = <?php echo WC()->cart->total; ?>;
                    var url = 'tel:*123*' + amount + '*<?php echo $this->admin_phone; ?>#';
                    window.location.href = url;
                });
            </script>
            <?php
            return ob_get_clean();
        }
    }

    /**
     * Add payment gateway to WooCommerce
     */
    function add_zain_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_Zain';
        return $methods;
    }
    add_filter( 'woocommerce_payment_gateways', 'add_zain_gateway_class' );
    
    /**
     * Register shortcode for payment button
     */
    function zain_payment_shortcode() {
        $gateway = new WC_Gateway_Zain();
        return $gateway->render_payment_button();
    }
    add_shortcode('zain_payment_button', 'zain_payment_shortcode');
}
