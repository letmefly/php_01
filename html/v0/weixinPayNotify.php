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


echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
?>
