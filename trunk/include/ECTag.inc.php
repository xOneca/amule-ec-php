<?php
/*
 * Copyright (C) 2009 Xabier Oneca <xoneca+amule-ec-php@gmail.com>
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

require_once('ECTagTypes.inc.php');

define('bsName',        0);
define('bsType',        1);
define('bsLength',      2);
define('bsLengthChild', 3);
define('bsChildCnt',    4);
define('bsChildren',    5);
define('bsData1',       6);
define('bsData2',       7);
define('bsFinished',    8);

/**
 * Class to hold IPv4 address.
 */
class EC_IPv4
{
    var $ip = array(0,0,0,0);
    var $port = 0;

    /**
     * Constructor.
     *
     * @param ip   IP address (32-bit integer).
     * @param port Port number (16-bit integer).
     *
     * Stores the IP and por numbers.
     */
    function __construct($ip=0, $port=0)
    {
        $this->ip[0] = $ip & 0xff;
        $this->ip[1] = ($ip >> 8) & 0xff;
        $this->ip[2] = ($ip >> 16) & 0xff;
        $this->ip[3] = ($ip >> 24) & 0xff;
        $this->port = $port;
    }

    /**
     * Return IP address
     *
     * @return IP adress (32-bit integer).
     */
    function IP()
    {
        return $this->ip[0] | ($this->ip[1] << 8) | ($this->ip[2] << 16) | ($this->ip[3] << 24);
    }

    /**
     * IP:port string.
     *
     * @param brackets If set to true, output is enclosed in brackets.
     *
     * @return IP:port or [IP:port] string.
     * NOTE: Are brackets really needed?
     */
    function StringIPSTL($brackets=true)
    {
        $string_ip = ($brackets) ? '[' : '';
        $string_ip .= intval($this->ip[0]) . '.' . intval($this->ip[1]) . '.' . intval($this->ip[2]) . '.' . intval($this->ip[3]) . ':' . intval($this->port);
        $string_ip .= ($brackets) ? ']' : '';
        return $string_ip;
    }
}

/**
 * CECTag class.
 */
class CECTag
{
    var $state = bsName;
    var $tagData = null;
    var $tagName = 0; // ec_tagname_t (uint16_t)
    var $dataLen = 0;
    var $dataType = EC_TAGTYPE_UNKNOWN;
    var $tagList = array();
    var $haschildren = false;

