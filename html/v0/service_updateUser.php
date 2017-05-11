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
if ($user['redPackVal'] == 0 && isset($userData['redPackVal']) == true) {
	$ret = helper_reward_introducer($unionid);
}

if (isset($msg['roomResult'])) {
	$roomResult = $msg['roomResult'];
	if (count($roomResult['history']) > 0) {
		$ret = helper_reward_introducer2($unionid);
		$gameData->insertRoomResult($unionid, $roomResult);
	}
}


if (isset($userData['redPackVal']) == true) {
	$gameData->addRedPackCount($userData['redPackVal']);
	$ret = helper_per_redpack_reward($unionid, $userData['redPackVal'], $user['channel']);
}

$gameData->updateUser2($user, $userData);

helper_sendMsg_2(array('errno' => 1000));

?>
