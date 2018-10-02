<?php

/**
 * Plugin Name: WC Product Location
 * Plugin URI:
 * Description: Product Locations for WooCommerce
 * Author:      Luminus Alabi
 * Author URI:  https://luminus.alabi.blog
 * Version:     1.0.0
 * Text Domain: wc-product-location
 * Domain Path: /i18n/languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     VedutaNova
 * @author      Luminus Alabi
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @since  		1.0.0
 * @link 		https://luminus.alabi.blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Cheating&#8217; uh?' );
}

/**
 * WooCommerce Product Location Class
 */
class WC_Product_Location {

	public function __construct() {
		/*
		 * Check if WooCommerce is active
		*/
		if ( ! class_exists( 'WooCommerce' ) ) {
			// error_log( 'WooCommerce is NOT active' );
			add_action( 'admin_notices', array( $this, 'show_woocommerce_required_notice' ) );
		} else {
			// error_log( 'WooCommerce is active' );

			// Display the Product Location Field in the General Tab of the Product
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_product_location_field' ), 10, 1 );

			// Save the data in the Product Location field when you publish or update the product
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_location' ) );
		}

	}

	/*
	 * Add Location field to the General Product Data tab
	 */
	public function add_product_location_field() {
		$args = array(
			'id'          => '_product_location',
			'label'       => __( 'Product Location', 'wc-product-location' ),
			'class'       => 'select short',
			'desc_tip'    => true,
			'description' => __( 'Select the Location where this product is available', 'wc-product-location' ),
			'options'     => array(
				'Orlando'   => __( 'Orlando', 'wc-product-location' ),
				'Louisiana' => __( 'Louisiana', 'wc-product-location' ),
			),
		);

		woocommerce_wp_select( $args );
	}

	/*
	 * Save product location data
	 */
	function save_product_location( $post_id ) {
		$product_location = isset( $_POST['_product_location'] ) ? $_POST['_product_location'] : '';
		update_post_meta( $post_id, '_product_location', esc_attr( $product_location ) );
	}

	/*
	 * Display a notice to show that WooCommerce is required for the plugin to function
	 */
	function show_woocommerce_required_notice() {
		$message = sprintf(
			/* translators: 1: URL of WooCommerce plugin */
			__( 'The WooCommerce Product Location feature plugin requires <a href="%1$s">WooCommerce</a> to be installed and active.', 'wc-product-location' ),
			'https://wordpress.org/plugins/woocommerce/'
		);
		printf( '<div class="error"><p>%s</p></div>', $message ); /* WPCS: xss ok. */
	}

}

/*
 * Instantiate the plugin after all plugins are loaded.
 */
function wc_product_locations_loader() {
	new WC_Product_Location();
}

add_action( 'plugins_loaded', 'wc_product_locations_loader' );
