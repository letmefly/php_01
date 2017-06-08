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

$rewardCoinNum = 0;
if ($gameData->isAddCoinToday($unionid) == false) {
	if ($user['score'] < 24) {
		$rewardCoinNum = 24 - $user['score'];
		$updateData = array(
			'unionid' => $unionid,
			'score' => 24
		);
		$gameData->updateUser($updateData);
		$user['score'] = 24;
	}
}
$mobile = "0";
if (isset($user['mobile'])) {
	$mobile = $user['mobile'];
}
$isExchange1Yuan = 0;
if (isset($user['isExchange1Yuan'])) {
	$isExchange1Yuan = $user['isExchange1Yuan'];
}
$user = $gameData->addUser_reward($user);

helper_sendMsg(array (
	'errno' => 1000,
	'unionid' => $user['unionid'],
	'nickname' => $user['nickname'],
	'shortNickName' => $shortNickName,
	'sex' => $user['sex'],
	'headimgurl' => $user['headimgurl'],
	'city' => $user['city'],
	'roomCardNum' => $user['roomCardNum'],
	'score' => $user['score'],
	'win' => $user['win'],
	'lose' => $user['lose'],
	'ip' => $user['ip'],
	'level' => $user['level'],
	'userno' => intval($user['userno']),
	'inviteTimes' => $user['inviteTimes'],
	'redPackVal' => $user['redPackVal'],
	'mobile' => $mobile,
	'isExchange1Yuan' => $isExchange1Yuan
));

?>
