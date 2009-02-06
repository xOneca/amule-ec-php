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

/// Purpose: Socket controller

require_once('ECPacket.inc.php');

// utf8_mbtowc() = utf8_decode()
// utf8_wctomb() = utf8_encode()

define('EC_COMPRESSION_LEVEL', 9);
define('EC_MAX_UNCOMPRESSED', 1024);

class CQueuedData
{
    var $data = '';
    var $rd_ptr = 0;
    var $wr_ptr = 0;

    function __construct($len)
    {
        $this->rd_ptr = 0;
        $this->wr_ptr = 0;
        $this->data = str_repeat("\0", $len); // What char should be used to allocate mem?
    }

    function Rewind()
    {
        $this->rd_ptr = 0;
        $this->wr_ptr = 0;
    }

    function Write($data, $len)
    {
        $canWrite = min($this->GetRemLength(), $len);
        assert($len == $canWrite);

        // memcpy()
        $this->data = substr($this->data, 0, $this->wr_ptr) . substr($data, 0, $canWrite) . substr($this->data, $this->wr_ptr + $canWrite);
        $this->wr_ptr += $canWrite;
    }

    function WriteAt($data, $len, $offset)
    {
        assert($len + $offset <= strlen($this->data));

        if($offset > strlen($this->data))
            return false;
        elseif($offset + $len > strlen($this->data))
            $len = strlen($this->data) - $offset;

        // memcpy()
        $this->data = substr($this->data, 0, $offset) . substr($data, 0, $len) . substr($this->data, $offset + $len);
    }

    function Read(&$data, $len)
    {
        $canRead = min($this->GetUnreadDataLength(), $len);
        assert($len == $canRead);

        $data = substr($this->data, $this->rd_ptr, $canRead);
        $this->rd_ptr += $canRead;
    }

//     function ToZlib($z){}
    function WriteToSocket($sock)
    {
        $sock->SocketWrite(substr($this->data, $this->rd_ptr, $this->GetUnreadDataLength()));
        $this->rd_ptr += $sock->GetLastCount();
    }

    function ReadFromSocket($sock, $len){}
    function ReadFromSocketAll($sock, $len){}
    function GetLength(){}
    function GetDataLength(){}
    function GetRemLength(){}
    function GetUnreadDataLength(){}
}

/**
 * \class CECSocket
 *
 * \brief Socket handler for External Communications (EC).
 *
 * CECSocket takes care of the transmission of EC packets
 */
class CECSocket
{
    var $socket = null;
    var $curr_rx_data = '';
    var $curr_tx_data = '';
    var $rx_flags = 0;
    var $tx_flags = 0;
    var $my_flags = 0x20 | EC_FLAG_ZLIB | EC_FLAG_UTF8_NUMBERS | EC_FLAG_ACCEPTS;
    var $bytes_needed = 8; // Initial state: 4-bytes flags + 4-bytes length
    var $in_header = true;

    function __destruct()
    {
        if($socket)
            fclose($socket);
    }

    function ConnectSocket($ip, $port)
    {
        // Maybe it's better pfsockopen (persistent connection)
        $this->socket = fsockopen($ip, $port, $errno);

        return ($this->socket && !$errno);
    }

    function WritePacket($packet)
    {
        $flags = 0x20;

        if($packet->GetacketLength() > EC_MAX_UNCOMPRESSED)
            $flags |= EC_FLAG_ZLIB;
        else
            $flags |= EC_FLAG_UTF8_NUMBERS;

        $flags &= $this->my_flags;
        $this->tx_flags = $flags;

        if($flags & EC_FLAG_ZLIB){
            // zlib? gzcompress() what?
        }

        $tmp_flags = pack('N', $flags); // unsigned 32-bit int MSB first
        socket_write($this->socket, $tmp_flags);

        // ...
    }

    function ReadPacket()
    {
        $flags = socket_read($this->socket, 4); // read 4 bytes

        if((($flags &0x60) != 0x20) || ($flags & EC_FLAG_UNKNOWN_MASK)){
            // Protocol error - other end might use an older protocol
            return false;
        }

        if($flags & EC_FLAG_ZLIB){
            // inflateInit()
        }
        // ...
    }

    function ReadBufferFromSocket($buffer, $required_len){
        assert($required_len);

    }

    function ReadBuffer(/* ... */){}
    function ReadNumber(/* ... */){}
    function WriteNumber(/* ... */){}
    function GetLastCount(/* ... */){}
}

class utf8_table
{
    var $cmask;
    var $cval;
    var $shift;
    var $lmask;
    var $lval;

    function __construct($cmask, $cval, $shift, $lmask, $lval)
    {
        $this->cmask = $cmask;
        $this->cval = $cval;
        $this->shift = $shift;
        $this->lmask = $lmask;
        $this->lval = $lval;
    }
}

function utf8_mb_remain($c)
{
    static $utf8_table = array(
        new utf8_table(0x80,  0x00,   0*6,    0x7F,           0),        // 1 byte sequence
        new utf8_table(0xE0,  0xC0,   1*6,    0x7FF,          0x80),     // 2 byte sequence
        new utf8_table(0xF0,  0xE0,   2*6,    0xFFFF,         0x800),    // 3 byte sequence
        new utf8_table(0xF8,  0xF0,   3*6,    0x1FFFFF,       0x10000),  // 4 byte sequence
        new utf8_table(0xFC,  0xF8,   4*6,    0x3FFFFFF,      0x200000), // 5 byte sequence
        new utf8_table(0xFE,  0xFC,   5*6,    0x7FFFFFFF,     0x4000000) // 6 byte sequence
    );

    for($i=0; $i < 5; $i++)
        if(($c & $utf8_table[$i]->cmask) == $utf8_table[$i]->cval)
            break;

    return $i;
}
