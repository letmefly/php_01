<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

$errno = 1000;
$clientIp = helper_getIP();
if (!$clientIp) {$clientIp="unknown";}

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$targetUserno = $msg['targetUserno'];
$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

$targetUnionid = $gameData->getUnionid($targetUserno);
if ($targetUnionid) {
	$targetUserData = $gameData->getUser($targetUnionid);
	if (empty($targetUserData) == false) {
		if ($targetUserData['isInvited'] == 1) {
			if ($targetUserData['win']+$targetUserData['lose'] >= 6) {
				$userData = $gameData->getUser($unionid);
				if ($userData['inviteTimes'] < 30) {
					$gameData->updateUser(array(
						'unionid' => $unionid,
						'roomCardNum' => $userData['roomCardNum'] + 20,
						'inviteTimes' => $userData['inviteTimes'] + 1
					));
					$gameData->updateUser(array(
						'unionid' => $targetUnionid,
						'isInvited' => 2
					));
				}
				else {
					$errno = 1006;
				}
			}
			else {
				$errno = 1005;
			}
		}
		else {
			$errno = 1004;
		}
	}
	else {
		$errno = 1003;
	}
}
else {
	$errno = 1002;
}

helper_sendMsg(array('errno' => $errno));

?>
