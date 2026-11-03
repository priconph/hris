<?php

require_once('../global_includes/hris-oop.php');

//include the main excel class
require_once('excel/Classes/PHPExcel.php');

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
	array('Position','Position',''),
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
    array('FullName','Employee Name',''),
    array('Age','Age',''),
    array('LengthOfService','Length Of Service',''),
    array('Classification','Classification',''),
    array('Month','Month','')
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

    if($h == 'EmpNo')
    {
        //if contains letters dont use the text formula
        if (preg_match('/[A-Za-z]/', $inputValue))
        {
            return $inputValue;
        }
        else
        {   //formula for excel to retain leading zeros
            //get length of the number value
            //=text(text,"00000")
            $zeroMatch = '';
            for ($i=0; $i < strlen($inputValue); $i++) { 
                $zeroMatch .= '0';
            }
            return '=text('.$inputValue.',"'.$zeroMatch.'")';
        }
    }
    else if($h=='Classification')
    {
        return HRISDB::getInstance()->get_class_by_position($inputValue);
    }
    else if($h=='DOBirth')
    {
        $date = new DateTime($inputValue);
        return $date->format('M j, Y');
    }
    else if($h=='pkid')
    {
    	//hide values of the pkid
        return '';
    }
    else if($h=='lastupdate')
    {
        //hide values of the last update
        return '';
    }
    else if($h=='Month')
    {
        $timestamp = mktime(0, 0, 0, $inputValue, 1, 2012);
    
        return date("M", $timestamp);
    }
    else if($h=='Age')
    {
        return $inputValue.' years';
    }
    else if($h=='LengthOfService')
    {
        return $inputValue.' years';
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
                             ->setLastModifiedBy("Session user")
                             ->setTitle("Office 2007 XLSX Document")
                             ->setSubject("Office 2007 XLSX Document")
                             ->setDescription("Report.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Report file");

//set cells value
function setValue($cell,$value)
{
	global $objPHPExcel;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell,$value);
}

//set cell to bold
function setBold($cell)
{
	global $objPHPExcel;
	$objPHPExcel->setActiveSheetIndex(0)->getStyle($cell)->getFont()->setBold(true);
}

//warp and align vertical top the cell
function setStyle($cell)
{
	global $objPHPExcel;
	$objPHPExcel->setActiveSheetIndex(0)->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->setActiveSheetIndex(0)->getStyle($cell)->getAlignment()->setWrapText(true);
}

function incrementLetter($letter,$inc)
{
	global $objPHPExcel;
	for ($i=0; $i < $inc; $i++) {
		$letter++;
	}
	return $letter;
}

function endExcel($title)
{
	global $objPHPExcel;
	$now_date = DATE('m-d-Y H:i');
	$filename = "$title $now_date";

	// Rename worksheet
	$objPHPExcel->setActiveSheetIndex(0)->setTitle($title);

	// Redirect output to a client’s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
}


//set color for alternates
function setAlternatingColor($cell)
{
	global $objPHPExcel;
	$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->getStartColor()->setARGB('c5d9f1');
}


//replace value according to the sql header result
function formatValue($result,$value,$index,$location)
{
	global $objPHPExcel;
	global $headerValues;

	$finfo = mysqli_fetch_fields($result);
	getHeaderIndex($finfo[$index]->name,$location);
	replaceValue($value,$location);

	return replaceValue($value,$location);
}


function setWidth($column,$width)
{
    global $objPHPExcel;
    $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($width);
    return;
}

function setHeight($row,$height)
{
    global $objPHPExcel;
    $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($height);
    return;
}

function mergeCells($cells)
{
    global $objPHPExcel;
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells($cells);
    return;
}

function setAutosize($column)
{
    global $objPHPExcel;
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($column)->setAutoSize(true);
    return;
}

