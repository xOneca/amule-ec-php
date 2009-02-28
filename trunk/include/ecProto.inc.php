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

require_once('ECCodes.inc.php');
require_once('ECTagTypes.inc.php');

// int lengths in pack() format
define('SIZEOF_SUBTAG_COUNT', 'v'); // 2
define('SIZEOF_TAGNAME', 'v'); // 2
define('SIZEOF_TAGSIZE', 'V'); // 4
define('SIZEOF_TAGTYPE', 'C'); // 1

function utf8_chars_left($first_char)
{
    if($first_char & 0x80 == 0x00){ // only one byte (ASCII char)
        return 0;
    }if($first_char & 0xe0 == 0xc0){ // three first bits 110
        return 1;
    }elseif($first_char & 0xf0 == 0xe0){ // four first bits 1110
        return 2;
    }elseif($first_char & 0xf8 == 0xf0){ // five first bits 11110
        return 3;
    }
    return false; // I don't know...
}

function read_utf8($socket)
{
    // Take first char and guess remaining bytes
    // If there are remaining bytes, read them too
    // Discard utf8 information from characters and
    // join them into one integer
}

class ecSocket
{
//     var $host = null;
//     var $port = null;
    var $fsp = false;

    function __construct($host, $port)
    {
//         $this->host = $host;
//         $this->port = $port;

        $this->fsp = fsockopen($host, $port);

        if($this->fsp === false) return false;
    }

    function __destruct()
    {
        if($this->fsp !== false)
            fclose($this->fsp);
    }

    function Read($length)
    {
        if($this->fsp === false) return false;

        $ret = '';
        while(strlen($ret) < $length)
            $ret .= fread($this->fsp, $length - strlen($ret));

        return $ret;
    }

    function Write($data)
    {
        if($this->fsp === false) return false;

        $len = 0;
        do{
            $len += fwrite($this->fsp, substr($data, $len));
        }
        while($len < strlen($data));
    }
}

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
            $socket->Write(pack(SIZEOF_SUBTAG_COUNT, $count));
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

        $socket->Write(pack(SIZEOF_TAGNAME, $name));
        $socket->Write(pack(SIZEOF_TAGTYPE, $this->type));
        $socket->Write(pack(SIZEOF_TAGSIZE, $this->Size()));

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
            $socket = false;
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

    function __construct($name, $data_or_socket, $subtags=null)
    {
        parent::__construct($name, EC_TAGTYPE_HASH16, $subtags);

        if($subtags !== null){
            $data = $data_or_socket;
            // Hash is a string of hexadecimal chars (?)
            $data = unpack('N4', pack('H*', $data)); // Read entire hash in 4 chunks
        }
        else{
            $socket = $data_or_socket;
            $data = unpack('N4', $socket->Read(16)); // Read entire hash in 4 chunks
        }
        $this->val = $data; // Save it in 4 chunks (too big int)
        $this->size = 16;
    }

    function Write($socket)
    {
        assert(count($this->val) == 4);

        parent::Write($socket);
        $socket->Write(pack('N*', $this->val[1], $this->val[2], $this->val[3], $this->val[4]));
    }

//     function ecMD4Hash()
//     {
//         return new CMD4Hash($this->val);
//     }
}

class ecTagIPv4 extends ecTag
{
    var $address;
    var $port;

    function __construct($name, $socket, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_IPV4, $subtags);

        $this->size = 4 + 2;
        list(, $this->address) = unpack('V', $socket->Read(4)); // Read 4 bytes (Int32)
        list(, $this->port) = unpack('v', $socket->Read(2)); // Read 2 bytes (Int16)
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

        $this->val = rtrim($string); // Discard \0 at the end of the string
        $this->size = strlen($string); // Size with \0 too
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

// Specific-purpose tags
class ecLoginPacket extends ecPacket
{
    function __construct($client_name, $version, $pass)
    {
        parent::__construct(EC_OP_AUTH_REQ);
        $this->flags |= 0x20 | EC_FLAG_ACCEPTS;

        $this->AddSubtag(new ecTagString(EC_TAG_CLIENT_NAME, $client_name));
        $this->AddSubtag(new ecTagString(EC_TAG_CLIENT_VERSION, $version));
        $this->AddSubtag(new ecTagInt(EC_TAG_PROTOCOL_VERSION, EC_CURRENT_PROTOCOL_VERSION, 2)); // I think size is 2 bytes
        $this->AddSubtag(new ecTagMD4(EC_TAG_PASSWD_HASH, $pass, array()));
    }
}

class ecDownloadsInfoReq extends ecPacket
{
    function __construct()
    {
        parent::__construct(EC_OP_GET_DLOAD_QUEUE);
    }
}

// Only for parsing purpose
class ecConnStateTag
{
    var $tag;
    var $tag_val;

    function __construct($tag)
    {
        $this->tag = $tag;
        $this->tag_val = $tag->ValueInt();
        // $this->tag_val = 0xfff;
    }

    function IsConnected()
    {
        return IsConnectedED2K() || IsConnectedKademlia();
    }

    function IsConnectedED2K()
    {
        return ($this->tag_val & 0x01) != 0;
    }

    function IsConnectingED2K()
    {
        return ($this->tag_val & 0x02) != 0;
    }

    function IsConnectedKademlia()
    {
        return ($this->tag_val & 0x04) != 0;
    }

    function IsKadFirewalled()
    {
        return ($this->tag_val & 0x08) != 0;
    }

    function IsKadRunning()
    {
        return ($this->tag_val & 0x10) != 0;
    }

    function Server()
    {
        return $this->tag->SubTag(EC_TAG_SERVER);
    }
}