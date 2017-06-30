<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');

function appstore_verify($receipt_data, $sandbox=0){
    //小票信息
    $POSTFIELDS = array("receipt-data" => $receipt_data);
    $POSTFIELDS = json_encode($POSTFIELDS);

    //正式购买地址 沙盒购买地址
    $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";    
    $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
    $url = $sandbox ? $url_sandbox : $url_buy;

    //简单的curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
    $result = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($result,true);

    // $data['status']==0  成功
    // $data['receipt']['in_app'][0]['transaction_id']  苹果订单号  
   //  $data['receipt']['in_app'][0]['product_id'];  商品价格
    return $data; 
}

$clientIp = helper_getIP();
// check ip if invalid access

$msg = helper_receiveMsg();
if (empty($msg) == true) {
	helper_sendMsg(array('errno' => 1100));
	helper_log('receiveMsg invalid');
	exit();
}

$unionid = $msg['unionid'];
$platform = $msg['platform'];
$addCoin = $msg['addCoin'];
$receipt_data = $msg['receipt_data'];

$chargeMoney = 0;
if ($addCoin == 30) {
	$chargeMoney = 600;
} else if ($addCoin == 60) {
	$chargeMoney = 1200;
} else if ($addCoin == 125) {
	$chargeMoney = 2500;
} else if ($addCoin == 200) {
	$chargeMoney = 4000;
} else if ($addCoin == 340) {
	$chargeMoney = 6800;
} else if ($addCoin == 440) {
	$chargeMoney = 8800;
}

$gameData = new GameData ();
if (!$gameData) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('gameData init fail');
	exit();
}

// First check if the orderNo is valid by refering appstore
$isValid = false;
if ($platform == "appstore") {
	$ret = appstore_verify($receipt_data, 1);
	if ($ret['status'] == 0 && $ret['receipt']['bid'] == "com.ywxx.doudizhu8") {
		$val = $gameData->getAppstoreOrderId($ret['receipt']['transaction_id']);
		if (!($val == 1)) {
			$isValid = true;
			$gameData->setAppstoreOrderId($ret['receipt']['transaction_id'], 1);
		}
	}
}
else if ($platform == "android" || $platform == "ios_weixin") 
{
	//sleep(3);//wait weixin pay callback
	$record = $gameData->getOutTradeNoRecord($receipt_data);
	if ($record == $chargeMoney) {
		$isValid = true;
		$gameData->clearOutTradeNoRecord($receipt_data);
	} else {
		$isValid = false;
		$ret = helper_weixin_query($receipt_data);
		if ($ret['return_code'] == "SUCCESS" && $ret['result_code'] == "SUCCESS" && $ret['cash_fee'] == $chargeMoney) {
			$isValid = true;
		}
	}
}

if($isValid == false) {
	helper_sendMsg(array('errno' => 1001));
	helper_log('order invalid');
	exit();	
}

$user = $gameData->getUser($unionid);
if (empty($user) == true) {
	helper_sendMsg(array ('errno' => 1003));
	exit();
}


$gameData->addRewardPool($chargeMoney*0.05);

$updateData = array(
	'unionid' => $unionid,
	'score' => $user['score'] + $addCoin,
	'isAcceptDailyReward' => 0,
	'rechargeVal' => $user['rechargeVal'] + $chargeMoney,
	'lastRechargeDate' => date("Y-m-d")
);

$gameData->updateUser($updateData);
$gameData->addChargeCount($chargeMoney);

$pay_name = "coin_".$addCoin;
$pay_time = date("Y-m-d H:i:s");
$amount = $chargeMoney/100;
helper_recharge_record($unionid, $pay_name, $pay_time, $amount, $user['channel']);

helper_sendMsg(array (
	'errno' => 1000,
	'coinNum' => $user['score'] + $addCoin
));

?>
