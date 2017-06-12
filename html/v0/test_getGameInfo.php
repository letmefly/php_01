<?php
include_once('../lib/SSDB.php');
include_once('../lib/helper.php');
include_once('GameData.php');


$gameData = new GameData ();

$gameInfo = $gameData->getGameInfo();

print(json_encode($gameInfo));

