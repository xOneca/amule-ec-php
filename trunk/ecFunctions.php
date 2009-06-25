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

/// Purpose: Functions for interacting with aMule

require_once('include/ecProto.inc.php');

class ecProtocol
{
    var $socket = false;
    var $response;
    var $server_version;

    /**
     * ecProtocol constructor
     *
     * Makes the host connection.
     *
     * @param $host Host name or IP address.
     * @param $port Host port to connect to.
     *
     * @return ecProtocol object or false on error.
     */
    function __construct($host, $port)
    {
        $this->socket = new ecSocket($host, $port);
        if($this->socket === false) return false;
    }

    /**
     * Log in to aMule EC
     *
     * @param $client_name    Name of your script that will appear in
     *                        the host log.
     * @param $client_version Version of your script that will appear in
     *                        the host log.
     * @param $passwd         MD5-password string to log in to the host.
     *
     * @return True on success. False otherwise.
     *
     * NOTE: This function sets server_version attribute on success.
     * WARNING: If you supply wrong password the connection will be closed
     *  suddenly by the server.
     */
    function Login($client_name, $client_version, $passwd)
    {
        if($this->socket === false) return false;

        $login = new ecLoginPacket($client_name, $client_version, $passwd);
        $login->Write($this->socket);
        $this->socket->SendPacket();

        $this->response = new ecPacket();
        $this->response->Read($this->socket);

        if($this->response->Opcode() == EC_OP_AUTH_OK){
            $this->server_version = $this->response->SubTag(EC_TAG_SERVER_VERSION)->Value();
            return true;
        }

        return false;
    }

    /**
    * Request download queue
    *
    * @return Response packet (ecPacket object)
    *
    * TODO: Maybe better return an array?
    */
    function DownloadsInfoReq()
    {
        if($this->socket === false) return false;

        $request = new ecPacket(EC_OP_GET_DLOAD_QUEUE);
        $request->Write($this->socket);
        $this->socket->SendPacket();

        $this->response = new ecPacket();
        $this->response->Read($this->socket);

        if($this->response->Opcode() != EC_OP_DLOAD_QUEUE)
            return false;
var_dump($this->response);
        if(!$this->response->HasSubtags())
            return array();

        foreach($this->response->subtags as $download)
        {
            $downloads[] = new ecPartFileTag($download);
        }
        return $downloads;
    }

//     function ...
}


// Taken from amule text client
/**
 * @param $response ecPacket object
 */
function process_answer($response)
{
    switch($response->Opcode())
    {
        case EC_OP_NOOP:
            print "Operation was successful.\n";
            return true;
            break;

        case EC_OP_FAILED:
            if($response->GetTagCount())
            {
                if($tag = $response->subtags[0]){
                    printf("Request failed with the following error: %s\n", $tag->Value());
                }
                else{
                    print "Request failed with an unknown error.\n";
                }
            }
            else{
                print "Request failed with an unknown error.\n";
            }
            return false;
            break;

        case EC_OP_SET_PREFERENCES:
        case EC_OP_STRINGS:
        case EC_OP_STATS:
            print "** NOT YET IMPLEMENTED **";
            break;

        case EC_OP_DLOAD_QUEUE:
            foreach($response->subtags as $tag){
                // $tag == PartFile Tag
            }
            print "** NOT YET IMPLEMENTED **";
            break;

        case EC_OP_ULOAD_QUEUE:
            $uploads = array();
            foreach($response->subtags as $tag){
                $clientName = $tag->GetTagByName(EC_TAG_CLIENT_NAME);
                $partfileName = $tag->GetTagByName(EC_TAG_PARTFILE_NAME);
                $partfileSizeXfer = $tag->GetTagByName(EC_TAG_PARTFILE_SIZE_XFER);
                $partfileSpeed = $tag->GetTagByName($EC_TAG_CLIENT_UP_SPEED);
                if($tag && $clientName && $partfileName && $partfileSizeXfer && $partfileSpeed){
                    printf('%10u ', $tag->Value());
                    printf('%s ', $clientName->Value());
                    printf('%s ', $partfileName->Value());
                    printf('%d ', $partfileSizeXfer->Value());
                    printf("%d\n", $partfileSpeed->Value());
                    $uploads[] = array(
                        $tag->Value(),
                        $clientName->Value(),
                        $partfileName->Value(),
                        $partfileSizeXfer->Value(),
                        $partfileSpeed->Value()
                    );
                }
            }
            return $uploads;
            break;

        case EC_OP_LOG:
            $log = array();
            foreach($response->subtags as $tag){
                printf("%s\n", $tag->Value());
                $log[] = $tag->Value();
            }
            return $log;
            break;

        case EC_OP_SERVER_LIST:
            $serverlist = array();
            foreach($response->subtags as $tag){
                $serverName = $tag->GetTagByName(EC_TAG_SERVER_NAME);
                printf("[%15s] %s\n", $tag->IP(), $serverName->Value());
                $serverlist[] = array($tag->IP(), $serverName->Value());
            }
            return $serverlist;
            break;

        case EC_OP_STATSTREE:
            $tree = $response->GetTagByName(EC_TAG_STATTREE_NODE);
            // $tree == StatTree Node Tag
            print "** NOT YET IMPLEMENTED **";
            break;

        case EC_OP_SEARCH_RESULTS:
            printf("Number of search results: %i\n", $response->GetTagCount());
            foreach($response->subtags as $tag){
                // $tag == SearchFile Tag
            }
            print "** NOT YET IMPLEMENTED **";
            break;

        case EC_OP_SEARCH_PROGRESS:
            $tag = $response->GetTagByName(EC_TAG_SEARCH_STATUS);
            printf("Search progress: %u%%", $tag->Value());
            return $tag->Value();
            break;

        default:
            printf("Received an unknown reply from the server, OpCode = %x.\n", $response->Opcode());
    }
}
