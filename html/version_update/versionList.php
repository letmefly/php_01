<?php
//$serverVersionList = array("1.0.1", "1.0.2", "1.0.3", "1.0.4", "1.0.5", "1.0.6");
$serverVersionList = array();
$updateVersionSize = array("1.0.0"=>2048, "1.0.1"=>2345);
$serverAddr = "http://192.168.56.101/php_01/html/version_update/";
//$serverAddr = "http://chess.ifunhealth.com:8080/html/version_update/";
$client_version = $_GET['clientVersion'];
$client_os = $_GET['clientOS'];
$client_firstVersion = (int)strtok($client_version, ".");
$client_secondVersion = (int)strtok(".");
$client_thirdVersion = (int)strtok(".");
$client_int_version = $client_firstVersion*100 + $client_secondVersion*10 + $client_thirdVersion;
//print("[client]".$client_firstVersion.$client_secondVersion.$client_thirdVersion);
$updateVersionList = array();
foreach ($serverVersionList as $value) {
    $server_firstVersion = (int)strtok($value, ".");
    $server_secondVersion = (int)strtok(".");
    $server_thirdVersion = (int)strtok(".");
    //print("[server]".$server_firstVersion.$server_secondVersion.$server_thirdVersion);
    if ($server_firstVersion*100 + $server_secondVersion*10 + $server_thirdVersion > $client_int_version)
    {
        $arrayName = array( "versionUrl" => $serverAddr.$value."/version.php",
                            "packageUrl"=>$serverAddr.$value."/Hi88.zip");
        array_push($updateVersionList, $arrayName);
    }
}
if ($client_os!="ios") {
    //$updateVersionList = array("1.0.3");
}
$arr = array("code"=>200, "list"=>$updateVersionList, "clientOS"=>$client_os, "totalSize"=>1*1024);
echo stripslashes(json_encode($arr));
?>