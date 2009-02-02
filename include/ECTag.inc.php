<?php
/*
 * Copyright (C) 2009  Xabier Oneca <xoneca+amule-ec-php@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/// Purpose: EC packet TAGs handler class


class EC_IPv4
{
    var $ip = array(0,0,0,0);
    var $port = 0;

    function __construct($ip=0, $port=0)
    {
        $this->ip[0] = $ip & 0xff;
        $this->ip[1] = ($ip >> 8) & 0xff;
        $this->ip[2] = ($ip >> 16) & 0xff;
        $this->ip[3] = ($ip >> 24) & 0xff;
        $this->port = $port;
    }

    function IP()
    {
        return $this->ip[0] | ($this->ip[1] << 8) | ($this->ip[2] << 16) | ($this->ip[3] << 24);
    }

    function StringIPSTL($brackets=true)
    {
        $string_ip = ($brackets) ? '[' : '';
        $string_ip .= intval($this->ip[0]) . '.' . intval($this->ip[1]) . '.' . intval($this->ip[2]) . '.' . intval($this->ip[3]) . ':' . intval($this->port);
        $string_ip .= ($brackets) ? ']' : '';
        return $string_ip;
    }
}

/**
 * Null-valued CECTag.
 */
class CECTag
{
    var $error = 0;
    var $tagData = null;
    var $tagName = 0; // ec_tagname_t (uint16_t)
    var $dataLen = 0; // maybe not necessary
    var $dataType = EC_TAGTYPE_UNKNOWN;
    var $tagList = array();
    var $haschildren = false;

    function __construct($name, &$data=null)
    {
        $this->name = $name;
        $this->data =& $data; // Is reference needed?

        if($data === null){
            $this->dataType = EC_TAGTYPE_UNKNOWN;
        }
        elseif(is_object($data)){
            if(get_class($data) == 'EC_IPv4'){
                $this->dataType = EC_TAGTYPE_IPV4;
                $this->dataLen = 6; /// NOTE: I really don't know the length (probably don't needed)
            }elseif(get_class($data) == 'MD4Hash'){
                $this->dataType = EC_TAGTYPE_HASH16
                $this->dataLen = 16;
            }
        }
        elseif(is_float($data)){
            $this->dataType = EC_TAGTYPE_DOUBLE;
        }
        elseif(is_int($data)){
            if($data <= 0xff){
                $this->dataType = EC_TAGTYPE_UINT8;
                $this->dataLen = 1;
            }elseif($data <= 0xffff){
                $this->dataType = EC_TAGTYPE_UINT16;
                $this->dataLen = 2;
            }elseif($data <= 0xffffffff){
                $this->dataType = EC_TAGTYPE_UINT32;
                $this->dataLen = 4;
            }else{
                $this->dataType = EC_TAGTYPE_UINT64;
                $this->dataLen = 8;
            }
        }
        else{
            $this->dataType = EC_TAGTYPE_CUSTOM;
            $this->dataLen = strlen($data);
        }
    }
}
