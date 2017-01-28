<?php
/**
 * General settings
 */
$domain     = "http://mydomain.com";
$systemName = "My Project"; // name of your platform or service, displayed throughotu GOJIRA
$currentVer = "3.0"; // the current top-level version of your project; if the version number is used in your SVN URL, then it should be that value; e.g. svn://myproject.com/versions/3.0/trunk, then the version should be 3.0
$jiraSalt   = "dee45e3bd9c7b54470e6cdb0575fddd5299e445823493464c531005d02e506098319d4570f964e7063f8ae7928ff503c6ddfead869c7c945cf66ece173793b2d"; // used to salt JIRA credentials
$sshSalt    = "44d25b3ba0de2af4ab4f5c88f87fa2b8c442ccfd4982fbdb72915cea1918d9720c106ba409a3ab12dd271236670384ed718fdd48476a7507ea49f24903a3672d"; // used to salt SSH credentials

/**
 * JIRA settings
 */
$baseUrl      = "https://myservice.atlassian.net"; // your Atlassian host URL
$projectId    = 10001; // Atlassian project ID of your primary project
$branchField  = "customfield_10102"; // custom field ID of the branch field in JIRA
$zendeskField = "customfield_11100"; // custom field ID of the Zendesk field in JIRA

/**
 * SVN settings
 */
// Trunk and dev URLs in your SVN setup.
// You can use {{CONSTANT_NAME}} to insert a constant value from constants.php
$svnTrunk = "svn://mydomain.com/platform/branches/{{CURRENT_VER}}/trunk";
$svnDev   = "svn://mydomain.com/platform/branches/{{CURRENT_VER}}/dev";
?>
