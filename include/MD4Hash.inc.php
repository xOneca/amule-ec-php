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

/// Purpose: Implementation of MD4 Hash


define('MD4HASH_LENGTH', 16);

/**
 * Container-class for the MD4 hashes.
 *
 * This is a safe representation of the MD4 hashes used in aMule.
 *
 * Please remember that the hashes are strings with length 16 WITHOUT a zero-terminator!
 */
class CMD4Hash
{
    /**
     * The raw MD4-hash.
     *
     * The raw representation of the MD4-hash. In most cases, you should
     * try to avoid direct access and instead use the member functions.
     */
    var $sHash;

    /**
     * Class constructor.
     *
     * @param string Raw MD4 hash or hex string to store.
     *
     * Creates MD4Hash class from the (optionally) passed string data.
     */
    function __construct($string='')
    {
        if(strlen($string) == MD4HASH_LENGTH) // Treat as raw md4 hash
            $this->sHash = $string;
        elseif(strlen($string) == MD4HASH_LENGTH * 2) // Treat as hex string
            if(!$this->Decode($string)) return false;
    }

    /**
     * Returns true if the hash is empty.
     *
     * @return True if all fields are zero, false otherwise.
     *
     * This functions checks the contents of the hash and returns true
     * only if each field of the array contains the value zero.
     * To achive an empty hash, the function Clear() can be used.
     */
    function IsEmpty()
    {
        return ($this->sHash === '');
    }

    /**
     * Resets the contents of the hash.
     *
     * This functions sets the value of the hash to zero.
     * IsEmpty() will return true after a call to this function.
     */
    function Clear()
    {
        $this->sHash = '';
    }

    /**
     * Decodes a 32-char-long hexadecimal representation of a MD4 hash.
     *
     * @param hash The hash representation to be converted. Length must be 32.
     * @return Return value specifies if the hash was succesfully decoded.
     *
     * This function converts a hexadecimal representation of a MD4
     * hash and stores it in sHash.
     */
    function Decode($hash)
    {
        if(strlen($hash) != MD4HASH_LENGTH * 2)
            return false;

        // Only these chars are allowed
        $hex = '0123456789ABCDEF';
        for($i = 0; $i < MD4HASH_LENGTH * 2; $i++){
            $char = strtoupper($hash{$i});

            // Not found
            if(strpos($hex, $char) === false)
                return false;
        }

        // Store as binary data
        $this->sHash = pack('H*', $hash);
        return true;
    }

    /**
     * Creates a 32-char-long hexadecimal representation of a MD4 hash.
     *
     * @return Hexadecimal representation of sHash.
     *
     * This function creates a hexadecimal representation of the MD4
     * hash stored in sHash and returns it.
     */
    function EncodeSTL()
    {
        list(,$hash) = unpack('H*', $this->sHash);
        return $hash;
    }

    /**
     * Explicitly set the hash to the contents of a raw hash.
     *
     * @param hash The raw hash to be assigned.
     *
     * @return true on success, false otherwise.
     */
    function SetHash($hash)
    {
        if(strlen($hash) == MD4HASH_LENGTH){
            $this->sHash = $hash;
            return true;
        }
        else
            return false;
    }

    /**
     * Explicit access to the hash.
     *
     * @return The hash.
     */
    function GetHash()
    {
        return $this->sHash;
    }
}
