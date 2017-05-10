<?php
session_start();

// Get the referring URI page
$referringPage = explode("?", array_slice(array_values(explode("/", $_SERVER["HTTP_REFERER"])), -1)[0])[0];
$action        = false;

// Ensure the referring page action matches the user session
// e.g. a logged in user should not be on the register page, and a user should not be on the manage page if they are not logged in
if ($referringPage === "register" && !$_SESSION["auth"]) {
	$action = "register";
} else if ($referringPage === "manage" && $_SESSION["auth"]) {
	$action = "update";
}

// If the action didn't match, output an error
if (!$action) {
	echo "<div class='ui error message'>";
	echo "<i class='checkmark icon'></i>AJAX anti-tamper check failed. Please try again.";
	echo "</div>";
	return;
}

// Now we can include our main loader file, because there's no point in including it before the anti-tamper check
require_once(__DIR__ . "/../includes/include.php");

if (!$_POST) {
	echo "<div class='ui error message'>";
	echo "<i class='checkmark icon'></i>POST request not received. Please try again.";
	echo "</div>";
	return;
}

$username    = $_POST["username"];
$password    = $_POST["password"];
$jiraUser    = trim($_POST["jira_username"]);
$jiraPass    = trim($_POST["jira_password"]);
$useSSH      = ($_POST["ssh"] === "on") ? true : false;
$sshUsername = $_POST["ssh_username"];
$sshPassword = $_POST["ssh_password"];
$sshIp       = $_POST["ssh_ip"];
$devFolder   = $_POST["dev_folder"];

// Check if the username already exists
if ($action === "register" && User::checkIfUsernameExists($username)) {
	echo "<div class='ui error message'>";
	echo "<i class='remove icon'></i>That username is already taken. Please choose another.";
	echo "</div>";
	return false;
}

// Do a quick API call to see if the user passed the correct JIRA credentials
$headers     = [
	"header" => [
		"Content-Type: application/json"
	],
	"user"   => [
		$jiraUser,
		$jiraPass
	]
];

$response = doCurl(API_URL . "/myself", $headers);
if (!processErrors($response, false)) {
	echo "<div class='ui error message'>";
	echo "<i class='remove icon'></i>Could not validate your JIRA credentials. Please try again.";
	echo "</div>";
	return false;
}
$jiraAssignee = $response["key"];

// Cleanup the SSH variables based on the SSH flag
if (!$useSSH) {
	$sshUsername = null;
	$sshPassword = null;
	$sshIp       = null;
}
$useSSH = intval($useSSH);

// Insert the user into the database
$userData = [
	"username"      => [
		"value" => $username,
		"type"  => "string"
	],
	"password"      => [
		"value" => $password,
		"type"  => "string"
	],
	"jira_username" => [
		"value" => $jiraUser,
		"type"  => "string"
	],
	"jira_password" => [
		"value" => $jiraPass,
		"type"  => "string"
	],
	"jira_assignee" => [
		"value" => $jiraAssignee,
		"type"  => "string"
	],
	"use_ssh"       => [
		"value" => $useSSH,
		"type"  => "int"
	],
	"ssh_username"  => [
		"value" => $sshUsername,
		"type"  => "string"
	],
	"ssh_password"  => [
		"value" => $sshPassword,
		"type"  => "string"
	],
	"ssh_ip"        => [
		"value" => $sshIp,
		"type"  => "string"
	],
	"dev_folder"    => [
		"value" => $devFolder,
		"type"  => "string"
	]
];

if ($action === "update") {
	foreach ($userData as $key => $value) {
		if ($value["value"] === $currentUser->get($key)) {
			unset($userData[$key]);
		}
	}

	if ($currentUser->update($userData)) {
		echo "<div class='ui success message'>";
		echo "<i class='checkmark icon'></i>Your account was successfully updated.";
		echo "</div>";
		return true;
	}

	return false;
}

$user = User::createUser($userData);

if (!$user) {
	echo "<div class='ui error message'>";
	echo "<i class='remove icon'></i>There was an error creating your account. Please try again.";
	echo "</div>";
	return false;
}

$_SESSION["auth"]    = true;
$_SESSION["user_id"] = $user->get("id");

// At this point, registration was a success, but we output null so that the system knows to forward the user to the JIRA creation page
?>
