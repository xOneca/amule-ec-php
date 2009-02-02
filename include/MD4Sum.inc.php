<?php

class MD4Sum
{
    var $sHash;
    var $rawhash;
    function __construct($string='')
    {
//         $this->sHash = md5($string);
    }

    function Calculate($string)
    {
//         $this->sHash = md5($string);
    }

    function GetHash()
    {
        return $this->sHash;
    }
}
