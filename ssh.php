<?php
$requiresAuth = true;
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/includes/phpseclib");
require_once(__DIR__ . "/includes/include.php");
include("Net/SSH2.php");

$ip  = ($currentUser->get("ssh_ip")) ?: $_SERVER["REMOTE_ADDR"];
echo "Using IP: {$ip}<br>";

$ssh = new Net_SSH2($ip);
if (!$ssh->login(SSH_USERNAME, SSH_PASSWORD)) {
	exit("Login failed.");
}

echo "Login succeeded. Running whoami.<br>";
echo $ssh->exec("whoami");
?>
