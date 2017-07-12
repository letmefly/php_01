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

	// mysql
	private $db_name = "doudizhu";
	private $db_ip = "127.0.0.1";
	private $db_user = "root";
	private $db_pw = "root123!";
	private $connect;

	function __construct() {
		try {
		    $this->ssdb = new SimpleSSDB('127.0.0.1', 9999);
		} catch(SSDBException $e){
		    die(__LINE__ . ' ' . $e->getMessage());
		}
		$this->connectMysql();
	}

	function __destruct() {
		$this->ssdb->close();
		$this->closeMysql();
	}

	// mysql
	function connectMysql() {
		$this->connect = mysql_connect($this->db_ip, $this->db_user, $this->db_pw);
		if (!$this->connect) {
			helper_log("connect mysql fail");
			return false;
		}
		mysql_select_db($this->db_name, $this->connect);
		return true;
	}
	function closeMysql() {
		mysql_close($this->connect);
	}
	public function addUser_mysql($user) {
		$user['roomCardNum'] = 30;
		if (substr($user['unionid'], 0, 14) == "score_race_ai_") {
			$user['score'] = 99999999;
			$user['score2'] = 99999999;
		}
		else {
			$user['score'] = 36;
			$user['score2'] = 48;
		}
		
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['level'] = 0;
		$user['isInvited'] = 1;
		$user['inviteTimes'] = 0;
		$user['redPackVal'] = 0;
		$user['registerTime'] = date('Y-m-d G:i:s');
		$user['getRedPackTime'] = time();
		$user['loginDayCount'] = 0;
		$user['todayRedPackCount'] = 0;
		$user['lastLoginTime'] = "";
		$user['isAcceptDailyReward'] = 0;
		$user['rechargeVal'] = 0;
		$user['lastRechargeDate'] = "";
		$user['add_score'] = 0;
		$user['add_roomCardNum'] = 0;
		$user['add_redPackVal'] = 0;
		$sql = helper_getInsertSQL("op_user", $user);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insertUser failed ". $sql);
			return false;
		}
		return true;
	}
	public function getUser_mysql($unionid) {
		if (!$this->connect) {
			helper_log("connect invalid");
			return false;
		}
		$sql = "SELECT * FROM op_user WHERE unionid={$unionid}";
		$result = mysql_query($sql, $this->connect);
		if (!$result) {
			helper_log("select failed");
			return false;
		}
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		return $row;
	}
	public function updateUser_mysql($userData) {
		if (!$userData['unionid']) {
			helper_log("param invalid");
			return false;
		}
		if (!$this->connect) {
			helper_log("connect invalid");
			return false;
		}
		$sql = helper_getUpdateSQL("op_user", "unionid", $userData);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("updateUser failed");
			return false;
		}
		return true;
	}
	public function addRechargeRecord_mysql($rechargeRecord) {
		$sql = helper_getInsertSQL("op_recharge_record", $rechargeRecord);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insert op_recharge_record failed ". $sql);
			return false;
		}
		return true;
	}
	public function addRedpackRecord_mysql($redpackRecord) {
		$sql = helper_getInsertSQL("op_getRedpack_record", $redpackRecord);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insert op_recharge_record failed ". $sql);
			return false;
		}
		return true;
	}
	public function addGameResult_mysql($gameResult) {
		$sql = helper_getInsertSQL("op_user_game_record", $gameResult);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insert op_user_game_record failed ". $sql);
			return false;
		}
		return true;
	}

	public function insertWeixinOrderInfo_mysql($orderInfo) {
		$sql = helper_getInsertSQL("op_wexin_pay_record", $orderInfo);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insert op_user_game_record failed ". $sql);
			return false;
		}
		return true;
	}

	public function insertAddScoreLog_mysql($addScoreLog) {
		$sql = helper_getInsertSQL("op_score_log", $addScoreLog);
		if (!mysql_query($sql, $this->connect)) {
			helper_log("insert op_score_log failed ". $sql);
			return false;
		}
		return true;
	}


	public function addUser($user) {
		$user['roomCardNum'] = 30;
		if (substr($user['unionid'], 0, 14) == "score_race_ai_") {
			$user['score'] = 99999999;
			$user['score2'] = 99999999;
		}
		else {
			$user['score'] = 36;
			$user['score2'] = 48;
		}
		
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['level'] = 0;
		$user['isInvited'] = 1;
		$user['inviteTimes'] = 0;
		$user['redPackVal'] = 0;
		$user['registerTime'] = date('Y-m-d G:i:s');
		$user['getRedPackTime'] = time();
		$user['loginDayCount'] = 0;
		$user['todayRedPackCount'] = 0;
		$user['lastLoginTime'] = "";
		$user['isAcceptDailyReward'] = 0;
		$user['rechargeVal'] = 0;
		$user['lastRechargeDate'] = "";
		$user['add_score'] = 0;
		$user['add_roomCardNum'] = 0;
		$user['add_redPackVal'] = 0;
		$user['userno'] = $this->ssdb->hsize($this->user_set)+1+100000;
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$user['unionid'], json_encode($user));
		$this->ssdb->hset($this->userno2unionid_map, ''.$user['userno'], $user['unionid']);

		// mysql
		$this->addUser_mysql($user);
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

		// mysql 
		$this->updateUser_mysql($data);
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
		}
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$unionid, json_encode($user));

		// mysql
		$this->updateUser_mysql($data);
	}

	public function insertRoomResult($unionid, $roomResult) {
		$roomResult['t'] =  date('Y-m-d G:i:s');
		$set_name = $this->roomresult_set_prefix . $unionid;
		if ($this->ssdb->qsize($set_name) >= 10) {
			$this->ssdb->qpop_back($set_name);
		}
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
	public function getSmallRedPackCount() {
		return $this->ssdb->get("redpack_small_count");
	}
	public function addSmallRedPackCount($redPackVal) {
		$redPackCount = $this->getSmallRedPackCount();
		if (empty($redPackCount)) {
			$redPackCount = 0;
		}
		$redPackCount = $redPackVal + $redPackCount;
		$this->ssdb->set("redpack_small_count", $redPackCount);
	}
	public function getSmallRedPackPlayTimes() {
		return $this->ssdb->get("redpack_small_times");
	}
	public function addSmallRedPackPlayTimes() {
		$times = $this->getSmallRedPackPlayTimes();
		if (empty($times)) {
			$times = 0;
		}
		$times = $times + 1;
		$this->ssdb->set("redpack_small_times", $times);
	}
	public function getBigRedPackPlayTimes() {
		return $this->ssdb->get("redpack_big_times");
	}
	public function addBigRedPackPlayTimes() {
		$times = $this->getBigRedPackPlayTimes();
		if (empty($times)) {
			$times = 0;
		}
		$times = $times + 1;
		$this->ssdb->set("redpack_big_times", $times);
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

	public function insertWeixinPayInfo($outTradeNo, $transaction_id, $payInfo, $cash_fee) {
		$this->ssdb->hset("wexin_pay_set", $transaction_id, $payInfo);
		$this->ssdb->hset("wexin_outTradeNo_set", $outTradeNo, intval($cash_fee));
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
			$addScoreLog = array(
				'unionid' => $user['unionid'],
				'nickname' => $user['nickname'],
				'time' => date('Y-m-d G:i:s'),
				'old_score' => $user['score'],
				'add_score' => $user['add_score'],
				'now_score' => $user['score'] + $user['add_score'],
				'add_way' => "houtai"
			);
			$gameData->insertAddScoreLog_mysql($addScoreLog);

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
			// mysql
			$updateData = array(
				'unionid' => $user['unionid'],
				'roomCardNum' => $user['roomCardNum'],
				'add_roomCardNum' => $user['add_roomCardNum'],
				'score' => $user['score'],
				'add_score' => $user['add_score'],
				'redPackVal' => $user['redPackVal'],
				'add_redPackVal' => $user['add_redPackVal']
			);
			$this->updateUser_mysql($updateData);
		}
		return $user;
	}

	public function addRewardPool($rewardVal) {
		if ($this->ssdb->exists('k_rewardPool') == false) {
			$this->ssdb->set("k_rewardPool", $rewardVal);
		}
		else {
			$this->ssdb->incr("k_rewardPool", $rewardVal);
		}
	}

	public function getRewardPoolVal() {
		$poolVal = $this->ssdb->get("k_rewardPool");
		if (empty($poolVal)) {
			$poolVal = 0;
		}
		return $poolVal;
	}

	public function getGameInfo() {
		$ret = array();
		$totalPlayTimes = 0;
		$totalRedPackVal = 0;
		$someUserInfo = array();
		$allUserInfo = $this->ssdb->hgetall($this->user_set);
		foreach ($allUserInfo as $key => $value) {
			$userInfo = json_decode($value, true);
			$totalPlayTimes = $totalPlayTimes + $userInfo['win'] + $userInfo['lose'];
			$totalRedPackVal = $totalRedPackVal + $userInfo['redPackVal'];
			if ($userInfo['win'] + $userInfo['lose'] >= 5 && $userInfo['userno'] > 100300 && $userInfo['userno'] %80 == 0) {
				array_push($someUserInfo, array('unionid'=>$userInfo['unionid'],'win'=>$userInfo['win'], 'lose'=>$userInfo['lose'], 'redPackVal'=>$userInfo['redPackVal']));
			}
		}
		$ret['totalPlayTimes'] = $totalPlayTimes;
		$ret['totalRedPackVal'] = $totalRedPackVal;
		$ret['someUserInfo'] = $someUserInfo;
		return $ret;
	}

	public function addNotice($noticeStr, $level) {
		if ($level == 1) {
			if ($this->ssdb->qsize('notice_queue_1') >= 100) {
				$this->ssdb->qpop_back('notice_queue_1');
			}
			$this->ssdb->qpush_front("notice_queue_1", $noticeStr);
		}
		else {
			if ($this->ssdb->qsize('notice_queue_2') >= 100) {
				$this->ssdb->qpop_back('notice_queue_2');
			}
			$this->ssdb->qpush_front("notice_queue_2", $noticeStr);
		}
	}

	public function getNoticeList() {
		$noticeList = array();
		$noticeList['level_1'] = $this->ssdb->qrange("notice_queue_1", 0, 10);
		$noticeList['level_2'] = $this->ssdb->qrange("notice_queue_2", 0, 10);
		return $noticeList;
	}
}

?>

