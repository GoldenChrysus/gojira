<?php
require_once(__DIR__ . "/../includes/include.php");

if ($_POST) {
	$username = $_POST["username"];
	$password = $_POST["password"];
	$result   = User::tryLogin($username, $password);

	if (!$result) {
		echo "<div class='ui error message'>";
		echo "<i class='remove icon'></i>Login failed. Please try again.";
		echo "</div>";
	}
}
?>
