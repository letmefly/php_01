<?php
$DEBUG = false;
function helper_log($str) {
	error_log($str . "\r\n", 3, '/tmp/dizhu.log');
}
function helper_receiveMsg() {
	$postdata = file_get_contents("php://input");
	if ($postdata == '') {
		helper_log('[helper] post data is blank..');
		return '';
	}
    $private_key = "fuck_angelababy"; 	
	$msgRaw = json_decode($postdata, true);
	$msgJson = $msgRaw['msg'];
	$msgSigh = $msgRaw['sign'];
	if (md5($msgJson . $private_key) != $msgSigh) {
		helper_log('[helper] sign is not right!!');
		if ($GLOBALS['DEBUG']) {
			return $msgRaw;
		}
		return null;
	}
	
	$msg64 = base64_decode($msgJson);
	$msg = json_decode($msg64, true);
	return $msg;
}
function helper_sendMsg($dataArray) {
	$jsonStr = json_encode($dataArray);
	$base64Str = base64_encode($jsonStr);
	if ($GLOBALS['DEBUG']) {
		echo $jsonStr;
		return;
	}
	$private_key = "fuck_angelababy";
	$msg = array('msg' => $base64Str, 'sign' => md5($base64Str . $private_key));
	echo json_encode($msg);
}
function helper_receiveMsg_2() {
	$postdata = file_get_contents("php://input");
	if ($postdata == '') {
		helper_log('[helper] post data is blank..');
		return '';
	}
	$msg = json_decode($postdata, true);
	return $msg;
}
function helper_sendMsg_2($dataArray) {
	$msgStr = json_encode($dataArray);
	echo $msgStr;
}
function helper_getInsertSQL($tableName, $dataArray) {
	$str1 = '';
	$str2 = '';
	foreach ($dataArray as $key => $value) {
		$str1 = $str1 . $key . ',';
		if (is_string($value)) {
			$str2 = $str2 . "'" . $value . "'" . ',';
		} else {
			$str2 = $str2 . $value . ',';
		}
	}
	$str1 = substr($str1, 0, strlen($str1) - 1);
	$str2 = substr($str2, 0, strlen($str2) - 1);
	$sql = "INSERT INTO " . $tableName . " (" . $str1 . ")" . " VALUES " . "(" . $str2 . ")";
	return $sql;
}
function helper_getUpdateSQL($tableName, $keyName, $dataArray) {
	$str = '';
	foreach ($dataArray as $key => $value) {
		if ($key == $keyName) continue;
		if (is_string($value)) {
			$str .= $key . "=" . "'" . $value . "'" . ",";
		} else {
			$str .= $key . "=" . $value . ",";
		}
	}
	$str = substr($str, 0, strlen($str) - 1);
	if ($keyName == null) {
		$sql = "UPDATE " . $tableName . " SET " . $str;
		return $sql;
	}
	$tableKeyValue = $dataArray[$keyName];
	if (is_string($tableKeyValue)) {
		$tableKeyValue = "'" . $tableKeyValue . "'";
	}
	$sql = "UPDATE " . $tableName . " SET " . $str . " WHERE " . $keyName . "=" . $tableKeyValue;
	
	return $sql;
}

function helper_getIP() {
	$ip = "";
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else 
		$ip = "";
	return $ip;
}

?>
