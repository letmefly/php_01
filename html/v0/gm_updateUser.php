<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();

$msg = helper_receiveMsg_2();
if (empty($msg) == true) {
	helper_sendMsg_2(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$roomCardNum = $msg['roomCardNum'];
$testPoker = $msg['testPoker'];

$userData = array(
	'unionid' => $unionid, 
	'roomCardNum' => $roomCardNum,
	'testPoker' => $testPoker
);

$gameData = new GameData ();
if (!$gameData || !$unionid) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$gameData->updateUser($userData);

helper_sendMsg_2(array('errno' => 1000));

?>
