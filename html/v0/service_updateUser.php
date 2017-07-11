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

$userData = $msg['userData'];

//$dispatchRedPackVal = $msg['dispatchRedPackVal'];
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}

$unionid = $userData['unionid'];
$gameData = new GameData ();
if (!$gameData || !$unionid) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

//$gameData->updateUser($userData);
$user = $gameData->getUser($unionid);

// first accept red pack
if ($user['redPackVal'] == 0 && isset($userData['redPackVal']) == true) {
	$ret = helper_reward_introducer($unionid);
}

if (isset($msg['roomResult'])) {
	$roomResult = $msg['roomResult'];
	if (count($roomResult['history']) > 0) {
		$ret = helper_reward_introducer2($unionid);
		$gameData->insertRoomResult($unionid, $roomResult);
		//mysql 
		/*
		$gameData->addGameResult_mysql(array(
			'userno' => $user['userno'],
			'unionid' => $user['unionid'],
			'nickname' => $user['nickname'],
			'duringTime' => $roomResult['duringTime'],
			'loseScore' => $user['loseScore'],
			'isOffline' => 0,
			'date' => date('Y-m-d G:i:s')
		));
		*/
	}
	if (isset($roomResult['coinType'])) {
		if ($roomResult['coinType'] == 1) {
			$gameData->addBigRedPackPlayTimes();
		}
		else {
			$gameData->addSmallRedPackPlayTimes();
		}
	}
}


if (isset($userData['redPackVal']) == true) {
	if ($userData['redPackVal'] - $user['redPackVal'] > 120) {
		$userData['redPackVal'] = $user['redPackVal'] + 120;
	}
	$lastGetRedPackTime = $user['getRedPackTime'];
	$nowTime = time();
	if ($nowTime - $lastGetRedPackTime > 5*60 - 20) {
		$userData['getRedPackTime'] = $nowTime;
		// insert notice
		$shortNickName = helper_substr($user['nickname'], 4, 0, "UTF-8");
		$addRedPackVal = $userData['redPackVal'] - $user['redPackVal'];
		$addRedPackVal = $addRedPackVal/100;
		if ($addRedPackVal > 0) {
			$noticeStr = "{$shortNickName}获得了{$addRedPackVal}元现金红包!!";
			$gameData->addNotice($noticeStr, 2);
		}
	}
	else {
		helper_sendMsg_2(array('errno' => 1001));
		helper_log('[ERR]this user get redpack less then 5 min');
		exit();
	}
	if (isset($roomResult['coinType'])) {
		if ($roomResult['coinType'] == 1) {
			$gameData->addRedPackCount($userData['redPackVal'] - $user['redPackVal']);
		}
		else {
			$gameData->addSmallRedPackCount($userData['redPackVal'] - $user['redPackVal']);
		}
	}
	
	//$ret = helper_per_redpack_reward($unionid, $userData['redPackVal'], $user['channel']);
}

if (isset($userData['playTurn']) && isset($userData['redPackVal'])) {
	$gameData->addRedpackRecord_mysql(array(
		'userno' => $user['userno'],
		'unionid' => $user['unionid'],
		'nickname' => $user['nickname'],
		'coinVal' => $userData['coinVal'],
		'redPackVal' => $userData['redPackVal'] - $user['redPackVal'],
		'playTurn' => $userData['playTurn'],
		'getTime' => date('Y-m-d G:i:s')
	));
}

$gameData->updateUser2($user, $userData);

helper_sendMsg_2(array('errno' => 1000));

?>
