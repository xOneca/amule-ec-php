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

/// Purpose: Classes for implementing aMule EC protocol

require_once('ecConstants.inc.php');
require_once('include/ecData.inc.php');

// Debugging
define('DEBUG', false);         // Enable ALL debug options
// Or enable indibidually:

//* (Remove a slash from this line to SWITCH OFF debugging)

define('DEBUG_OP_DESC', true);  // OpCode names
define('DEBUG_TAG_DESC', true); // TagType names

/*/

define('DEBUG_OP_DESC', false);
define('DEBUG_TAG_DESC', false);

//*/

// int lengths in pack() format
// maybe can be hardcoded
define('TYPEOF_SUBTAG_COUNT', 'n'); // 2
define('TYPEOF_TAGNAME', 'n');      // 2
define('TYPEOF_TAGSIZE', 'N');      // 4
define('TYPEOF_TAGTYPE', 'C');      // 1

/**
 * Socket management class
 *
 * Sending a packet in a whole write is neccessary to work.
 */
class ecSocket
{
    /**
     * Internal vars
     */
    var $fsp = false;
    var $buffer = '';

    /**
     * ecSocket class constructor
     *
     * @param $host IP or hostname of the aMule host.
     * @param $port The port to connect to.
     *
     * @return ecSocket object or false if can't connect.
     */
    function __construct($host, $port)
    {
//         $this->host = $host;
//         $this->port = $port;

        $this->fsp = fsockopen($host, $port);

        if($this->fsp === false) return false;
    }

    /**
     * ecSocket class destructor
     */
    function __destruct()
    {
        if($this->fsp !== false)
            fclose($this->fsp);
    }

    /**
     * Read from socket
     *
     * @param $length Number of bytes to read.
     *
     * @return Raw data read from the socket.
     */
    function Read($length)
    {
        if($this->fsp === false) return false;

        $ret = '';
        while(strlen($ret) < $length){str_dump($t = fread($this->fsp, $length - strlen($ret)));
            $ret .= $t;}

        return $ret;
    }

    /**
     * Write data to the buffer
     *
     * @param $data Raw data to write.
     *
     * @return False if there's no connection to the host. True otherwise.
     */
    function Write($data)
    {
        if($this->fsp === false) return false;

        $this->buffer .= $data;
        return true;
    }

    /**
     * Send buffer data to the host
     *
     * @return Length of sent data, or false if there's no connection to the host.
     */
    function SendPacket()
    {
        if($this->fsp === false) return false;

        $len = 0;
        do{
            $len += fwrite($this->fsp, substr($this->buffer, $len));
        }
        while($len < strlen($this->buffer));

        $this->buffer = '';
        return $len;
    }
}

/**
 * Generic tag class
 *
 * All other tags (including transmitted packet) inherit from this tag.
 */
class ecTag
{
    /**
     * Internal vars
     */
    var $size;
    var $type;
    var $type_desc;
    var $name;
    var $name_desc;
    var $subtags;

    /**
     * ecTag constructor
     *
     * @param $name Tag name.
     * @param $type Tag type.
     * @param $subtags Array of sub-tags.
     *
     * @return ecTag object.
     */
    function __construct($name=EC_TAG_STRING, $type=EC_TAGTYPE_UNKNOWN, $subtags=array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->subtags = $subtags;

        if(DEBUG || DEBUG_TAG_DESC)
        {
            $constants = get_defined_constants(true);
            foreach($constants['user'] as $k => $v)
                if(substr($k, 0, 7) == 'EC_TAG_' && $v == $name)
                    $this->name_desc = $k;
                elseif(substr($k, 0, 11) == 'EC_TAGTYPE_' && $v == $type)
                    $this->type_desc = $k;
        }
    }

    /**
     * Get number of sub-tags
     *
     * @return Sub-tag count.
     */
    function SubtagCount()
    {
        return count($this->subtags);
    }

    /**
     * Write Sub-tags to socket
     *
     * @param $socket ecSocket object where sub-tags will be written
     *
     * @return True on success, false otherwise.
     */
    function WriteSubtags($socket)
    {
        $count = count($this->subtags);
        $success = true;
        if($count)
        {
            $socket->Write(pack(TYPEOF_SUBTAG_COUNT, $count));
            foreach($this->subtags as $tag)
                if(!$tag->Write($socket)) $success = false;
        }
        return $success;
    }

