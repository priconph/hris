<?php
	require_once 'Browscap.php';
	$data = "";
	$bc = new Browscap('');
	$current_browser = $bc->getBrowser();
	foreach ($current_browser as $key => $value)
	{
		//$data .= $key."=".$value.", ";
	    if($key == "Browser") $browser = $value;
	}
?>