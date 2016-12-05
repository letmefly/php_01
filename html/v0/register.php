<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

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
$city = $msg['city'];

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
	'city' => $city
);

$user = $gameData->getUser($unionid);
if (empty($user) == false)
{
	$gameData->updateUser($userData);
} 
else 
{
	$gameData->addUser($userData);
}

helper_sendMsg(array (
	'errno' => 1000
));

?>
