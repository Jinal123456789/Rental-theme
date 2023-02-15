<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    Booking.php
 * CREATED AT:  13.10.2020 17:19
 * CREATED BY:  PhpStorm
 */

/**
 * Class Booking
 */
class Booking
{
    /**
     * @var Pull_ListAmenities_RS
     */
    public static $Pull_ListAmenities_RS;

    /**
     * @var Pull_ListAmenitiesAvailableForRooms_RS
     */
    public static $Pull_ListAmenitiesAvailableForRooms_RS;

    /**
     * @var int
     */
    public $IDProperty;

    /**
     * @var int
     */
    public $PUIDProperty;

    /**
     * @var int
     */
    public $LocationIDProperty;

    /**
     * @var int
     */
    public $OwnerIDProperty;

    /**
     * @var string
     */
    public $titleProperty;

    /**
     * @var array
     */
    public $arrayLocations;

    /**
     * @var string
     */
    public $FirstName;

    /**
     * @var string
     */
    public $LastName;

    /**
     * @var string
     */
    public $Phone;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $CompanyName;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string
     */
    public $guests;

    /**
     * @var string
     */
    public $street;

    /**
     * @var string
     */
    public $zip;

    /**
     * @var float
     */
    public $Latitude;

    /**
     * @var float
     */
    public $Longitude;

    /**
     * @var array
     */
    public $images;

    /**
     * @var array
     */
    public $check;

    /**
     * @var string
     */
    public $IsArchived;

    /**
     * @var array
     */
    public $amenities;

    /**
     * @var SimpleXMLElement
     */
    private $rentalsUnited;

    /**
     * @var rentalsUnited
     */
    private $RentalObject;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var int
     */
    public $CanSleepMax;

    /**
     * Booking constructor.
     * @param $IDProperty
     * @param $PUID
     * @param $DetailedLocationID
     * @param $OwnerID
     * @param $rentalsUnited
     * @param $RentalObject
     */
    public function __construct($IDProperty, $PUID, $DetailedLocationID, $OwnerID, $rentalsUnited, $RentalObject) {
        $this->rentalsUnited = $rentalsUnited;
        $this->RentalObject = $RentalObject;
        $this->IDProperty = (int) $IDProperty;
        $this->PUIDProperty = (int) $PUID;
        $this->LocationIDProperty = (int) $DetailedLocationID;
        $this->OwnerIDProperty = (int) $OwnerID;
        $this->currency = (string) $rentalsUnited->Property->attributes()->Currency;
        $this->CanSleepMax = (int) $rentalsUnited->Property->CanSleepMax;
        $this->сancellation = (string) $rentalsUnited->CancellationPoliciesText->CancellationPolicyText->text;
        $this->run();
    }

    /**
     * Run parsing
     */
    private function run() {
        $this->parse_amenities();
        $this->parse_property();
        $this->parse_locations();
        $this->parse_owner_details();
    }

