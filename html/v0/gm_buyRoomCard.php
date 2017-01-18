<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
// check ip if invalid access

$msg = helper_receiveMsg_2();
if (empty($msg) == true) {
	helper_sendMsg_2(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

//$userno = $msg['userno'];
$unionid = $msg['unionid'];
$buyRoomCardNum = $msg['room_card_num'];

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

//$unionid = $gameData->getUnionid($userno);
$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg_2(array ('errno' => 1003));
	exit();
}

$updateData = array(
	'unionid' => $user['unionid'],
	'roomCardNum' => $user['roomCardNum']+$buyRoomCardNum
);

$gameData->updateUser($updateData);

helper_sendMsg_2(array (
	'errno' => 1000,
	'roomCardNum' => $user['roomCardNum']+$buyRoomCardNum,
	'userno' => $user['userno']
));

?>