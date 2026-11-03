<?php

require_once('../global_includes/hris-oop.php');
require_once('excel/Classes/PHPExcel.php');


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
    array('FullName','Employee Name',''),
    array('Age','Age',''),
    array('LengthOfService','Length Of Service','')
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


function setValue($cell,$value)
{
	global $objPHPExcel;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell,$value);
}

function setBold($cell)
{
	global $objPHPExcel;
	$objPHPExcel->setActiveSheetIndex(0)->getStyle($cell)->getFont()->setBold(true);
}
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
	//define title for .doc or .xls file: EDIT this if you want
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

function setAlternatingColor($cell)
{
	global $objPHPExcel;
	$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->getStartColor()->setARGB('c5d9f1');//9199FF  //c5d9f1
}



function formatValue($result,$value,$index)
{
	global $objPHPExcel;
	global $headerValues;

	$finfo = mysqli_fetch_fields($result);
	getHeaderIndex($finfo[$index]->name,-1);
	replaceValue($value,-1);

	return replaceValue($value,-1);
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


function generatereport($sql,$title)
{
	global $objPHPExcel;
    setHeight(1,5);
    setWidth('A',1);
    setWidth('B',2);
    setWidth('C',1);

	// $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(5);
	// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1);

	$baseletter = 'B';
	$rowNumber = 1;


    $mergeLetter = incrementLetter($baseletter,4);

    $rowNumber++;
    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell,'PRICON MICROELECTRONICS');
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

    $currentCell = $baseletter.$rowNumber;
    setValue($currentCell,$title);
    mergeCells($baseletter.$rowNumber.':'.$mergeLetter.$rowNumber);
    setBold($currentCell);
    $rowNumber++;

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
    $rowNumber++;

	
	//execute query
	//$sql[0];

	$empCounter = 0;
	$totalCounter = 0;
	$subgroupCounter = 0;
	$rowNumber++;

	//put headers and get index
	$result = HRISDB::getinstance()->execute_search_query($sql[0][0]);
	//print headers
	$finfo = mysqli_fetch_fields($result);
	
    $letterIncrement = -1;
    for($i=0; $i < count($finfo); $i++) {
    	setValue(incrementLetter(incrementLetter($baseletter,$i),2).$rowNumber,getHeaderIndex($finfo[$i]->name,$i));
    	setBold(incrementLetter(incrementLetter($baseletter,$i),2).$rowNumber);
        adjustcolumnHeader(incrementLetter(incrementLetter($baseletter,$i),2),$rowNumber,$finfo[$i]->name);
        $letterIncrement++;
    	//echo(getHeaderIndex($finfo[$i]->name,$i).'   ');
    }
    $lastLetter = incrementLetter(incrementLetter($baseletter,$letterIncrement),2);
    $rowNumber++;
    createLine($rowNumber,incrementLetter($baseletter,2),$lastLetter);
    $rowNumber++;
    //echo('<br>');

	//execute first group
	$result = HRISDB::getinstance()->execute_search_query($sql[1][0]);

    //sgfsdga
    

	while($row = mysqli_fetch_array($result))
    {
		//print group 1
		//echo(formatValue($result,$row[0],0).'<br>');
		setValue(incrementLetter($baseletter,0).$rowNumber,formatValue($result,$row[0],0));
		setBold(incrementLetter($baseletter,0).$rowNumber);
        mergeCells(incrementLetter($baseletter,0).$rowNumber.':'.$lastLetter.$rowNumber);
		$rowNumber++;
		//formatValue($result,$row[0],0);		

		//get query of second group with condition of first group
		$sql2 = $sql[2][0].' where '.$sql[1][1].' = "'.$row[0].'" '.$sql[2][2];
        //die($sql2);
        /////////////////////
		$result2 = HRISDB::getinstance()->execute_search_query($sql2);
		$subgroupCounter = 0;
		while($row2 = mysqli_fetch_array($result2))
	    {
			//echo('space '.$row2[0].'<br>');
			setValue(incrementLetter($baseletter,1).$rowNumber,formatValue($result2,$row2[0],0));
			setBold(incrementLetter($baseletter,1).$rowNumber);
            mergeCells(incrementLetter($baseletter,1).$rowNumber.':'.$lastLetter.$rowNumber);
			$rowNumber++;

            createLine($rowNumber,incrementLetter($baseletter,2),$lastLetter);
            $rowNumber++;
			//echo one space

			$sql3 = $sql[0][0].' where '.$sql[1][1].' = "'.$row[0].'" and '.$sql[2][1].' = "'.$row2[0].'" '.$sql[0][1];
            
			$result3 = HRISDB::getinstance()->execute_search_query($sql3);
			//echo($sql3);
			$empCounter = 0;
			$color = 0;
			while($row3 = mysqli_fetch_array($result3))
		    {
		    	$empCounter++;
				$totalCounter++;
				$subgroupCounter++;
				$color++;
				$forIncrement = -1;

				for($i=0; $i<mysqli_num_fields($result3);$i++)
    			{
    				//echo(replaceValue($row3[$i],$i).'     ');
    				setValue(incrementLetter($baseletter,(2+$i)).$rowNumber,replaceValue($row3[$i],$i));
                    //setAutosize(incrementLetter($baseletter,(2+$i)));
    				$forIncrement++;
    			}
    			if($color%2)
                {
                	setAlternatingColor(incrementLetter($baseletter,2).$rowNumber.':'.incrementLetter($baseletter,(2+$forIncrement)).$rowNumber);
                    // $objPHPExcel->getActiveSheet()->getStyle('C'.$rownumber.':D'.$rownumber)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    // $objPHPExcel->getActiveSheet()->getStyle('C'.$rownumber.':D'.$rownumber)->getFill()->getStartColor()->setARGB('c5d9f1');//9199FF  //c5d9f1
                }
    			$rowNumber++;
				//echo('<br>');
				//apply styles
				//echo double space
			}
			//echo($empCounter.' Employee(s)<br>');
            if($sql[2][1]!="''")
            {
    			setValue(incrementLetter($baseletter,1).$rowNumber,$empCounter.' Employee(s)');
                $rowNumber++;
            }
			//print total of group 2
		}
		//echo('Sub total: '.$subgroupCounter.' Employee(s)<br>');
        if($sql[1][1]!="''")
        {
		  setValue(incrementLetter($baseletter,0).$rowNumber,'Sub total: '.$subgroupCounter.' Employee(s)');
          $rowNumber++;
        }
		$rowNumber++;
		//print total of group 1
	}
	//echo('Total Employees(s): '.$totalCounter.'<br>');
	setValue(incrementLetter($baseletter,0).$rowNumber,'Total Employees(s): '.$totalCounter.'');
	endExcel($title);
}



