<?php

require_once("../global_includes/hris-oop.php");

//if get all, including inactive sections
if($_GET['all']=='true')
{
	$result = HRISDB::getInstance()->execute_search_query("SELECT * FROM tbl_Department WHERE fkDivision='".$_GET['fkDivision']."' ORDER BY department ASC");
}
else
{
	$result = HRISDB::getInstance()->get_departments_by_division($_GET['fkDivision']);
}

$str="";

while($row = mysqli_fetch_array($result))
{
	//replace " with \" to prevent errors in parsing the array to jav
	$DepartmentName = str_replace('"', '\"', $row['Department']);

    $str .= "new Array(\"".$row['pkid']."\",\"".$DepartmentName."\",\"".$row['isActive']."\"),";
}
$str=substr($str,0,(strLen($str)-1));

$array = 'new Array ("'.$str.'")';

//"var x = new Array(new Array(1,2,3),new Array('A','B','C'),new Array('x','y','z'))";

//outputs the new Array
//output is then transferred into javscript
?>new Array (<?php echo $str; ?>);