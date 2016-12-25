<?php
include_once 'helper.php';
include_once 'SSDB.php';

ini_set('date.timezone','Asia/Shanghai');

class GameData {
	private $ssdb;
	private $user_set = 'user';
	private $user_set_prefix = 'u_';
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
		$user['roomCardNum'] = 1000;
		$user['score'] = 0;
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['level'] = 0;
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$user['unionid'], json_encode($user));
	}

	public function getUser($unionid) {
		$userJson = $this->ssdb->hget($this->user_set, $this->user_set_prefix.$unionid);
		if ($userJson) {
			return json_decode($userJson, true);
		}
		return array();
	}

	public function updateUser($data) {
		if (!$data || !isset($data['unionid'])) {
			helper_log("[updateUserInfo] param invalid");
			return;
		}
		$unionid = $data['unionid'];
		$user = $this->getUser($unionid);
		foreach ($data as $key => $value) {
			if (isset($user[$key])) {
				$user[$key] = $value;
			}
		}
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$unionid, json_encode($user));
	}

	public function insertRoomResult($unionid, $roomResult) {
		$roomResult['time'] =  date('Y-m-d G:i:s');
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
}

