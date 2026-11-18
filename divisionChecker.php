<?php
	session_start();
	require_once __DIR__ . '/../global_includes/hris-oop.php';

	// same session check as index.php / excel.php
	if(!isset($_SESSION['username'])){
		http_response_code(403);
		echo "Please login to systemone!";
		exit;
	}

	$rosterOption = (isset($_POST['rosterOptions']) && is_string($_POST['rosterOptions'])) ? $_POST['rosterOptions'] : '';

	try
	{
		// the lookup is also the allowed-values check: only an existing division name is accepted
		$result = HRISDB::getInstance()->query_params("SELECT pkid FROM tbl_Division WHERE Division = ?", array($rosterOption));
		$row = $result ? mysqli_fetch_array($result) : null;
	}
	catch (Throwable $e)
	{
		// never show SQL or exception text in the browser
		error_log('divisionChecker.php: ' . $e->getMessage());
		http_response_code(500);
		echo 'A database error occurred. Please try again or contact your administrator.';
		exit;
	}

	if (!$row)
	{
		http_response_code(400);
		echo 'Invalid division.';
		exit;
	}

	header("Location: excel.php?" . http_build_query(array('Custom' => 'ManpowerRosterCreation', 'id' => $row['pkid'], 'divname' => $rosterOption)));
	exit;
?>
