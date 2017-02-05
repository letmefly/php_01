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
$redPackMoney = $msg['redPackMoney'];

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

if ($user['redPackVal'] < $redPackMoney) {
	helper_sendMsg(array ('errno' => 1004));
	exit();
}
$updateData = array(
	'unionid' => $user['unionid'],
	'redPackVal' => $user['redPackVal'] - $redPackMoney
);
$gameData->updateUser($updateData);

$exchangeCode = "ABCDEFG";
$gameData->insertExchangeRecord($unionid, array('m' => $redPackMoney, 'c' => $exchangeCode));

helper_sendMsg(array (
	'errno' => 1000,
	'redPackVal' => $user['redPackVal'] - $redPackMoney,
	'exchangeCode' => $exchangeCode,
	'exchangeMoney' => $redPackMoney
));

?>
