<?php
include_once('../lib/helper.php');
include_once('GameData.php');

$url = $_SERVER["REQUEST_URI"];

$postdata = file_get_contents("php://input");
if ($postdata == '')
{
	print_r('post data is blank..');
	exit();
}
$xml = simplexml_load_string($postdata);
$rowdata = array();
foreach($xml->children() as $child)
{
  	$rowdata[$child->getName()] = $child->__toString();
}

helper_log(json_encode($rowdata));
ksort($rowdata);
$stringA = "";
foreach ($rowdata as $key => $value) {
	if ($key != "sign") {
		$stringA = $stringA . $key . "=" . $value . "&";
	}
}
$stringA = $stringA . "key=14Nt0EmPY6e741Pan5SHmBeiWQQ3wQwE";
$sign = strtoupper(md5($stringA));
if ($sign == $rowdata['sign'] && $rowdata['return_code'] = "SUCCESS") {
	$outTradeNo = $rowdata['out_trade_no'];
	$transaction_id = $rowdata['transaction_id'];
	$cash_fee = $rowdata['cash_fee'];
	if (isset($rowdata['attach'])==false) {
		$rowdata['attach'] = "none";
	}
	$gameData = new GameData ();
	$gameData->insertWeixinPayInfo($outTradeNo, $transaction_id, json_encode($rowdata), $cash_fee);
	$gameData->insertWeixinOrderInfo_mysql($rowdata);
} else {
	helper_log("sign is not right");
}

echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
?>
