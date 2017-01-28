<?php require_once(__DIR__ . "/includes/template/head.php"); ?>
<div class="ui inverted dimmer" id="loader">
	<div class="ui text loader">Logging In</div>
</div>
<div class="ui middle aligned grid child">
	<div class="column">
		<div class="ui center aligned page grid">
			<div class="sixteen wide tablet eight wide computer column">
				<div class="ui left aligned segment">
					<h4 class="ui dividing header">Login to GOJIRA</h4>
					<form class="ui form" name="login-form" id="login-form">
						<div class="field">
							<label>Username</label>
							<div class="ui icon input">
								<input type="text" placeholder="Username" name="username"> <i class="user icon"></i>
							</div>
						</div>
						<div class="field">
							<label>Password</label>
							<div class="ui icon input">
							  <input type="password" placeholder="Password" name="password"> <i class="lock icon"></i>
							</div>
						</div>
						<input type="submit" name="submit" class="ui primary button" value="Login">
					</form>
					<div class="ajax-response" data-for="login-form"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="ajax-response" data-for="login-form"></div>
<?php require_once(__DIR__ . "/includes/template/foot.php"); ?>
