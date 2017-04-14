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

$userData = $msg['userData'];
$roomResult = $msg['roomResult'];
$dispatchRedPackVal = $msg['dispatchRedPackVal'];
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}

$unionid = $userData['unionid'];
$gameData = new GameData ();
if (!$gameData || !$unionid) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$gameData->updateUser($userData);

if (empty($roomResult) == false) {
	$gameData->insertRoomResult($unionid, $roomResult);
}

if (empty($dispatchRedPackVal) == false) {
	$gameData->addRedPackCount($dispatchRedPackVal);
}

helper_sendMsg_2(array('errno' => 1000));

?>
