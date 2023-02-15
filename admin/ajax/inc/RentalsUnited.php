<?php

/**
 * Copyright Gold-Dev.COM 2020
 *
 * FILENAME:    RentalsUnited.php
 * CREATED AT:  13.10.2020 17:08
 * CREATED BY:  PhpStorm
 */

/**
 * Class RentalsUnited
 */
class RentalsUnited
{
    private $server_url = 'http://rm.rentalsunited.com/api/Handler.ashx';
    private $user_name = '';
    private $user_pass = '';

    private $username_user_credentials = '';
    private $password_user_credentials = '';

    /**
     * RentalsUnited constructor.
     */
    public function __construct()
    {
        $this->user_name = get_option('username_plugin');
        $this->user_pass = get_option('password_plugin');

        $this->username_user_credentials = 'personalsaleschannel@rentalsunited.com'; //get_option('username_user_credentials', 'personalsaleschannel@rentalsunited.com');
        $this->password_user_credentials = 'personalsc!'; // get_option('password_user_credentials', 'personalsc!');
    }

    /**
     * Get the details of a single owner, Email, phone number etc..
     *
     * @param int $owner_id
     * @return SimpleXMLElement
     */
    function getOwnerDetails($owner_id){ // user to main
        $post[] = "<Pull_GetOwnerDetails_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
            <OwnerID>$owner_id</OwnerID>
        </Pull_GetOwnerDetails_RQ>";
        return $this->responseFromData('Pull_GetOwnerDetails_RQ_'.$owner_id.'.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages']);
    }

    /**
     * Get a list of all properties in a location
     *
     * @param int $LocationID, Location ID listed in getLocations()
     * @return SimpleXMLElement
     */
    function getPropertiesList($LocationID){
        $post[] = "<Pull_ListOwnerProp_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
            <Username>".$this->user_name."</Username>
            <IncludeNLA>false</IncludeNLA>
        </Pull_ListOwnerProp_RQ>";
        $x = $this->curlPushBack($this->server_url,$post);
        return simplexml_load_string($x['messages']);
    }

    function Pull_ListStatuses_RQ() {
        $post[] = "<Pull_ListStatuses_RQ>
            <Authentication>
                <UserName>".$this->user_name."</UserName>
                <Password>".$this->user_pass."</Password>
            </Authentication>
        </Pull_ListStatuses_RQ>";

        $x = $this->curlPushBack($this->server_url,$post);
        if ($x['messages']) {
            return simplexml_load_string($x['messages']);
        }
        return false;
    }

    /**
     * Get all property details based on a property ID from getPropertiesList()
     *
     * @param mixed $PropertyID, property ID
     * @return SimpleXMLElement
     */

    function getProperty($PropertyID){ // user to main
        $post[] = "<Pull_ListSpecProp_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
            <PropertyID>" . $PropertyID . "</PropertyID>
        </Pull_ListSpecProp_RQ>";
        return $this->responseFromData('Pull_ListSpecProp_RQ'.$PropertyID.'.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages'], null,LIBXML_NOCDATA);
    }

    /**
     * Get the details for the location from getLocations()
     *
     * @param int $LocationID, location ID
     * @return SimpleXMLElement
     */
    function getLocationDetails($LocationID){ // User to main
        $post[] = "<Pull_GetLocationDetails_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
            <LocationID>$LocationID</LocationID>
        </Pull_GetLocationDetails_RQ>";
        return $this->responseFromData('Pull_GetLocationDetails_RQ_' . $LocationID . '.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages']);
    }

    /**
     * Get all amenities available per room
     *
     * @return SimpleXMLElement
     */
    function getRoomAmenities(){ // user to main
        $post[] = "<Pull_ListAmenitiesAvailableForRooms_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
        </Pull_ListAmenitiesAvailableForRooms_RQ>";
        return $this->responseFromData('Pull_ListAmenitiesAvailableForRooms_RQ.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages']);
    }

    /**
     * Get a list of all amenities available
     *
     * @return SimpleXMLElement
     */
    function getAmenities(){ // user to main
        $post[] = "<Pull_ListAmenities_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
        </Pull_ListAmenities_RQ>";
        return $this->responseFromData('Pull_ListAmenities_RQ.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages'], 'SimpleXMLElement',LIBXML_NOCDATA | LIBXML_DTDLOAD | LIBXML_BIGLINES);
    }

    /**
     * Get the prices for a property
     *
     * @param int $PropertyID, property ID
     * @return SimpleXMLElement
     */
    function getRates($PropertyID){ // user to main
        $post[] = "<Pull_ListPropertyPrices_RQ>
            <Authentication>
                <UserName>".$this->username_user_credentials."</UserName>
                <Password>".$this->password_user_credentials."</Password>
            </Authentication>
            <PropertyID>$PropertyID</PropertyID>
            <DateFrom>".date("Y-m-d")."</DateFrom>
            <DateTo>".date("Y-m-d",strtotime("+ 1 year"))."</DateTo>
        </Pull_ListPropertyPrices_RQ>";
        return $this->responseFromData('Pull_ListPropertyPrices_RQ_' . $PropertyID . '.data', $post);
//        $x = $this->curlPushBack($this->server_url,$post);
//        return simplexml_load_string($x['messages']);
    }

    private function responseToData($response, $file) {
        file_put_contents(dirname(__FILE__) . '/cache/' . $file, $response);
    }

    private function responseFromData($file, $post) {
        if (!is_dir(dirname(__FILE__) . '/cache/')) {
            mkdir(dirname(__FILE__) . '/cache/' . $file, 0777, true);
        }
        if (is_file(dirname(__FILE__) . '/cache/' . $file) && filemtime(dirname(__FILE__) . '/cache/' . $file) > time() - 1800) {
            return simplexml_load_string(file_get_contents(dirname(__FILE__) . '/cache/' . $file), 'SimpleXMLElement',LIBXML_NOCDATA | LIBXML_DTDLOAD | LIBXML_BIGLINES);
        }
        else {
            $x = $this->curlPushBack($this->server_url,$post);
            if (isset($x['messages'])) {
                $this->responseToData($x['messages'], $file);
            }
            return simplexml_load_string($x['messages'], 'SimpleXMLElement',LIBXML_NOCDATA | LIBXML_DTDLOAD | LIBXML_BIGLINES);
        }
    }

    /**
     * Default Curl connection
     *
     * @param mixed $url
     * @param mixed $post_fields
     * @param mixed $head
     * @param mixed $follow
     * @param mixed $header
     * @param mixed $referer
     * @param mixed $is_ssl
     * @param mixed $debug
     * @return mixed
     */
    function curlPushBack($url, $post_fields = "", $head = 0, $follow = 1, $header="", $referer="", $is_ssl = false, $debug = 0){
//        file_put_contents(dirname(__FILE__) . '/logs.log', print_r([
//            'date' => date('Y-m-d H:i:s'),
//            'url' => $url,
//            'fields' => $post_fields
//        ], true), FILE_APPEND);
        $ch = curl_init ();
        $header = [];
        $header[]="Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[]="Accept-Language: en-us";
        $header[]="Accept-Charset: SO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[]="Keep-Alive: 300";
        $header[]="Connection: keep-alive";

        curl_setopt ($ch, CURLOPT_HEADER, $head);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, $follow);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt ($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.0; en; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array
        (
            'Content-type: application/x-www-form-urlencoded; charset=utf-8',
            'Set-Cookie: ASP.NET_SessionId='.uniqid().'; path: /; HttpOnly'
        ));
        curl_setopt($ch, CURLOPT_REFERER,$referer);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $is_ssl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $is_ssl);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

        if ($post_fields != ""){
            if(is_array($post_fields)){
                $post_fields = implode("&",$post_fields);
            }
            curl_setopt ($ch, CURLOPT_POST,1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS,$post_fields);
        }

        $result=curl_exec($ch);
        $err=curl_error($ch);

        $results["messages"] = $result;
        $results["errors"] = $err;
        curl_close($ch);

        if($result == '<error ID="-4">Incorrect login or password</error>'){
            return [];
        }

        return $results;
    }
}