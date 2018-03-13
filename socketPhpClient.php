<?php

$Address = 'localhost';
$Port = '8080';

class socketTalk {

    public $uuid, $connected = false;

    function __construct($uuid, $SSL = true) {
        global $Address, $Port;
        if ($SSL) {
            $context = stream_context_create();
            stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
            stream_context_set_option($context, 'ssl', 'verify_peer', false);
            stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
            $this->socketMaster = stream_socket_client("ssl://$Address:$Port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        } else {
            $Address = 'localhost';
            $Port = '8080';
            $this->socketMaster = stream_socket_client("tcp://$Address:$Port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT);
        }

        if (!$this->socketMaster) {
            echo $errstr;
            return;
        }
        $this->uuid = $uuid;
        $this->connected = true;

        fwrite($this->socketMaster, "php process\n\n");
        $buff = fread($this->socketMaster, 1024);
    }

    function talk($msg) {
        if ($this->connected) {

            $json = json_encode((object) $msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            $l = sprintf("%'032d", strlen($json));
            /*
             * *****************************************
             * The server expects a 32 character long buffer
             * first with the the length of the json data to
             * come. Send length first 
             * *****************************************
             */
            $what = fwrite($this->socketMaster, $l . '');
            /*
             * *****************************************
             * send data now
             * *****************************************
             */
            $what = fwrite($this->socketMaster, $json);
        }
    }

    function silent() {
        if ($this->connected) {
            fclose($this->socketMaster);
        }
    }

}
