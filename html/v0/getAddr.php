<?php
include_once('../lib/helper.php');

$clientIp = helper_getIP();
if ($clientIp != "60.186.204.149") {
	exit();
}

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}
$roomNo = $msg['roomNo'];

$ret = array();
$ip = "192.168.1.110";
// assign a game server
if ($roomNo == "000000") {
	$ret['ip'] = $ip;
	$ret['port'] = 8888;
} 
else if (intval($roomNo) >= 1000000 and intval($roomNo) <= 500000) 
{
	$ret['ip'] = $ip;
	$ret['port'] = 8888;
}
else
{
	$ret['ip'] = $ip;
	$ret['port'] = 8888;
}

helper_sendMsg($ret);

?>