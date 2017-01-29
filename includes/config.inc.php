<?php
/**
 * General settings
 */
$domain     = "";
$systemName = ""; // name of your platform or service, displayed throughout GOJIRA
$currentVer = ""; // the current top-level version of your project; if the version number is used in your SVN URL, then it should be that value; e.g. svn://myproject.com/versions/3.0/trunk, then the version should be 3.0
$jiraSalt   = ""; // used to salt JIRA credentials; note that these salts are only ONE of the salts used; multiple salts are also generated for each user
$sshSalt    = ""; // used to salt SSH credentials

/**
 * JIRA settings
 */
$baseUrl      = ""; // your Atlassian host URL
$projectId    = 10001; // Atlassian project ID of your primary project
$branchField  = ""; // custom field ID of the branch field in JIRA
$zendeskField = ""; // custom field ID of the Zendesk field in JIRA

/**
 * SVN settings
 */
// Trunk and dev URLs in your SVN setup.
// You can use {{CONSTANT_NAME}} to insert a constant value from constants.php
$svnTrunk = "";
$svnDev   = "";
?>
