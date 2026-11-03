<?php

require_once("../global_includes/hris-oop.php");

//if get all, including inactive sections
if($_GET['all']=='true')
{
	$result = HRISDB::getInstance()->execute_search_query("SELECT * FROM tbl_Section WHERE fkDepartment='".$_GET['fkDepartment']."' ORDER BY section ASC");
}
else
{
	$result = HRISDB::getInstance()->get_sections_by_department($_GET['fkDepartment']);
}

$str="";

while($row = mysqli_fetch_array($result))
{
	//replace " with \" to prevent errors in parsing the array to javascript
	$SectionName = str_replace('"', '\"', $row['Section']);

    $str .= "new Array(\"".$row['pkid']."\",\"".$SectionName."\",\"".$row['isActive']."\"),";
}
$str=substr($str,0,(strLen($str)-1));

$array = 'new Array ("'.$str.'")';

//"var x = new Array(new Array(1,2,3),new Array('A','B','C'),new Array('x','y','z'))";

//outputs the new Array
//output is then transferred into javscript
?>new Array (<?php echo $str; ?>);
