<?php
require_once(__DIR__ . "/config.inc.php");
require_once(__DIR__ . "/functions.php");

// User settings
$jiraUsername = ($currentUser) ? $currentUser->get("jira_username") : null;
$jiraPassword = ($currentUser) ? $currentUser->get("jira_password") : null;
$assignee     = ($currentUser) ? $currentUser->get("jira_assignee") : null;

// Local machine settings
$sshUsername  = ($currentUser) ? $currentUser->get("ssh_username") : null;
$sshPassword  = ($currentUser) ? $currentUser->get("ssh_password") : null;

// Construct our basic headers
$headers     = [
	"header" => [
		"Content-Type: application/json"
	],
	"user"   => [
		$jiraUsername,
		$jiraPassword
	]
];

// These are JIRA's ID's for these issue types
$issueTypes  = [
	1 => "Bug",
	2 => "New Feature",
	3 => "Task",
	4 => "Improvement"
];
asort($issueTypes);

// Browse URL is used to provide links to JIRA issues; API URL is for making cURL calls
$browseUrl  = $baseUrl . "/browse";
$apiUrl     = $baseUrl . "/rest/api/2";

// Pages that shouldn't be accessed when authenticated
$nonAuthPages = [
	"login"    => "create",
	"register" => "manage"
];

// Pages that shouldn't be accessed when not authenticated
$authPages   = [
	"manage" => "login",
	"create" => "login"
];

// Menu
$menuItems = [
	"create"   => [
		"name"            => "Create a New Jira",
		"require_session" => true
	],
	"manage"   => [
		"name"            => "Manage Account",
		"require_session" => true
	],
	"login" => [
		"name"            => "Login",
		"if_no_session"   => true
	],
	"register" => [
		"name"            => "Register",
		"if_no_session"   => true
	],
	"download" => [
		"name"            => "Download GOJIRA",
		"require_session" => true,
		"class"           => "bash-download"
	]
];

// Define the majority of our constants
define("SYSTEM_NAME",    $systemName);
define("DOMAIN",         $domain);
define("HEADERS",        serialize($headers));
define("ISSUE_TYPES",    serialize($issueTypes));
define("BROWSE_URL",     $browseUrl);
define("API_URL",        $apiUrl);
define("ASSIGNEE",       $assignee);
define("BRANCH_FIELD",   $branchField); // JIRA's field ID for the issue's branch
define("ZENDESK_FIELD",  $zendeskField); // JIRA's field ID for the issue's Zendesk ticket
define("PROJECT_ID",     $projectId); // JIRA's ID for the main project
define("CURRENT_VER",    $currentVer);
define("SSH_USERNAME",   $sshUsername);
define("SSH_PASSWORD",   $sshPassword);
define("NON_AUTH_PAGES", serialize($nonAuthPages));
define("AUTH_PAGES",     serialize($authPages));
define("MENU_ITEMS",     serialize($menuItems));

// Handle SVN trunk and dev URL replacement
$svnMatch = preg_match_all("/{{[A-Za-z0-9_]+}}/", $svnTrunk, $matches);
if ($svnMatch) {
	foreach($matches[0] as $match) {
		$matchMatch = preg_match("/[A-Za-z0-9_]+/", $match, $svnMatches);

		if (!$matchMatch) {
			continue;
		}

		$value    = constant($svnMatches[0]);
		$svnTrunk = str_replace($match, $value, $svnTrunk);
	}
}

$svnMatch = preg_match_all("/{{[A-Za-z0-9_]+}}/", $svnDev, $matches);
if ($svnMatch) {
	foreach($matches[0] as $match) {
		$matchMatch = preg_match("/[A-Za-z0-9_]+/", $match, $svnMatches);

		if (!$matchMatch) {
			continue;
		}

		$value  = constant($svnMatches[0]);
		$svnDev = str_replace($match, $value, $svnDev);
	}
}

define("SVN_TRUNK", $svnTrunk);
define("SVN_DEV",   $svnDev);

// Some pages, like get.php, don't need this data, so they pass a $skipLoad flag
if (!$skipLoad && $_SESSION["auth"]) {
	// Get the latest development version from JIRA
	if (!isset($_SESSION["fix_versions"])) {
		$majorVersion = explode(".", CURRENT_VER)[0];
		$response     = doCurl(API_URL . "/project/" . PROJECT_ID . "/versions", unserialize(HEADERS));

		if (!processErrors($response)) {
			exit("There was an error fetching the versions from JIRA.");
		}

		$latestVersion = end($response);
		while (substr($latestVersion["name"], 0, strlen($majorVersion)) !== $majorVersion) {
			$unsetKeys = array_keys($response);
			$unsetKey  = end($unsetKeys);
			unset($response[$unsetKey]);
			$latestVersion = end($response);
		}

		$fixVersions   = [
			$latestVersion["id"] => $latestVersion["name"]
		];
		$_SESSION["fix_versions"] = $fixVersions;
	} else {
		$fixVersions = $_SESSION["fix_versions"];
	}

	define("FIX_VERSIONS", serialize($fixVersions));

	// Pull a full list of components from JIRA
	if (!isset($_SESSION["components"])) {
		$response   = doCurl(API_URL . "/project/" . PROJECT_ID . "/components", unserialize(HEADERS));
		if (!processErrors($response)) {
			exit("There was an error fetching the components from JIRA.");
		}

		$components = [];
		foreach ($response as $component) {
			$components[$component["id"]] = $component["name"];
		}

		asort($components);
		$_SESSION["components"] = $components;
	} else {
		$components = $_SESSION["components"];
	}

	define("COMPONENTS", serialize($components));
}

unset($systemName);
unset($currentVer);
unset($baseUrl);
unset($projectId);
unset($branchField);
unset($zendeskField);
unset($svnTrunk);
unset($svnDev);
unset($jiraUsername);
unset($jiraPassword);
unset($assignee);
unset($sshUsername);
unset($sshPassword);
?>
