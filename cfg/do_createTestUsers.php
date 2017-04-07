<?php
$imageBaseUrl = "http://192.168.16.83:80/php_01/cfg/jpg/";
$loginUrl = "http://127.0.0.1:80/php_01/html/v0/login.php";
//$loginUrl = "http://chess.ifunhealth.com:8080/html/v0/login.php";

function http_post($url, $post_data = '', $timeout = 5){//curl
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_POST, 1);
	if($post_data != ''){
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}

function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		die('possible deep recursion attack');
	}
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}
  
		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}

function JSON($array) {
	arrayRecursive($array, 'urlencode', true);
	$json = json_encode($array);
	return urldecode($json);
}

function helper_encode($dataArray) {
	$jsonStr = json_encode($dataArray);
	$base64Str = base64_encode($jsonStr);
	$private_key = "fuck_angelababy";
	$msg = array('msg' => $base64Str, 'sign' => md5($base64Str . $private_key));
	return json_encode($msg);
}

$max_test_user_num = 100;

for ($i=1; $i <= $max_test_user_num; $i++) { 
	$nickname = "test_".$i;
	$unionid = "test_race_".$i;
	$sex = $i % 2 + 1;
	$city = "beijing";
	$headimgurl = "";
	$data = array(
		"unionid" => $unionid,
		"nickname" => $nickname,
		"sex" => $sex,
		"headimgurl" => $headimgurl,
		"city" => $city,
		"urlencode" => 1,
		"os" => "ios",
        "loginType" => "weixin"
	);

	$private_key = "fuck_angelababy";

	$msgStr = helper_encode($data);
	$retStr = http_post($loginUrl, $msgStr);
	$msgRaw = json_decode($retStr, true);
	$msgJson = $msgRaw['msg'];
	$msgSigh = $msgRaw['sign'];
	//print_r(json_encode($msgRaw));
	//print_r($retStr);
	//print_r(md5($msgJson . $private_key) );
	//print_r($msgSigh);
	if (md5($msgJson . $private_key) != $msgSigh) {
		print_r('sign is not right!!');
		exit();
	}

	$msg64 = base64_decode($msgJson);
	$msg = json_decode($msg64, true);
	print_r($msg64."\n");
}

?>

