<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    getPropertiesList.php
 * CREATED AT:  13.10.2020 17:08
 * CREATED BY:  PhpStorm
 */

add_action( 'wp_ajax_getPropertiesList', 'wp_ajax_getPropertiesList' );

/**
 * getPropertiesList
 */
function wp_ajax_getPropertiesList () {
    if(isset($_POST['username'])) {
        update_option( 'username_plugin', $_POST['username']);
    }
    if(isset($_POST['userpassword'])) {
        update_option( 'password_plugin', $_POST['userpassword']);
    }
    if(isset($_POST['username_user_credentials'])) {
        update_option('username_user_credentials', $_POST['username_user_credentials']);
    }
    if(isset($_POST['password_user_credentials'])) {
        update_option('password_user_credentials', $_POST['password_user_credentials']);
    }

    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'RentalsUnited.php';
    $RentalsUnited = new RentalsUnited();

    if ($RentalsUnited->Pull_ListStatuses_RQ() === false) {
        echo json_encode(['message' => 'Incorrect login or password']);
        wp_die();
    }

    //Pull_ListStatuses_RQ
    global $wpdb;
    $properties = get_posts( array('post_type'=>'properties', 'numberposts'=>-1) );
    foreach ($properties as $propertie) {
        wp_delete_post($propertie->ID, true);
    }


    $payload = [];
    $DetailedLocationIDs = [];

    foreach ($RentalsUnited->getPropertiesList(0)->Properties->Property as $simpleXMLElement) {
        try {
            $DetailedLocationID = intval($simpleXMLElement->DetailedLocationID);
            $payload[] = [
                'ID' => intval($simpleXMLElement->ID),
                'PUID' => intval($simpleXMLElement->PUID),
                'DetailedLocationID' => $DetailedLocationID,
                'OwnerID' => intval($simpleXMLElement->OwnerID)
            ];

            $getLocationDetails = $RentalsUnited->getLocationDetails($DetailedLocationID);
            $value = $getLocationDetails->Locations->Location[count($getLocationDetails->Locations->Location) - 1];
            $id = intval($value->attributes()->LocationID);
            if($value) {
                $DetailedLocationIDs[$id] = htmlspecialchars($value);
            }
        } catch(Exception $e) {}
    }

    /* CITIES LIST BEGIN */
    $wpdb->query('CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix . 'ru_locations` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `PropertyID` INT,
        `Title` VARCHAR(255),
        PRIMARY KEY (`id`)
    );');
    $wpdb->query("TRUNCATE TABLE `". $wpdb->prefix . 'ru_locations' . "`");
    foreach ($DetailedLocationIDs as $PropertyID => $Title) {
        $wpdb->insert($wpdb->prefix . 'ru_locations', [
            'PropertyID' => $PropertyID,
            'Title' => $Title
        ]);
    }
    /* CITIES LIST END */
    echo json_encode($payload);
    wp_die();
}