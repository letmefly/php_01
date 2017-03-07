<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

function util_getCurl($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    //curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
	return json_decode($output,true);
}

function util_getCode($unionid, $amount) {
	$timeStamp = time();
	$privateKey = "6JNVkTk4jHPgF0e1oOVLwOZDeq83pDXu";
	$tokent = md5($unionid . $amount . $timeStamp . $privateKey);
	$url = "http://ddz.ifunhealth.com/index.php?r=site/redeemcode&unionid={$unionid}&amount={$amount}&op_time={$timeStamp}&token={$tokent}";
	$ret = util_getCurl($url);
	if ($ret['errno'] == 1000) {
		return $ret['redeemCode'];
	}
	else {
		return "";
	}
}


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

$exchangeCode = util_getCode($user['unionid'], $redPackMoney);
if ($exchangeCode == "") {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}

$updateData = array(
	'unionid' => $user['unionid'],
	'redPackVal' => $user['redPackVal'] - $redPackMoney
);
$gameData->updateUser($updateData);


$gameData->insertExchangeRecord($unionid, array('m' => $redPackMoney, 'c' => $exchangeCode));

helper_sendMsg(array (
	'errno' => 1000,
	'redPackVal' => $user['redPackVal'] - $redPackMoney,
	'exchangeCode' => $exchangeCode,
	'exchangeMoney' => $redPackMoney
));

?>