    /**
     * Name of the tag
     *
     * @return The name of the tag
     */
    function Name()
    {
        return $this->name;
    }

    /**
     * Write tag to socket
     *
     * @param $socket ecSocket object where tag will be written to.
     *
     * @return True on success, false otherwise.
     */
    function Write($socket)
    {
        $name = $this->name << 1;
        if(count($this->subtags))
            $name |= 1;

        $success = true;

        if(!$socket->Write(pack(TYPEOF_TAGNAME, $name))) $success = false;
        if(!$socket->Write(pack(TYPEOF_TAGTYPE, $this->type))) $success = false;
        if(!$socket->Write(pack(TYPEOF_TAGSIZE, $this->Size()))) $success = false;

        if(!$this->WriteSubtags($socket)) $success = false;

        // Here derived class will put actual data.
        return $success;
    }

    /**
     * Size of the tag
     *
     * @return Size, in bytes, that this tag would take if written to a socket.
     */
    function Size()
    {
        $total_size = $this->size;

        if(count($this->subtags)){
            foreach($this->subtags as $tag){
                $total_size += $tag->Size();
                $total_size += (2 + 1 + 4); // name + type + size
                if($tag->HasSubtags())
                    $total_size += 2;
            }
        }

        return $total_size;
    }

    /**
     * Add a sub-tag
     *
     * @param $tag Tag object to add as sub-tag.
     */
    function AddSubtag($tag)
    {
        assert(count($this->subtags) < 0xffff);
        $this->subtags[] = $tag;
    }

    /**
     * Has sub-tags?
     *
     * @return True if this tag has sub-tags. False if not.
     */
    function HasSubtags()
    {
        return (count($this->subtags) > 0);
    }

    /**
     * Find a sub-tag
     *
     * @param $name The name of the sub-tag
     *
     * @return The sub-tag object that has the same name
     */
    function SubTag($name)
    {
        foreach($this->subtags as $tag){
            if($tag->name == $name) return $tag;
        }
    }
}

/**
 * Integer tag
 *
 * Works!
 */
class ecTagInt extends ecTag
{
    /**
     * Internal vars
     */
    var $val;

    function __construct($name, $value_or_size, $size_or_socket, $subtags=null)
    {
        if($subtags === null)
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

    function Value()
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

/**
 * MD5 Hash tag
 *
 * Works!
 */
class ecTagMD5 extends ecTag
{
    var $val;

    function __construct($name, $data, $socket=null, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_HASH16, $subtags);

        if($socket === null){
            // Hash must be a string of hexadecimal chars
            $data = unpack('N4', pack('H*', $data)); // Read entire hash in 4 chunks
        }
        else{
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

    function Value()
    {
        return sprintf('%x%x%x%x', $this->val[1], $this->val[2], $this->val[3], $this->val[4]);
    }

    function RawValue()
    {
        return pack('N*', $this->val[1], $this->val[2], $this->val[3], $this->val[4]);
    }
}

/**
 * IPv4 tag
 *
 * Only for parsing purpose
 *
 * Works!
 */
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

    function IP()
    {
        return (($this->address >> 24) & 0xff) . '.' .
               (($this->address >> 16) & 0xff) . '.' .
               (($this->address >> 8) & 0xff) . '.' .
               (($this->address) & 0xff);
    }

    function Value()
    {
        return $this->IP() . ':' . $this->port;
    }
}

/**
 * Custom tag
 */
class ecTagCustom extends ecTag
{
    var $val;

    function __construct($name, $size, $socket, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_CUSTOM, $subtags);

        $this->val = $socket->Read($size);
        $this->size = $size;
    }

    function Value()
    {
        return $this->val;
    }
}

/**
 * String tag
 *
 * Works!
 */
class ecTagString extends ecTag
{
    var $val;

    function __construct($name, $string_or_size, $socket=null, $subtags=array())
    {
        parent::__construct($name, EC_TAGTYPE_STRING, $subtags);

        if($socket !== null){
            $size = $string_or_size;
            $string = $socket->Read($size);
            $this->val = rtrim($string); // Discard \0 at the end of the string
            $this->size = strlen($string); // Size with \0 too
        }
        else{
            $string = $string_or_size;
            $this->val = $string; // String supplyed by the user/developer (without \0 ending)
            $this->size = strlen($string) + 1; // Size with \0 too
        }
    }

