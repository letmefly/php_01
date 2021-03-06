<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
if (!$clientIp) {$clientIp="unknown";}

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$nickname = $msg['nickname'];
$sex = $msg['sex'];
$headimgurl = $msg['headimgurl'];
$clientOS = $msg['os'];// "ios" or "android" or "win32"
$city = $msg['city'];
if (isset($msg['urlencode'])) {
	$nickname = urldecode($nickname);
}
$channel = "none";
if (isset($msg['channel'])) {
	$channel = $msg['channel'];
}

$password = "";
$loginType = "weixin";
if (isset($msg['password'])) {
	$password = $msg['password'];
}
if (isset($msg['loginType'])) {
	$loginType = $msg['loginType'];
}

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$userData = array (
	'unionid' => $unionid,
	'nickname' => $nickname,
	'sex' => $sex,
	'headimgurl' => $headimgurl,
	'city' => $city,
	'ip' => $clientIp,
	'password' => $password,
	'channel' => $channel
);

$user = $gameData->getUser($unionid);
if (empty($user) == false)
{
	if ($loginType == "weixin") {
		$gameData->updateUser($userData);
	}
	else if ($loginType == "my_login") {
		if ($user['password'] != $password) {
			// user not exist
			helper_sendMsg(array ('errno' => 5001));
			exit();
		}
	}
	else if ($loginType == "my_reg") {
		// user aleay exits
		helper_sendMsg(array ('errno' => 5002));
		exit();
	}
	
} 
else 
{
	if ($loginType == "weixin" or $loginType == "my_reg") {
		$gameData->addUser($userData);
	}
	else {
		// user not exist
		helper_sendMsg(array ('errno' => 5000));
		exit();
	}
}

$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

if (isset($user['loginSwitch'])) {
	if ($user['loginSwitch'] == 'off') {
		helper_sendMsg(array ('errno' => 1003));
		exit();
	}
}
$shortNickName = helper_substr($user['nickname'], 4, 0, "UTF-8");

$redPackSwitch = "off";
if ($clientOS == "ios") {
	$redPackSwitch = "off";
}
else if ($clientOS == "android") {
	$redPackSwitch = "on";
}
else if ($clientOS == "win32") {
	$redPackSwitch = "on";
}
$rewardCoinNum = 0;
$rewardCoin2Num = 0;

$nowDate = date('Y-m-d');
if ($nowDate != $user['lastLoginTime']) {
	$updateData = array(
		'unionid' => $unionid,
		'lastLoginTime' => $nowDate,
		'loginDayCount' => $user['loginDayCount'] + 1,
		'todayRedPackCount' => 0,
		'todayRechargeVal' => 0
	);
	$gameData->updateUser($updateData);
	$user['loginDayCount'] = $user['loginDayCount'] + 1;
	$user['todayRedPackCount'] = 0;
	$user['todayRechargeVal'] = 0;
}

if ($gameData->isAddCoinToday($unionid) == false) {
	if ($user['score'] < 24 ) {
		$rewardCoinNum = 24 - $user['score'];
		$updateData = array(
			'unionid' => $unionid,
			'score' => 24,
			'isAcceptDailyReward' => 1
		);
		$gameData->updateUser($updateData);
		$user['score'] = 24;
	}

	/*
	if ($user['score2'] < 24) {
		$rewardCoin2Num = 35 - $user['score2'];
		$updateData = array(
			'unionid' => $unionid,
			'score2' => 35,
			'isAcceptDailyReward' => 1
		);
		$gameData->updateUser($updateData);
		$user['score2'] = 35;
	}
	*/
}
$mobile = "0";
if (isset($user['mobile'])) {
	$mobile = $user['mobile'];
}
$isExchange1Yuan = 0;
if (isset($user['isExchange1Yuan'])) {
	$isExchange1Yuan = $user['isExchange1Yuan'];
}
if (isset($user['todayRechargeVal']) == false) {
	$user['todayRechargeVal'] = 0;
}
$user = $gameData->addUser_reward($user);

$noticeMsg = 
"";

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
	'score2' => $user['score2'],
	'win' => $user['win'],
	'lose' => $user['lose'],
	'ip' => $user['ip'],
	'level' => $user['level'],
	'userno' => intval($user['userno']),
	'inviteTimes' => $user['inviteTimes'],
	'redPackVal' => $user['redPackVal'],
	'redPackSwitch' => $redPackSwitch,
	'rewardCoinNum' => $rewardCoinNum,
	'rewardCoin2Num' => $rewardCoin2Num,
	'mobile' => $mobile,
	'isExchange1Yuan' => $isExchange1Yuan,
	'rechargeVal' => $user['rechargeVal'],
	'lastLoginTime' => $user['lastLoginTime'],
	'loginDayCount' => $user['loginDayCount'],
	'todayRedPackCount' => $user['todayRedPackCount'],
	'lastRechargeDate' => $user['lastRechargeDate'],
	'noticeMsg' => $noticeMsg,
	'todayRechargeVal' => $user['todayRechargeVal']
));

?>
