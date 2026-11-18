<?php
	session_start();
	require_once __DIR__ . '/../global_includes/hris-oop.php';

	// same session check as index.php / excel.php
	if(!isset($_SESSION['username'])){
		http_response_code(403);
		echo "Please login to systemone!";
		exit;
	}

	// the cut-off date must be a real YYYY-MM-DD date (value of the <input type="date"> in excel.php)
	$idx = (isset($_POST['txtDate']) && is_string($_POST['txtDate'])) ? $_POST['txtDate'] : '';
	if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $idx, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1]))
	{
		http_response_code(400);
		echo 'Invalid cut-off date.';
		exit;
	}

	header("Location: excel.php?" . http_build_query(array('Custom' => 'LengthOfServiceCOCreation', 'idx' => $idx)));
	exit;
?>