if (isset($_POST["SQL"])) 
{
    $sql  = $_POST["SQL"];
}
elseif(isset($_GET["Custom"]))
{
    if ($_GET["Custom"] == "SSS") {
        //EMPLOYEE'S SSS NUMBER REPORT
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, SSN from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S SSS NUMBER REPORT");
    }
    else if ($_GET["Custom"] == "TIN") {
        //EMPLOYEE'S TAX IDENTIFICATION NUMBER
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, TIN from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S TAX IDENTIFICATION NUMBER");
    }
    else if ($_GET["Custom"] == "Philhealth") {
        //EMPLOYEE'S PHILHEALTH NUMBER
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PhilHealth from tbl_EmployeeInfo "," AND  empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1 ", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ")
                    );
        generatereport($sql,"EMPLOYEE'S PHILHEALTH NUMBER");
    }
    else if ($_GET["Custom"] == "Pagibig") {
        //Pagibig number
        $sql = array(
                    array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, PagIbig from tbl_EmployeeInfo "," AND empstatus = 1 ORDER BY EmpNo "),
                    array("SELECT '' FROM tbl_EmployeeInfo limit 1", "''"),
                    array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ","")
                    );
        generatereport($sql,"EMPLOYEE'S PAGIBIG NUMBER");
    }
    else if ($_GET["Custom"] == "Address") {
        // Employee Address
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, CONCAT(CityAddress1,', ',CityAddress2,', ',CityAddress3) AS Address from tbl_EmployeeInfo "," AND empstatus = 1"),
                    array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),
                    array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment","")
                    );
        generatereport($sql,"Employee Address Report");
    }
    else if ($_GET["Custom"] == "LengthOfService") {
        //Length of service
        //creation of view
        //view already filtered into active employees
        //Create View LengthOfService AS Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName , DateHired, EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),DateHired))))+0 AS LengthOfService FROM tbl_EmployeeInfo WHERE empstatus=1 ORDER BY LengthOfService DESC
        $sql = array(
                    array("SELECT EmpNo, FullName, DateHired, CONCAT(LengthOfService,' years') AS LengthOfService FROM LengthOfService ",""),
                    array("SELECT '' FROM ages limit 1", "''"),
                    array("SELECT DISTINCT(LengthOfService) FROM LengthOfService", "LengthOfService","","")
                    );
        generatereport($sql,"Length of Service Report");
    }
    else if ($_GET["Custom"] == "Age") {
        //Age report
        //gets values from age query view
        //create statement below just in case
        //view already filtered into active employees
        //CREATE ALGORITHM=UNDEFINED DEFINER=`builder`@`%` SQL SECURITY DEFINER VIEW  `db_hris`.`ages` AS select `tbl_EmployeeInfo`.`EmpNo` AS `EmpNo`,concat(`tbl_EmployeeInfo`.`LastName`,', ',`tbl_EmployeeInfo`.`FirstName`,' (',`tbl_EmployeeInfo`.`MiddleName`,')') AS `FullName`,concat((extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DOBirth`)))) + 0)) AS `Age` from `tbl_EmployeeInfo` where (`tbl_EmployeeInfo`.`EmpStatus` = 1) order by concat((extract(year from from_days((to_days(now()) - to_days(`tbl_EmployeeInfo`.`DOBirth`)))) + 0),' years') desc;
        $sql = array(
                    array("SELECT EmpNo, FullName, CONCAT(Age,' years old') AS Age FROM ages ",""),
                    array("SELECT '' FROM ages limit 1", "''"),
                    array("SELECT DISTINCT(Age) FROM ages", "Age","")
                    );
        generatereport($sql,"Employee Age Report");
    }
    else if ($_GET["Custom"] == "EducAttain") {
        //educational Attainment
        $sql = array(
                    array("Select EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName from tbl_EmployeeInfo ",""),
                    array("SELECT distinct(HighestEduc) FROM tbl_EmployeeInfo", "HighestEduc"),
                    array("SELECT DISTINCT(Degree) FROM tbl_EmployeeInfo", "Degree","")
                    );
        generatereport($sql,"Educational Attainment Report");
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
    //Pagibig number
    $sql = array(
                array("SELECT * from tbl_EmployeeInfo "," AND empstatus = 1 ORDER BY EmpNo "),
                array("SELECT '' FROM tbl_EmployeeInfo limit 1", "''"),
                array("SELECT '' FROM tbl_EmployeeInfo ", "''", " limit 1 ","")
                );
    generatereport($sql,"EMPLOYEE'S PAGIBIG NUMBER");
}


?>