# phpWebSocketServer
Server witten in PHP that can handle connections via websocksets and normal sockets.
The original version was implemented by Bryan Bliewert, nVentis@GitHub
https://github.com/nVentis/PHP-WebSocketServer
Credits go to him.
I have just made some minor modifiacations.

## webSocketClass.php##

This implements a server that connects a to system/host that has <b>no<b> certificate implemented.
Accepts cleint connectiosn via <b>ws://</b> and normal sockets connections from a php process.
<b>socketTal.php</b>

## webSocketSecureClass.php##

This implements a server that connects to a system/host that has a <b>certificate implemented</b> 
Accepts only client connectiosn via <b>wss://</b> and sockets connections from a php process via <b>ssl://</b>.
See client class <b>socketTalkSecure.php</b>
