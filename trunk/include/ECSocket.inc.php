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

class CECSocket
{
    var $socket = null;
    var $rx_flags = 0;
    var $tx_flags = 0;
    var $my_flags = 0x20 | EC_FLAG_ZLIB | EC_FLAG_UTF8_NUMBERS | EC_FLAG_ACCEPTS;
    var $bytes_needed = 8; // Initial state: 4-bytes flags + 4-bytes length
    var $in_header = true;

    function ConnectSocket($ip, $port){}

    function WritePacket($packet)
    {
        $flags = 0x20;

        if($packet->GetacketLength() > EC_MAX_UNCOMPRESSED)
            $flags |= EC_FLAG_ZLIB;
        else
            $flags |= EC_FLAG_UTF8_NUMBERS;

        $flags &= $this->my_flags;

        if($flags & EC_FLAG_ZLIB){
            // zlib? gzcompress() what?
        }

        $tmp_flags = pack('N', $flags); // unsigned 32-bit int MSB first
        socket_write($this->socket, $tmp_flags);
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
}