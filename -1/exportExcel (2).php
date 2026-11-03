<?PHP

require_once("../global_includes/hris-oop.php");

// $headerValues[$i][0]
// the original header

// $headerValues[$i][1]
// header to replace original

// $headerValues[$i][2]
// index location of the header


$headerValues = array(
    array('pkid','pkid',''),
    array('lastupdate','last update',''),
    array('EmpNo', 'Employee Number',''),
    array('LastName','Last Name',''),
    array('FirstName','First Name',''),
    array('MiddleName','Middle Name',''),
    array('NickName','Nick Name',''),
    array('TelephoneNo','Telephone Number',''),
    array('DOBirth','Date of Birth',''),
    array('Gender','Gender',''),
    array('CivilStatus','Civil Status',''),
    array('Spouse','Spouse',''),
    array('SSN','SSS number',''),
    array('TIN','TIN number',''),
    array('fkPosition','Position',''),
    array('fkSection','Section',''),
    array('fkDepartment','Department',''),
    array('fkDivision','Division',''),
    array('fkSuperior','Superior',''),
    array('Emergency','Emergency Contact',''),
    array('DateHired','Date Hired',''),
    array('HiringStatus','Employment Category',''),
    array('SalaryType','Salary Type',''),
    array('CityAddress1','City Street',''),
    array('CityAddress2','Barangay',''),
    array('CityAddress3','City',''),
    array('ProvAddress1','Provincial Barangay',''),
    array('ProvAddress2','Municipality',''),
    array('ProvAddress3','Province',''),
    array('HighestEduc','Educational Attainment',''),
    array('Degree','Degree Obtained',''),
    array('GovtExams','Government Exams',''),
    array('FatherName',"Father's name"),
    array('MotherName',"Mother's name"),
    array('EmpStatus','Employment Status',''),
    array('School','School',''),
    array('DOGraduated','Date of Graduation',''),
    array('Honors','Honors',''),
    array('Picturepath','Picture Location',''),
    array('Effectivity','Effectivity',''),
    array('Reason','Reason',''),
    array('HDMF','HDMF',''),
    array('PhilHealth','Phil Health number',''),
    array('Incoming','Incoming Shuttle Service',''),
    array('Outgoing','Outgoing Shuttle Service',''),
    array('ExemptBaseCode','Tax Exemption Base',''),
    array('Remarks','Remarks',''),
    array('PagIbig','PagIbig number','')
);


function getHeaderIndex($inputHeader,$headerIndex)
{
    global $headerValues;
    for ($i=0; $i < count($headerValues); $i++) {
        if($inputHeader==$headerValues[$i][0])
        {
            $headerValues[$i][2]=$headerIndex;
            return $headerValues[$i][1];
        }
    }
    return 'error';
}

function replaceValue($inputValue,$inputIndex)
{
    global $headerValues;

    for ($i=0; $i < count($headerValues); $i++) {
        //fix comparing 0 values
        if($headerValues[$i][2]==strval($inputIndex))
        {
            $h = $headerValues[$i][0];
            break;
        }
    }

    //$h = $headerValues[$inputIndex][0];
    if($h == 'EmpNo')
    {
        //if contains letters dont use the text formula
        if (preg_match('/[A-Za-z]/', $inputValue))
        {
            return $inputValue;
        }
        else
        {//formula for excel to retain leading zeros
            //get length of the number value
            $zeroMatch = '';
            for ($i=0; $i < strlen($inputValue); $i++) { 
                $zeroMatch .= '0';
            }
            return '=text('.$inputValue.',"'.$zeroMatch.'")';
        }
    }
    else if($h=='pkid')
    {
        return '0';
    }
    else if($h=='CivilStatus')
    {
        return HRISDB::getInstance()->get_civil_status_by_id($inputValue);
    }
    else if($h=='HiringStatus')
    {
        return HRISDB::getInstance()->get_employment_category_by_id($inputValue);
    }
    else if($h=='SalaryType')
    {
        return HRISDB::getInstance()->get_salary_type_by_id($inputValue);
    }
    else if($h=='HighestEduc')
    {
        return HRISDB::getInstance()->get_educ_attainment_by_id($inputValue);
    }
    else if($h=='EmpStatus')
    {
        return HRISDB::getInstance()->get_employment_status_by_id($inputValue);
    }
    else if($h=='Incoming' || $h == 'Outgoing')
    {
        return HRISDB::getInstance()->get_shuttle_service_by_id($inputValue);
    }
    else if($h=='ExemptBaseCode')
    {
        return HRISDB::getInstance()->get_excempt_code_by_id($inputValue);
    }
    else if($h == 'Gender')
    {
        return HRISDB::getInstance()->get_gender_by_id($inputValue);
    }
    else if($h == 'fkPosition')
    {
        return HRISDB::getInstance()->get_position_name_by_id($inputValue);
    }
    else if($h == 'fkSection')
    {
        return HRISDB::getInstance()->get_section_name_by_id($inputValue);
    }
    else if($h == 'fkDepartment')
    {
        return HRISDB::getInstance()->get_department_name_by_id($inputValue);
    }
    else if($h == 'fkDivision')
    {
        return HRISDB::getInstance()->get_division_name_by_id($inputValue);
    }
    //return original value
    return $inputValue;
}


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
    $sql = "Select EmpNo, TIN, fkDepartment, Gender from tbl_EmployeeInfo WHERE empstatus=1";
    //$sql  = "SELECT * FROM tbl_EmployeeInfo";







//Optional: print out title to top of Excel or Word file with Timestamp
//for when file was generated:
//set $Use_Titel = 1 to generate title, 0 not to use title
$Use_Title = 1;
//define date for title: EDIT this to create the time-format you need
$now_date = DATE('m-d-Y H:i');
//define title for .doc or .xls file: EDIT this if you want
$title = "HRIS Employee List on $now_date";

$filename="HRIS Employee List $now_date";
/*
 
Leave the connection info below as it is:
just edit the above.
 
(Editing of code past this point recommended only for advanced users.)
*/
//create MySQL connection
$Connect = @MYSQL_CONNECT($DB_Server, $DB_Username, $DB_Password)
     or DIE("Couldn't connect to MySQL:<br>" . MYSQL_ERROR() . "<br>" . MYSQL_ERRNO());
//select database
$Db = @MYSQL_SELECT_DB($DB_DBName, $Connect)
     or DIE("Couldn't select database:<br>" . MYSQL_ERROR(). "<br>" . MYSQL_ERRNO());
//execute query
$result = @MYSQL_QUERY($sql,$Connect)
     or DIE("Couldn't execute query:<br>" . MYSQL_ERROR(). "<br>" . MYSQL_ERRNO());
 
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
     FOR ($i = 0; $i < MYSQL_NUM_FIELDS($result); $i++)
     {
        //replace header name here
        ECHO getHeaderIndex(MYSQL_FIELD_NAME($result,$i),$i).$sep;
        //ECHO MYSQL_FIELD_NAME($result,$i) . $sep; 
     }



     print("\n\n");
     //end of printing column names
 
     //start while loop to get data
     while($row = MYSQL_FETCH_ROW($result))
     {
         //set_time_limit(60); // HaRa
         $schema_insert = " ";
         for($j=0; $j<mysql_num_fields($result);$j++)
         {
             if (!ISSET($row[$j]))
                 $schema_insert .= "NULL".$sep;
             else if ($row[$j] != "")
             {
                $schema_insert .= replaceValue($row[$j],$j).$sep;
                //$schema_insert .= $row[$j].$sep;
             }
             else
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