    function __construct($name, $data=null)
    {
        $this->name = $name;
        $this->tagData = $data;

        if($data === null){
            $this->dataType = EC_TAGTYPE_UNKNOWN;
        }
        elseif(is_object($data)){
            if(get_class($data) == 'EC_IPv4'){
                $this->dataType = EC_TAGTYPE_IPV4;
                $this->dataLen = 6; /// NOTE: I don't know the real length
            }elseif(get_class($data) == 'MD4Hash'){
                $this->dataType = EC_TAGTYPE_HASH16;
                $this->dataLen = 16;
            }
        }
        elseif(is_float($data)){
            // EC protocol transmits floats as strings.
            $this->tagData = "$data";
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
        elseif(is_string($data)){
            $this->dataType = EC_TAGTYPE_STRING;
            // Strings are \0 terminated. Is \0 included into data? I don't think so...
            $this->dataLen = strlen($data) + 1;
        }
        else{
            $this->dataType = EC_TAGTYPE_CUSTOM;
            $this->dataLen = strlen($data); // Any idea?
        }
    }

    function &GetTagByIndex($index)
    {
        return (($index >= count($this->tagList)) ? null : $this->tagList[$index]);
    }

    /**
     * Finds the (first) child tag with given name.
     *
     * @param name TAG name to look for.
     * @return the tag found, or \b false.
     */
    function &GetTagByName($name)
    {
        foreach($this->tagList as &$child)
            if($child->tagName == $name) return $child;

        return false;
    }

    /**
     * Query TAG length that is suitable for the TAGLEN field
     * (i.e.  without it's own header size).
     *
     * @return Tag length, containing its childs' length.
     */
    function GetTagLen()
    {
        $length = $this->dataLen;
        foreach($this->tagList as $child){
            $length += $child->GetTagLen();
            $length += SIZEOF_EC_TAGNAME_T + SIZEOF_EC_TAGTYPE_T + SIZEOF_EC_TAGLEN_T + (($child->GetTagCount() > 0) ? 2 : 0);
        }
        return $length;
    }

    /**
     * Get number of children tags
     *
     * @return integer Children count
     */
    function GetTagCount()
    {
        return count($this->tagList);
    }

    function GetTagData()
    {
        assert($this->dataType == EC_TAGTYPE_CUSTOM);
        return $this->tagData;
    }

    function GetTagDataLen()
    {
        return $this->dataLen;
    }

    function GetTagName()
    {
        return $this->tagName;
    }

    function GetInt()
    {
        if($this->tagData === null){
            // Empty tag - This is NOT an error.
            assert($this->dataType == EC_TAGTYPE_UNKNOWN);
            return 0;
        }

        switch($this->dataType){
            case EC_TAGTYPE_UINT8:
            case EC_TAGTYPE_UINT16:
            case EC_TAGTYPE_UINT32:
                return intval($this->tagData);
            case EC_TAGTYPE_UINT64:
                return $this->tagData; // PHP intval() has 32-bit limit
            case EC_TAGTYPE_UNKNOWN:
                // Empty tag - This is NOT an error.
                return 0;
            default:
                assert(0);
                return 0;
        }
    }

    /**
     * Returns a double value.
     *
     * @note The returned value is what we get by converting the string form
     * of the number to a double.
     *
     * @return The double value of the tag.
     */
    function GetDoubleData()
    {
        if($this->dataType != EC_TAGTYPE_DOUBLE){
            assert($this->dataType == EC_TAGTYPE_UNKNOWN);
            return 0;
        }elseif($this->tagData === null){
            assert(0);
            return 0;
        }
        return doubleval($this->tagData);
    }

    function GetStringDataSTL()
    {
        if($this->dataType != EC_TAGTYPE_STRING){
            assert($this->dataType == EC_TAGTYPE_UNKNOWN);
            return '';
        }
        elseif($this->tagData === null){
            assert(0);
            return '';
        }

        return strval($this->tagData);
    }

    function GetStringData()
    {
        return $this->GetStringDataSTL();
    }

    /**
     * Returns an EC_IPv4_t object.
     *
     * @return EC_IPv4_t object.
     */
    function GetIPv4Data()
    {
        if($this->tagData === null){
            assert(0);
            return new EC_IPv4();
        }
        elseif($this->dataType != EC_TAGTYPE_IPV4){
            assert(0);
            return new EC_IPv4();
        }
        else{
            return $this->tagData;
        }
    }

    /**
     * Returns a MD4Hash object
     *
     * @return MD4Hash object
     */
    function GetMD4Data()
    {
        if($this->dataType != EC_TAGTYPE_HASH16){
            assert($this->dataType == EC_TAGTYPE_UNKNOWN);
            return new CMD4Hash();
        }

        assert($this->tagData !== null);

        // Doesn't matter if tagData is NULL in CMD4Hash(),
        // that'll just result in an empty hash.
        return new CMD4Hash($this->tagData);
    }

    function GetType()
    {
        return $this->dataType;
    }

    /**
     * Add a child tag to this one.
     *
     * Be very careful that this creates a copy of \e tag. Thus, the following code won't work as expected:
     * \code
     * {
     *  p = new CECPacket(whatever);
     *  t1 = new CECTag(whatever);
     *  t2 = new CECTag(whatever);
     *  p->AddTag(t1);
     *  t1->AddTag(t2); // t2 won't be part of p !!!
     * }
     * \endcode
     *
     * To get the desired results, the above should be replaced with something like:
     *
     * \code
     * {
     *  p = new CECPacket(whatever);
     *  t1 = new CECTag(whatever);
     *  t2 = new CECTag(whatever);
     *  t1->AddTag(t2);
     *  unset(t2);  // we can safely delete t2 here, because t1 holds a copy
     *  p->AddTag(t1);
     *  unset(t1);  // now p holds a copy of both t1 and t2
     * }
     * \endcode
     *
     * Then why copying? The answer is to enable simplifying the code like this:
     *
     * \code
     * {
     *  p = new CECPacket(whatever);
     *  t1(whatever);
     *  t1->AddTag(new CECTag(whatever));    // t2 is now created on-the-fly
     *  p->AddTag(t1);   // now p holds a copy of both t1 and t2
     * }
     * \endcode
     *
     * @param tag a CECTag class instance to add.
     * @return \b true on succcess, \b false when an error occured
     */
    function AddTag($tag)
    {
        // Cannot have more than 64k tags
        assert(count($this->tagList) < 0xffff);

        $this->tagList[] = $tag;
        return true;
    }

    function ReadFromSocket($socket)
    {
        if($this->state == bsName){
            if(!$socket->ReadNumber($tmp_tagName, SIZEOF_EC_TAGNAME_T)){
                $this->tagName = 0;
                return false;
            }
            else{
                $this->tagName = $tmp_tagName >> 1;
                $this->haschildren = ($tmp_tagName & 0x01) ? true : false;
                $this->state = bsType;
            }
        }

        if($this->state == bsType){
            if(!$socket->ReadNumber($type, SIZEOF_EC_TAGTYPE_T)){
                $this->dataType = EC_TAGTYPE_UNKNOWN;
                return false;
            }
            else{
                $this->dataType = $type;
                if($this->haschildren)
                    $this->state = bsLengthChild;
                else
                    $this->state = bsLength;
            }
        }

        if($this->state == bsLength || $this->state == bsLengthChild){
            if(!$socket->ReadNumber($tagLen, SIZEOF_EC_TAGLEN_T)){
                return false;
            }
            else{
                $this->dataLen = $tagLen;
                if($this->state == bsLength)
                    $this->state = bsData1;
                else
                    $this->state = bsChildCnt;
            }
        }

        if($this->state == bsChildCnt || $this->state == bsChildren){
            if(!$this->ReadChildren($socket))
                return false;
        }

        if($this->state == bsData1){
            $tmp_len = $this->dataLen;
            $this->dataLen = $tmp_len - $this->GetTagLen();
            if($this->dataLen > 0){
                $this->tagData = '';
                $this->state = bsData2;
            }
            else{
                $this->tagData = null;
                $this->state = bsFinished;
            }
        }

        if($this->state == bsData2){
            if($this->tagData !== null){
                if(!$socket->ReadBuffer($tagData, $this->dataLen)){
                    return false;
                }
                else{
                    $this->tagData = $tagData;
                    $this->state = bsFinished;
                }
            }
            else{
                return false;
            }
        }

        return true;
    }

    function WriteTag($socket)
    {
        $tmp_tagName = ($this->tagName << 1) | (count($this->tagList) ? 1 : 0);
        $type = $this->dataType;
        $tagLen = $this->GetTagLen();
        assert($type != EC_TAGTYPE_UNKNOWN);

        if(!$socket->WriteNumber($tmp_tagName, SIZEOF_EC_TAGNAME_T)) return false;
        if(!$socket->WriteNumber($type, SIZEOF_EC_TAGTYPE_T)) return false;
        if(!$socket->WriteNumber($tagLen, SIZEOF_EC_TAGLEN_T)) return false;

        if(count($this->tagList)){
            if(!$this->WriteChildren($socket)) return false;
        }

        if($this->dataLen > 0){
            if($this->tagData !== null)
                if(!$socket->WriteBuffer($this->tagData, $this->dataLen)) return false;
        }

        return true;
    }

    function ReadChildren($socket)
    {
        if($this->state == bsChildCnt){
            if(!$socket->ReadNumber($tmp_tagCount, 2)){ // SIZEOF_UINT16 (not defined)
                return false;
            }
            else{
                $this->tagList = array();
                if($tmp_tagCount > 0){
                    for($i=0; $i < $tmp_tagCount; $i++)
                        $this->tagList[] = new CECTag(0);
                    $this->haschildren = true;
                    $this->state = bsChildren;
                }
            }
        }

        if($this->state == bsChildren){
            for($i=0; $i < count($this->tagList); $i++){
                $tag = &$this->tagList[$i];
                if(!$tag->IsOk()){
                    if(!$tag->ReadFromSocket($socket)){
                        return false;
                    }
                }
            }
            $this->state = bsData1;
        }

        return true;
    }

    function WriteChildren($socket)
    {
        assert(count($this->tagList) < 0xffff);
        $count = count($this->tagList);
        if(!$socket->WriteNumber($count, 2)) // SIZEOF_UINT16 (not defined)
            return false;
        if($count){
            for($i=0; $i < $count; $i++)
                if(!$this->tagList[$i]->WriteTag($socket)) return false;
        }
        return true;
    }

    function IsOk()
    {
        return ($this->state == bsFinished);
    }
}

class CECEmptyTag extends CECTag
{
    function __construct($name)
    {
        parent::__construct($name, null);
    }
}
