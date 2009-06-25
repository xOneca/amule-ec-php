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
function RLE_Decode($rle_encoded, $start_offset = 0)
{
    $len = strlen($rle_encoded);
    $i = $start_offset;
    while($i < $len)
    {
        if($rle_encoded[$i + 1] == $rle_encoded[$i])
        {
            // This is sequence
            $decoded .= str_repeat($rle_encoded[$i], ord($rle_encoded[$i + 2]));
            $i += 3;
        }
        else
        {
            $decoded .= $rle_encoded[$i];
            $i++;
        }
    }

    return $decoded;
}
