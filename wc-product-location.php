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

			add_action( 'woocommerce_product_query', array( $this, 'filter_products_by_location' ) );

			add_action( 'woocommerce_before_shop_loop', array( $this, 'add_product_location_search' ), 11, 2 );
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
			// 'options'     => array(
			// 	'FL' => __( 'Florida', 'wc-product-location' ),
			// 	'LA' => __( 'Louisiana', 'wc-product-location' ),
			// ),
			// 'options'    => WC()->countries->get_countries(),
			'options'    => WC()->countries->get_states( 'US' ),
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
	 * Filter Products by Location
	 *
	 * TO DO:
	 * - Add a second parameter to the method for location so that it can be passed to the 'value' meta in the query
	 * - Figure out how to do the search and retrieve this parameter
	 */
	public function filter_products_by_location( $q ) {
		$location = isset( $_GET['product-location'] ) ? $_GET['product-location'] : '';

		if ( $location !== '' ) {
			$meta_query   = $q->get( 'meta_query' );
			$meta_query[] = array(
				'key'     => '_product_location',
				'value'   => $location,
				'compare' => '=',
			);

			$q->set( 'meta_query', $meta_query );
		}
	}

	public function add_product_location_search() {
		$states = WC()->countries->get_states( 'US' );
		?>
		<form class="woocommerce-ordering-location" method="get">
			<select name="product-location" class="product-location">
				<?php foreach ( $states as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $_GET['product-location'], $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="hidden" name="paged" value="1" />
			<!-- <input type="submit"> -->
			<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page', 'product-location' ) ); ?>
		</form>

		<script>
			jQuery( function( $ ) {
				// Orderby
				$( '.woocommerce-ordering-location' ).on( 'change', 'select.product-location', function() {
					$( this ).closest( 'form' ).submit();
				});
			});
		</script>
<?php }

	/*
	 * Display a notice to show that WooCommerce is required for the plugin to function
	 */
	public function show_woocommerce_required_notice() {
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
