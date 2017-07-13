<?php
$DEBUG = false;
$client_msg_key = "fuck_angelababy";
$api_url_base = "http://127.0.0.1";
$api_key = "test_sign";


function helper_log($str) {
	//error_log($str . "\r\n", 3, '/tmp/dizhu.log');
	file_put_contents('/tmp/dizhu.log',$str."\r\n",FILE_APPEND);
}
function helper_receiveMsg() {
	$postdata = file_get_contents("php://input");
	if ($postdata == '') {
		helper_log('[helper] post data is blank..');
		return '';
	}
	$private_key = $GLOBALS['client_msg_key'];
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
	$private_key = $GLOBALS['client_msg_key'];
	$msg = array('msg' => $base64Str, 'sign' => md5($base64Str . $private_key));
	echo json_encode($msg);
}
function helper_receiveMsg_2() {
	$postdata = file_get_contents("php://input");
	if ($postdata == '') {
		helper_log('[helper] post data is blank..');
		return '';
	}
	helper_log($postdata);
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

function helper_substr($string, $sublen, $start = 0, $code = 'UTF-8')
{
    if($code == 'UTF-8')
    {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);

        if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."..";
        return join('', array_slice($t_string[0], $start, $sublen));
    }
    else
    {
        $start = $start*2;
        $sublen = $sublen*2;
        $strlen = strlen($string);
        $tmpstr = '';

        for($i=0; $i< $strlen; $i++)
        {
            if($i>=$start && $i< ($start+$sublen))
            {
                if(ord(substr($string, $i, 1))>129)
                {
                    $tmpstr.= substr($string, $i, 2);
                }
                else
                {
                    $tmpstr.= substr($string, $i, 1);
                }
            }
            if(ord(substr($string, $i, 1))>129) $i++;
        }
        if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
        return $tmpstr;
    }
}

function helper_getCurl($url)
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

function helper_reward_introducer($unionid) {
	$timeStamp = time();
	$privateKey = $GLOBALS['api_key'];
	$tokent = md5($unionid . $timeStamp . $privateKey);
	$base_url = $GLOBALS['api_url_base'];
	$url = "{$base_url}/index.php?r=site/reward-introducer&unionid={$unionid}&op_time={$timeStamp}&token={$tokent}";
	$ret = helper_getCurl($url);
	//helper_log($ret);
	return $ret;
}

function helper_per_redpack_reward($unionid, $redPackVal, $channel) {
	$timeStamp = time();
	$privateKey = $GLOBALS['api_key'];
	$tokent = md5($channel . $unionid . $redPackVal. $timeStamp . $privateKey);
	$base_url = $GLOBALS['api_url_base'];
	$url = "{$base_url}/index.php?r=site/per-redpack-reward&channel={$channel}&unionid={$unionid}&redpack_val={$redPackVal}&op_time={$timeStamp}&token={$tokent}";
	$ret = helper_getCurl($url);
	//helper_log($ret);
	return $ret;
}

function helper_recharge_record($unionid, $pay_name, $pay_time, $amount, $channel) {
	$timeStamp = time();
	$privateKey = $GLOBALS['api_key'];
	$tokent = md5($amount . $channel . $pay_name . $pay_time. $unionid . $timeStamp . $privateKey);
	$base_url = $GLOBALS['api_url_base'];
	$url = "{$base_url}/index.php?r=site/order&amount={$amount}&channel={$channel}&pay_name={$pay_name}&pay_time={$pay_time}&unionid={$unionid}&op_time={$timeStamp}&token={$tokent}";
	$ret = helper_getCurl($url);
	//helper_log($ret);
	return $ret;
}

function helper_getCode($unionid, $amount) {
	$timeStamp = time();
	$privateKey = $GLOBALS['api_key'];
	$tokent = md5($unionid . $amount . $timeStamp . $privateKey);
	$base_url = $GLOBALS['api_url_base'];
	$url = "{$base_url}/index.php?r=site/redeemcode&unionid={$unionid}&amount={$amount}&op_time={$timeStamp}&token={$tokent}";
	$ret = helper_getCurl($url);
	if ($ret['errno'] == 1000) {
		return $ret['redeemCode'];
	}
	else {
		return "";
	}
}

function helper_reward_introducer2($unionid) {
	$timeStamp = time();
	$privateKey = $GLOBALS['api_key'];
	$tokent = md5($unionid . $timeStamp . $privateKey);
	$base_url = $GLOBALS['api_url_base'];
	$url = "{$base_url}/index.php?r=site/reward-room-card&unionid={$unionid}&op_time={$timeStamp}&token={$tokent}";
	$ret = helper_getCurl($url);
	//helper_log($ret);
	return $ret;
}

function helper_http_post($url, $post_data = '', $timeout = 5){//curl
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_POST, 1);
	if($post_data != ''){
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}

function helper_http_get($url) {
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function helper_array_to_xml(array $arr, SimpleXMLElement $xml)
{
    foreach ($arr as $k => $v) {
        is_array($v)
            ? array_to_xml($v, $xml->addChild($k))
            : $xml->addChild($k, $v);
    }
    return $xml;
}

function helper_xml_to_array(SimpleXMLElement $parent)
{
    $array = array();

    foreach ($parent as $name => $element) {
        ($node = & $array[$name])
            && (1 === count($node) ? $node = array($node) : 1)
            && $node = & $node[];

        $node = $element->count() ? XML2Array($element) : trim($element);
    }

    return $array;
}

function helper_generateRandomString($length = 31) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function helper_weixin_query($out_trade_no) {
	$postData = array();
	$sign_key = "R4Nt0EmPY6e741Pat6SH4BeixQQ3wQw4";
	$postData['appid'] = "wx71cc6367ec267fa9";
	$postData['mch_id'] = "1437311002";
	$postData['out_trade_no'] = $out_trade_no;
	$postData['nonce_str'] = helper_generateRandomString();
	ksort($postData);

	$stringA = "";
	foreach ($postData as $key => $value) {
	    if ($key != "sign") {
	        $stringA = $stringA . $key . "=" . $value . "&";
	    }
	}
	$stringA = $stringA . "key={$sign_key}";
	$sign = strtoupper(md5($stringA));
	$postData['sign'] = $sign;
	$postDataXml = helper_array_to_xml($postData, new SimpleXMLElement('<xml/>'))->asXML();

	$orderInfoStr = helper_http_post("https://api.mch.weixin.qq.com/pay/orderquery", $postDataXml);
	$xml = simplexml_load_string($orderInfoStr);
	$orderInfo = helper_xml_to_array($xml);
	//$return_code = $orderInfo['return_code'];
	//return $return_code;
	return $orderInfo;
}

?>
