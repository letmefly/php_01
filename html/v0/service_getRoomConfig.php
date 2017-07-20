<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
if ($clientIp != "127.0.0.1") {
	exit();
}

$msg = helper_receiveMsg_2();
if (empty($msg) == true) {
	helper_sendMsg_2(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}

$roomTimerTicks = 7;

helper_sendMsg_2(array(
	'errno' => 1000, 
	'roomTimerTicks' => $roomTimerTicks
));

?>

