<?php
$csvFile = fopen("csv/names.csv","r");
$count = 1;
$imageBaseUrl = "http://192.168.16.83:80/php_01/cfg/jpg/";
$loginUrl = "http://192.168.16.83:80/php_01/html/v0/login.php";
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

while(!feof($csvFile)) {
	$row = fgetcsv($csvFile);
	$nickname = $row[0];
	if ($nickname == "") continue;
	$unionid = "score_race_ai_".$count;
	$sex = $count % 2 + 1;
	$city = "beijing";
	$headimgurl = $imageBaseUrl.$count.".jpg";
	$data = array(
		"unionid" => $unionid,
		"nickname" => urlencode($nickname),
		"sex" => $sex,
		"headimgurl" => $headimgurl,
		"city" => $city,
		"urlencode" => 1
	);

	//$dataStr = json_encode($data);
	//$data = json_decode($dataStr, true);
	//print_r(urldecode($data['nickname'])."\n");
	//print_r($dataStr."\n");

	//$base64Str = base64_encode($dataStr);
	$private_key = "fuck_angelababy";
	//$msg = array('msg' => $base64Str, 'sign' => md5($base64Str.$private_key));
	//$msgStr = json_encode($msg);
	//print_r($msgStr."\n");

	$msgStr = helper_encode($data);
	$retStr = http_post($loginUrl, $msgStr);
	$msgRaw = json_decode($retStr, true);
	$msgJson = $msgRaw['msg'];
	$msgSigh = $msgRaw['sign'];
	if (md5($msgJson . $private_key) != $msgSigh) {
		print_r('sign is not right!!');
		exit();
	}

	$msg64 = base64_decode($msgJson);
	$msg = json_decode($msg64, true);
	print_r($msg64);
	print_r(urldecode(urldecode($msg['nickname']))."\n");
  
  	$count = $count + 1;
}
fclose($csvFile);

?>

