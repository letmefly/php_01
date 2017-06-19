<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');

$sign_key = "R4Nt0EmPY6e741aghjSH4BeixQQ3wQw4";
function array_to_xml(array $arr, SimpleXMLElement $xml)
{
    foreach ($arr as $k => $v) {
        is_array($v)
            ? array_to_xml($v, $xml->addChild($k))
            : $xml->addChild($k, $v);
    }
    return $xml;
}

function xml_to_array(SimpleXMLElement $parent)
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

function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/*
function getToken()
{
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx71cc6367ecd67fa9&secret=fefc2bb2ebd59b604d198b40854cc872";
    $res = helper_getCurl($url);
    return isset($res['access_token']) ? $res['access_token'] : false;
}

$token = getToken();
*/

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}


/*
$total_fee = $msg['total_fee'];
$body = $msg['body'];
$nonce_str = generateRandomString();
$out_trade_no = generateRandomString();
$timestamp = strtotime(date("Y-m-d H:i:s",time()));

$ntf_url = "https://chess.ifunhealth.com:443/html/v0/weixinPayNotify.php";
$package = "bank_type=WX&body={$body}&fee_type=1&input_charset=UTF-8&notify_url={$ntf_url}&out_trade_no={$out_trade_no}&partner=1437371002&spbill_create_ip=1
27.0.0.1&total_fee={$total_fee}";
$tmpPackage = $package . "&key=14Nt0EmPY6e741Pan5SHmBeiWQQ3wQwE";
$sign = strtoupper(md5($tmpPackage));

$ntf_url = urlencode("https://chess.ifunhealth.com:443/html/v0/weixinPayNotify.php");
$package = "bank_type=WX&body={$body}&fee_type=1&input_charset=UTF-8&notify_url={$ntf_url}&out_trade_no={$out_trade_no}&partner=1437371002&spbill_create_ip=1
27.0.0.1&total_fee={$total_fee}&sign={$sign}";
helper_log($package);
$postData = array(
	'appid' => "wx71cc6367ecd67fa9",
	'traceid' => "xxx",
	'noncestr' => $nonce_str,
	'package' => $package,
	'timestamp' => $timestamp
);

ksort($postData);
$stringA = "";
foreach ($postData as $key => $value) {
	if ($key != "sign") {
		$stringA = $stringA . $key . "=" . $value . "&";
	}
}
$stringA = $stringA . "key=14Nt0EmPY6e741Pan5SHmBeiWQQ3wQwE";
$app_signature = sha1($stringA);
$postData['app_signature'] = $app_signature;
$postData['sign_method'] = "sha1";

$url = "https://api.weixin.qq.com/pay/genprepay?access_token={$token}";
$orderInfoStr = helper_http_post($url, json_encode($postData));
echo $orderInfoStr
*/


$total_fee = $msg['total_fee'];
$body = $msg['body'];
$nonce_str = generateRandomString();
$out_trade_no = generateRandomString();

$postData = array(
	'appid' => "wx71cc67ecd6fa9",
	'attach' => "xxx",
	'body' => $body,
	'mch_id' => "1431002",
	'nonce_str' => $nonce_str,
	'notify_url' => "https:weixinPayNotify.php",
	'out_trade_no' => $out_trade_no,
	'sign_type' => 'MD5',
	'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
	'total_fee' => $total_fee,
	'trade_type' => "APP",
);
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


$postDataXml = array_to_xml($postData, new SimpleXMLElement('<xml/>'))->asXML();

$orderInfoStr = helper_http_post("https://api.mch.weixin.qq.com/pay/unifiedorder", $postDataXml);

$xml = simplexml_load_string($orderInfoStr);
$orderInfo = xml_to_array($xml);
$timestamp = time()."";
$noncestr = generateRandomString();
$tmpData = array(
	'appid' => "wx71cc6367e7fa9",
	'noncestr' => $noncestr,
	'package' => "Sign=WXPay",
	'partnerid' => "1437002",
	'prepayid' => $orderInfo['prepay_id'],
	'timestamp' => $timestamp
);
ksort($tmpData);
$stringA = "";
foreach ($tmpData as $key => $value) {
	if ($key != "sign") {
		$stringA = $stringA . $key . "=" . $value . "&";
	}
}
$stringA = $stringA . "key={$sign_key}";
$sign = strtoupper(md5($stringA));
$tmpData['sign'] = $sign;
$tmpData['errno'] = 1000;
$tmpData['out_trade_no'] = $out_trade_no;
helper_sendMsg($tmpData);


?>
