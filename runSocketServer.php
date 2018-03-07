<?php

if ($argc > 1) {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

// WebSocket Server Example
if (isset($_GET['SSL'])) {
    $secure = true;
    $keyAndCertFile = '/etc/ssl/certs/certAndKeyFile.pem'; //<= example
    $pathToCert = '/etc/ssl/certs'; //<= example
    $Address = '127.0.01';
    $Port = 8080;
    include "webSocketSecureClass.php";
} else {
    $secure = false;
    $Address = '127.0.0.1';
    $Port = 8080;
    include "webSocketSecureClass.php";
}

class customServer extends WebSocketServer {

    private $packet;

    function onData($SocketID, $M) {
        /*
         * *****************************************
         * $M is JSON like
         * {'opcode':task, <followed by whatever is expected based on the value of opcode>}
         * Thsi is just an example used here , you can send what ever you want.
         * *****************************************
         */
        $packet = json_decode($M);

        if ($packet == NULL) {
            /*
             * *****************************************
             * probably a pong request from a client
             * We see this only when client connects via
             * websocket from IE11 or EDGE.
             * *******************************************
             */
            $this->Log("No data $packet // $M from  #$SocketID");
            return;
        }

        $this->packet = $packet;
        if ($packet->opcode === 'quit') {
            /*
             * *****************************************
             * client quits
             * *****************************************
             */
            $this->Log("QUIT; Connection closed to socket #$SocketID");
            $this->Close($SocketID);
            return;
        }

        if ($packet->opcode === 'uuid') {
            /*
             * *****************************************
             * client registers
             * *****************************************
             */
            $this->Clients[$SocketID]->uuid = $packet->message;
            return;
        }
        if ($packet->opcode === 'feedback') {
            /*
             * *****************************************
             * send feedback to client with uuid found
             * $packet
             * *****************************************
             */
            $this->feedback($packet);
            return;
        }
        /*
         * *****************************************
         * no opcode-> broadcast to all
         * *****************************************
         */
        $this->log("Broadcast $M");
        $this->broadCast($SocketID, $M);
    }

    function onOpen($SocketID) {
        $this->Log("Telling Client to start on  #$SocketID");
        $msg = (object) Array('opcode' => 'ready');
        $this->Write($SocketID, json_encode($msg));
    }

    function feedback($packet) {
        foreach ($this->Clients as $client) {
            if ($packet->uuid == $client->uuid) {
                $this->Write($client->ID, json_encode($packet));
                return;
            }
        }
    }

    function broadCast($SocketID, $M) {
        foreach ($this->Clients as $client) {
            if ($SocketID == $client->ID) {
                continue;
            }
            $this->Write($client->ID, $M);
        }
    }

    function broadCastPong($SocketID) {
        foreach ($this->Clients as $client) {
            if ($SocketID == $client->ID) {
                continue;
            }
            if ($this->Write($client->ID, 'pong') === false) {
                $this->Close($client->ID);
            }
        }
    }

}
/*
 * *****************************************
 * start server 
 * *****************************************
 */
$customServer = new customServer($Address, $Port, $secure);
$customServer->Start();
?>
