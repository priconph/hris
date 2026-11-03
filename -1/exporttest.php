<?php

//EDIT YOUR MySQL Connection Info:
$DB_Server = "localhost";        //your MySQL Server
$DB_Username = "builder";        //your MySQL User Name
$DB_Password = "newd3v";         //your MySQL Password
$DB_DBName = "db_hris";           //your MySQL Database Name
$DB_TBLName = "tbl_EmployeeInfo";      //your MySQL Table Name
 
//$DB_TBLName,  $DB_DBName, may also be commented out & passed to the browser
//as parameters in a query string, so that this code may be easily reused for
//any MySQL table or any MySQL database on your server
 
//DEFINE SQL QUERY:
//edit this to suit your needs

if (isset($_POST["SQL"])) 
{
    $sql  = $_POST["SQL"];
    
}
else
    $sql  = "SELECT * FROM tbl_EmployeeInfo";


$Connect = @MYSQL_CONNECT($DB_Server, $DB_Username, $DB_Password)
     or DIE("Couldn't connect to MySQL:<br>" . MYSQL_ERROR() . "<br>" . MYSQL_ERRNO());
//select database
$Db = @MYSQL_SELECT_DB($DB_DBName, $Connect)
     or DIE("Couldn't select database:<br>" . MYSQL_ERROR(). "<br>" . MYSQL_ERRNO());
//execute query
$result = @MYSQL_QUERY($sql,$Connect)
     or DIE("Couldn't execute query:<br>" . MYSQL_ERROR(). "<br>" . MYSQL_ERRNO());



    function xlsBOF()
    {
    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
    return;
    }
    function xlsEOF()
    {
    echo pack("ss", 0x0A, 0x00);
    return;
    }
    function xlsWriteNumber($Row, $Col, $Value)
    {
    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
    echo pack("d", $Value);
    return;
    }
    function xlsWriteLabel($Row, $Col, $Value )
    {
    $L = strlen($Value);
    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    echo $Value;
    return;
    }
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");;
    header("Content-Disposition: attachment;filename=list.xls");
    header("Content-Transfer-Encoding: binary ");
    xlsBOF();
     
    xlsWriteLabel(0,0,"test");
    xlsWriteLabel(0,1,"test2");
    xlsWriteLabel(0,2,"test3");
    $xlsRow = 1;
    while($row=mysql_fetch_array($result))
    {
    xlsWriteNumber($xlsRow,0,$row['firstname']);
    xlsWriteLabel($xlsRow,1,$row['DOB']);
    xlsWriteLabel($xlsRow,2,$row['fkdepartment']);
    $xlsRow++;
    }
    xlsEOF();


 ?>