<?php
/**
 * General settings
 */
$domain     = "http://mydomain.com";
$systemName = "My Project"; // name of your platform or service, displayed throughotu GOJIRA
$currentVer = "3.0"; // the current top-level version of your project; if the version number is used in your SVN URL, then it should be that value; e.g. svn://myproject.com/versions/3.0/trunk, then the version should be 3.0

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
