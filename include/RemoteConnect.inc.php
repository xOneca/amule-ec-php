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

/// Purpose: Connection controller

require_once('ECPacket.inc.php');

class CECLoginPacket extends CECPacket
{
    function __construct($pass, $client, $version)
    {
        parent::__construct(EC_OP_AUTH_REQ);

        $this->AddTag(new CECTag(EC_TAG_CLIENT_NAME, $client));
        $this->AddTag(new CECTag(EC_TAG_CLIENT_VERSION, $version));
        $this->AddTag(new CECTag(EC_TAG_PROTOCOL_VERSION, EC_CURRENT_PROTOCOL_VERSION));

        $passhash = new CMD4Hash();
        $passhash->Decode($pass);
        $this->AddTag(new CECTag(EC_TAG_PASSWD_HASH, $passhash));
    }
}

class CRemoteConnect
{
    var $ec_state;
    var $req_count;
    var $connectionPassword;
    var $server_reply;
    var $client;
    var $version;

//     function __construct(){}
    function ConnectToCore($host, $port, $login, $pass, $client, $version)
    {
        $this->connectionPassword = $pass;
        $this->client = $client;
        $this->version = $version;

        // Don't try to connect without a valid password
        if($pass == '' || $pass == 'd41d8cd98f00b204e9800998ecf8427e') // md5sum('')
            return false

        $hash = new CMD4Hash();
        if(!$hash->Decode($pass))
            return false;
        elseif($hash->IsEmpty())
            return false;

        if(fsockopen($host, $port)){}
    }

    function GetServerReply()
    {
        return $this->server_reply;
    }

    function SendPacket($request){}

    function ShutDown(){}
    function Ed2kLink($link){}
    function StartKad(){}
    function StopKad(){}
    function ConnectED2K($ip, $port){}
    function DisconnectED2K(){}
    function AddServer($ip, $port){}
    function RemoveServer($ip, $port){}
    function GetServerList(){}
    function UpdateServerList($url){}
    function StartSearch(){}
    function StopSearch(){}
    function GetSearchProgress(){}
    function DownloadSearchResult($file){}
    function GetStatistics(){}
    function GetConnectionState(){}
    // ...
}
