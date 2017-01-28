<?php
class Database {
	public static function get($sql, $params = []) {
		global $pdo;

		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public static function query($sql, $params = []) {
		global $pdo;

		$stmt = $pdo->prepare($sql);

		if ($params) {
			foreach ($params as $param) {
				$stmt->execute($param);
			}
		} else {
			$stmt->execute();
		}
	}

	public static function insert($table, $keys, $values) {
		global $pdo;
		
		$keys      = implode(", ", $keys);
		$questions = [];

		for ($i = 0; $i < count($values); $i++) {
			$questions[] = "?";
		}

		$questions = implode(", ", $questions);
		$values    = implode(", ", $values);

		$sql = 
			"INSERT INTO 
				{$table}
					({$keys})
			VALUES 
				({$questions})";

		$stmt = $pdo->prepare($sql);
		$stmt = $pdo->execute($values);

		return $pdo->lastInsertId();
	}
}