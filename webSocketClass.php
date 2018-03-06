<?php

// WebSocketServer implementation in PHP
// by Bryan Bliewert, nVentis@GitHub
// https://github.com/nVentis/PHP-WebSocketServer
// with minor modifications by me

class WebSocketClient {

    // get according socket in WebSocketServer
    //  using $this->Sockets[$Client->ID]
    public
            $ID,
            $uuid,
            $Headers = null,
            $Handshake = null,
            $timeCreated = null;

    function __construct($Socket) {
        $this->ID = intval($Socket);
        $this->timeCreated = time();
    }

}

class WebSocketServer {

    public
            $logToFile = false,
            $logFile = "log.txt",
            $logToDisplay = true,
            $Sockets = array(),
            $bufferLength = 4069 * 100,
            $maxClients = 20,
            // applied with Start()
            $errorReport = E_ALL,
            $timeLimit = 0,
            $implicitFlush = true;
    protected
            $Address,
            $Port,
            $socketMaster,
            $Clients = array();

    function __construct($Address, $Port) {
        $this->socketMaster = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!is_resource($this->socketMaster)) {
            $this->Log("The master socket could not be created: " . socket_strerror(socket_last_error()), true);
        }
        socket_set_option($this->socketMaster, SOL_SOCKET, SO_REUSEADDR, 1);
        if (!socket_bind($this->socketMaster, $Address, $Port)) {
            $this->Log("Can't bind on master socket: " . socket_strerror(socket_last_error()), true);
        }
        if (!socket_listen($this->socketMaster, $this->maxClients)) {
            $this->Log("Can't listen on master socket: " . socket_strerror(socket_last_error()), true);
        }

