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

$unionid = $msg['unionid'];
$addCoin = $msg['addCoin'];
$orderNo = $msg['orderNo'];

// First check if the orderNo is valid by refering appstore
$isValid = true;
if($isValid == false) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('order invalid');
	exit();	
}

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

$updateData = array(
	'unionid' => $unionid,
	'score' => $user['score'] + $addCoin
);
$gameData->updateUser($updateData);

helper_sendMsg(array (
	'errno' => 1000,
	'coinNum' => $user['score'] + $addCoin
));

?>
