<?php

/**
 * The plugin bootstrap file
 *
 * @link              http://rentalsunited.com/
 * @since             0.0.1
 * @package           RUB
 *
 * @wordpress-plugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('username_plugin');
delete_option('password_plugin');

$properties = get_posts( array('post_type'=>'properties', 'numberposts'=>-1) );

foreach ($properties as $propertie) {
	wp_delete_post($propertie->ID, true);
}
