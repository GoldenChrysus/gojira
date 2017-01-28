</div>
<div class="ui small modal bash">
	<div class="header">
		Download the GOJIRA Bash Script
	</div>
	<div class="content">
		<p>
			The GOJIRA bash script quickly creates and checks out your branch. Download the script, use Terminal to navigate to the folder you saved it in (<code>cd &lt;your_folder&gt;</code>), then run <code>./gojira &lt;JIRA_ID&gt;</code>. The JIRA ID is provided after the issue is created. If it's your first time running the bash script, you may need to give yourself permissions by running <code>chmod 700 gojira</code>.
		</p>
	<? if ($_SESSION["auth"] && $currentUser && !$currentUser->get("dev_folder")) { ?>
		<p>
			Enter your <?php echo CURRENT_VER; ?> development folder relative to <em>~</em>. e.g. <code>~/Documents/<?php echo CURRENT_VER; ?>-dev</code>
		</p>
		<form class="ui form fluid" name="bash-download">
			<div class="field">
				<input type="text" name="dev_folder" placeholder="<?php echo CURRENT_VER; ?> development folder">
			</div>
			<div class="field">
				<input type="submit" value="Download" class="ui primary submit button">
			</div>
		</form>
		<div class="ajax-response" data-for="bash-download"></div>
	<? } else { ?>
		<div class="field">
		<? if ($_SESSION["auth"]) { ?>
			<input type="submit" value="Download" class="ui primary submit button authed" name="quick-bash-download">
		<? } else { ?>
			Please first login or register to download the GOJIRA script.
		<? } ?>
		</div>
		<div class="ajax-response" data-for="quick-bash-download"></div>
	<? } ?>
	</div>
</div>
</body>
</html>
