<?php

class socketTalk {

    public $uuid, $connected = false;

    function __construct($uuid) {
        global $Address, $Port;
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        stream_context_set_option($context, 'ssl', 'verify_peer', false);
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);

        $this->socketMaster = stream_socket_client("ssl://$Address:$Port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

        if (!$this->socketMaster) {
            echo $errstr;
            return;
        }
        $this->uuid = $uuid;
        $this->connected = true;
        fwrite($this->socketMaster, "php process\n\n");
    }

    function talk($msg) {
        if ($this->connected) {
            $json = json_encode((object) Array('opcode' => 'feedback', 'uuid' => $this->uuid, 'message' => $msg));
            fwrite($this->socketMaster, ($json));
        }
    }

    function talkDirect($uuid, $msg) {
        $this->uuid = $uuid;
        $this->talk($msg);
    }

    function silent() {
        if ($this->connected) {
            $this->talk('');
            stream_socket_shutdown($this->socketMater, STREAM_SHUT_RDWR);
        }
    }

}