    function Write($socket)
    {
        parent::Write($socket);

        $socket->Write($this->val . "\0");
    }

    function Value()
    {
        return $this->val;
    }
}

define('MAX_UNCOMPRESSED_PACKET', 1024);
class ecPacket extends ecTag
{
    var $opcode;
    var $opcode_desc;
    var $flags;

    function __construct($cmd=0)
    {
        $this->flags = 0x20;
        $this->opcode = $cmd;

        if(DEBUG || DEBUG_OP_DESC)
        {
            $constants = get_defined_constants(true);
            foreach($constants['user'] as $k => $v)
                if(substr($k, 0, 6) == 'EC_OP_' && $v == $cmd){
                    $this->opcode_desc = $k;
                    break;
                }
        }
    }

    function Read($socket)
    {
        list(, $this->flags) = unpack('N', $socket->Read(4)); // Int32
        if($this->flags & EC_FLAG_ACCEPTS != 0)
            list(, $this->accepts) = unpack('N', $socket->Read(4)); // Int32
        list(, $this->size) = unpack('N', $socket->Read(4)); // Int32
        list(, $this->opcode) = unpack('C', $socket->Read(1)); // Char

        list(, $tag_count) = unpack('n', $socket->Read(2)); // Int16

        if($tag_count){
            for($i = 0; $i < $tag_count; $i++)
                $this->AddSubtag($this->ReadTag($socket));
        }

        if(DEBUG || DEBUG_OP_DESC)
        {
            $constants = get_defined_constants(true);
            foreach($constants['user'] as $k => $v)
                if(substr($k, 0, 6) == 'EC_OP_' && $v == $this->opcode){
                    $this->opcode_desc = $k;
                    break;
                }
        }
    }

    // Parsing
    function ReadTag($socket)
    {
        $tag = null;
        list(, $tag_name16) = unpack('n', $socket->Read(2)); // Read 2 bytes (Int16)
        $has_subtags = ($tag_name16 & 1) != 0;
        $tag_name = $tag_name16 >> 1;

        list(, $tag_type) = unpack('C', $socket->Read(1)); // Read 1 byte (Char)
        list(, $tag_size) = unpack('N', $socket->Read(4)); // Read 4 bytes (Int32)

        $subtags = array();
        if($has_subtags)
            $subtags = $this->ReadSubtags($socket);

        switch($tag_type){
            case EC_TAGTYPE_CUSTOM:
                $tag = new ecTagCustom($tag_name, $tag_size, $socket, $subtags);
                break;
            case EC_TAGTYPE_UINT8:
                $tag = new ecTagInt($tag_name, 1, $socket, null);
                $tag->subtags = $subtags;
                break;
            case EC_TAGTYPE_UINT16:
                $tag = new ecTagInt($tag_name, 2, $socket, null);
                $tag->subtags = $subtags;
                break;
            case EC_TAGTYPE_UINT32:
                $tag = new ecTagInt($tag_name, 4, $socket, null);
                $tag->subtags = $subtags;
                break;
            case EC_TAGTYPE_UINT64:
                $tag = new ecTagInt($tag_name, 8, $socket, null);
                $tag->subtags = $subtags;
                break;
            case EC_TAGTYPE_STRING:
                $tag = new ecTagString($tag_name, $tag_size, $socket, $subtags);
                break;
            case EC_TAGTYPE_IPV4:
                $tag = new ecTagIPv4($tag_name, $socket, $subtags);
                break;
            case EC_TAGTYPE_HASH16:
                $tag = new ecTagMD5($tag_name, '', $socket, $subtags);
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

// TODO: Implement ZLIB compression
        if(($this->flags & EC_FLAG_ZLIB) != 0){
            $this->flags &= ~EC_FLAG_ZLIB;
//             return false;
        }

        $socket->Write(pack('N', $this->flags)); // Int32
        if(($this->flags & EC_FLAG_ACCEPTS) != 0)
            $socket->Write(pack('N', $this->flags & ~EC_FLAG_ACCEPTS)); // Int32

        $socket->Write(pack('N', $packet_size)); // Int32
        $socket->Write(pack('C', $this->opcode));
        if(count($this->subtags))
            $this->WriteSubtags($socket);
        else
            $socket->Write(pack('n', 0)); // Int16
    }
}

// Specific-purpose tags

/**
 * Login Packet
 *
 * Works!
 */
class ecLoginPacket extends ecPacket
{
    function __construct($client_name, $version, $pass)
    {
        parent::__construct(EC_OP_AUTH_REQ);
        $this->flags |= 0x20 | EC_FLAG_ACCEPTS;

        $this->AddSubtag(new ecTagString(EC_TAG_CLIENT_NAME, $client_name));
        $this->AddSubtag(new ecTagString(EC_TAG_CLIENT_VERSION, $version));
        $this->AddSubtag(new ecTagInt(EC_TAG_PROTOCOL_VERSION, EC_CURRENT_PROTOCOL_VERSION, 2, array())); // I think size is 2 bytes
        $this->AddSubtag(new ecTagMD5(EC_TAG_PASSWD_HASH, $pass));
    }
}

/**
 * Connection state tag
 *
 * Only for parsing purpose.
 */
class ecConnStateTag
{
    var $tag;
    var $tag_val;

