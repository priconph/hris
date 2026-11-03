<?php

require_once("../global_includes/hris-oop.php");

$result = HRISDB::getInstance()->get_sections_by_department($_GET['fkDepartment']);

$str="";
while($row = mysqli_fetch_array($result))
{
    $str .= "new Array(\"".$row['pkid']."\",\"".$row['Section']."\"),";
}
$str=substr($str,0,(strLen($str)-1));

$array = 'new Array ("'.$str.'")';

//"var x = new Array(new Array(1,2,3),new Array('A','B','C'),new Array('x','y','z'))";

?>new Array (<?php echo $str; ?>);