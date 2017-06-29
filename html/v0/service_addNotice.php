<?php
include_once('../lib/helper.php');
include_once('GameData.php');

$clientIp = helper_getIP();
if ($clientIp != "127.0.0.1") {
	exit();
}
$msg = helper_receiveMsg_2();
$token = $msg['token'];
if ($token != "this_token") {
	exit();
}
$noticeStr = $msg['noticeStr'];
$level = $msg['level'];
$gameData = new GameData ();

$gameData->addNotice($noticeStr, $level);
helper_sendMsg_2(array('errno' => 1000));

?>

