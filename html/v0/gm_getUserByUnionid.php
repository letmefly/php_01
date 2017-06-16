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

$mobile = "0";
if (isset($user['mobile'])) {
	$mobile = $user['mobile'];
}

$channel = "none";
if (isset($user['channel'])) {
	$channel = $user['channel'];
}

helper_sendMsg(array (
	'errno' => 1000,
	'unionid' => $user['unionid'],
	'nickname' => $user['nickname'],
	'sex' => $user['sex'],
	'headimgurl' => $user['headimgurl'],
	'city' => $user['city'],
	'roomCardNum' => $user['roomCardNum'],
	'score' => $user['score'],
	'score2' => $user['score2'],
	'win' => $user['win'],
	'lose' => $user['lose'],
	'ip' => $user['ip'],
	'level' => $user['level'],
	'userno' => intval($user['userno']),
	'mobile' => $mobile,
	'channel' => $channel
));

?>