function createLine($row,$fromLetter,$toLetter)
{
    global $objPHPExcel;
    $objPHPExcel->getActiveSheet()->getStyle($fromLetter.$row.':'.$toLetter.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle($fromLetter.$row.':'.$toLetter.$row)->getFill()->getStartColor()->setARGB('black');
    $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(2);
    return;
}

//adjust to predefined cell widths
function adjustcolumnHeader($Letter,$number,$headerName)
{
    global $objPHPExcel;
    
    if($headerName=='EmpNo')
        setWidth($Letter,10);
    else if($headerName=='FullName')
        setWidth($Letter,33);
    else if($headerName=='Address')
        setWidth($Letter,45);
    else if($headerName=='TIN')
        setWidth($Letter,20);
    else if($headerName=='SSN')
        setWidth($Letter,15);
    else if($headerName=='PhilHealth')
        setWidth($Letter,15);
    else if($headerName=='PagIbig')
        setWidth($Letter,25);
    else
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($Letter)->setAutoSize(true);

    $objPHPExcel->getActiveSheet()->getStyle($Letter.$number)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
    $objPHPExcel->getActiveSheet()->getStyle($Letter.$number)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle($Letter.$number)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    return;
}

//main function
function generatereport($sql,$title)
{
    $test=0;
    global $objPHPExcel;
    setHeight(1,5);
    setWidth('A',1);
    setWidth('B',2);
    setWidth('C',2);
    setWidth('D',2);

    $group1space = 0;
    $group2space = 1;
    $group3space = 2;
    $group4space = 3;

    $baseletter = 'B';
    $rowNumber = 1;

    $mergeLetter = incrementLetter($baseletter,6);

    //set worksheet headers
    $rowNumber++;
    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell,'PRICON MICROELECTRONICS');
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

    //set title
    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell,$title);
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

    //set worksheet date
    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell, 'as of '.DATE('F d, Y'));
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell, '');
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

    $empCounter = 0;
    $totalCounter = 0;
    $subgroupCounter = 0;
    $rowNumber++;

    //put headers and get index
    $result = HRISDB::getinstance()->execute_search_query($sql[0][0]);

    //show headers
    $finfo = mysqli_fetch_fields($result);
    $letterIncrement = -1;
    for($i=0; $i < count($finfo); $i++) {
        setValue(incrementLetter(incrementLetter($baseletter,$i),$group4space).$rowNumber,getHeaderIndex($finfo[$i]->name,$i));
        setBold(incrementLetter(incrementLetter($baseletter,$i),$group4space).$rowNumber);
        adjustcolumnHeader(incrementLetter(incrementLetter($baseletter,$i),$group4space),$rowNumber,$finfo[$i]->name);
        $letterIncrement++;
    }
    $lastLetter = incrementLetter(incrementLetter($baseletter,$letterIncrement),$group4space);
    $rowNumber++;
    createLine($rowNumber,incrementLetter($baseletter,$group4space),$lastLetter);
    $rowNumber++;

    //execute first group
    $result = HRISDB::getinstance()->execute_search_query($sql[1][0]);
    
    while($row = mysqli_fetch_array($result))
    {
        //show group 1
        setValue(incrementLetter($baseletter,$group1space).$rowNumber,formatValue($result,$row[0],0,-1));
        setBold(incrementLetter($baseletter,$group1space).$rowNumber);
        mergeCells(incrementLetter($baseletter,$group1space).$rowNumber.':'.$lastLetter.$rowNumber);
        $rowNumber++;

        //get query of second group with condition of first group
        $sql2 = $sql[2][0].' where '.$sql[1][1].' = "'.$row[0].'" '.$sql[2][2];

        //execute second group
        $result2 = HRISDB::getinstance()->execute_search_query($sql2);
        $subgroupCounter = 0;
        while($row2 = mysqli_fetch_array($result2))
        {
            if($sql[2][1]!="''")
            {
                setValue(incrementLetter($baseletter,$group2space).$rowNumber,formatValue($result2,$row2[0],0,-2));
                setBold(incrementLetter($baseletter,$group2space).$rowNumber);
                mergeCells(incrementLetter($baseletter,$group2space).$rowNumber.':'.$lastLetter.$rowNumber);
                $rowNumber++;
            }
            
            $subCounter = 0;

            //get query of thrid group with condition of first and second group
            $sql3 = $sql[3][0].' where '.$sql[1][1].' = "'.$row[0].'" and '.$sql[2][1].' = "'.$row2[0].'" '.$sql[3][2];

            //execute third group
            $result3 = HRISDB::getinstance()->execute_search_query($sql3);

            while($row3 = mysqli_fetch_array($result3))
            {
                if($sql[3][1]!="''")
                {
                    setValue(incrementLetter($baseletter,$group3space).$rowNumber,formatValue($result3,$row3[0],0,-3));
                    setBold(incrementLetter($baseletter,$group3space).$rowNumber);
                    mergeCells(incrementLetter($baseletter,$group3space).$rowNumber.':'.$lastLetter.$rowNumber);
                    $rowNumber++;
                }
                
                //create line for elements
                createLine($rowNumber,incrementLetter($baseletter,$group4space),$lastLetter);
                $rowNumber++;

                $sql4 = $sql[0][0].' where '.$sql[1][1].' = "'.$row[0].'" and '.$sql[2][1].' = "'.$row2[0].'" and '.$sql[3][1].' = "'.$row3[0].'" '.$sql[0][1];
                
                //get main values
                $result4 = HRISDB::getinstance()->execute_search_query($sql4);
                $empCounter = 0;
                $color = 0;
                while($row4 = mysqli_fetch_array($result4))
                {
                    $empCounter++;
                    $totalCounter++;
                    $subgroupCounter++;
                    $subCounter++;
                    $color++;
                    $forIncrement = -1;

                    for($i=0; $i<mysqli_num_fields($result4);$i++)
                    {
                        setValue(incrementLetter($baseletter,($group4space+$i)).$rowNumber,replaceValue($row4[$i],$i));
                        $forIncrement++;
                    }
                    if($color%2)
                    {
                        setAlternatingColor(incrementLetter($baseletter,$group4space).$rowNumber.':'.incrementLetter($baseletter,($group4space+$forIncrement)).$rowNumber);
                    }
                    $rowNumber++;
                }
                if($sql[3][1]!="''")
                {
                    setValue(incrementLetter($baseletter,$group3space).$rowNumber,$empCounter.' Employee(s)');
                    $rowNumber++;
                }
                //print total of group 3
            }
            if($sql[2][1]!="''")
            {
                setValue(incrementLetter($baseletter,$group2space).$rowNumber,$subCounter.' Employee(s)');
                $rowNumber++;
            }
            //print total of group 2
        }
        if($sql[1][1]!="''")
        {
          setValue(incrementLetter($baseletter,$group1space).$rowNumber,'Sub total: '.$subgroupCounter.' Employee(s)');
          $rowNumber++;
        }
        $rowNumber++;
        //print total of group 1
    }
    if($title=="Employee Civil Status")
    {
        $totalCounter = HRISDB::getinstance()->get_total_active();
    }
    setValue(incrementLetter($baseletter,$group1space).$rowNumber,'Total Employees(s): '.$totalCounter.'');
    endExcel($title);
}



