<?PHP


function getHeaderIndex()
{

    $gender = "";
    $civilStatus = "";
    $position = "";
    $section = "";
    $department = "";
    $division = "";
    $fksuperior = "";
    $hiringStatus = "";
    $SalaryType = "";
    $HighestEducAttainment = "";
    $EmpStatus = "";
    $Incoming = "";
    $Outgoing = "";
    $ExemptBaseCode = "";

}

function replaceValue()
{
    
}


//MySQL connection comes from db_hris.php (credentials from the server environment)
require_once __DIR__ . '/../db_hris.php';
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

$filename="HRIS Employee List";


//Optional: print out title to top of Excel or Word file with Timestamp
//for when file was generated:
//set $Use_Titel = 1 to generate title, 0 not to use title
$Use_Title = 1;
//define date for title: EDIT this to create the time-format you need
$now_date = DATE('m-d-Y H:i');
//define title for .doc or .xls file: EDIT this if you want
$title = "HRIS Employee List on $now_date";
/*
 
Leave the connection info below as it is:
just edit the above.
 
(Editing of code past this point recommended only for advanced users.)
*/
//execute query
//OPEN ITEM: $sql is the full query posted by the browser; it cannot be bound and must be redesigned
if (!isset($sql)) {
     DIE("Couldn't execute query.");
}
try {
     $result = getPdo()->prepare($sql);
     $result->execute();
} catch (PDOException $e) {
     error_log('-1/exportExcel.php: ' . $e->getMessage());
     DIE("Couldn't execute query.");
}
$numFields = $result->columnCount();
 
//if this parameter is included ($w=1), file returned will be in word format ('.doc')
//if parameter is not included, file returned will be in excel format ('.xls')
IF (ISSET($w) && ($w==1))
{
     $file_type = "msword";
     $file_ending = "doc";
}ELSE {
     $file_type = "vnd.ms-excel";
     $file_ending = "xls";
}
//header info for browser: determines file type ('.doc' or '.xls')
HEADER("Content-Type: application/$file_type");
HEADER("Content-Disposition: attachment; filename=$filename.$file_ending");
HEADER("Pragma: no-cache");
HEADER("Expires: 0");
 
/*    Start of Formatting for  Excel    */
 
 /*    FORMATTING FOR EXCEL DOCUMENTS ('.xls')   */
     //create title with timestamp:
     IF ($Use_Title == 1)
     {
         ECHO("$title\n");
     }
     //define separator (defines columns in excel & tabs in word)
     $sep = "\t"; //tabbed character
 
     //start of printing column names as names of MySQL fields
     FOR ($i = 0; $i < $numFields; $i++)
     {
         $meta = $result->getColumnMeta($i);
         ECHO $meta['name'] . $sep;
     }



     PRINT("\n\n");
     //end of printing column names
 
     //start while loop to get data
     WHILE($row = $result->fetch(PDO::FETCH_NUM))
     {
         //set_time_limit(60); // HaRa
         $schema_insert = " ";
         FOR($j=0; $j<$numFields;$j++)
         {
             IF(!ISSET($row[$j]))
                 $schema_insert .= "NULL".$sep;
             ELSEIF ($row[$j] != "")
                 $schema_insert .= "$row[$j]".$sep;
             ELSE
                 $schema_insert .= "".$sep;
         }
         $schema_insert = STR_REPLACE($sep."$", "", $schema_insert);
         //following fix suggested by Josue (thanks, Josue!)
         //this corrects output in excel when table fields contain \n or \r
         //these two characters are now replaced with a space
         $schema_insert = PREG_REPLACE("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
         $schema_insert .= "\t";
         echo trim($schema_insert);
         echo "\n";

     }
 
?>