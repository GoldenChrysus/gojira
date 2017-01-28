<?php
class User {
	private $id;
	private $data;

	// We'll add salts to the config file soon so they are not hard-coded into a public repo
	private static $salts = [
		"jira" => "a random SHA-512 salt",
		"ssh"  => "a random SHA-512 salt"
	];

	public function __construct($id = null) {
		if ($id) {
			$sql = 
				"SELECT
					*
				FROM
					users
				WHERE
					id = ?";

			$result = Database::get($sql, [$id]);

			if (!$result) {
				return false;
			}

			$row        = reset($result);
			$this->data = $row;
			$this->id   = $id;
		}
	}

	public static function createUser($data) {
		$userSalt          = self::createSalt();
		$jiraSalt          = self::createSalt();
		$sshSalt           = self::createSalt();
		$token             = substr(self::createSalt(), 0, 32);

		$data["user_salt"] = [
			"value" => $userSalt,
			"type"  => "string"
		];
		$data["jira_salt"] = [
			"value" => $jiraSalt,
			"type"  => "string"
		];
		$data["ssh_salt"]  = [
			"value" => $sshSalt,
			"type"  => "string"
		];
		$data["token"]     = [
			"value" => $token,
			"type"  => "string"
		];

		$data["password"]["value"]      = self::hashPassword($userSalt, $data["password"]["value"]);
		$data["jira_password"]["value"] = ($data["jira_password"]["value"]) ? self::encryptPassword($jiraSalt, self::$salts["jira"], $data["jira_password"]["value"]) : null;
		$data["ssh_password"]["value"]  = ($data["ssh_password"]["value"]) ? self::encryptPassword($sshSalt, self::$salts["ssh"], $data["ssh_password"]["value"]) : null;

		$keys   = array_keys($data);
		$values = array_values($data);
		$userId = Database::insert("users", $keys, $values);

		if (!$userId) {
			return false;
		}

		return new User($userId);
	}

	public function update($data) {
		if (!is_array($data)) {
			return false;
		}

		$updates = [];
		$values  = [];

		if (isset($data["username"]) || array_key_exists("username", $data)) {
			unset($data["username"]);
		}

		if (isset($data["password"]) && !empty($data["password"]["value"])) {
			$data["password"]["value"] = self::hashPassword($this->get("user_salt"), $data["password"]["value"]);
		} else {
			unset($data["password"]);
		}

		if (isset($data["jira_password"]) && !empty($data["jira_password"]["value"])) {
			$data["jira_password"]["value"] = self::encryptPassword($this->get("jira_salt"), self::$salts["jira"], $data["jira_password"]["value"]);
		}

		if (isset($data["ssh_password"]) && !empty($data["ssh_password"]["value"])) {
			$data["ssh_password"]["value"] = self::encryptPassword($this->get("ssh_salt"), self::$salts["ssh"], $data["ssh_password"]["value"]);
		}

		foreach ($data as $key => $value) {
			$updates[] = "{$key} = ?";
			$values[]  = $value["value"];
		}

		if (count($updates) === 0) {
			return true;
		}

		$updateString = implode(", ", $updates);
		$values[]     = $this->id;
		$sql          = 
			"UPDATE
				users
			SET
				{$updateString}
			WHERE
				id = ?";

		Database::query($sql, $values);
		return true;
	}

	public function set($key, $value) {
		if (stripos($key, "_password") !== false) {
			list($type) = explode("_", $key);
			if (!isset(self::$salts[$type])) {
				return false;
			}

			$userSalt = $this->get("{$type}_salt");
			if (!$userSalt) {
				return false;
			}

			$value = self::encryptPassword($userSalt, self::$salts[$type], $value);
		}

		if ($key === "password") {
			$userSalt = $this->get("{$key}_salt");

			if (!$userSalt) {
				return false;
			}

			$value = self::hashPassword($userSalt, $value);
		}

		$sql = 
			"UPDATE
				users
			SET
				{$key} = ?
			WHERE
				id = ?";

		Database::query($sql, [0 => [$value, $this->id]]);

		return true;
	}

	public function getAll($realValue = false) {
		if (!is_array($this->data)) {
			return false;
		}
		$result = [];
		foreach ($this->data as $key => $foo) {
			$result[$key] = $this->get($key, $realValue);
		}
		return $result;
	}

	public function get($key, $realValue = false) {
		if (!$this->id) {
			return false;
		}

		if (!$realValue) {
			if (stripos($key, "_password") !== false) {
				list($type) = explode("_", $key);
				return $this->getPassword($type);
			}
		}

		if (is_array($this->data)) {
			return $this->data[$key];
		}

		return false;
	}

	private function getPassword($type) {
		if (!$this->id) {
			return false;
		}

		$userSalt = $this->get("{$type}_salt");
		if (!$userSalt) {
			return false;
		}

		$serverSalt = (self::$salts[$type]) ?: null;
		if (!$serverSalt) {
			return false;
		}

		$password = $this->get("{$type}_password", true);
		if (!$password) {
			return false;
		}

		return self::decryptPassword($userSalt, $serverSalt, $password);
	}

	public static function checkIfUsernameExists($username, $returnId = false) {
		$sql = 
			"SELECT
				id
			FROM
				users
			WHERE
				username = ?
			LIMIT 1";

		$result = Database::get($sql, [$username]);

		if ($result) {
			return $result[0]["id"];
		}

		return true;
	}

	public static function tryLogin($username, $password) {
		$result = self::checkIfUsernameExists($username, true);

		if (!$result) {
			return false;
		}

		$user = new User($result);

		if (!$user) {
			return false;
		}

		$salt         = $user->get("user_salt");
		$hashPassword = self::hashPassword($salt, $password);
		$password     = $user->get("password");

		if ($hashPassword === $password) {
			$_SESSION["auth"]    = true;
			$_SESSION["user_id"] = $result;
			return $user;
		}

		return false;
	}

	private static function createSalt() {
		return hash("sha512", mt_rand(10000,9999999999) . time());
	}

	private static function hashPassword($salt, $password) {
		return hash("sha512", $salt . hash("sha512", $password));
	}

	private static function encryptPassword($userSalt, $serverSalt, $password) {
		$cryptKey = substr(hash("sha512", $userSalt . $serverSalt), 0, 32);
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $cryptKey, $password, MCRYPT_MODE_ECB));
	}

	private static function decryptPassword($userSalt, $serverSalt, $password) {
		$cryptKey = substr(hash("sha512", $userSalt . $serverSalt), 0, 32);
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $cryptKey, base64_decode($password), MCRYPT_MODE_ECB));
	}
}
?>
