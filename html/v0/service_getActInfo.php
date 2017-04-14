<?php
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
if ($clientIp != "127.0.0.1") {
	exit();
}
$msg = helper_receiveMsg_2();
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}
$gameData = new GameData ();
$activitySwitch = $gameData->getActivity();

$retVal = 1;
if ($activitySwitch == "off") {
	$retVal = 0;
}

helper_sendMsg_2(array (
	'isOpen' => $retVal
));

?>