<?php
$sqlHost     = "localhost";
$sqlUsername = "";
$sqlPassword = "";
$sqlDatabase = "";

$dsn         = "mysql:host={$sqlHost};dbname={$sqlDatabase};charset=utf8";
$options     = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo         = new PDO($dsn, $sqlUsername, $sqlPassword, $options);
?>
