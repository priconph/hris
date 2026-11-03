<?

require_once('excel/Classes/PHPExcel.php');
require_once('../global_includes/hris-oop.php');



// $headerValues[$i][0]
// the original header

// $headerValues[$i][1]
// header to replace original

// $headerValues[$i][2]
// index location of the header


$headerValues = array(
    array('pkid','',''),
    array('lastupdate','',''),
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
    array('TIN','Tax Identification number',''),
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
    array('PagIbig','PagIbig number',''),
    array('FullName','Employee Name','')
);

//replace header and get index
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
    return $inputHeader;
}

//replace the values corresponding to the header
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
    	//hide values of the pkid
        return '';
    }
    else if($h=='lastupdate')
    {
        //hide values of the lu
        return '';
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





// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("System One")
                             ->setLastModifiedBy("session user")
                             ->setTitle("Office 2007 XLSX Document")
                             ->setSubject("Office 2007 XLSX Document")
                             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Test result file");
$rownumber = 2;

//shrink borders
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1);



if (isset($_POST["SQL"])) 
{
    $sql  = $_POST["SQL"];
}
elseif(isset($_GET["Custom"]))
{
    if ($_GET["Custom"] == "SSS") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, SSN from tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY EmpNo";
        $title = "EMPLOYEE'S SSS NUMBER REPORT";
    }
    else if ($_GET["Custom"] == "TIN") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, TIN from tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY EmpNo";
        $title = "EMPLOYEE'S TAX IDENTIFICATION NUMBER";
    }
    else if ($_GET["Custom"] == "Philhealth") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PhilHealth from tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY EmpNo";
        $title = "EMPLOYEE'S PHILHEALTH NUMBER";
    }
    else if ($_GET["Custom"] == "Pagibig") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PagIbig from tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY EmpNo";
        $title = "EMPLOYEE'S PAGIBIG NUMBER";
    }
    else if ($_GET["Custom"] == "Address") {




    }
    else if ($_GET["Custom"] == "LengthOfService") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, DateHired, CONCAT(EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),DateHired))))+0,' years') AS 'Length of Service' FROM tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY DateHired ASC";
        $title = "EMPLOYEE'S LENGTH OF SERVICE REPORT";
    }
    else if ($_GET["Custom"] == "Age") {
        $sql = "Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, CONCAT(EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),DOBirth))))+0,' years') AS Age FROM tbl_EmployeeInfo WHERE empstatus = 1 ORDER BY Age DESC";
        $title = "EMPLOYEE'S AGE";
    }
    else if ($_GET["Custom"] == "EducAttain") {

        



    }
    else if ($_GET["Custom"] == "CivilStatus") {
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employee Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "MealStub") {
        //contains blank spaces for print input, all employees, complete group
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "MonthlyBirthday") {
        //input month
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "PerformanceRating") {
        //almost same as meal stub
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "ResignedEmployees") {
        //input start and end date
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "RaffleStub") {
        //same as meal stub with length of service, pending pa kay ate arlene
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "ShuttleService") {
        //Div - Dep - Section - ingoing - outgoing - remarks
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
    else if ($_GET["Custom"] == "ManpowerClassification") {
        //status - salary type - classification - Gender
        $sql = 'SELECT CivilStatus, COUNT(*) AS "Employees Count" FROM tbl_EmployeeInfo WHERE EmpStatus = 1 GROUP BY CivilStatus';
        $title = "EMPLOYEE CIVIL STATUS";
    }
}
else
{
    $sql = "Select * from tbl_EmployeeInfo WHERE empstatus=1 ORDER BY EmpNo";
    $title = "EMPLOYEE INFORMATION REPORT";
}




$result = HRISDB::getInstance()->execute_search_query($sql);

function incrementLetter($letter,$inc)
{
	for ($i=0; $i < $inc; $i++) {
		$letter++;
	}
	return $letter;
}






//starting row number
//$rownumber = 2;

