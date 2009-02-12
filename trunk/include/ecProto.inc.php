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

/// Purpose: Class implementing EC protocol

define('SIZEOF_SUBTAG_COUNT', 2);
define('SIZEOF_TAGNAME', 2);
define('SIZEOF_TAGSIZE', 4);
define('SIZEOF_TAGTYPE', 1);

class ecTag
{
    var $size;
    var $type;
    var $name;
    var $subtags;

    function __construct($name=EC_TAG_STRING, $type=EC_TAGTYPE_UNKNOWN, $subtags=array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->subtags = $subtags;
    }

    function SubtagCount()
    {
        return count($this->subtags);
    }

    function WriteSubtags($socket)
    {
        $count = count($this->subtags);
        if($count)
        {
            $socket->Write($count, SIZEOF_SUBTAG_COUNT);
            foreach($this->subtags as $tag)
                $tag->Write($socket);
        }
    }

    function Name()
    {
        return $this->name;
    }

    function Write($socket)
    {
        $name = $this->name << 1;
        if(count($this->subtags))
            $name |= 1;

        $socket->Write($name, SIZEOF_TAGNAME);
        $socket->Write($this->type, SIZEOF_TAGTYPE);
        $socket->Write($this->Size(), SIZEOF_TAGSIZE);

        $this->WriteSubtags($socket);

        // Here derived class will put actual data.
    }

    function Size()
    {
        $total_size = $this->size;

        foreach($this->subtags as $tag){
            $total_size += $tag->Size();
            $total_size += (2 + 1 + 4); // name + type + size
            if($tag->HasSubtags())
                $total_size += 2;
        }

        return $total_size;
    }

    function AddSubtag($tag)
    {
        assert(count($this->subtags) < 0xffff);
        $this->subtags[] = $tag;
    }

    function HasSubtags()
    {
        return (count($this->subtags) > 0);
    }

    function SubTag($name)
    {
        foreach($this->subtags as $tag){
            if($tag->name == $name) return $tag;
        }
    }
}

class ecTagInt extends ecTag
{
    var $val;

    function __construct($name, $value_or_size, $size_or_socket, $subtags=null)
    {
        if($subtags !== null)
        {
            $size = $value_or_size;
            $socket = $size_or_socket;
        }
        else{
            $value = $value_or_size;
            $size = $size_or_socket;
            $subtags = array();
        }

        switch($size)
        {
            case 1:
                parent::__construct($name, EC_TAGTYPE_UINT8, $subtags);
                if($socket) list(, $value) = unpack('C', $socket->Read(1));
                break;
            case 2:
                parent::__construct($name, EC_TAGTYPE_UINT16, $subtags);
                if($socket) list(, $value) = unpack('n', $socket->Read(2));
                break;
            case 4:
                parent::__construct($name, EC_TAGTYPE_UINT32, $subtags);
                if($socket) list(, $value) = unpack('N', $socket->Read(4));
                break;
            case 8:
                parent::__construct($name, EC_TAGTYPE_UINT64, $subtags);
                if($socket){
                    list(, $value) = unpack('N2', $socket->Read(8));
                    $value = ($value[1] << 32) | $value[2];
                }
                break;
            default:
                return false;
        }

        assert($value >= 0);

        $this->val = $value;
        $this->size = $size;
    }

    function ValueInt()
    {
        assert(is_numeric($this->val));
        return $this->val;
    }

    function Write($socket)
    {
        parent::Write($socket);

        switch($this->size)
        {
            case 1:
                $socket->Write(pack('C', ($this->val & 0xff))); // Unsigned char
                break;
            case 2:
                $socket->Write(pack('n', ($this->val & 0xffff))); // Unsigned short
                break;
            case 4:
                $socket->Write(pack('N', ($this->val & 0xffffffff)));
                break;
            case 8:
                // Write in two 32-bit chunks
                $val32 = ($this->val >> 32) & 0xffffffff;
                $socket->Write(pack('N', $val32));
                $val32 = $this->val & 0xffffffff;
                $socket->Write(pack('N', $val32));
                break;
            default:
                return false;
        }
    }
}

class ecTagMD4 extends ecTag
{
    var $val;

    function __construct($name, $socket, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_HASH16, $subtags);
        $md4 = unpack('N4', $socket->Read(16)); // Read entire md4 in 4 chunks
        $this->val = $md4[1] << 96 | $md4[2] << 64 | $md4[3] << 32 | $md4[4];
        $this->size = 16;
    }

    function Write($socket)
    {
        assert(strlen($this->val) == 16);

        parent::Write($socket);
        $socket->Write($this->val);
    }

    function ecMD4Hash()
    {
        return new CMD4Hash($this->val);
    }
}

class ecTagIPv4 extends ecTag
{
    var $address;
    var $port;

    function __construct($name, $socket, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_IPV4, $subtags);

        $this->size = 4 + 2;
        list(, $this->address) = unpack('N', $socket->Read(4)); // Read 4 bytes (Int32)
        list(, $this->port) = unpack('n', $socket->Read(2)); // Read 2 bytes (Int16)
    }
}

