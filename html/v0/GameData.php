<?php
include_once 'helper.php';
include_once 'SSDB.php';

ini_set('date.timezone','Asia/Shanghai');

class GameData {
	private $ssdb;
	private $user_set = 'user';
	private $user_set_prefix = 'u_';

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
		$user['roomCardNum'] = 0;
		$user['score'] = 0;
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['ip'] = '';
		$this->ssdb->hset($this->user_set, $this->user_set_prefix.$user['unionid'], json_encode($user));
	}

	public function getUser($unionid) {
		$userJson = $this->ssdb->hget($this->user_set, $this->user_set_prefix.$unionid);
		if ($userJson) {
			return json_decode($userJson, true);
		}
		return array()
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
}

