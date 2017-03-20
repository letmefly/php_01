<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
// check ip if invalid access

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$sender_unionid = $msg['sender_unionid'];
$receiver_userno = $msg['receiver_userno'];
$room_card_num = $msg['room_card_num'];

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

//$sender_unionid = $gameData->getUnionid($sender_userno);
$receiver_unionid = $gameData->getUnionid($receiver_userno);
$sender_user = $gameData->getUser($sender_unionid);
$receiver_user = $gameData->getUser($receiver_unionid);
if (empty($sender_unionid) == true or empty($receiver_user) == true) {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

if ($sender_user['roomCardNum']-$room_card_num < 0) {
	helper_sendMsg(array ('errno' => 1004));
	exit();
}

$sender_updateData = array(
	'unionid' => $sender_unionid,
	'roomCardNum' => $sender_user['roomCardNum']-$room_card_num
);
$receiver_updateData = array(
	'unionid' => $receiver_unionid,
	'roomCardNum' => $receiver_user['roomCardNum']+$room_card_num
);

$gameData->updateUser($sender_updateData);
$gameData->updateUser($receiver_updateData);

helper_sendMsg(array (
	'errno' => 1000,
	'sender_userno' => $sender_unionid,
	'receiver_userno' => $receiver_userno,
	'sender_room_card' => $sender_user['roomCardNum']-$room_card_num,
	'receiver_room_card' => $receiver_user['roomCardNum']+$room_card_num,
));

?>
