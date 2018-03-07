<?php

if ($argc > 1) {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

require 'socketTalkSecure.php';

$message = trim($_GET['m']);
if ($message == '') {
    //return;
    $message = 'hallo';
}
$uuid = uniqid('broadcast');
$talk = new socketTalk($uuid, false);

//$talk->talk([]);
//$talk->talk([]);

for ($i = 0; $i < 5; $i++) {

    $talk->talk(['opcode' => $i, 'message' => $i]);
    $talk->talk(['opcode' => 'broadcast', 'message' => $_GET['m']]);
    $talk->talk(['opcode' => 'broadcast', 'message' => $_GET['m']]);
    $talk->talk(['opcode' => 'broadcast', 'message' => $_GET['m']]);
    $talk->talk(['opcode' => 'broadcast', 'message' => $_GET['m']]);
    $talk->talk(['opcode' => 'broadcast', 'message' => $_GET['m']]);
}
//sleep(2);

$talk->talk(['opcode' => 'quit']);

$talk->silent();

