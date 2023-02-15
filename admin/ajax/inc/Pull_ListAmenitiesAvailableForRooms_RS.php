<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    Pull_ListAmenitiesAvailableForRooms_RS.php
 * CREATED AT:  13.10.2020 17:19
 * CREATED BY:  PhpStorm
 */

/**
 * Class Pull_ListAmenitiesAvailableForRooms_RS
 */
class Pull_ListAmenitiesAvailableForRooms_RS
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * Pull_ListAmenitiesAvailableForRooms_RS constructor.
     * @param $node
     */
    public function __construct($node)
    {
        foreach($node->AmenitiesAvailableForRooms->AmenitiesAvailableForRoom as $item) {
            $ID = (int) $item->attributes()->CompositionRoomID;
            $VALUE = (string) $item->attributes()->CompositionRoom;
            $this->attributes[$ID] = [
                'value' => $VALUE,
                'amenity' => []
            ];
            foreach ($item->Amenity as $amenity) {
                $aid = (int) $amenity->attributes()->AmenityID;
                $this->attributes[$ID]['amenity'][$aid] = (string) $amenity;
            }
        }
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function get($id)
    {
        return isset($this->attributes[$id]) ? $this->attributes[$id] : null;
    }
}