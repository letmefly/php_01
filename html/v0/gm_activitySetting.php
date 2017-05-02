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

$activityInfo = array();

if (isset($msg['activitySwitch'])) {
	$activityInfo['activitySwitch'] = $msg['activitySwitch'];
}
if (isset($msg['rate_120'])) {
	$activityInfo['rate_120'] = $msg['rate_120'];
}
if (isset($msg['rate_80'])) {
	$activityInfo['rate_80'] = $msg['rate_80'];
}
if (isset($msg['rate_40'])) {
	$activityInfo['rate_40'] = $msg['rate_40'];
	if ($activityInfo['rate_120'] + $activityInfo['rate_80'] + $activityInfo['rate_40'] != 100) {
		helper_sendMsg(array('errno' => 1200));
		helper_log('setting rate invalid');
		exit();
	}
}

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$unionid = $gameData->setActivity($activityInfo);

helper_sendMsg(array('errno' => 1000));

?>
