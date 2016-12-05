<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$ip = $_SERVER['HTTP_CLIENT_IP'];

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$gameData = new GameData ();
if (!$gameData || !$unionid) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$gameData->updateUser($msg);
helper_sendMsg(array('errno' => 1000));

?>
