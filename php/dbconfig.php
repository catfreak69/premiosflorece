<?php
function PDOCreateConn() {
	
	$serverName = "SJUSQDV09";
	$uid = "instantmed";
	$pwd = "Aa123456";
	$dbname = "imed";
	
	$PDOconn = new PDO("sqlsrv:server=" . $serverName . ";Database=" . $dbname, $uid, $pwd);
	return $PDOconn;
}
?>