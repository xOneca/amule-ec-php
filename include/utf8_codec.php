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

/// Purpose: encode and decode numbers from and to utf8

// Translating from C++
// But I was thinking of using PHP's utf8_encode and utf8_decode
// for this purpose...
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

$utf8_table = array(
    new utf8_table(0x80,  0x00,   0*6,    0x7F,           0),        /* 1 byte sequence */
    new utf8_table(0xE0,  0xC0,   1*6,    0x07FF,          0x80),     /* 2 byte sequence */
    new utf8_table(0xF0,  0xE0,   2*6,    0x00FFFF,         0x800),    /* 3 byte sequence */
    new utf8_table(0xF8,  0xF0,   3*6,    0x001FFFFF,       0x10000),  /* 4 byte sequence */
    new utf8_table(0xFC,  0xF8,   4*6,    0x0003FFFFFF,      0x200000), /* 5 byte sequence */
    new utf8_table(0xFE,  0xFC,   5*6,    0x00007FFFFFFF,     0x4000000) /* 6 byte sequence */
);

/**
 * How many 8-bit chars are left to complete the UTF-8 code
 */
function utf8_chars_left($char)
{
    global $utf8_table;

    foreach($utf8_table as $k => $v)
        if(($char & $v->cmask) == $v->cval) return $k;

    return false; // This should not happen
}

/**
 * Read UTF-8 number from socket
 */
function read_utf8($socket)
{
    // Take first char and guess remaining bytes
    // If there are remaining bytes, read them too
    // Discard utf8 information from characters and
    // join them into one integer

  global $utf8_table;
  $nc = 0;

  foreach($utf8_table as $t)
  {
      $nc++;
      
  }
}
