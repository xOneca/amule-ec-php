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

/// Purpose: Class implementing a packet

require_once('ECCodes.inc.php');

// Class for managing packets
class ECPacket
{
    var $flags = 0x20; // Bit 5 allways on (is the minimum)
    var $opcode = 0;
    var $datasize = 0;
    var $accepts = 0;

    function __construct(){}

    // Parse raw packet to get the information
    function parse_packet($str)
    {
        list(,$this->flags) = unpack('N', $str); // Get first 4 bytes
        $str = substr($str, 4); // Delete first 4 bytes
        if(($this->flags & EC_FLAG_ACCEPTS) == EC_FLAG_ACCEPTS)
        {
            list(,$this->accepts) = unpack('N', $str);
            $str = substr($str, 4);
        }
        else
        {
            // Should we accept the same as we send?
            $this->accepts = $this->flags;
        }
        list(,$this->datasize) = unpack('N', $str);
    }
}
