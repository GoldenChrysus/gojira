<?php
require_once(__DIR__ . "/../includes/include.php");
if (!$_SESSION["auth"]) {
	return false;
}

$userId    = $currentUser->get("id");
$token     = $currentUser->get("token");
$devFolder = ($_POST["dev_folder"]) ?: $currentUser->get("dev_folder");

if ($_POST["dev_folder"]) {
	$result = $currentUser->set("dev_folder", $devFolder);
	if (!$result) {
		return false;
	}
}

header("Content-type: text/sh");
header("Content-Disposition: attachment; filename=gojira");
header("Set-Cookie: fileDownload=true; path=/");
header("Pragma: no-cache");
header("Expires: 0");
?>
#!/bin/bash
jiraId=$1

if [[ ! $jiraId ]]
then
	echo "A JIRA ID is required. e.g. ./gojira 12345"
	exit 0
fi

response=$(curl -s "<?php echo rtrim(DOMAIN, "/ "); ?>/get?id=$jiraId&field=<?php echo BRANCH_FIELD; ?>&user_id=<?php echo $userId; ?>&token=<?php echo $token; ?>")

if [[ ! $response ]]
then
	echo "No response was received from the server. Please try again."
	exit 0
fi

if [[ $response == *"There was an error"* ]]
then
	echo "There was an error. Please try again."
	exit 0
fi

cd <?php echo $devFolder; ?> 

svn cp <?php echo SVN_TRUNK; ?> <?php echo rtrim(SVN_DEV, "/ "); ?>/$response -m "Created with the GOJIRA service."

svn co <?php echo rtrim(SVN_DEV, "/ "); ?>/$response

echo "Branch $response has been checked out to <?php echo rtrim($devFolder, "/ "); ?>/$response"
