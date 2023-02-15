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

require_once( RUB_DIR.'/app/hooks.php' );

add_action('wp_dashboard_setup', 'RUB_widgets');

function RUB_widgets() {
    global $wp_meta_boxes;
    wp_add_dashboard_widget('RUB_widget', 'Rentals United Booking', 'RUB_widgets_list');
}

function RUB_widgets_list() {
    add_filter( 'list_pro', 'RUB_get_List_pro');
    $list_pros = apply_filters( 'list_pro' ,  null );

    if( !empty( $list_pros ) ){
        foreach($list_pros as $list_pro){
            $RUB_props[] = $list_pro->prop_name;
        }
    }
    print('<img src="https://rentalsunited.com/site/assets/files/1080/logo_positive.svg" width="200"/> <br/><br/>');
    if( !empty( $RUB_props ) ){
        echo '<ul>';
            foreach( $RUB_props as $RUB_prop ){
                echo '<li>'.$RUB_prop.'</li>';
            }
        echo '</ul>';
    } else {
        echo '<p>set yours access in rental sunited wordpress plugin</p>';
    };
}