if(isset($_GET["Custom"]))
{
    if ($_GET["Custom"] == "exportResult") {
        //result of main page search
        session_start();
        //"SELECT EmpNo,LastName,FirstName,MiddleName,Incoming,Outgoing FROM tbl_EmployeeInfo where Incoming='7'";//
        $exportSQL = $_SESSION['SQL'];
        $sql = array(
                array($exportSQL.'-- -',""),
                array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                );
        generatereport($sql,"EMPLOYEE REPORT");
    }
    else if ($_GET["Custom"] == "SSS") {
        //EMPLOYEE'S SSS NUMBER REPORT
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, SSN from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S SSS NUMBER REPORT");
    }
    else if ($_GET["Custom"] == "TIN") {
        //EMPLOYEE'S TAX IDENTIFICATION NUMBER
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, TIN from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"TAX IDENTIFICATION NUMBER");
    }
    else if ($_GET["Custom"] == "Philhealth") {
        //EMPLOYEE'S PHILHEALTH NUMBER
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PhilHealth from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S PHILHEALTH NUMBER");
    }
    else if ($_GET["Custom"] == "Pagibig") {
        //Pagibig number
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PagIbig from tbl_EmployeeInfo "," AND empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S PAGIBIG NUMBER");
    }
    else if ($_GET["Custom"] == "Address") {
        // Employee Address
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, CONCAT(CityAddress1,', ',CityAddress2,', ',CityAddress3) AS Address from tbl_EmployeeInfo "," AND empstatus = 1"),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"Employee Address Report");
    }
    else if ($_GET["Custom"] == "LengthOfService") {
        //Length of service
        //creation of view
        //view already filtered to active employees
        //Create View LengthOfService AS Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName , DateHired, EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),DateHired))))+0 AS LengthOfService FROM tbl_EmployeeInfo WHERE empstatus=1 ORDER BY LengthOfService DESC
        
        HRISDB::getinstance()->execute_search_query("DROP VIEW IF EXISTS `db_hris`.`LengthOfService`;");
        HRISDB::getinstance()->execute_search_query("CREATE ALGORITHM=UNDEFINED DEFINER=`builder`@`%` SQL SECURITY DEFINER VIEW  `db_hris`.`LengthOfService` AS select `tbl_EmployeeInfo`.`EmpNo` AS `EmpNo`,concat(`tbl_EmployeeInfo`.`LastName`,', ',`tbl_EmployeeInfo`.`FirstName`,' (',`tbl_EmployeeInfo`.`MiddleName`,')') AS `FullName`,`tbl_EmployeeInfo`.`DateHired` AS `DateHired`,(extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DateHired`)))) + 0) AS `LengthOfService` from `tbl_EmployeeInfo` where (`tbl_EmployeeInfo`.`EmpStatus` = 1) order by (extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DateHired`)))) + 0) desc;");

        $sql = array(
                    array("SELECT EmpNo, FullName, DateHired, CONCAT(LengthOfService,' year(s)') AS LengthOfService FROM LengthOfService "," ORDER BY EmpNo ASC"),
                    array("SELECT '' FROM ages limit 1", "''"),
                    array("SELECT DISTINCT(LengthOfService) FROM LengthOfService", "LengthOfService",""),
                    array("SELECT '' FROM LengthOfService ", "''", " limit 1 ")
                    );
        generatereport($sql,"Employee Length of Service");
    }
    else if ($_GET["Custom"] == "Age") {
        //Age report
        //gets values from age query view
        //create statement below just in case
        //view already filtered to active employees
        //CREATE ALGORITHM=UNDEFINED DEFINER=`builder`@`%` SQL SECURITY DEFINER VIEW  `db_hris`.`ages` AS select `tbl_EmployeeInfo`.`EmpNo` AS `EmpNo`,concat(`tbl_EmployeeInfo`.`LastName`,', ',`tbl_EmployeeInfo`.`FirstName`,' (',`tbl_EmployeeInfo`.`MiddleName`,')') AS `FullName`,concat((extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DOBirth`)))) + 0)) AS `Age` from `tbl_EmployeeInfo` where (`tbl_EmployeeInfo`.`EmpStatus` = 1) order by concat((extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DOBirth`)))) + 0),' years') desc;
        
        HRISDB::getinstance()->execute_search_query("DROP VIEW IF EXISTS `db_hris`.`ages`");
        HRISDB::getinstance()->execute_search_query("CREATE VIEW ages AS SELECT *, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, concat((extract(year from from_days((to_days(now()) - to_days(DOBirth)))) + 0)) AS Age  FROM tbl_EmployeeInfo");

        $sql = array(
                    array("SELECT EmpNo, FullName, CONCAT(Age,' years old') AS Age, DOBirth, Gender, fkPosition FROM ages ",""),
                    array("SELECT '' FROM ages limit 1", "''"),
                    array("SELECT DISTINCT(Age) FROM ages", "Age",""),
                    array("SELECT '' FROM ages ", "''", " limit 1 ")
                    );
        generatereport($sql,"Employee Age Report");
    }
    else if ($_GET["Custom"] == "EducAttain") {
        //educational Attainment
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT distinct(HighestEduc) FROM tbl_EmployeeInfo", "HighestEduc"),
                    array("SELECT DISTINCT(Degree) FROM tbl_EmployeeInfo", "Degree","  AND EmpStatus = 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"Educational Attainment Report");
    }

    else if ($_GET["Custom"] == "CivilStatus") {
        $sql = array(
                    array("SELECT CivilStatus, COUNT(*) AS 'Employee Count' FROM tbl_EmployeeInfo "," AND EmpStatus = 1 GROUP BY CivilStatus "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"Employee Civil Status");
    }
    else if ($_GET["Custom"] == "MealStub") {
        //contains blank spaces for print input, all employees, active, complete group
        //headers?
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, '_______________' as signature from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
                    );
        generatereport($sql,"EMPLOYEE MEAL STUB STATUS");
    }
    else if ($_GET["Custom"] == "MonthlyBirthday") {

        // //creation of month view
        // HRISDB::getinstance()->execute_search_query("DROP VIEW IF EXISTS `db_hris`.`tblmonths`");
        // HRISDB::getinstance()->execute_search_query("CREATE ALGORITHM=UNDEFINED DEFINER=`builder`@`%` SQL SECURITY DEFINER VIEW  `db_hris`.`tblmonths` AS select month(`tbl_EmployeeInfo`.`DOBirth`) AS `MOBirth` from `tbl_EmployeeInfo`");

        if (!isset($_GET["Month"]))
        {
            //if getting all months
            $_GET["Month"] = '1 or TRUE';
        }

        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, month(DOBirth) as Month, DOBirth, concat((extract(year from from_days((to_days(now()) - to_days(DOBirth)))) + 0)) AS Age  from tbl_EmployeeInfo "," AND (month(DOBirth) = ".$_GET["Month"].") AND EmpStatus = 1 ORDER BY month(DOBirth) ASC, day(DOBirth) ASC "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 "),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE MONTHLY BIRTHDAY");
    }
    else if ($_GET["Custom"] == "PerformanceRating") {
        //employee performance rating
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, '_______________' as 'P.A. Rating', '_______________' as Remarks from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
                    );
        generatereport($sql,"EMPLOYEE PERFORMANCE RATING");
    }
    else if ($_GET["Custom"] == "ResignedEmployees") {
        //input start and end date
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName from tbl_EmployeeInfo "," AND EmpStatus = 2 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 2", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 2 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 2 ")
                    );
        generatereport($sql,"RESIGNED EMPLOYEES");
    }
    else if ($_GET["Custom"] == "RaffleStub") {
        //same as meal stub with length of service, pending pa kay ate arlene
        //headers?
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, '_______________' as 'recieved by' from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
                    );
        generatereport($sql,"EMPLOYEE REFFLE STUB");
    }
    else if ($_GET["Custom"] == "ShuttleService") {
        //Div - Dep - Section - ingoing - outgoing - remarks
        //ok
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, Incoming, Outgoing, Remarks from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
                    );
        generatereport($sql,"EMPLOYEE SHUTTLE SERVICE");
    }
    else if ($_GET["Custom"] == "ManpowerCountdown") {
        //status - salary type - classification - Gender
        //status?? and classification
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, HiringStatus, SalaryType, fkPosition as Classification, Gender from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),
                    array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
                    );
        generatereport($sql,"MANPOWER COUNTDOWN");
    }
}
else
{
    $sql = array(
                array("SELECT '' from tbl_EmployeeInfo ",""),
                array("SELECT '' from tbl_EmployeeInfo ",""),
                array("SELECT '' FROM tbl_EmployeeInfo limit 1", "''"),
                array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ","")
                );
    generatereport($sql,"Report");
}


?>