<?php
include_once('../lib/helper.php');

$clientIp = helper_getIP();
$msg = helper_receiveMsg_2();

helper_sendMsg_2(array (
	'isOpen' => 1
));

?>