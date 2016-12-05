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
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$gameData->updateUser(array('unionid' => $unionid, 'ip' => $ip));
$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg(array ('errno' => 1003));
	exit();
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
	'win' => $user['win'],
	'lose' => $user['lose'],
	'ip' => $user[ip]
));

?>
