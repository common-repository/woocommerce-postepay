<?php
/*
Plugin Name:       WooCommerce PostePay
Plugin URI:        https://github.com/PinchOfCode/woocommerce-postepay
Description:       A WooCommerce Extension that adds the payment gateway "PostePay"
Version:           1.1.1
Author:            Pinch Of Code
Author URI:        http://pinchofcode.com
Textdomain:        wc_pp
Domain Path:       /i18n
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
GitHub Plugin URI: https://github.com/PinchOfCode/woocommerce-postepay
*/

/**
 * WooCommerce PostePay
 * Copyright (C) 2013-2014 Pinch Of Code. All rights reserved.
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

/**
 * Start the plugin
 */
function wc_pp_init() {
    global $woocommerce;

    if( !isset( $woocommerce ) ) { return; }

    require_once( 'classes/class.wc-pp.php' );
}
add_action( 'plugins_loaded', 'wc_pp_init' );

/**
 * Add PP in WooCommerce payment gateways
 * @param $methods
 * @return array
 */
function wc_pp_add_postepay( $methods ) {
    $methods[] = 'WC_Gateway_PostePay';
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'wc_pp_add_postepay' );

/**
 * Add "Donate" link in plugins list page
 *
 * @param $links
 * @param $file
 * @return mixed
 */
function wc_pp_add_donate_link( $links, $file ) {
    if( $file == plugin_basename( __FILE__ ) ) {
        //Settings link
        array_unshift( $links, '<a href="' . site_url() . '/wp-admin/admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Gateway_PostePay" title="' . __( 'Settings', 'wc_pp' ) . '">' . __( 'Settings', 'wc_pp' ) . '</a>' );
        //Donate link
        array_unshift( $links, '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@pinchofcode.com&item_name=Donation+for+Pinch+Of+Code" title="' . __( 'Donate', 'wc_pp' ) . '" target="_blank">' . __( 'Donate', 'wc_pp' ) . '</a>' );
    }

    return $links;
}
add_filter( 'plugin_action_links', 'wc_pp_add_donate_link', 10, 4 );
