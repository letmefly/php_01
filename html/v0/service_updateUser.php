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
//$dispatchRedPackVal = $msg['dispatchRedPackVal'];
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

//$gameData->updateUser($userData);
$user = $gameData->getUser($unionid);

// first accept red pack
if (empty($user['redPackVal']) == true && empty($msg['dispatchRedPackVal']) == false) {
	helper_reward_introducer($unionid);
}

if (empty($roomResult['history']) == false) {
	$gameData->insertRoomResult($unionid, $roomResult);
}

if (empty($msg['dispatchRedPackVal']) == false) {
	$gameData->addRedPackCount($msg['dispatchRedPackVal']);
	helper_per_redpack_reward($unionid);
}

$gameData->updateUser2($user, $userData);

helper_sendMsg_2(array('errno' => 1000));

?>
