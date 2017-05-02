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
	$updateData['add_roomCardNum'] = $rewardRoomCard;
}
if ($rewardCoin) {
	$updateData['add_score'] = $rewardCoin;
}
if ($rewardRedPack) {
	$updateData['add_redPackVal'] = $rewardRedPack;
}

$gameData->updateUser($updateData);

helper_sendMsg(array('errno' => 1000));

?>
