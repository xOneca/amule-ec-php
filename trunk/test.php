<?php
// Purpose: Testing EC Protocol

require_once('include/ecProto.inc.php');

// First, oepn a socket connection
$socket = new ecSocket('127.0.0.1', 4661);

print "Preparing login packet...\n";
$packet = new ecLoginPacket('amule-remote', '2.2.3', '3CA7FA9B6781D94D763D07EBFAA5C515');
print "Writting packet...\n";
$packet->Write($socket);
print "Going to send:\n";
print "[" . str_dump($socket->buffer) . "]\n";
print "[" . bin2hex($socket->buffer) . "]\n";
print "Sending packet...\n";
$socket->SendPacket();

print "Preparing to receive...\n";
$response = new ecPacket();
print "Receiving...\n";
$response->Read($socket);
print "Response received:\n";
var_dump($response);
print "END\n";

function str_dump($str)
{
    $print = '';
    for($i = 0; $i < strlen($str); $i++){
        $c = $str{$i};
        if(($c >= '0' && $c <= '9') ||
           ($c >= 'a' && $c <= 'z') ||
           ($c >= 'A' && $c <= 'Z'))
            $print .= $c;
        elseif(($c < ' ' && $c != "\n" && $c != "\r") || $c > chr(0x7f))
            $print .= '.';
        else
            $print .= $c;
    }

    return $print;
}