<?php

if ($argc > 1) {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

require 'socketPhpClient.php';

$message = trim($_GET['m']);
if ($message == '') {
    //return;
    $message = 'hallo from PHP';
}
$secure = false;
if (isset($_GET['SSL'])) {
    $secure = true;
}
$uuid = uniqid('broadcast');
$talk = new socketTalk($uuid, $secure); 
$talk->talk(['opcode' => 'broadcast', 'message1' => $message]);   
$talk->talk(['opcode' => 'broadcast', 'message2' => $message]);   
$talk->talk(['opcode' => 'broadcast', 'message3' => $message]);   
$talk->talk(['opcode' => 'broadcast', 'message4' => $message]);   
$talk->talk(['opcode' => 'broadcast', 'message5' => $message]);   
$talk->talk(['opcode' => 'quit']);

$talk->silent();