    /**
     * Parse amenities
     */
    private function parse_amenities() {
        $amenities = [];
        $payload = [];

        /* Get General Amenities BEGIN */
        if(isset($this->rentalsUnited->Property->Amenities->Amenity)) {
            foreach ($this->rentalsUnited->Property->Amenities->Amenity as $amenity) {
                $value = self::$Pull_ListAmenities_RS->get((int)$amenity);
                if (isset($amenity->attributes()->Count)) {
                    if ($amenity->attributes()->Count > 1) {
                        $amenities[] = (string)$amenity->attributes()->Count . " x " . $value;
                    } else {
                        $amenities[] = $value;
                    }
                } else {
                    $amenities[] = $value;
                }
            }
        }

        if(count($amenities) > 0) {
            $payload[0] = [];
            $payload[0][0] = 'General';
            $payload[0][1] = implode(', ', $amenities);
        }
        /* Get General Amenities END */

        /* Get room Amenities BEGIN */
        if(isset($this->rentalsUnited
                ->Property
                ->CompositionRoomsAmenities
                ->CompositionRoomAmenities)) {
            $composition_exists = [];
            foreach ($this->rentalsUnited
                         ->Property
                         ->CompositionRoomsAmenities
                         ->CompositionRoomAmenities as $CompositionRoomAmenities) {
                $index = count($payload);
                $id = (int)$CompositionRoomAmenities->attributes()->CompositionRoomID;
                $room_info = self::$Pull_ListAmenitiesAvailableForRooms_RS->get($id);
                $amenities = [];
                foreach ($CompositionRoomAmenities->Amenities->Amenity as $amenity) {
                    if (isset($amenity->attributes()->Count)) {
                        if ($amenity->attributes()->Count > 1) {
                            $amenities[] = (string)$amenity->attributes()->Count . " x " .
                                self::$Pull_ListAmenities_RS->get((int)$amenity);
                            // $amenities[] = (string)$amenity->attributes()->Count . " x " .
                            //    $room_info['amenity'][(int)$amenity];
                        } else {
                            $amenities[] = self::$Pull_ListAmenities_RS->get((int)$amenity);
                            // $amenities[] = (string)$room_info['amenity'][(int)$amenity];
                        }
                    } else {
                        $amenities[] = self::$Pull_ListAmenities_RS->get((int)$amenity);
                        // $amenities[] = (string)$room_info['amenity'][(int)$amenity];
                    }
                }
                if (isset($composition_exists[$id])) {
                    $composition_exists[$id]['count']++;
                    $latest_payload = $payload[$composition_exists[$id]['index']];
                    $latest_payload[0] = $composition_exists[$id]['count'] . ' x ' . $room_info['value'];
                    $latest_payload[1] = implode(', ',
                        array_unique(
                            array_merge(
                                explode(', ', $latest_payload[1]),
                                $amenities
                            )
                        )
                    );
                    $payload[$composition_exists[$id]['index']] = $latest_payload;
                } else {
                    $composition_exists[$id] = [
                        'count' => 1,
                        'index' => $index
                    ];
                    $payload[$index] = [];
                    $payload[$index][] = $room_info['value'];
                    $payload[$index][] = implode(', ', $amenities);
                }
            }
        }
        /* Get room Amenities END */

        $this->amenities = $payload;
    }

    /**
     * Parse title & description
     */
    private function parse_property() {
        $this->titleProperty = (string) $this->rentalsUnited->Property->Name;
        if(isset($this->rentalsUnited->Property->Descriptions->Description->Text[0])) {
            $this->description = (string) $this->rentalsUnited->Property->Descriptions->Description->Text[0];
        } else {
            $this->description = "";
        }
        $this->guests = (string) $this->rentalsUnited->Property->StandardGuests;
        $this->street = (string) $this->rentalsUnited->Property->Street;
        $this->zip = (string) $this->rentalsUnited->Property->ZipCode;
        $this->Latitude = (float) $this->rentalsUnited->Property->Coordinates->Latitude;
        $this->Longitude = (float) $this->rentalsUnited->Property->Coordinates->Longitude;
        $this->images = (array) $this->rentalsUnited->Property->Images->Image;
        $this->check = (array) $this->rentalsUnited->Property->CheckInOut;
        $this->IsArchived = (string) $this->rentalsUnited->Property->IsArchived;

        // $price = $this->RentalObject->getRates($this->IDProperty);
        $this->price = 0.0; //isset($price->Prices->Season->Price) ? (float) $price->Prices->Season->Price : 0;
    }

    /**
     * Parse Location Details
     */
    private function parse_locations() {
        $LocationDetails = $this->arrayLocations = $this->RentalObject->getLocationDetails($this->LocationIDProperty);
        if(isset($LocationDetails->Locations->Location)) {
            $this->arrayLocations = $LocationDetails->Locations->Location;
        } else {
            $this->arrayLocations = [];
        }
    }

    private function parse_owner_details() {
        $OwnerDetails = $this->RentalObject->getOwnerDetails($this->OwnerIDProperty);
        $this->FirstName = (string) $this->rentalsUnited->Property->ArrivalInstructions->Landlord;
        $this->LastName = ''; //(string) $OwnerDetails->Owner->SurName;
        $this->Phone = (string) $this->rentalsUnited->Property->ArrivalInstructions->Phone;
        $this->email = (string) $this->rentalsUnited->Property->ArrivalInstructions->Email;
        $this->CompanyName = (string) $OwnerDetails->Owner->CompanyName;
    }
}