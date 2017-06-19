<?php

include_once('../lib/SSDB.php');
include_once('../lib/helper.php');

$sign_key = "R4Nt0Em";
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

$postData = array();
$postData['appid'] = "wx71cc667fa9";
$postData['mch_id'] = "14373002";
$postData['out_trade_no'] = "242c8196e76accb00ad2262f7d";
$postData['nonce_str'] = generateRandomString();
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

$orderInfoStr = helper_http_post("https://api.mch.weixin.qq.com/pay/orderquery", $postDataXml);
echo $orderInfoStr;
$xml = simplexml_load_string($orderInfoStr);
$orderInfo = xml_to_array($xml);
$return_code = $orderInfo['return_code'];
echo $return_code;
?>