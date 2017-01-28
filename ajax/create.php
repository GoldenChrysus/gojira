<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/../includes/phpseclib");
require_once(__DIR__ . "/../includes/include.php");
include("Net/SSH2.php");
if ($_POST) {
	// Get our posted variables
	$title       = $_POST["title"];
	$issueType   = $_POST["issue_type_hidden"];
	$components  = $_POST["components"];
	$zendesk     = $_POST["zendesk"];
	$description = $_POST["description"];

	// Construct a data array to send to JIRA's API
	$newJira = [
		"fields" => [
			"project"     => [
				"id" => (int) PROJECT_ID
			],
			"issuetype"   => [
				"id" => (int) $issueType
			],
			"fixVersions" => [
				[
					"id" => (string) array_keys(unserialize(FIX_VERSIONS))[0]
				]
			],
			"assignee"    => [
				"name" => (string) ASSIGNEE
			],
			"summary"     => (string) $title,
			"description" => (string) $description
		]
	];

	// Only set the Zendesk field if the user specified a URL
	if ($zendesk) {
		$newJira["fields"][ZENDESK_FIELD] = (string) $zendesk;
	}

	// Add the selected components to the JIRA data array
	if (is_array($components) && is_array($components[0])) {
		$newJira["fields"]["components"] = [];
		foreach ($components[0] as $component) {
			$newJira["fields"]["components"][] = [
				"id" => (string) $component
			];
		}
	}

	// Debug code
	/* $ip  = ($currentUser->get("ssh_ip")) ?: $_SERVER["REMOTE_ADDR"];
	echo "Connecting to {$ip}.<br>";
	$ssh = new Net_SSH2($ip);
	if (!$ssh->login(SSH_USERNAME, SSH_PASSWORD)) {
		echo "Failed login.<br>";
	} else {
		echo "Achieved login.<br>";
	}

	echo "Just printing the JIRA API issue array for testing and to prevent you cucks from using this for my account: <pre>";
	print_r($newJira);
	echo "</pre>";
	return; */

	// Try to create the JIRA issue via API
	$response = doCurl(API_URL . "/issue/", unserialize(HEADERS), json_encode($newJira));

	if (!processErrors($response)) {
		return;
	}

	$id                   = $response["id"];
	$key                  = $response["key"];
	list($project, $jira) = explode("-", $key);
	$branchName           = array_values(unserialize(FIX_VERSIONS))[0] . "-" . ASSIGNEE . "-{$jira}";

	// Construct a data array to send to JIRA's API
	$editJira = [
		"fields" => [
			BRANCH_FIELD => $branchName
		]
	];

	// Try to update the issue branch via API
	$response = doCurl(API_URL . "/issue/{$id}/", unserialize(HEADERS), json_encode($editJira), "PUT");

	if (!processErrors($response, true, true)) {
		return;
	}

	// Display the JIRA creation output
	echo "<div class='ui success message'>";
	echo "<i class='checkmark icon'></i>JIRA created with branch name \"{$branchName}\". <a href='" . BROWSE_URL . "/{$key}' target='_blank'>JIRA {$jira} (ID: {$id})</a>";
	echo "</div>";

	// Determine if user has opted for SSH checkout
	if (!intval($currentUser->get("use_ssh"))) {
		echo "<div class='ui message'>";
		echo "<i class='info circle icon'></i>You have opted not to use SSH for automated checkout. Navigate to the folder containing the GOJIRA bash script, then run <code>./gojira {$id}</code> to checkout your branch.<br>";
		echo "Don't have the GOJIRA script? Please <a href='#' class='bash-download'>download it now.</a>";
		echo "</div>";
		return;
	}

	// Try to SSH into the user's computer and checkout the branch
	$ip  = ($currentUser->get("ssh_ip")) ?: $_SERVER["REMOTE_ADDR"];
	$ssh = new Net_SSH2($ip);
	if (!$ssh->login(SSH_USERNAME, SSH_PASSWORD)) {
		// SSH failed, so fallback to GOJIRA bash script instructions
		echo "<div class='ui error message'>";
		echo "<i class='warning icon'></i>Remote SSH for automated checkout failed. Navigate to the folder containing the GOJIRA bash script, then run <code>./gojira {$id}</code> to checkout your branch.<br>";
		echo "Don't have the GOJIRA script? Please <a href='#' class='bash-download'>download it now.</a>";
		echo "</div>";
	} else {
		$devFolder = $currentUser->get("dev_folder");
		// SSH succeeded, so checkout the branch via SVN
		echo "<div class='ui success message'>";
		echo "<i class='checkmark icon'></i>Now attempting to checkout {$branchName} on your machine. Refer to your Terminal for any further updates.";
		echo "</div>";
		$ssh->exec("open -a Terminal");
		$ssh->exec("cd {$devFolder}");
		$ssh->exec("svn cp " . SVN_TRUNK . " " . rtim(SVN_DEV, "/ ") . "/{$branchName} -m \"Created with the GOJIRA service.\"");
		$ssh->exec("svn co " . rtrim(SVN_DEV, "/ ") . "/{$branchName}");
	}
}
?>
