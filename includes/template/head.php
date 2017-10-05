<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/../phpseclib");
require_once(__DIR__ . "/../include.php");
$requestFile = getRequestFile();
if ($_SESSION["auth"]) {
	nonAuthRedirect($requestFile);
} else {
	authRedirect($requestFile);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo unserialize(MENU_ITEMS)[$requestFile]["name"]; ?> - GOJIRA Ticket &amp; Branch Creator</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta charset="UTF-8">
		<script src="assets/jquery/jquery.3.1.1.min.js" type="text/javascript"></script>
		<script src="assets/semantic/semantic.min.js" type="text/javascript"></script>
		<script src="assets/jquery-file-download/jquery.file-download.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="assets/semantic/semantic.min.css">
		<script type="text/javascript">
		$(document).ready(function() {
			// Initialize Semantic dropdown selects
			// Have to set forceSelection to false otherwise it will autoselect the highlighted element if the user clicks away from the dropdown
			$(".ui.fluid.dropdown").dropdown({
				forceSelection: false,
				fullTextSearch: true
			});

			// Initalize Semantic checkboxes/radios
			$(".ui.checkbox").checkbox();

			// Semantic form validation
			$("#jira-form").form({
				inline: true,
				fields: {
					title:       {
						identifier: "title",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter a JIRA title."
							}
						]
					},
					issue_type:  {
						identifier: "issue_type_hidden",
						rules:      [
							{
								type:   "empty",
								prompt: "Select an issue type."
							}
						]
					},
					components:  {
						identifier: "components",
						rules:      [
							{
								type:   "minCount[1]",
								prompt: "Select at least one component."
							},
							{
								type:   "empty",
								prompt: "Select at least one component."
							}
						]
					},
					zendesk:     {
						identifier: "zendesk",
						optional:   true,
						rules:      [
							{
								type:   "url",
								prompt: "Enter a valid URL or leave it blank."
							}
						]
					},
					description: {
						identifier: "description",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter a description."
							}
						]
					}
				},
				onSuccess: function(event, fields) {
					event.preventDefault();
					var $this    = $(this);
					var formName = $this.attr("name");
					$("#loader").addClass("active");
					$.ajax({
						url:     "ajax/create.php",
						type:    "POST",
						data:    fields,
						success: function(data, textStatus, jqXHR) {
							$this.parent().find(".ajax-response[data-for=" + formName + "]").html(data);
							$("#loader").removeClass("active");
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$this.parent().find(".ajax-response[data-for=" + formName + "]").html("<div class='ui error message'><i class='remove icon'></i>An unexpected error occurred. Please try again.</div>");
							$("#loader").removeClass("active");
						}
					});
				}
			});

			// Manage/register account form Semantic validation
			$("#account-form").form({
				inline: true,
				fields: {
					username:      {
						identifier: "username",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter a username."
							}
						]
					},
					password:      {
						identifier: "password",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter a password."
							}
						]
					},
					jira_username: {
						identifier: "jira_username",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your JIRA username."
							}
						]
					},
					jira_password: {
						identifier: "jira_password",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your JIRA password."
							}
						]
					},
					ssh_username: {
						identifier: "ssh_username",
						depends:    "ssh",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your SSH username or deselect the SSH option."
							}
						]
					},
					ssh_password: {
						identifier: "ssh_password",
						depends:    "ssh",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your SSH password or deselect the SSH option."
							}
						]
					},
					ssh_ip:      {
						identifier: "ssh_ip",
						depends:    "ssh",
						optional:   true,
						rules:      [
							{
								type:   "regExp",
								value:   /^(?!0)(?!.*\.$)((1?\d?\d|25[0-5]|2[0-4]\d)(\.|$)){4}$/,
								prompt: "Enter a valid SSH IP address or leave it blank."
							}
						]
					},
					dev_folder:  {
						identifier: "dev_folder",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your <?php echo CURRENT_VER; ?> development folder."
							}
						]
					}
				},
				onSuccess: function(event, fields) {
					event.preventDefault();
					var $this    = $(this);
					var formName = $this.attr("name");
					$("#loader").addClass("active");
					$.ajax({
						url:     "ajax/account.php",
						type:    "POST",
						data:    fields,
						success: function(data, textStatus, jqXHR) {
							if (data === "") {
								window.location = "create?new_account";
							} else {
								$this.parent().find(".ajax-response[data-for=" + formName + "]").html(data);
								$("#loader").removeClass("active");
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$this.parent().find(".ajax-response[data-for=" + formName + "]").html("<div class='ui error message'><i class='remove icon'></i>An unexpected error occurred. Please try again.</div>");
							$("#loader").removeClass("active");
						}
					});
				}
			});

			// Show/hide the SSH input options based on the SSH checkbox
			$("#account-form [name=ssh-info]").hide();
			$("#account-form [data-for=ssh-info]").hide();
			$("#account-form [name=ssh]").on("change", function() {
				var checked = this.checked;
				if (checked) {
					$("#account-form [name=ssh-info]").show();
					$("#account-form [data-for=ssh-info]").show();
				} else {
					$("#account-form [name=ssh-info]").hide();
					$("#account-form [data-for=ssh-info]").hide();
				}
			});
			if (<?php echo ($_SESSION["auth"] === true && intval($currentUser->get("use_ssh"))) ? "true" : "false"; ?>) {
				$("#account-form [name=ssh]").prop("checked", true).trigger("change");
			}

			// Handle bash script downloading and form validation
			$(".ui.modal.bash form").form({
				inline: true,
				fields: {
					dev_folder: {
						identifier: "dev_folder",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your development folder."
							}
						]
					}
				},
				onSuccess: function(event, fields) {
					event.preventDefault();
					var $this    = $(this);
					var formName = $this.attr("name");
					$.fileDownload("ajax/bash-script.php", {
						httpMethod: "POST",
						data:       fields
					}).done(function () {
						$this.closest(".ui.modal").modal("hide");
					}).fail(function() {
						$this.parent().find(".ajax-response[data-for=" + formName + "]").html("<div class='ui error message'><i class='remove icon'></i>An unexpected error occurred. Please try again.</div>");
					});
				}
			});

			$(".ui.modal.bash .button.authed").on("click", function (e) {
				e.preventDefault();
				var $this    = $(this);
				var formName = $this.attr("name");
				$.fileDownload("ajax/bash-script.php", {
					httpMethod: "GET"
				}).done(function () {
					$this.closest(".ui.modal").modal("hide");
				}).fail(function() {
					$this.parent().find(".ajax-response[data-for=" + formName + "]").html("<div class='ui error message'><i class='remove icon'></i>An unexpected error occurred. Please try again.</div>");
				});
			});

			// Login form handling and Semantic validation
			$("#login-form").form({
				inline: true,
				fields: {
					username: {
						identifier: "username",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your username."
							}
						]
					},
					password: {
						identifier: "password",
						rules:      [
							{
								type:   "empty",
								prompt: "Enter your password."
							}
						]
					}
				},
				onSuccess: function(event, fields) {
					event.preventDefault();
					var $this    = $(this);
					var formName = $this.attr("name");
					$("#loader").addClass("active");
					$.ajax({
						url:     "ajax/login.php",
						type:    "POST",
						data:    fields,
						success: function(data, textStatus, jqXHR) {
							if (data === "") {
								window.location = "create";
							} else {
								$this.parent().find(".ajax-response[data-for=" + formName + "]").html(data);
								$("#loader").removeClass("active");
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$this.parent().find(".ajax-response[data-for=" + formName + "]").html("<div class='ui error message'><i class='remove icon'></i>An unexpected error occurred. Please try again.</div>");
							$("#loader").removeClass("active");
						}
					});
				}
			});

			// Semantic has an issue with form validation on inline radio inputs: it displays an error prompt for each radio rather than one prompt for the whole group
			// Instead, we update a hidden input each time the radio group changes, then run form validation on that hidden input rather than the radios themselves
			$("#jira-form").on("change", "[type=radio]", function(e) {
				var name   = $(this).attr("name"),
					hidden = $("[type=hidden][name=" + name + "_hidden]");

				hidden.val($(this).val());
			});

			// Open the modal to download the bash script
			$(document).on("click", ".bash-download", function(e) {
				e.preventDefault();
				$(".ui.modal.bash").modal("show");
			});

			// Dismissable message handling
			$(".message .close").on("click", function() {
				$(this).closest(".message").transition("fade");
			});

			// Resize the height of the fixed menu, and disable the fixed menu when the menu collapses to a stacked menu
			$(".menu-spacer").height($(".ui.menu").height() + 14);
			$(window).on("resize", function() {
				if ($(window).innerWidth() >= 767) {
					$(".ui.menu").addClass("fixed");
					$(".menu-spacer").height($(".ui.menu").height() + 14);
				} else {
					$(".menu-spacer").height(0);
					$(".ui.menu").removeClass("fixed");
				}
			});
		});
		</script>
		<style type="text/css">
		.ui.menu,
		body {
			/* background:#fefeff; */
			background:#fdfdfd !important;
		}

		#main-container {
			padding-bottom:1em;
		}

		code {
			background-color:rgba(0, 0, 0, 0.08);
			border-radius:3px;
			display:inline-block;
			font-family:"Monaco","Menlo","Ubuntu Mono","Consolas","source-code-pro",monospace;
			font-size:0.875em;
			font-weight:bold;
			padding:1px 6px;
			vertical-align:baseline;
			-webkit-touch-callout:all;
			-webkit-user-select:all;
			-khtml-user-select:all;
			-moz-user-select:all;
			-ms-user-select:all;
			user-select:all;
		}

		p {
			text-align:justify;
		}

		.ajax-response {
			margin-bottom:1em;
			margin-top:1em;
		}

		@media only screen and (min-width: 992px) {
			.inline.fields {
				margin-top:0.8571428571428571rem !important;
				margin-bottom:0 !important;
			}

			.ui.menu.fixed {
				width:inherit;
				left:50%;
				transform:translateX(-50%);
			}

			.menu-spacer {
				width:100%;
				height:3.85714286em;
			}
		}

		@media only screen and (min-width: 767px) {
			.ui.menu.fixed {
				width:inherit;
				left:50%;
				transform:translateX(-50%);
			}

			.menu-spacer {
				width:100%;
				height:3.85714286em;
			}

			.child {
			  position:relative;
			  top:50%;
			  transform: translateY(-50%);
			}
		}

		.field .note,
		form .note {
			padding:0.40em;
			font-size:0.90em;
		}

		form .note {
			margin-top:-1em;
			margin-bottom:1em;
		}

		.segment .ajax-response {
			margin-bottom:0;
		}
		</style>
	</head>
<body>
<div id="main-container" class="ui container"<?php echo ($requestFile === "login") ? " style='height:calc(100% - 2.85714286em);'" : null; ?>>
<?php include_once(__DIR__ . "/menu.php"); ?>
