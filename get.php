<?php
$skipLoad = true;
require_once(__DIR__ . "/includes/include.php");
$jira        = preg_replace("/[^0-9]+/", "", $_GET["id"]);
$field       = $_GET["field"];
$userId      = $_GET["user_id"];
$token       = $_GET["token"];
$user        = new User($userId);

if (!$user) {
	exit("There was an error.");
}

if ($token !== $user->get("token")) {
	exit("There was an error.");
}

$headers     = [
	"header" => "Content-Type: application/json",
	"user"   => [
		$user->get("jira_username"),
		$user->get("jira_password")
	]
];

$response = doCurl(API_URL . "/issue/{$jira}", $headers);
if (!processErrors($response)) {
	return false;
}

if (isset($response["fields"][$field])) {
	echo $response["fields"][$field];
}

return false;
?>