class ecTagCustom extends ecTag
{
    var $val;

    function __construct($name, $size, $socket, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_CUSTOM, $subtags);

        $this->val = $socket->Read($size);
        $this->size = $size;
    }
}

class ecTagString extends ecTag
{
    var $val;

    function __construct($name, $string_or_size, $socket=null, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_STRING, $subtags);

        if($socket !== null){
            $size = $string_or_size;
            $string = $socket->Read($size);
        }
        else{
            $string = $string_or_size;
        }

        $this->val = rtrim($string); // discard \0
        $this->size = strlen($this->val);
    }

    function Write($socket)
    {
        parent::Write($socket);

        $socket->Write($this->val . "\0");
    }
}

define('MAX_UNCOMPRESSED_PACKET', 1024);
class ecPacket extends ecTag
{
    var $opcode;
    var $flags;

    function __construct($cmd=0)
    {
        $this->flags = 0x20;
        $this->opcode = $cmd;
    }

    function Read($socket)
    {
        list(, $this->flags) = unpack('N', $socket->Read(4)); // Int32
        list(, $this->size) = unpack('N', $socket->Read(4)); // Int32
        list(, $this->opcode) = unpack('C', $socket->Read(1)); // Char

        list(, $tag_count) = unpack('n', $socket->Read(2)); // Int16

        if($tag_count){
            for($i = 0; $i < $tag_count; $i++)
                $this->AddSubtag($this->ReadTag($socket));
        }
    }

    // Parsing
    function ReadTag($socket)
    {
        $tag = null;
        list(, $tag_name16) = unpack('n', $socket->Read(2)); // Read 2 bytes (Int16)
        $has_subtags = ($tag_name16 & 1) != 0;
        $tag_name = $tag_name16 >> 1;

        list(, $tag_type8) = unpack('C', $socket->Read(1)); // Read 1 byte (Char)
        list(, $tag_size32) = unpack('N', $socket->Read(4)); // Read 4 bytes (Int32)

        $subtags = array();
        if($has_subtags)
            $subtags = $this->ReadSubtags($socket);

        switch($tag_type8){
            case EC_TAGTYPE_CUSTOM:
                $tag = new ecTagCustom($tag_name, $tag_size32, $socket, $subtags);
                break;
            case EC_TAGTYPE_UINT8:
                $tag = new ecTagInt($tag_name, 1, $socket, $subtags);
                break;
            case EC_TAGTYPE_UINT16:
                $tag = new ecTagInt($tag_name, 2, $socket, $subtags);
                break;
            case EC_TAGTYPE_UINT32:
                $tag = new ecTagInt($tag_name, 4, $socket, $subtags);
                break;
            case EC_TAGTYPE_UINT64:
                $tag = new ecTagInt($tag_name, 8, $socket, $subtags);
                break;
            case EC_TAGTYPE_STRING:
                $tag = new ecTagString($tag_name, $tag_size32, $socket, $subtags);
                break;
            case EC_TAGTYPE_IPV4:
                $tag = new ecTagIPv4($tag_name, $socket, $subtags);
                break;
            case EC_TAGTYPE_HASH16:
                $tag = new ecTagMD4($tag_name, $socket, $subtags);
                break;

            case EC_TAGTYPE_UNKNOWN:
            case EC_TAGTYPE_DOUBLE:
            default:
                break;
        }

        if($tag === null)
            return false;

        return $tag;
    }

    function ReadSubtags($socket)
    {
        list(, $count16) = unpack('n', $socket->Read(2)); // Read 2 bytes (Int16)

        $taglist = array();
        for($i = 0; $i < $count16; $i++){
            $taglist[] = $this->ReadTag($socket);
        }

        return $taglist;
    }

    function PacketSize()
    {
        $packet_size = $this->Size();
        if(($this->flags & EC_FLAG_ACCEPTS) != 0)
            $packet_size += 4;

        // 1 (command) + 2 (tag count) + 4 (flags) + 4 (total size)
        return $packet_size + 1 + 2 + 4 + 4;
    }

    function Opcode()
    {
        return $this->opcode;
    }

    function Write($socket)
    {
        // 1 (command) + 2 (tag count)
        $packet_size = $this->Size() + 1 + 2;
        if($packet_size > MAX_UNCOMPRESSED_PACKET)
            $this->flags |= EC_FLAG_ZLIB;

        if(($this->flags & EC_FLAG_ZLIB) != 0){
            // Not implemented
            $this->flags &= ~EC_FLAG_ZLIB;
//             return false;
        }

        $socket->Write(pack('N', $this->flags)); // Int32
        if(($this->flags & EC_FLAG_ACCEPTS) != 0)
            $socket->Write(pack('N', $this->flags)); // Int32

        $socket->Write(pack('N', $packet_size)); // Int32
        $socket->Write(pack('C', $this->opcode));
        if(count($this->subtags))
            $this->WriteSubtags($socket);
        else
            $socket->Write(pack('n', 0)); // Int16
    }
}
