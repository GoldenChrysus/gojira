<?php $requiresAuth = true; ?>
<?php require_once(__DIR__ . "/includes/template/head.php"); ?>
<div class="ui inverted dimmer" id="loader">
	<div class="ui text loader">Creating the JIRA Issue</div>
</div>
<?php if (array_key_exists("new_account", $_GET)) { ?>
<div class="ui message">
	<i class="close icon"></i>
	<div class="header">
		Welcome to GOJIRA!
	</div>
	<p>
		Your new account has been created. Please feel free to <a href="#" class="bash-download">download the GOJIRA bash script</a> if you chose not to use SSH.<br>
		Protip: clicking a <code>code block</code> will select the entire block for easy copying and pasting.
	</p>
</div>
<?php } ?>
<?php if (intval($currentUser->get("use_ssh")) && (!$currentUser->get("ssh_ip"))) { ?>
<div class="ui grid tablet only mobile only" style="margin-bottom:1em;">
	<div class="column">
		<div class="ui warning message">
				<i class="warning icon"></i>
				<i class="close icon"></i>
				You have chosen to use SSH but don't have an IP address defined. Since you are connecting from a tablet or phone, GOJIRA's attempt to SSH into your current IP may fail. To prevent this, please <a href="manage">set an IP</a> in your account.
		</div>
	</div>
</div>
<?php } ?>
<form action="<?php echo stripPhpExtension($_SERVER["REQUEST_URI"]); ?>" method="POST" id="jira-form" name="jira-form" class="ui form fluid">
	<div class="ui equal width stackable grid">
		<div class="column computer only tablet only">
			<h1>Create a New JIRA</h1>
		</div>
		<div class="ui column middle aligned right aligned computer only tablet only">
			Latest Development Version: <?php echo array_values(unserialize(FIX_VERSIONS))[0]; ?>
		</div>
		<div class="column center aligned mobile only">
			<h1>Create a New JIRA</h1>
			Latest Development Version: <?php echo array_values(unserialize(FIX_VERSIONS))[0]; ?>
		</div>
	</div>
	<p>
		GOJIRA automatically creates a JIRA issue for the latest <?php echo CURRENT_VER; ?> development version. Once the issue is created, a branch name is assigned to it. If SSH is enabled, GOJIRA will attempt to SSH into your machine to checkout your branch, so you must have port 22 open on your firewall and router, and "Remote Login" must be enabled in Settings > Sharing on your Mac.
	</p>
	<p>
		If SSH fails or is not enabled, you can <a href="#" class="bash-download">download the GOJIRA bash script</a> to quickly create and checkout your branch. Download the script, use Terminal to navigate to the folder you saved it in (<code>cd &lt;your_folder&gt;</code>), then run <code>./gojira &lt;JIRA_ID&gt;</code>. The JIRA ID is provided after the issue is created. For example, <code>./gojira 38919</code> would create and checkout the branch 8.5.1-patrickg-9344. If it's your first time running the bash script, you may need to give yourself permissions by running <code>chmod 700 gojira</code>.
	</p>
	<div class="two fields">
		<div class="field">
			<label>Title</label>
			<input type="text" name="title" placeholder="Title or summary">
		</div>
		<div class="field">
			<label>Issue Type</label>
			<input type="hidden" name="issue_type_hidden" id="issue_type" value="">
			<div class="inline fields">
<?php
foreach (unserialize(ISSUE_TYPES) as $typeId => $typeName) {
	echo "<div class='field'>\n";
	echo "	<div class='ui radio checkbox'>\n";
	echo "		<input type='radio' name='issue_type' value='{$typeId}'>\n";
	echo "		<label>{$typeName}</label>\n";
	echo "	</div>\n";
	echo "</div>\n";
	$i++;
}
?>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Components</label>
		<select id="components" name="components[]" multiple="" class="ui fluid search dropdown">
			<option value=""><?php echo SYSTEM_NAME; ?> components</option>
<?php
foreach (unserialize(COMPONENTS) as $componentId => $componentName) {
	echo "<option value='{$componentId}'>{$componentName}</option>\n";
}
?>
		</select>
	</div>
	<div class="field">
		<label>Zendesk</label>
		<input name="zendesk" type="text" placeholder="Full Zendesk URL (optional)">
	</div>
	<div class="field">
		<label>Description</label>
		<textarea name="description" rows="10" placeholder="Issue description"></textarea>
	</div>
	<div class="field">
		<input type="submit" value="Create JIRA" class="ui primary submit button">
	</div>
</form>
<div class="ajax-response" data-for="jira-form"></div>
<?php require_once(__DIR__ . "/includes/template/foot.php"); ?>
