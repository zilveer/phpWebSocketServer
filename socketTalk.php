<?php

class socketTalk {

    public $uuid, $connected = false;

    function __construct($uuid) {
        global $Address, $Port;
        $this->socketMaster = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (@socket_connect($this->socketMaster, $Address, $Port)) {
            $what = socket_write($this->socketMaster, "php process\n\n");
            $what = socket_read($this->socketMaster, 12);
            $this->uuid = $uuid;
            $this->connected = true;
        } else {
            $error = socket_last_error($this->socketMaster);
        }
    }

    function talk($msg) {
        if ($this->connected) {
            $json = json_encode((object) Array('opcode' => 'feedback', 'uuid' => $this->uuid, 'message' => $msg));
            $what = socket_write($this->socketMaster, ($json));
        }
    }

    function talkDirect($uuid, $msg) {
        $this->uuid = $uuid;
        $this->talk($msg);
    }

    function silent() {
        if ($this->connected) {
            $this->talk('');
            socket_close($this->socketMaster);
        }
    }

}
