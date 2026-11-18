<?php 
		require_once __DIR__ . '/../global_includes/hris-oop.php';
		$sql = array(
        //            array("SELECT EmpNo from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
		array("SELECT EmpNo, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName, HiringStatus, SalaryType, fkPosition as Position, fkPosition as Classification, Gender from tbl_EmployeeInfo "," AND EmpStatus = 1 ORDER BY EmpNo ASC "),
							
		array("SELECT DISTINCT(fkDivision) FROM tbl_EmployeeInfo WHERE empstatus = 1", "fkDivision"),

		array("SELECT DISTINCT(fkDepartment) FROM tbl_EmployeeInfo ", "fkDepartment"," AND empstatus = 1 "),

		array("SELECT DISTINCT(fkSection) FROM tbl_EmployeeInfo ", "fkSection"," AND empstatus = 1 ")
							
		);
		
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
		array('Month','Month',''),
		array('fkPosition, Position')
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
		
		function replaceValue($inputValue,$inputIndex) {
			global $headerValues;

			for ($i=0; $i < count($headerValues); $i++) {
				//fix comparing 0 values
				if($headerValues[$i][2]==strval($inputIndex))
				{
					$h = $headerValues[$i][0];
					break;
				}
			}
			
			if($h=='Position')
			{
				return HRISDB::getInstance()->get_by_position($inputValue);
			} else {
				return $inputValue;
			}
		}
		
		//HEADERS BITCHES
		$result = HRISDB::getinstance()->execute_search_query($sql[0][0]);
		echo $sql[0][0]. "<br>";
		
	    $finfo = mysqli_fetch_fields($result);
	   
	    for($i=0; $i < count($finfo); $i++) {
			echo getHeaderIndex($finfo[$i]->name,$i) . "<br>";
			echo $finfo[$i]->name . "<br>";
			echo "<br>";
		}
		
		//PER DIVISION BITCHES
		$result = HRISDB::getinstance()->execute_search_query($sql[1][0]);
		echo "SQL1: " . $sql[1][0]. "<br>";
		while($row = mysqli_fetch_array($result))
		{
			echo "DIVISION: " . $row[0] . "<br>";
				
			//PER DEPARTMENT BITCHES
			$sql2 = $sql[2][0].' where '.$sql[1][1].' = "'.$row[0].'" '.$sql[2][2];
			$result2 = HRISDB::getinstance()->execute_search_query($sql2);
			echo "SQL2: " . $sql2. "<br>";
			while($row2 = mysqli_fetch_array($result2)) {
				echo "DEPARTMENT: " . $row2[0] . "<br>";
				
				 //PER SECTION BITCHES
				$sql3 = $sql[3][0].' where '.$sql[1][1].' = "'.$row[0].'" and '.$sql[2][1].' = "'.$row2[0].'" '.$sql[3][2];
				$result3 = HRISDB::getinstance()->execute_search_query($sql3);
				echo "SQL3: " . $sql3. "<br>";
				
				 while($row3 = mysqli_fetch_array($result3)) {
					echo "SECTION: " . $row3[0] . "<br>";
					
					$sql4 = $sql[0][0].' where '.$sql[1][1].' = "'.$row[0].'" and '.$sql[2][1].' = "'.$row2[0].'" and '.$sql[3][1].' = "'.$row3[0].'" '.$sql[0][1];
					$result4 = HRISDB::getinstance()->execute_search_query($sql4);	
					echo "SQL4: " . $sql4. "<br>";
					
					while($row4 = mysqli_fetch_array($result4))
					{
						echo "MAIN VALUES <br>";
						for($i=0; $i<mysqli_num_fields($result4);$i++)
						{
							echo replaceValue($row4[$i], $i) . " - ";
						}
						echo "<br>";
					}
				 }
			}
			
			
			
		}
		
?>
		