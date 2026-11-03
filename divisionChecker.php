<?php
	require_once('../global_includes/hris-oop.php');
	
	$sql = "SELECT pkid FROM tbl_Division WHERE Division = '" . $_POST['rosterOptions'] . "'";
	$result = HRISDB::getinstance()->execute_search_query($sql);	
	$holder = "blank";
	while($row = mysqli_fetch_array($result))
		{
			for($i=0; $i<mysqli_num_fields($result);$i++)
			{
				$holder = $row[$i];
			}
		}
		
	header("Location: excel.php?Custom=ManpowerRosterCreation&id=".$holder."&divname=".$_POST['rosterOptions']);
?>