        $this->Sockets["m"] = $this->socketMaster;
        $this->Log("Server initilaized on $Address:$Port");
    }

    public function Start() {
        $this->Log("Starting server...");
        error_reporting($this->errorReport);
        set_time_limit($this->timeLimit);
        if ($this->implicitFlush) {
            ob_implicit_flush();
        }

        while (true) {
            if (empty($this->Sockets)) {
                $this->Sockets["m"] = $this->socketMaster;
            }
            $socketArrayRead = $this->Sockets;
            $socketArrayWrite = $socketArrayExceptions = NULL;
            // by-ref function, thus we can now iterate over the array
            @socket_select($socketArrayRead, $socketArrayWrite, $socketArrayExceptions, NULL);

            foreach ($socketArrayRead as $Socket) {
                $SocketID = intval($Socket);
                if ($Socket == $this->socketMaster) {
                    $Client = socket_accept($Socket);
                    if (!is_resource($Client)) {
                        $this->onError($SocketID, "Connection could not be established");
                        continue;
                    } else {
                        $this->addClient($Client);
                        $this->onOpening($SocketID);
                    }
                } else {
                    $receivedBytes = @socket_recv($Socket, $dataBuffer, $this->bufferLength, 0);
                    if ($receivedBytes === false) {
                        // on error
                        $sockerError = socket_last_error($Socket);
                        $socketErrorM = socket_strerror($sockerError);
                        if ($sockerError >= 100) {
                            $this->onError($SocketID, "Unexpected disconnect with error $sockerError [$socketErrorM]");
                            $this->Close($Socket);
                        } else {
                            $this->onOther($SocketID, "Other socket error $sockerError [$socketErrorM]");
                            $this->Close($Socket);
                        }
                    } elseif ($receivedBytes == 0) {
                        // no headers received (at all) --> disconnect
                        $SocketID = $this->Close($Socket);
                        $this->onError($SocketID, "Client disconnected - TCP connection lost");
                    } else {
                        // no error, --> check handshake
                        $Client = $this->getClient($Socket);
                        //$this->Log("Client $SocketID is known - Handshake : " . (($Client->Handshake == false) ? "NO" : "TRUE" ));
                        if ($Client->Handshake == false) {
                            if (strpos(str_replace("\r", '', $dataBuffer), "\n\n") === false) { // headers have not been completely received --> wait --> handshake
                                $this->onOther($SocketID, "Continue receving headers");
                                continue;
                            }
                            $this->Handshake($Socket, $dataBuffer);
                        } else {
                            $this->log("Received bytes = $receivedBytes");
                            $this->Read($SocketID, $dataBuffer, $Socket);
                        }
                    }
                }
            }
        }
    }

    public function Log($M) {

        if ($this->logToFile) {
            $M = "[" . date(DATE_RFC1036, time()) . "] - $M \r\n";
            file_put_contents($this->logFile, $M, FILE_APPEND);
        }
        if ($this->logToDisplay) {
            $M = "[" . date(DATE_RFC1036, time()) . "] - $M \r\n";
            echo $M;
        }
    }

    protected function addClient($Socket) {
        $wantCi = intval($Socket);
        $this->Clients[$wantCi] = new WebSocketClient($Socket);
        $this->Sockets[$wantCi] = $Socket;
        return $wantCi;
    }

    protected function getClient($Socket) {
        return $this->Clients[intval($Socket)];
    }

    public function Close($Socket) {
        //socket_close($Socket);
        socket_shutdown($Socket);
        $SocketID = intval($Socket);
        unset($this->Clients[$SocketID]);
        unset($this->Sockets[$SocketID]);
        $this->onClose($SocketID);
        return $SocketID;
    }

    protected function Handshake($Socket, $Buffer) {
        $this->Log('Handshake:' . $Buffer);

        if ($Buffer == "php process\n\n") {
            $SocketID = intval($Socket);
            $this->Clients[$SocketID]->Headers = 'php process';
            $this->Clients[$SocketID]->Handshake = $Buffer;
            $this->onOpen($SocketID);
            return;
        }
        $addHeader = '';
        $SocketID = intval($Socket);
        $magicGUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
        $Headers = array();
        $Lines = explode("\n", $Buffer);
        foreach ($Lines as $Line) {
            if (strpos($Line, ":") !== false) {
                $Header = explode(":", $Line, 2);
                $Headers[strtolower(trim($Header[0]))] = trim($Header[1]);
            } else if (stripos($Line, "get ") !== false) {
                preg_match("/GET (.*) HTTP/i", $Buffer, $reqResource);
                $Headers['get'] = trim($reqResource[1]);
            }
        }

        if (!isset($Headers['host']) ||
                !isset($Headers['sec-websocket-key']) ||
                (!isset($Headers['upgrade']) || strtolower($Headers['upgrade']) != 'websocket') ||
                (!isset($Headers['connection']) || strpos(strtolower($Headers['connection']), 'upgrade') === FALSE)) {
            $addHeader = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($Headers['sec-websocket-version']) || strtolower($Headers['sec-websocket-version']) != 13) {
            $addHeader = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
        }
        if (!isset($Headers['get'])) {
            $addHeader = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";
        }
        if ($addHeader !== '') {
            $this->Log($addHeader);
            @socket_write($Socket, $addHeader, strlen($addHeader));
            $this->onError($SocketID, "Handshake aborted - [" . trim($addHeader) . "]");
            return $this->Close($Socket);
        }
        $Token = "";
        $sha1 = sha1($Headers['sec-websocket-key'] . $magicGUID);
        for ($i = 0; $i < 20; $i++) {
            $Token .= chr(hexdec(substr($sha1, $i * 2, 2)));
        }
        $Token = base64_encode($Token) . "\r\n";
        $addHeader = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $Token\r\n";
        @socket_write($Socket, $addHeader, strlen($addHeader));

        $this->Clients[$SocketID]->Headers = $Headers;
        $this->Clients[$SocketID]->Handshake = $Buffer;
        $this->onOpen($SocketID);
    }

    protected function Encode($M) {
        // inspiration for Encode() method : 
        // http://stackoverflow.com/questions/8125507/how-can-i-send-and-receive-websocket-messages-on-the-server-side
        $L = strlen($M);
        $bHead = [];
        $bHead[0] = 129; // 0x1 text frame (FIN + opcode)
        if ($L <= 125) {
            $bHead[1] = $L;
        } else if ($L >= 126 && $L <= 65535) {
            $bHead[1] = 126;
            $bHead[2] = ( $L >> 8 ) & 255;
            $bHead[3] = ( $L ) & 255;
        } else {
            $bHead[1] = 127;
            $bHead[2] = ( $L >> 56 ) & 255;
            $bHead[3] = ( $L >> 48 ) & 255;
            $bHead[4] = ( $L >> 40 ) & 255;
            $bHead[5] = ( $L >> 32 ) & 255;
            $bHead[6] = ( $L >> 24 ) & 255;
            $bHead[7] = ( $L >> 16 ) & 255;
            $bHead[8] = ( $L >> 8 ) & 255;
            $bHead[9] = ( $L ) & 255;
        }
        return (implode(array_map("chr", $bHead)) . $M);
    }

    public function Write($SocketID, $M) {
        if ($this->Clients[$SocketID]->Headers != 'php process') {
            $M = $this->Encode($M);
        }
        if (socket_write($this->Sockets[$SocketID], $M, strlen($M)) === false) {
            return false;
        }
        return true;
    }

    private function Decode($payload) {
        $length = ord($payload[1]) & 127;
        $this->log("Payload length=$length");
        if ($length == 126) {
            $masks = substr($payload, 4, 4);
            $data = substr($payload, 8);
        } else if ($length == 127) {
            $masks = substr($payload, 10, 4);
            $data = substr($payload, 14);
        } else {
            $masks = substr($payload, 2, 4);
            //     $data = substr($payload, 6;
            $data = substr($payload, 6, $length); // hgs 30.09.2016
        }
        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    public function Read($SocketID, $M) {
        if ($this->Clients[$SocketID]->Headers != 'php process') {
            $this->onData($SocketID, $this->Decode($M));
        } else {
            $this->onData($SocketID, ($M));
        }
    }

    // Methods to be configured by the user; executed directly after...
    function onOpen($SocketID) { //...successful handshake
        $this->Log("Handshake with socket #$SocketID successful");
    }

    function onData($SocketID, $M) { // ...message receipt; $M contains the decoded message
        $this->Log("Received " . strlen($M) . " Bytes from socket #$SocketID");
    }

    function onClose($SocketID) { // ...socket has been closed AND deleted
        $this->Log("Connection closed to socket #$SocketID");
    }

    function onError($SocketID, $M) { // ...any connection-releated error
        $this->Log("Socket $SocketID - " . $M);
    }

    function onOther($SocketID, $M) { // ...any connection-releated notification
        $this->Log("Socket $SocketID - " . $M);
    }

    function onOpening($SocketID) { // ...being accepted and added to the client list
        $this->Log("New client connecting on socket #$SocketID");
    }

}

?>
