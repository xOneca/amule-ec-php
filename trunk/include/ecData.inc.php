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

/// Purpose: Run Length Encoding (RLE) implementation

// RLE implementation. We need only decoder part
class RLE_Data
{
    var $use_diff = false;
    var $enc_buff = '';
    var $buff = '';

    function __construct($use_diff = false)
    {
        $this->use_diff = ($use_diff) ? true : false;
    }

    function Decode($buff, $start_offset = 0)
    {
        $len = strlen($buff);
        $i = $start_offset;
        while($i < $len)
        {
            if($buff[$i + 1] == $buff[$i])
            {
                // This is sequence
                $this->enc_buff .= str_repeat($buff[$i], ord($buff[$i + 2]));
                $i += 3;
            }
            else
            {
                $this->enc_buff .= $buff[$i];
                $i++;
            }
        }

        if($this->use_diff)
        {
            for($k = 0; $k < $this->len; $k++)
                $this->buff[$k] ^= $this->enc_buff[$k];
        }
    }
}

class PartFileEncoderData
{
    var $part_status;
    var $gap_status;

    function __construct()
    {
        $this->part_status = new RLE_Data(true);
        $this->gap_status = new RLE_Data(true);
    }

    function Decode($gapdata, $partdata)
    {
        $this->part_status->Decode($partdata);
        $this->gap_status->Decode($gapdata, 4);
    }
}
