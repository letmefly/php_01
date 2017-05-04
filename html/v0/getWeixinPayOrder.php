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

/*
$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}
*/
$postData = array(
	'appid' => "wx71cc6367ecd67fa9",
	'attach' => "xxx",
	'body' => "hello",
	'mch_id' => "1437371002",
	'nonce_str' => "dfdfdefdfadedf",
	'notify_url' => "https://chess.ifunhealth.com:443/html/v0/weixinPayNotify.php",
	'out_trade_no' => "dfdfdddfdfefdfdf",
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

$orderInfo = helper_http_post("https://api.mch.weixin.qq.com/pay/unifiedorder", $postDataXml);
echo $orderInfo;
?>
