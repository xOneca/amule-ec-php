<?php
// Purpose: Testing EC Protocol
header('Content-Type: text/plain');


//require_once('include/ecProto.inc.php');
require_once('ecFunctions.php');

$ec = new ecProtocol('127.0.0.1', 4661); // host, port
if($ec->Login('amule-php-remote-test'.rand(1000, 9999), '1.0', '3CA7FA9B6781D94D763D07EBFAA5C515'))
{
    // Log in successful.
    print("Log in successful.\n");
    $downloads = $ec->DownloadsInfoReq();
    if(count($downloads->subtags))
    {
        foreach($downloads->subtags as $k => $downloading_file)
        {
            $dl[] = $file = new ecPartFileTag($downloading_file);
            print($file->name . ":\n");
            print(str_dump($file->dwn_sts->gap_status->enc_buff));
            var_dump($file->dwn_sts->gaps);
//             print("File $k:\n".str_dump($downloading_file->SubTag(EC_TAG_PARTFILE_GAP_STATUS)->Value())."\n");
        }
    }
    print("\n\$downloads object:\n");
    var_dump($downloads);
}
else
{
    // Log in faliled.
    print("Log in failed. Maybe wrong password?\n");
    var_dump($ec->response);
}

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
            $print[$n] .= bin2hex($line[$i]) . ' ';
            if(($i+1) % 8 == 0 && $i != 0)
                $print[$n] .= ' '; // extra space
        }

        $print[$n] = sprintf('%- 50s', $print[$n]);

        for($i = 0; $i < strlen($line); $i++)
        {
            if(ord($line[$i]) > 0x1f && ord($line[$i]) < 0x7f)
                $print[$n] .= $line[$i];
            else
                $print[$n] .= '.'; // non-printable char
            if(($i+1) % 8 == 0 && $i != 0)
                $print[$n] .= ' ';
        }
    }

    return implode("\n", $print) . "\n";
}
