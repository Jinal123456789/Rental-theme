<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    getPropertiesBooking.php
 * CREATED AT:  13.10.2020 17:38
 * CREATED BY:  PhpStorm
 */

add_action( 'wp_ajax_getPropertiesBooking', 'wp_ajax_getPropertiesBooking' );

function wp_ajax_getPropertiesBooking() {
    if(!isset($_POST['ID']) || !isset($_POST['PUID']) || !isset($_POST['DetailedLocationID']) || !isset($_POST['OwnerID'])) {
        echo json_encode(array('status' => false));
        wp_die();
    } else {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR .
            'RentalsUnited.php';
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR .
            'Pull_ListAmenities_RS.php';
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR .
            'Pull_ListAmenitiesAvailableForRooms_RS.php';
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR .
            'Booking.php';
        $RentalsUnited = new RentalsUnited();

//        if ($RentalsUnited->Pull_ListStatuses_RQ() === false) {
//            echo json_encode(['status' => false, 'authorized' => false]);
//            wp_die();
//        }

        $Pull_ListAmenities_RS_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pull_ListAmenities_RS.dat';
        $Pull_ListAmenitiesAvailableForRooms_RS_file = dirname(__FILE__) . DIRECTORY_SEPARATOR .
            'Pull_ListAmenitiesAvailableForRooms_RS.dat';
        if (is_file($Pull_ListAmenities_RS_file) &&
            filemtime($Pull_ListAmenities_RS_file) >= time() - 3600) {
            // Load from cache
            Booking::$Pull_ListAmenities_RS = unserialize(file_get_contents($Pull_ListAmenities_RS_file));
        } else {
            Booking::$Pull_ListAmenities_RS = new Pull_ListAmenities_RS(
                $RentalsUnited->getAmenities()
            );
            file_put_contents($Pull_ListAmenities_RS_file, serialize(Booking::$Pull_ListAmenities_RS));
        }
        if (is_file($Pull_ListAmenitiesAvailableForRooms_RS_file) &&
            filemtime($Pull_ListAmenitiesAvailableForRooms_RS_file) >= time() - 3600) {
            // Load from cache
            Booking::$Pull_ListAmenitiesAvailableForRooms_RS = unserialize(
                file_get_contents($Pull_ListAmenitiesAvailableForRooms_RS_file)
            );
        } else {
            Booking::$Pull_ListAmenitiesAvailableForRooms_RS = new Pull_ListAmenitiesAvailableForRooms_RS(
                $RentalsUnited->getRoomAmenities()
            );
            file_put_contents(
                $Pull_ListAmenitiesAvailableForRooms_RS_file,
                serialize(
                    Booking::$Pull_ListAmenitiesAvailableForRooms_RS
                )
            );
        }

        $Booking = new Booking(
            intval($_POST['ID']),
            intval($_POST['PUID']),
            intval($_POST['DetailedLocationID']),
            intval($_POST['OwnerID']),
            $RentalsUnited->getProperty(intval($_POST['ID'])),
            $RentalsUnited
        );
        if ($Booking->IsArchived != 'true') {
            $post_data = array(
                'post_title' => $Booking->titleProperty,
                'post_content' => $Booking->description,
                'post_status' => 'publish',
                'post_type' => 'properties'
            );

            $post_id = wp_insert_post($post_data);
            update_field('id_property', (string) $Booking->IDProperty, $post_id);
            update_field('owner_id', (string) $Booking->OwnerIDProperty, $post_id);
            update_field('locations', (string) end($Booking->arrayLocations), $post_id);
            update_field('first_name_and_last_name', (string) $Booking->FirstName . ' ' . (string) $Booking->LastName, $post_id);
            update_field('phone', (string) $Booking->Phone, $post_id);
            update_field('email', (string) $Booking->email, $post_id);
            update_field('company_name', (string) $Booking->CompanyName, $post_id);
            update_field('price', (string) $Booking->price, $post_id);
            update_field('guests', (string) $Booking->guests, $post_id);
            update_field('street', (string) $Booking->street, $post_id);
            update_field('zip', (string) $Booking->zip, $post_id);
            update_field('latitude', (string) $Booking->Latitude, $post_id);
            update_field('longitude', (string) $Booking->Longitude, $post_id);
            update_field('check_in', (string) $Booking->check["CheckInFrom"] . ' to ' . (string) $Booking->check["CheckInTo"], $post_id);
            update_field('currency', (string) $Booking->currency, $post_id);
            update_field('CanSleepMax', (string) $Booking->CanSleepMax, $post_id);
            update_field('сancellation', (string) $Booking->сancellation, $post_id);

            for ($i = 0; $i <= (count($Booking->amenities) - 1); $i++) {
                $row_key = array(
                    'description' => (string) $Booking->amenities[$i][1],
                    'title_field' => (string) $Booking->amenities[$i][0],
                );

                add_row('amenities', $row_key, $post_id);
            }

            for ($i = 0; $i <= (count($Booking->images) - 2); $i++) {
                $row_keys = array(
                    'image_link' => (string) $Booking->images[$i]
                );

                add_row('gallery', $row_keys, $post_id);
            }
        }
        echo json_encode(array('status' => true));
        wp_die();
    }
}