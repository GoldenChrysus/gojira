<?php
if ($requiresAuth && !$_SESSION["auth"]) {
	header("Location: login");
	exit();
}

if ($_SESSION["auth"]) {
	$currentUser = new User($_SESSION["user_id"]);
	
	if (!$currentUser) {
		unset($_SESSION);
		session_destroy();
		header("Location: login?error");
		exit();
	}
}
?>
