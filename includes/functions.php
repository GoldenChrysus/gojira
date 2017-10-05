<?php
function doCurl($url, $headers, $postVars = null, $customRequest = false) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	if ($postVars) {
		if (!$customRequest) {
			curl_setopt($ch, CURLOPT_POST, true);
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
		}
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if (isset($header["header"])) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers["header"]);
	}

	if (isset($headers["user"]) && count(array_filter($headers["user"])) === 2) {
		curl_setopt($ch, CURLOPT_USERPWD, implode(":", $headers["user"]));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}

	$response = curl_exec($ch);

	curl_close($ch);
	return json_decode($response, true);
}

function processErrors($response, $output = true, $allowNull = false) {
	if (!$response && !$allowNull) {
		return false;
	}

	if (isset($response["errorMessages"]) || isset($response["errors"])) {
		$errors = array_merge($response["errorMessages"], $response["errors"]);

		if ($output) {
			echo "<div class='ui error message'>";
			echo "<i class='info circle icon'></i>";
			echo "There was an error:";

			foreach ($errors as $foo => $error) {
				echo "<br>" . $error;
			}

			echo "</div>";
		}

		return false;
	}

	return true;
}

function stripPhpExtension($string) {
	return str_replace(".php", "", $string);
}

function getRequestFile() {
	return stripPhpExtension(explode("?", array_slice(array_values(explode("/", $_SERVER["REQUEST_URI"])), -1)[0])[0]);
}

function nonAuthRedirect($page) {
	$keys = array_keys(unserialize(NON_AUTH_PAGES));

	if (in_array($page, $keys)) {
		header("Location: " . unserialize(NON_AUTH_PAGES)[$page]);
		exit();
	}
}

function authRedirect($page) {
	$keys = array_keys(unserialize(AUTH_PAGES));
	
	if (in_array($page, $keys)) {
		header("Location: " . unserialize(AUTH_PAGES)[$page]);
		exit();
	}
}
?>
