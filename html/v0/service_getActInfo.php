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
$actInfo = $gameData->getActivity();

if (!$actInfo) {
	$actInfo['activitySwitch'] = 'on';
	$actInfo['rate_120'] = 33;
	$actInfo['rate_80'] = 33;
	$actInfo['rate_40'] = 34;
}

if (isset($actInfo['activitySwitch']) == false) {
	$actInfo['activitySwitch'] = 'on';
}

if (isset($actInfo['rate_120']) == false) {
	$actInfo['rate_120'] = 33;
}

if (isset($actInfo['rate_80']) == false) {
	$actInfo['rate_80'] = 33;
}

if (isset($actInfo['rate_40']) == false) {
	$actInfo['rate_40'] = 34;
}

helper_sendMsg_2($actInfo);

?>