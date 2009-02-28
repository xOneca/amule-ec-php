<?php
// Purpose: Testing EC Protocol

require_once('include/ecProto.inc.php');

// First, oepn a socket connection
$socket = new ecSocket('127.0.0.1', 4661);

print "Preparing login packet...\n";
$packet = new ecLoginPacket('Client name', 1, '00000000000000000000000000000000');
print "Sending packet...\n";
$packet->Write($socket);
print "Preparing to receive...\n";
$response = new ecPacket();
print "Receiving...\n";
$response->Read($socket);
print "Response received:\n";
var_dump($response);
print "END\n";