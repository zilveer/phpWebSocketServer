<?php

if ($argc > 1) {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

require 'socketPhpClient.php';

$message = trim($_GET['m']);
if ($message == '') {
    //return;
    $message = 'hallo';
}
$secure = false;
if (isset($_GET['SSL'])) {
    $secure = true;
}
$uuid = uniqid('broadcast');
$talk = new socketTalk($uuid, $secure); 
$talk->talk(['opcode' => 'broadcast', 'message1' => $_GET['m']]);   
$talk->talk(['opcode' => 'broadcast', 'message2' => $_GET['m']]);   
$talk->talk(['opcode' => 'broadcast', 'message3' => $_GET['m']]);   
$talk->talk(['opcode' => 'broadcast', 'message4' => $_GET['m']]);   
$talk->talk(['opcode' => 'broadcast', 'message5' => $_GET['m']]);   
$talk->talk(['opcode' => 'quit']);

$talk->silent();

