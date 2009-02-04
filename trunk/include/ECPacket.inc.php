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

/// Purpose: Class implementing a packet (protocol's main class)

require_once('ECCodes.inc.php');
require_once('ECTag.inc.php');
require_once('MD4Hash.inc.php');

// Class for managing packets
class CECPacket extends CECEmptyTag
{
    var $opCode = 0;

    function __construct($opCode, $detail_level=EC_DETAIL_FULL)
    {
        parent::__construct(0);
        $this->opCode = $opCode;

        // EC_DETAIL_FULL is default. No point transmit ti
        if($detail_level != EC_DETAIL_FULL){
            $this->AddTag(new CECTag(EC_TAG_DETAIL_LEVEL, $detail_level));
        }
    }

    function GetOpCode()
    {
        return $this->opCode;
    }

    function GetPacketLength()
    {
        return $this->GetTagLen();
    }

    function GetDetailLevel()
    {
        $tag = $this->GetTagByName(EC_TAG_DETAIL_LEVEL);
        return $tag ? $tag->GetInt() : EC_DETAIL_FULL;
    }
}
