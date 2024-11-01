<?php
/**
 * WooCommerce Cash On Pickup
 * Copyright (C) 2013 Nicola Mustone. All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Contact the author at mustone.nicola@gmail.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists( 'WC_Gateway_PostePay' ) ):

/**
 * Main plugin class
 *
 * @author Nicola Mustone
 * @usedby WC_Gateway_PostePay
 */
class WC_Gateway_PostePay extends WC_Payment_Gateway {

    /**
     * Init languages files and gateway settigns
     */
    public function __construct() {
        load_plugin_textdomain( 'wc_pp', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/i18n/' );

        $this->id               = 'pp';
        $this->icon             = apply_filters('woocommerce_pp_icon', '');
        $this->has_fields       = false;
        $this->method_title     = __( 'PostePay', 'wc_pp' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        // Get settings
        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->cardholder_name    = $this->get_option( 'cardholder_name' );
        $this->cardholder_ssn     = $this->get_option( 'cardholder_ssn' );
        $this->card_number        = $this->get_option( 'card_number' );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_pp', array( $this, 'thankyou' ) );
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     *
     * @access public
     * @return void
     */
    function admin_options() {
        ?>
        <h3><?php _e('PostePay','wc_pp'); ?></h3>
        <p><?php _e('Have your customers pay charging your PostePay.', 'wc_pp' ); ?></p>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
    <?php
    }

    /**
     * Create form fields for the payment gateway
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'wc_pp' ),
                'type' => 'checkbox',
                'label' => __( 'Enable PostePay', 'wc_pp' ),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __( 'Title', 'wc_pp' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout', 'wc_pp' ),
                'default' => __( 'Charge PostePay', 'wc_pp' ),
                'desc_tip'      => true,
            ),
            'description' => array(
                'title' => __( 'Customer Message', 'wc_pp' ),
                'type' => 'textarea',
                'default' => __( 'Pay your order charging our PostePay.', 'wc_pp' )
            ),
            'cardholder_name' => array(
                'title' => __( 'Cardholder\'s Name', 'wc_pp' ),
                'type' => 'text',
                'default' => __( 'John Doe', 'wc_pp' ),
            ),
            'cardholder_ssn' => array(
                'title' => __( 'Cardholder\'s SSN', 'wc_pp' ),
                'type' => 'text',
                'description' => __( 'Your SSN may be needed to charge the card.', 'wc_pp' ),
                'desc_tip'      => true,
            ),
            'card_number' => array(
                'title' => __( 'Card number', 'wc_pp' ),
                'type' => 'text',
                'description' => __( 'Write the complete card number without spaces or any other sign.', 'wc_pp' ),
                'default' => __( '4023000000000000', 'wc_pp' ),
                'desc_tip'      => true,
            ),
        );
    }

    /**
     * Process the order payment status
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = new WC_Order( $order_id );

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status( 'on-hold', __( 'Awaiting PostePay charge', 'wc_pp' ) );

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url( $order )
        );
    }

    /**
     * Output for the order received page.
     *
     * @return void
     */
    public function thankyou() {
        if ( $description = $this->get_description() )
            echo wpautop( wptexturize( wp_kses_post( $description ) ) );

        echo '<h2>' . __( 'Our Details', 'wc_pp' ) . '</h2>';

        echo '<ul class="order_details bacs_details">';

        $fields = apply_filters( 'woocommerce_bacs_fields', array(
            'cardholder_name'  => __( 'Cardholder\'s Name', 'wc_pp' ),
            'cardholder_ssn'   => __( 'Cardholder\'s SSN', 'wc_pp' ),
            'card_number'      => __( 'Card number', 'wc_pp' ),
        ) );

        foreach ( $fields as $key => $value ) {
            if ( ! empty( $this->$key ) ) {
                echo '<li class="' . esc_attr( $key ) . '">' . esc_attr( $value ) . ': <strong>' . wptexturize( $this->$key ) . '</strong></li>';
            }
        }

        echo '</ul>';
    }
}
endif;