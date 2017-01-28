<?php
require_once(__DIR__ . "/includes/template/head.php");
$authed          = $_SESSION["auth"];
$action          = ($authed) ? "Manage" : "Register";
$usernameDisable = ($authed) ? " disabled" : null;
$submitAction    = ($authed) ? "Update" : "Register";
$ajaxAction      = ($authed) ? "Updating Your Settings" : "Creating Your Account";

$userData = [];
$userData = ($authed && $currentUser) ? $currentUser->getAll() : $userData;

?>
<div class="ui inverted dimmer" id="loader">
	<div class="ui text loader"><?php echo $ajaxAction; ?></div>
</div>
<form action="<?php echo stripPhpExtension($_SERVER["REQUEST_URI"]); ?>" name="account-form" id="account-form" method="POST" class="ui form fluid">
	<h1><?php echo $action; ?> Your Account</h1>
	<div class="two fields">
		<div class="field">
			<label>Username</label>
			<input type="text" name="username" placeholder="Username"<?php echo $usernameDisable; ?> value="<?php echo $userData["username"]; ?>">
		</div>
		<div class="field">
			<label>Password</label>
			<input type="password" name="password" placeholder="Password"  value="<?php echo $userData["password"]; ?>">
		</div>
	</div>
	<div class="two fields">
		<div class="field">
			<label>JIRA E-mail</label>
			<input type="text" name="jira_username" placeholder="JIRA e-mail" value="<?php echo $userData["jira_username"]; ?>">
		</div>
		<div class="field">
			<label>JIRA Password</label>
			<input type="password" name="jira_password" placeholder="JIRA password" value="<?php echo $userData["jira_password"]; ?>">
		</div>
	</div>
	<div class="field">
		<div class="ui checkbox" name="ssh-check">
			<input type="checkbox" name="ssh">
			<label>Create branches via SSH</label>
		</div>
	</div>
	<div class="note" data-for="ssh-check">
		GOJIRA can attempt to connect to your Mac via SSH to automatically create branches. Otherwise, you can <a href="#" class="bash-download">download the GOJIRA bash script</a> to quickly create branches.
	</div>
	<div class="three fields" name="ssh-info">
		<div class="field">
			<label>SSH Username</label>
			<input type="text" name="ssh_username" placeholder="SSH username" value="<?php echo $userData["ssh_username"]; ?>">
		</div>
		<div class="field">
			<label>SSH Password</label>
			<input type="password" name="ssh_password" placeholder="SSH password" value="<?php echo $userData["ssh_password"]; ?>">
		</div>
		<div class="field">
			<label>SSH IP Address</label>
			<input type="text" name="ssh_ip" placeholder="SSH IP address (optional)" value="<?php echo ($userData["ssh_ip"]) ?: $_SERVER["REMOTE_ADDR"]; ?>">
		</div>
	</div>
	<div class="note" data-for="ssh-info">
		These are your Mac username, Mac password, and your Mac IP address. If you leave the IP blank, GOJIRA will use the IP of your current machine.
	</div>
	<div class="field">
		<label><?php echo CURRENT_VER; ?> Development Folder</label>
		<input type="text" name="dev_folder" placeholder="<?php echo CURRENT_VER; ?> development folder" value="<?php echo $userData["dev_folder"]; ?>">
		<span class="note">
			This is your Mac development folder relative to ~. e.g. <code>~/Documents/<?php echo CURRENT_VER; ?>-dev</code>
		<?php if ($_SESSION["auth"]) { ?>
			If you update this value, you should <a href="#" class="bash-download">download a new bash script.</a>
		<?php } ?>
		</span>
	</div>
	<div class="field">
		<input type="submit" value="<?php echo $submitAction; ?> Account" class="ui primary submit button">
	</div>
</form>
<div class="ui message">
	<i class="info circle icon"></i>
	Note: Your JIRA and SSH info is stored using 256-bit AES encryption. Randomly-generated 512-bit client hashes and server hashes are used to generate keys, and your data is salted with 512-bit hashes.
</div>
<div class="ajax-response" data-for="account-form"></div>
<?php require_once(__DIR__ . "/includes/template/foot.php"); ?>