    function __construct($tag)
    {
        $this->tag = $tag;
        $this->tag_val = $tag->Value();
        // $this->tag_val = 0xfff;
    }

    function IsConnected()
    {
        return $this->IsConnectedED2K() || $this->IsConnectedKademlia();
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

// Not finished yet
class ecPartFileTag
{
    var $tag;
    var $name = '';
    var $partmetid = 0;
    var $size_full = 0;
    var $size_xfer = 0;
//     var $size_xfer_up;
    var $size_done = 0;
    var $speed = 0;
    var $status = 0;
    var $prio = 0;
    var $source_count = 0;
    var $source_count_a4af = 0;
    var $source_count_not_current = 0;
    var $source_count_xfer = 0;
    var $ed2k_link = '';
    var $cat = 0;
//     var $last_recv;
    var $last_seen_comp = 0;
    var $part_status;
    var $gap_status;
    var $req_status;
//     var $source_names;
    var $comments = array();

    function __construct($tag)
    {
        $this->tag = $tag;
        $this->name                     = $tag->SubTag(EC_TAG_PARTFILE_NAME)->Value();
        $this->partmetid                = $tag->SubTag(EC_TAG_PARTFILE_PARTMETID)->Value();
        $this->size_full                = $tag->SubTag(EC_TAG_PARTFILE_SIZE_FULL)->Value();
        $this->size_xfer                = $tag->SubTag(EC_TAG_PARTFILE_SIZE_XFER)->Value();
        $this->size_done                = $tag->SubTag(EC_TAG_PARTFILE_SIZE_DONE)->Value();
        $this->speed                    = $tag->SubTag(EC_TAG_PARTFILE_SPEED)->Value();
        $this->status                   = $tag->SubTag(EC_TAG_PARTFILE_PART_STATUS)->Value();
        $this->prio                     = $tag->SubTag(EC_TAG_PARTFILE_PRIO)->Value();
        $this->source_count             = $tag->SubTag(EC_TAG_PARTFILE_SOURCE_COUNT)->Value();
        $this->source_count_a4af        = $tag->SubTag(EC_TAG_PARTFILE_SOURCE_COUNT_A4AF)->Value();
        $this->source_count_not_current = $tag->SubTag(EC_TAG_PARTFILE_SOURCE_COUNT_NOT_CURRENT)->Value();
        $this->source_count_xfer        = $tag->SubTag(EC_TAG_PARTFILE_SOURCE_COUNT_XFER)->Value();
        $this->ed2k_link                = $tag->SubTag(EC_TAG_PARTFILE_ED2K_LINK)->Value();
        $this->cat                      = $tag->SubTag(EC_TAG_PARTFILE_CAT)->Value();
        $this->last_seen_comp           = $tag->SubTag(EC_TAG_PARTFILE_LAST_SEEN_COMP)->Value();
        $this->part_status              = $tag->SubTag(EC_TAG_PARTFILE_PART_STATUS)->Value();
        $this->gap_status               = $tag->SubTag(EC_TAG_PARTFILE_GAP_STATUS)->Value();
        $this->req_status               = $tag->SubTag(EC_TAG_PARTFILE_REQ_STATUS)->Value();
        $this->comments                 = $tag->SubTag(EC_TAG_PARTFILE_COMMENTS)->Value(); // Int
        $this->dwn_sts = new PartFileEncoderData();
        $this->dwn_sts->Decode($this->gap_status, $this->part_status);
    }
}
