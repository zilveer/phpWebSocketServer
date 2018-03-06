# phpWebSocketServer
Server witten in PHP that can handle connections via websocksets and normal sockets.
The original version was implemented by Bryan Bliewert, nVentis@GitHub
https://github.com/nVentis/PHP-WebSocketServer
Full Credits go to him.
I have just made some minor modifications and implemented the <b>secure version</b>.

The code given here is used in production running on a Linuxbox with SSL as well as
on a Windows-10 system with no certificate. Up to now we have no problems at all.
The setup is used to have several Web-Apps talk to each other via the server as well as having php processses
triggert by AJAX, talking back to the Web-Apps using the same server. 


## webSocketSecureClass.php

This implements a server that connects to a system/host that has a <b>certificate implemented</b> 
Accepts only client connectiosn via <b>wss://</b> and sockets connections from a php process via <b>ssl://</b>.
See client class <b>socketTalkSecure.php</b>

## webSocketClass.php

This implements a server that connects a to system/host that has <b>no<b> certificate implemented.
Accepts cleint connectiosn via <b>ws://</b> and normal sockets connections from a php process.
<b>socketTalk.php</b>

## Usage 
If time permits ... soon to come :-)
