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

//define('PARTSIZE', 9728000);

// RLE implementation. We need only decoder part
class RLE_Data
{
    var $decoded = '';

    function Decode($rle_encoded, $start_offset = 0)
    {
        $len = strlen($rle_encoded);
        $i = $start_offset;
        while($i < $len)
        {
            if($rle_encoded[$i + 1] == $rle_encoded[$i])
            {
                // This is sequence
                $this->decoded .= str_repeat($rle_encoded[$i], ord($rle_encoded[$i + 2]));
                $i += 3;
            }
            else
            {
                $this->decoded .= $rle_encoded[$i];
                $i++;
            }
        }

        return $this->decoded;
    }
}

class PartFileEncoderData
{
    var $part_status;
    var $gap_status;

    function __construct()
    {
        $this->part_status = new RLE_Data();
        $this->gap_status = new RLE_Data();
    }

    function Decode($gapdata, $partdata)
    {
        $this->part_status->Decode($partdata);
        $decoded_gaps = $this->gap_status->Decode($gapdata, 4);
        list(, $num_gaps) = unpack('N', $gapdata);
        for($i = 0; $i < $num_gaps; $i++)
        {
            $gaps = unpack('N2start/N2end', substr($gapdata, $i * 8 + 4));
            $this->gaps[$i]['start'] = $gaps['start1'] << 32 | $gaps['start2'];
            $this->gaps[$i]['end'] = $gaps['end1'] << 32 | $gaps['end2'];
        }
    }
}
