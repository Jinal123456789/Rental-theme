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

function RUB_menu_pages(){
    add_menu_page('Rentals United Booking', 'Rentals United Booking', 'manage_options', 'RUB', 'RUB_main');
}

add_action('admin_menu', 'RUB_menu_pages', 0);

function RUB_main(){
    require_once( RUB_DIR.'/admin/page-main.php' );
}