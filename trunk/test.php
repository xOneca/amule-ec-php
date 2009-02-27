<?php
// Purpose: Testing EC Protocol

require_once('include/ecProto.inc.php');

// First, oepn a socket connection
$socket = fsockopen('192.168.0.2', 4661);

$packet = new ecLoginPacket('Client name', 1, '');

$packet->Write($socket);

$response = new ecPacket();
$response->Read($socket);
var_dump($response);
