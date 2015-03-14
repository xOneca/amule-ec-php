# amule-ec-php
aMule External Connection implementation in PHP

I was porting aMule's EC code to PHP. With this project we'll be able to manage aMule with PHP scripts, or better, with a web interface (aMule's web server is not full-PHP).

Maybe some day I'll release the 1.0 version... who knows :)

# External Connection

[External Connections](http://wiki.amule.org/wiki/External_Connections) (EC) is a bi-directional interface aMule uses to communicate with external utilities.

## Details

When a user is using one of these programs, it is sending the commands via the External Connections port and aMule is reading them there.

Although there are alternate ways of communicating with aMule, External Connections is the only _bi-directional_ way. Other ways of communication with aMule would be the On-line Signature (outgoing direction) and ED2KLinks file (ingoing direction).
