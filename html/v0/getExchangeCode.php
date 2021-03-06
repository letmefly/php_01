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

if ($redPackMoney != 600 && $redPackMoney != 1000 && $redPackMoney != 1500) {
	helper_sendMsg(array ('errno' => 1004));
	exit();
}

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

if (isset($user['mobile']) == false) {
	helper_sendMsg(array ('errno' => 1005));
	exit();
}

if ($user['redPackVal'] < $redPackMoney) {
	helper_sendMsg(array ('errno' => 1004));
	exit();
}

if ($user['rechargeVal'] <= 0) {
	helper_sendMsg(array ('errno' => 1006));
	exit();
}

if ($user['score'] > 80)  {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

$exchangeCode = helper_getCode($user['unionid'], $redPackMoney);
if ($exchangeCode == "") {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

$updateData = array(
	'unionid' => $user['unionid'],
	'redPackVal' => $user['redPackVal'] - $redPackMoney
);
if ($redPackMoney == 100) {
	if (isset($updateData['isExchange1Yuan'])==false) {
		$updateData['isExchange1Yuan'] = 1;
	}
	else 
	{
		helper_sendMsg(array ('errno' => 1005));
		exit();
	}
}
$gameData->updateUser($updateData);


$gameData->insertExchangeRecord($unionid, array('m' => $redPackMoney, 'c' => $exchangeCode));

helper_sendMsg(array (
	'errno' => 1000,
	'redPackVal' => $user['redPackVal'] - $redPackMoney,
	'exchangeCode' => $exchangeCode,
	'exchangeMoney' => $redPackMoney
));

?>
