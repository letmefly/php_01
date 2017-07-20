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

$token = $msg['token'];
if ($token != "this_token") {
	exit();
}
$cmd = $msg['cmd'];
$unionid = $msg['unionid'];
$gameData = new GameData ();
if (!$gameData || !$unionid) {
	helper_sendMsg_2(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}
$user = $gameData->getUser($unionid);

if ($cmd == "cmd_addRedpack") {
	$cashVal = $msg['cashVal'];
	$coinVal = $msg['coinVal'];
	$playTurn = $msg['playTurn'];
	$gameData->addRedpackRecord_mysql(array(
		'userno' => $user['userno'],
		'unionid' => $user['unionid'],
		'nickname' => $user['nickname'],
		'coinVal' => $coinVal,
		'redPackVal' => $cashVal,
		'playTurn' => $playTurn,
		'getTime' => date('Y-m-d G:i:s')
	));
	if ($cashVal > 120) {
		helper_log('Invalid redpack: cash-'.$cashVal);
		$cashVal = 120;
	}
	if ($coinVal > 9) {
		helper_log("Invalid redpack: coin-".$coinVal);
		$coinVal = 9;
	}

	$lastGetRedPackTime = $user['getRedPackTime'];
	$nowTime = time();
	if ($nowTime - $lastGetRedPackTime > 5*60 - 20) {
		$userData['getRedPackTime'] = $nowTime;
		// insert notice
		$shortNickName = helper_substr($user['nickname'], 4, 0, "UTF-8");
		$addRedPackVal = $cashVal/100;
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
			$gameData->addRedPackCount($cashVal);
		}
		else {
			$gameData->addSmallRedPackCount($coinVal);
		}
	}
	if (isset($user['totalGetRedPackVal']) == false) {
		$user['totalGetRedPackVal'] = 0;
	}
	$updateData = array(
		'unionid' => $unionid,
		'score' => $user['score'] + $coinVal,
		'redPackVal' => $user['redPackVal'] + $cashVal,
		'totalGetRedPackVal' => $user['totalGetRedPackVal'] + $cashVal,
		'todayRedPackCount' => $user['todayRedPackCount'] + 1
	);
	$gameData->updateUser2($user, $updateData);
	if ($coinVal > 0) {
		$addScoreLog = array(
			'unionid' => $user['unionid'],
			'nickname' => $user['nickname'],
			'time' => date('Y-m-d G:i:s'),
			'old_score' => $user['score'],
			'add_score' => $coinVal,
			'now_score' => $updateData['score'],
			'add_way' => "redpack"
		);
		$gameData->insertAddScoreLog_mysql($addScoreLog);
	}
}
else if ($cmd == "cmd_submitGameResult") {
	$roomResult = $msg['roomResult'];
	$addCoin = $msg['addCoin'];
	if (count($roomResult['history']) > 0) {
		$gameAddCoin = 0;
		$myShortName = helper_substr($user['nickname'], 4, 0, "UTF-8");
		$gameData->insertRoomResult($unionid, $roomResult);
		for ($i=0; $i < count($roomResult['history']); $i++) {
			$shortName = $roomResult['history'][$i]['n'];
			if ($myShortName == $shortName) {
				$gameAddCoin = $roomResult['history'][$i]['s'];
				if ($gameAddCoin > 0) {
					$gameAddCoin = floor($gameAddCoin*2/3);
				}
			}
		}
	}
	if ($gameAddCoin != $addCoin) {
		helper_log("$gameAddCoin != $addCoin");
	}
	if ($addCoin != 0) {
		$win = 0;
		$lose = 0;
		if ($addCoin > 0) {
			$win = 1;
		}
		else {
			$lose = 1;
		}
		$updateData = array(
			'unionid' => $unionid,
			'score' => $user['score'] + $addCoin,
			'win' => $user['win'] + $win,
			'lose' =>$user['lose'] + $lose
		);
		$gameData->updateUser2($user, $updateData);

		$addScoreLog = array(
			'unionid' => $user['unionid'],
			'nickname' => $user['nickname'],
			'time' => date('Y-m-d G:i:s'),
			'old_score' => $user['score'],
			'add_score' => $addCoin,
			'now_score' => $updateData['score'],
			'add_way' => "game"
		);
		$gameData->insertAddScoreLog_mysql($addScoreLog);
	}
}
else if ($cmd == "cmd_costRoomCard") {
	$costRoomCard = $msg['costRoomCard'];
	$updateData = array(
		'unionid' => $unionid,
		'roomCardNum' => $user['roomCardNum'] + $costRoomCard
	);
	$gameData->updateUser2($user, $updateData);
}

helper_sendMsg_2(array('errno' => 1000));
