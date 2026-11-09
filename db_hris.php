<?php
	// Credentials come from global_includes/db_env_hris.php, never from this file.
	require_once __DIR__ . '/../global_includes/db_env_hris.php';

	function getPdo(): PDO
	{
		static $pdo = null;
		if ($pdo === null) {
			[$host, $user, $pass, $name] = gi_hris_db_connect_args();
			// TODO: confirm table charset
			$dsn  = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4';
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
	} catch (PDOException $e) {
		error_log('db_hris.php: ' . $e->getMessage());
		die('Could not connect to the database.');
	}
?>
