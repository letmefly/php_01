<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
if ($clientIp != "127.0.0.1") {
	exit();
}

$msg = helper_receiveMsg_2();
if (empty($msg) == true) {
	helper_sendMsg_2(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg_2(array ('errno' => 1003));
	exit();
}
$shortNickName = helper_substr($user['nickname'], 4, 0, "UTF-8");
if (isset($user['score2']) == false) {
	$user['score2'] = 1000;
}

if (isset($user['totalGetRedPackVal']) == false) {
	$user['totalGetRedPackVal'] = 0;
}

helper_sendMsg_2(array (
	'errno' => 1000,
	'unionid' => $user['unionid'],
	'nickname' => $shortNickName,
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
	'isInvited' => $user['isInvited'],
	'redPackVal' => $user['redPackVal'],

	'lastLoginTime' => $user['lastLoginTime'],
	'loginDayCount' => $user['loginDayCount'],
	'todayRedPackCount' => $user['todayRedPackCount'],
	'lastRechargeDate' => $user['lastRechargeDate'],
	'rechargeVal' => $user['rechargeVal'],
	'totalGetRedPackVal' => $user['totalGetRedPackVal'],
	'todayRechargeVal' => $user['todayRechargeVal']
));

?>
