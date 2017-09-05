<?php

$postdata = file_get_contents("php://input");
if ($postdata == '') {
	helper_log('[helper] post data is blank..');
	return '';
}
$msg64 = base64_decode($postdata);
$msg = json_decode($msg64, true);
$inapp_purchase_data = $msg["inapp_purchase_data"];
$inapp_data_signature = $msg["inapp_data_signature"];
$google_public_key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv+pHxrrEKT6qlJ/uQdI+qAlyCh7TdUGMyebXhWg5qT38SDpDo1H2SsUKnHoM7q+Yjkpr4RJHeGZlOO27DC/FkoN/zDv6PX4Ua6BUyJHd4hzrnAq572zaeJVAIE4JnCrCPsiEBZas581xf9T+VmqqMbmGkKDyzHjZqov6lZoEr3UdwU/xESf4P0LtLPyqNpzkukFMoDXJf0W2DpdKc0uoajGdsEJ5GXytkL+1/DXKOCmO59Ieid9ncxzs71Nyw6WuHbTiVW4tcMSAJcPn4zfX2TCCJTpbRXPAskbDEAdNxyj9DThyWLnEg4ykQr7XqoAxYTm/oeBpaopaIcrU8KwTNwIDAQAB';

$public_key = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($google_public_key, 64, "\n") . "-----END PUBLIC KEY-----";

$public_key_handle = openssl_get_publickey($public_key);

$result = openssl_verify($inapp_purchase_data, base64_decode($inapp_data_signature, $public_key_handle, OPENSSL_ALGO_SHA1);

$retMsg = array();
if (1 === $result) {
    $retMsg["result"] = "ok";
}
else {
	$retMsg["result"] = "fail";
}
$retJson = json_encode($retMsg);
$retStr = base64_encode($retJson);
echo $retStr;


?>

