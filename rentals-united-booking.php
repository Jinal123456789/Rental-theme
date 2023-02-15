<?php

/**
 * The plugin bootstrap file
 *
 * @link              http://rentalsunited.com/
 * @since             0.0.3
 * @package           RUB
 *
 * @wordpress-plugin
 * Plugin Name:       Rentals United Booking
 * Plugin URI:        https://developer.rentalsunited.com/#introduction
 * Description:       Rentals United Plugin for Property Managers
 * Version:           0.1.0
 * Author:            Rentals United
 * Author URI:        rentalsunited.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rentals-united-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'RUB_VERSION', '0.0.4' );
define( 'RUB_DIR', dirname(__FILE__) );

function properties() {

	$args = array(
		'labels' => array (
			'name' => __( 'Properties', 'properties' ),
			'singular_name' => __( 'Property Item', 'properties' ),
		),
		'description' => 'Add Property items with their details.',
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies' => array( 'properties' ),
		'public' => true,
		'menu_icon' => 'dashicons-admin-site-alt3',
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'properties', ),
	);

	register_post_type( 'properties', $args );
}

add_action( 'init', 'properties' );

require_once( RUB_DIR.'/admin/panel.php' );