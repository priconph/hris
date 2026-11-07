<?php
// SQL Server access through pdo_sqlsrv. Credentials come from the server environment, never from source.

function mssql_pdo_connect(): PDO
{
	static $pdo = null;
	if ($pdo === null) {
		$host = getenv('MSSQL_HOST');
		$db   = getenv('MSSQL_DB');
		$user = getenv('MSSQL_USER');
		$pass = getenv('MSSQL_PASS');
		if ($host === false || $db === false || $user === false || $pass === false) {
			throw new RuntimeException('MSSQL_HOST, MSSQL_DB, MSSQL_USER or MSSQL_PASS is not set');
		}
		$pdo = new PDO('sqlsrv:Server=' . $host . ';Database=' . $db . ';TrustServerCertificate=1', $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
	}
	return $pdo;
}

function mssql_pdo_query(string $sql, array $params = []): PDOStatement
{
	$stmt = mssql_pdo_connect()->prepare($sql);
	$stmt->execute($params);
	return $stmt;
}

function mssql_pdo_fetch_all(string $sql, array $params = []): array
{
	return mssql_pdo_query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}