//main titles
$Letter = 'B';
$mergeLetter = incrementLetter($Letter,4);
$currentCell = $Letter.$rownumber;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue($currentCell, 'PRICON MICROELECTRONICS');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells($Letter.$rownumber.':'.$mergeLetter.$rownumber);
$objPHPExcel->setActiveSheetIndex(0)->getStyle($currentCell)->getFont()->setBold(true);
$rownumber++;

$currentCell = $Letter.$rownumber;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue($currentCell, $title);
$objPHPExcel->setActiveSheetIndex(0)->mergeCells($currentCell.':'.$mergeLetter.$rownumber);
$objPHPExcel->setActiveSheetIndex(0)->getStyle($currentCell)->getFont()->setBold(true);
$rownumber++;

$currentCell = $Letter.$rownumber;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue($currentCell, 'as of '.DATE('F d, Y'));
$objPHPExcel->setActiveSheetIndex(0)->mergeCells($currentCell.':'.$mergeLetter.$rownumber);
$objPHPExcel->setActiveSheetIndex(0)->getStyle($currentCell)->getFont()->setBold(true);
$rownumber++;
$rownumber++;


$objPHPExcel->getActiveSheet()->getStyle('A1:D4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
$objPHPExcel->getActiveSheet()->getStyle('A1:D4')->getAlignment()->setWrapText(true);


$Letter = 'A';
	//getting header names
	$finfo = mysqli_fetch_fields($result);
    for ($i=0; $i < count($finfo); $i++) {
    	$Letter++;
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($Letter.$rownumber, getHeaderIndex($finfo[$i]->name,$i));
        $objPHPExcel->setActiveSheetIndex(0)->getStyle($Letter.$rownumber)->getFont()->setBold(true);

        if($finfo[$i]->name=='EmpNo')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(10);
        else if($finfo[$i]->name=='FullName')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(33);
    	else if($finfo[$i]->name=='Address')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(45);
        else if($finfo[$i]->name=='TIN')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(20);
        else if($finfo[$i]->name=='SSN')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(15);
        else if($finfo[$i]->name=='PhilHealth')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(15);
        else if($finfo[$i]->name=='PagIbig')
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setWidth(25);
        else
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getStyle($Letter.$rownumber)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getStyle($Letter.$rownumber)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle($Letter.$rownumber)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

//one line space between the headers and the values
$rownumber++;

//create line
$objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->getStartColor()->setARGB('black');
$objPHPExcel->getActiveSheet()->getRowDimension($rownumber)->setRowHeight(2);



//getting the values
//add styles

$color = 0;
$counter = 0;
while($row = mysqli_fetch_array($result))
{
    $counter++;
	$rownumber++;
    $color++;
	$Letter = 'A';
	for($i=0; $i<mysqli_num_fields($result);$i++)
    {
     	$Letter++;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($Letter.$rownumber, replaceValue($row[$i],$i));
    
        $objPHPExcel->getActiveSheet()->getStyle($Letter.$rownumber)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getStyle($Letter.$rownumber)->getAlignment()->setWrapText(true);
    }
    if($color%2)
    {
        $objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->getStartColor()->setARGB('c5d9f1');//9199FF  //c5d9f1
    }
}
mysqli_free_result($result);
$rownumber ++;
//create line
$objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('B'.$rownumber.':'.$Letter.$rownumber)->getFill()->getStartColor()->setARGB('black');
$objPHPExcel->getActiveSheet()->getRowDimension($rownumber)->setRowHeight(1);

//footer
$rownumber ++;
$rownumber ++;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$rownumber, 'Total Employees results: '.$counter);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$rownumber)->getFont()->setBold(true);
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$rownumber.':'.'E'.$rownumber);



$now_date = DATE('m-d-Y H:i');
//define title for .doc or .xls file: EDIT this if you want
$title = "HRIS Employee List on $now_date";
$filename = "HRIS Employee List $now_date";

// Rename worksheet
$objPHPExcel->setActiveSheetIndex(0)->setTitle('Simple');

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;


// support for excel 2003
// // Redirect output to a client’s web browser (Excel5)
// header('Content-Type: application/vnd.ms-excel');
// header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
// header('Cache-Control: max-age=0');

// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
// $objWriter->save('php://output');
// exit;

?>