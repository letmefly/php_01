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
		$user['roomCardNum'] = 1000;
		$user['score'] = 0;
		$user['win'] = 0;
		$user['lose'] = 0;
		$user['level'] = 0;
		$user['isInvited'] = 1;
		$user['inviteTimes'] = 0;
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

	public function updateUser($data) {
		if (!$data || !isset($data['unionid'])) {
			helper_log("[updateUserInfo] param invalid");
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

	public function getUnionid($userno) {
		return $this->ssdb->hget($this->userno2unionid_map, ''.$userno);
	}
}

?>