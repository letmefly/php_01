<?php
include_once '../lib/helper.php';
include_once '../lib/SSDB.php';

ini_set('date.timezone','Asia/Shanghai');

class GameData {
	private $ssdb;
	private $user_set = 'user';
	private $user_set_prefix = 'u_';
	private $userno2unionid_map = 'userno2unionid';
	private $roomresult_set_prefix = 'roomresult_';

	function __construct() {
		try {
		    $this->ssdb = new SimpleSSDB('127.0.0.1', 9999);
		} catch(SSDBException $e){
		    die(__LINE__ . ' ' . $e->getMessage());
		}
	}

	function __destruct() {
		$this->ssdb->close();
	}

	public function addUser($user) {
		$user['roomCardNum'] = 30;
		$user['score'] = 400;
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['level'] = 0;
		$user['isInvited'] = 1;
		$user['inviteTimes'] = 0;
		$user['redPackVal'] = 0;
		$user['userno'] = $this->ssdb->hsize($this->user_set)+1+100000;
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$user['unionid'], json_encode($user));
		$this->ssdb->hset($this->userno2unionid_map, ''.$user['userno'], $user['unionid']);
	}

	public function getUser($unionid) {
		$ret = array();
		$userJson = $this->ssdb->hget($this->user_set, $this->user_set_prefix.$unionid);
		if ($userJson) {
			$ret = json_decode($userJson, true);
			if (isset($ret['userno']) == false) {
				$userno = rand(1000,99999);
				$ret['userno'] = ''.$userno;
				$this->ssdb->hset($this->userno2unionid_map, ''.$userno, $ret['unionid']);
				//$this->updateUser(array('unionid' =>$unionid, 'userno' => ''.$userno));
			}
		}
		return $ret;
	}

	public function updateUser2($user, $data) {
		if (!$data || !isset($data['unionid'])) {
			helper_log("[updateUser2] param invalid");
			return;
		}
		$unionid = $data['unionid'];
		foreach ($data as $key => $value) {
			$user[$key] = $value;
		}
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$unionid, json_encode($user));
	}

	public function updateUser($data) {
		if (!$data || !isset($data['unionid'])) {
			helper_log("[updateUser] param invalid");
			return;
		}
		$unionid = $data['unionid'];
		$user = $this->getUser($unionid);
		foreach ($data as $key => $value) {
			$user[$key] = $value;

			//if (isset($user[$key]) || $key=='userno' || $key=='isInvited' || $key == "inviteTimes") {
			//	$user[$key] = $value;
			//}
		}
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$unionid, json_encode($user));
	}

	public function insertRoomResult($unionid, $roomResult) {
		$roomResult['t'] =  date('Y-m-d G:i:s');
		$set_name = $this->roomresult_set_prefix . $unionid;
		//$size = $ssdb->qsize($set_name);
		$itemStr = json_encode($roomResult);
		$ret = $this->ssdb->qpush_front($set_name, $itemStr);
		return $ret;
	} 

	public function getRoomResults($unionid, $offset) {
		$set_name = $this->roomresult_set_prefix . $unionid;
		$ret = $this->ssdb->qrange($set_name, $offset, 30);
		return $ret;
	}

	public function insertExchangeRecord($unionid, $record) {
		$record['t'] = date('Y-m-d G:i:s');
		$set_name = "ex_" . $unionid;
		$itemStr = json_encode($record);
		$ret = $this->ssdb->qpush_front($set_name, $itemStr);
		return $ret;
	}

	public function getExchangeRecord($unionid, $offset) {
		$set_name = "ex_" . $unionid;
		$ret = $this->ssdb->qrange($set_name, $offset, 30);
		return $ret;
	}		

	public function getUnionid($userno) {
		return $this->ssdb->hget($this->userno2unionid_map, ''.$userno);
	}

	public function setActivity($activityInfo) {
		$originActInfo = $this->getActivity();
		if (!$originActInfo) {
			$originActInfo = array();
			if (isset($originActInfo['activitySwitch']) == false) {
				$originActInfo['activitySwitch'] = 'on';
			}
			if (isset($originActInfo['rate_120']) == false) {
				$originActInfo['rate_120'] = 33;
			}
			if (isset($originActInfo['rate_80']) == false) {
				$originActInfo['rate_80'] = 33;
			}
			if (isset($originActInfo['rate_40']) == false) {
				$originActInfo['rate_40'] = 34;
			}
		}
		foreach ($activityInfo as $key => $value) {
			$originActInfo[$key] = $value;
		}
		$this->ssdb->set("k_activityInfo", json_encode($originActInfo));
	}

	public function getActivity() {
		$str = $this->ssdb->get("k_activityInfo");
		return json_decode($str, true);
	}

	public function getAppstoreOrderId($orderId) {
		return $this->ssdb->hget("appstore_orderid_set", $orderId);
	}
	public function setAppstoreOrderId($orderId, $val) {
		$this->ssdb->hset("appstore_orderid_set", $orderId, $val);
	}
	public function getRedPackCount() {
		return $this->ssdb->get("redpack_count");
	}
	public function addRedPackCount($redPackVal) {
		$redPackCount = $this->getRedPackCount();
		if (empty($redPackCount)) {
			$redPackCount = 0;
		}
		$redPackCount = $redPackVal + $redPackCount;
		$this->ssdb->set("redpack_count", $redPackCount);
	}
	public function getChargeCount() {
		return $this->ssdb->get("charge_count");
	}
	public function addChargeCount($chargeVal) {
		$chargeCount = $this->getChargeCount();
		if (empty($chargeCount)) {
			$chargeCount = 0;
		}
		$chargeCount = $chargeVal + $chargeCount;
		$this->ssdb->set("charge_count", $chargeCount);
	}
	public function isAddCoinToday($unionid) {
		$loginSetName = "login-".date("Y-m-d");
		$ret = $this->ssdb->hget($loginSetName, $unionid);
		if ($ret) {
			return true;
		}
		else {
			$this->ssdb->hset($loginSetName, $unionid, 1);
			return false;
		}
	}

	public function insertWeixinPayInfo($outTradeNo, $transaction_id, $payInfo) {
		$this->ssdb->hset("wexin_pay_set", $transaction_id, $payInfo);
		$this->ssdb->hset("wexin_outTradeNo_set", $outTradeNo, 1);
	}

	public function getOutTradeNoRecord($outTradeNo) {
		return $this->ssdb->hget("wexin_outTradeNo_set", $outTradeNo);
	}

	public function clearOutTradeNoRecord($outTradeNo) {
		$this->ssdb->hset("wexin_outTradeNo_set", $outTradeNo, 0);
	}

	public function addUser_reward($user) {
		$isAdd = false;
		$unionid = $user['unionid'];
		if (isset($user['add_roomCardNum']) && $user['add_roomCardNum'] > 0) {
			$isAdd = true;
			$user['roomCardNum'] = $user['roomCardNum'] + $user['add_roomCardNum'];
			$user['add_roomCardNum'] = 0;
		}
		if (isset($user['add_score']) && $user['add_score'] > 0) {
			$isAdd = true;
			$user['score'] = $user['score'] + $user['add_score'];
			$user['add_score'] = 0;
		}
		if (isset($user['add_redPackVal']) && $user['add_redPackVal'] > 0) {
			$isAdd = true;
			$user['redPackVal'] = $user['redPackVal'] + $user['add_redPackVal'];
			$user['add_redPackVal'] = 0;
		}
		if ($isAdd == true) {
			$this->ssdb->hset($this->user_set, $this->user_set_prefix.$unionid, json_encode($user));
		}
		return $user;
	}
}

?>