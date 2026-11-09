<?php
	require_once('/global_includes/hris-oop.php');
	$idx = $_POST['txtDate'];
	header("Location: excel.php?Custom=LengthOfServiceCOCreation&idx=".$idx);
?>