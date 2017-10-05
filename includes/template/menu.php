<?php
?>
<div class="ui stackable secondary fixed pointing menu">
<?php
foreach (unserialize(MENU_ITEMS) as $url => $data) {
	if ($data["require_session"] && (!$_SESSION["auth"])) {
		continue;
	}

	if ($data["if_no_session"] && ($_SESSION["auth"])) {
		continue;
	}

	// Can't pass $_SERVER by reference to end() so we have to do this to get the last element
	$currentUrl = getRequestFile();
	$active     = ($currentUrl === $url) ? " active" : null;
	$class      = (isset($data["class"])) ? " " . $data["class"] : null;
?>
	<a href="<?php echo $url; ?>" class="item<?php echo $active . $class; ?>">
		<?php echo $data["name"]; ?>
	</a>
<?php
}
?>
</div>
<div class="menu-spacer"></div>
