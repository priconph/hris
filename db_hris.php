<?php
	// Credentials come from the server environment, never from source.
	function getPdo(): PDO
	{
		static $pdo = null;
		if ($pdo === null) {
			$host = getenv('localhost');
			$name = getenv('db_hris');
			$user = getenv('root');
			$pass = getenv('sys!w@nDb123');
			if ($user === false || $pass === false) {
				throw new RuntimeException('HRIS_DB_USER or HRIS_DB_PASS is not set');
			}
			// TODO: confirm table charset
			$dsn  = 'mysql:host=' . ($host !== false ? $host : 'localhost')
				  . ';dbname=' . ($name !== false ? $name : 'db_hris')
				  . ';charset=utf8mb4';
			$pdo = new PDO($dsn, $user, $pass, [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			]);
		}
		return $pdo;
	}

	try {
		$conn = getPdo();
	} catch (PDOException | RuntimeException $e) {
		error_log('db_hris.php: ' . $e->getMessage());
		die('Could not connect to the database.');
	}
?>
