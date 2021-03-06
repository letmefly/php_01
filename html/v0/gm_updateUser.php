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

$userno = $msg['userno'];
$loginSwitch = $msg['loginSwitch'];

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}
$unionid = $gameData->getUnionid($userno);
$userData = array(
	'unionid' => $unionid,
	'loginSwitch' => $loginSwitch
);
$gameData->updateUser($userData);

helper_sendMsg(array('errno' => 1000));

?>
