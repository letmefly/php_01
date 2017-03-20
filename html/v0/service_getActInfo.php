<?php
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
$msg = helper_receiveMsg_2();

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