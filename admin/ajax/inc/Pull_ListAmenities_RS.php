<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    Pull_ListAmenities_RS.php
 * CREATED AT:  13.10.2020 17:18
 * CREATED BY:  PhpStorm
 */

/**
 * Class Pull_ListAmenities_RS
 */
class Pull_ListAmenities_RS
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * Pull_ListAmenities_RS constructor.
     * @param $node
     */
    public function __construct($node)
    {
        foreach($node->Amenities->Amenity as $item) {
            $ID = (int) $item->attributes()->AmenityID;
            $this->attributes[$ID] = (string) $item;
        }
    }

    /**
     * Get amenities
     *
     * @param $id
     * @return mixed|string
     */
    public function get($id)
    {
        return isset($this->attributes[$id]) ? $this->attributes[$id] : "Unknown";
    }
}