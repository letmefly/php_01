<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');

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

function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$total_fee = $msg['total_fee'];
$body = $msg['body'];
$nonce_str = generateRandomString();
$out_trade_no = generateRandomString();

$postData = array(
	'appid' => "wx71cc6367ecd67fa9",
	'attach' => "xxx",
	'body' => $body,
	'mch_id' => "1437371002",
	'nonce_str' => $nonce_str,
	'notify_url' => "https://chess.ifunhealth.com:443/html/v0/weixinPayNotify.php",
	'out_trade_no' => $out_trade_no,
	'spbill_create_ip' => "127.0.0.1",
	'total_fee' => "1",
	'trade_type' => "APP",
);

$stringA = "";
foreach ($postData as $key => $value) {
	if ($key != "sign") {
		$stringA = $stringA . $key . "=" . $value . "&";
	}
}
$stringA = $stringA . "key=14Nt0EmPY6e741Pan5SHmBeiWQQ3wQwE";
$sign = strtoupper(md5($stringA));

$postData['sign'] = $sign;

$postDataXml = array_to_xml($postData, new SimpleXMLElement('<root/>'))->asXML();

$orderInfoStr = helper_http_post("https://api.mch.weixin.qq.com/pay/unifiedorder", $postDataXml);
$xml = simplexml_load_string($orderInfoStr);
$orderInfo = xml_to_array($xml);

$timestamp = strtotime(date("Y-m-d H:i:s",time()));
$noncestr = generateRandomString();
$tmpData = array(
	'partnerid' => "1437371002",
	'prepayid' => $orderInfo['prepay_id'],
	'package' => "Sign=WXPay",
	'noncestr' => $noncestr,
	'timestamp' => $timestamp
);
$stringA = "";
foreach ($tmpData as $key => $value) {
	if ($key != "sign") {
		$stringA = $stringA . $key . "=" . $value . "&";
	}
}
$stringA = $stringA . "key=14Nt0EmPY6e741Pan5SHmBeiWQQ3wQwE";
$sign = strtoupper(md5($stringA));

$tmpData['sign'] = $sign;
$tmpData['errno'] = 1000;
helper_sendMsg($tmpData);


?>
