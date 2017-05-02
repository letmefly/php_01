<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$rewardRoomCard = $msg['rewardRoomCard'];
$rewardCoin = $msg['rewardCoin'];
$rewardRedPack = $msg['rewardRedPack'];

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

$updateData = array('unionid' => $unionid);
if ($rewardRoomCard) {
	if (isset($user['add_roomCardNum']) == false) {
		$user['add_roomCardNum'] = 0;
	}
	$updateData['add_roomCardNum'] = $user['add_roomCardNum'] + $rewardRoomCard;
}
if ($rewardCoin) {
	if (isset($user['add_score']) == false) {
		$user['add_score'] = 0;
	}
	$updateData['add_score'] = $user['add_score'] + $rewardCoin;
}
if ($rewardRedPack) {
	if (isset($user['add_redPackVal']) == false) {
		$user['add_redPackVal'] = 0;
	}
	$updateData['add_redPackVal'] = $user['add_redPackVal'] + $rewardRedPack;
}

$gameData->updateUser($updateData);

helper_sendMsg(array('errno' => 1000));

?>
