# phpWebSocketServer

Server witten in PHP that can handle connections via websocksets and normal sockets.
The original version was implemented by Bryan Bliewert, nVentis@GitHub
https://github.com/nVentis/PHP-WebSocketServer
Full Credits go to him.
I have just made some minor modifications and implemented the <b>secure version</b>.

The code given here is used in test, running on a Linuxbox with SSL as well as
on a Windows-10 system with no certificate. Up to now we have no problems at all.
The setup is used to have several Web-Apps talk to each other via the server as well as having php processses
triggert by AJAX, talking back to the Web-Apps using the same server. 

# Still under development !!

Use at own risc. 

## webSocketServer.php

This implements a server that ,by default, connects to a system/host that has a <b>certificate implemented</b> <br>
Accepts only client connectiosn via <b>wss://</b> and sockets connections from a php process via <b>ssl://</b>.
<br>
You can also use this to just accept connections via  <b>ws:// </b> and  <b>tcp:// </b>
<br>

## runSocketServer.php

This extends and customizes webSocketSecureClass.php and starts the server.

## socketPhpClient.php

This is class that allows you to talk to a SocketServer from a php process using ssl://
by default, or just tcp://

## testWithPHPSockets.php

This shows usage of socketPhpClient.php

## socketWebClient.js

Javascript function that establish the connection via WebSocket and
reveals some functions upon return.
<NOTE> If this function returns, it might still be in the 
process of connecting to the server, therefor it is not guaranteed
that the socket is ready to be used. This is checked outside this
function.  

## testWithwebSockets.php

This shows usage of socketWebClient.js
<note> Here we are busy waiting using setTimeout() to check if the socket is 
open or if we get an error, in both cases we no longer wait for the socket connection.






## Usage 
If time permits ... soon to come :-)
