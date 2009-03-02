<?php
// Purpose: Testing EC Protocol

require_once('include/ecProto.inc.php');

// First, oepn a socket connection
$socket = new ecSocket('127.0.0.1', 4661);

print "Preparing login packet...\n";
$packet = new ecLoginPacket('amule-php-remote', '1.0', '3CA7FA9B6781D94D763D07EBFAA5C515');
print "Writting packet...\n";
$packet->Write($socket);
print "Going to send:\n";
print str_dump($socket->buffer);
print "Sending packet...\n";
$socket->SendPacket();

print "Preparing to receive...\n";
$response = new ecPacket();
print "Receiving...\n";
$response->Read($socket);
print "Response received:\n";
// var_dump($response);
if($response->HasSubtags() && $response->SubTag(EC_TAG_SERVER_VERSION)->val)
    print 'Server version: ' . $response->SubTag(EC_TAG_SERVER_VERSION)->val . "\n";

print "Checking server status...\n";
$packet = new ecPacket(EC_OP_GET_CONNSTATE);
$packet->Write($socket);
$socket->SendPacket();

print "Waiting response...\n";
$response = new ecPacket();
$response->Read($socket);
$connstatetag = $response->SubTag(EC_TAG_CONNSTATE);
print 'Server IP: ' . $connstatetag->SubTag(EC_TAG_SERVER)->IP() . "\n";
print 'Server port: ' . $connstatetag->SubTag(EC_TAG_SERVER)->port . "\n";
// print 'Server ping: ' . $connstatetag->SubTag(
var_dump($response);
print "END\n";

// Function to view a string as in an hex viewer
function str_dump($str)
{
    $print = array();
    $lines = str_split($str, 16);

    foreach($lines as $n => $line)
    {
        $print[$n] = '';
        for($i = 0; $i < strlen($line); $i++)
        {
            $print[$n] .= bin2hex($line{$i}) . ' ';
            if(($i+1) % 8 == 0 && $i != 0)
                $print[$n] .= ' '; // extra space
        }

        $print[$n] = sprintf('%- 50s', $print[$n]);

        for($i = 0; $i < strlen($line); $i++)
        {
            if(ord($line{$i}) > 0x1f && ord($line{$i}) < 0x7f)
                $print[$n] .= $line{$i};
            else
                $print[$n] .= '.'; // non-printable char
            if(($i+1) % 8 == 0 && $i != 0)
                $print[$n] .= ' ';
        }
    }

    return implode("\n", $print) . "\n";
}
