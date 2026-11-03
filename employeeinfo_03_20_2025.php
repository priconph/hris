<?php

//for layout testing
$border = 0;

require_once("../global_includes/hris-oop.php");
include_once 'db_hris.php';

ob_start(); 
session_start();
$username = $_SESSION['username'];
$checkSection = HRISDB::getInstance()->getSectionID($username);

//validations done in javascript
function GetAge($bday)
{
    $date = new DateTime($bday);
    $diff = $date->diff(new DateTime());

    $age = ($diff->y!=0) ? $diff->y.' years ':'';
    $age .= ($diff->m!=0) ? $diff->m.' months ':'';
    $age .= ($diff->d!=0) ? $diff->d.' days ':'';

    return $age;
}

function convertDateFromat($date)
{
    try
    {
        //change format of date
        $date = new DateTime($date);
        $returnDate = $date->format('Y-m-d');
    }
    catch (Exception $e)
    {
        $returnDate = '';
    }
    return $returnDate;
}

function convertDateFromatViewing($date)
{
    try
    {
        //change format of date
        $date = new DateTime($date);
        $returnDate = $date->format('m/d/Y');
    }
    catch (Exception $e)
    {
        $returnDate = '';
    }
    return $returnDate;
}

//initialize empkid
$pkid = "";

    //get gor testing
    if ($_SERVER["REQUEST_METHOD"] == "POST")// || $_SERVER["REQUEST_METHOD"] == "GET")
    {
        ////////for development
        ////$_POST["pkid"] = $_GET["pkid"];

        //validate date fields
        if($_POST["DateOfBirth"]!='')
        {
            $DOB = convertDateFromat($_POST["DateOfBirth"]);
        }
        else $DOB = '';

        if($_POST["DateGraduated"]!='')
        {
            $DOGrad = convertDateFromat($_POST["DateGraduated"]);
        }
        else $DOGrad = '';

        if($_POST["DateHired"]!='')
        {
            $DOHired = convertDateFromat($_POST["DateHired"]);
        }
        else $DOHired = '';

        if($_POST["DepDateOfBirth"]!='')
        {
            $_POST["DepDateOfBirth"] = convertDateFromat($_POST["DepDateOfBirth"]);
        }

        if($_POST["EffectivityDate"]!='')
        {
            $_POST["EffectivityDate"] = convertDateFromat($_POST["EffectivityDate"]);
        }

        if($_POST["NotificationDate"]!='')
        {
            $_POST["NotificationDate"] = convertDateFromat($_POST["NotificationDate"]);
        }

        //put leading zeros in employee number
        if(($_POST["EmployeeNo"] != "") && (strlen($_POST["EmployeeNo"]) < 4))
        {
            for ($i=4-strlen($_POST["EmployeeNo"]); $i > 0; $i--) { 
                $_POST["EmployeeNo"] = '0'.$_POST["EmployeeNo"];
            }
        }

        //for dependents form
        //get value of tax dependent
        if($_POST["TaxDependent"]=='yes') $taxdep = 1; else $taxdep = 0;

        

        if($_POST["pkid"]!="")
        {
            //get pkid
            $pkid = $_POST["pkid"];
            // echo $pkid;

            if($_POST["EmployeeNo"]!="")
            {
                //check if the employee number is already existing
                $empNotUnique = HRISDB::getInstance()->verify_employee_number($_POST["EmployeeNo"],$pkid);
                if($empNotUnique)
                {
                    $message = 'unable to save, employee number '.$_POST["EmployeeNo"].' is already existing';
                }
            }
            else
                $empNotUnique = false;

            //update employee
            if ($_POST["formtype"]=="SaveBasicInfo" && !$empNotUnique)
            {
                //update basic info
                HRISDB::getInstance()->update_employee($pkid,
                    $_POST["LastName"],
                    $_POST["FirstName"],
                    $_POST["MiddleName"],
                    $_POST["NickName"],
                    $DOB,
                    $_POST["Gender"],
                    $_POST["CivilStatus"],
                    $_POST["Spouse"],
                    $_POST['TaxExemptionBase'],
                    $_POST["CityAddStreet"],
                    $_POST["CityAddBarangay"],
                    $_POST["CityAdd"],
                    $_POST["ProvAddressBarangay"],
                    $_POST["ProvAddressMunicipality"],
                    $_POST["ProvAddress"],
                    $_POST["Telephone"],
                    $_POST["IncomingShuttle"],
                    $_POST["OutgoingShuttle"],
                    $_POST["Remarks"]);

                //update educational background
                HRISDB::getInstance()->update_employee_educ($pkid,
                    $_POST["HighestEducationalAttainment"],
                    $_POST["SchoolAddress"],
                    $DOGrad,
                    $_POST["DegreeObtained"],
                    $_POST["HonorsAwards"],
                    $_POST["GovernmentExams"]);

                //check for employment history changes
                if(HRISDB::getInstance()->change_employement_history($pkid,
                    $_POST["Division"],
                    $_POST["Department"],
                    $_POST["Section"],
                    $_POST["Position"]
                    ))
                {
                //insert into employment history table if there is change detected
                HRISDB::getInstance()->add_employee_history($pkid,$_POST["Division"],$_POST["Department"],$_POST["Section"],$_POST["Position"],date("Y-m-d"),'Present');
                }

                //update employee employment status
                HRISDB::getInstance()->update_employee_employment_info($pkid,
                    $_POST["EmployeeNo"],
                    $_POST["EmploymentStatus"], //employment category
                    $_POST["Division"],
                    $_POST["Status"],  //active resigned
                    $_POST["Department"],
                    $_POST["SalaryType"],
                    $_POST["Section"],
                    $_POST["SSS"],
                    $_POST["Position"],
                    $_POST["TIN"],
                    $_POST["PagIbig"],
                    $DOHired,
                   	$_POST["PhilHealth"],
					$_POST["BloodType"],
					$_POST["ResEffectDate"],
					$_POST["ResReason"]);

                //update family info
                HRISDB::getInstance()->update_employee_family($pkid,$_POST["FatherName"],
                    $_POST["MotherName"],
                    $_POST["PersonToBeNotified"]);

                $message = 'Employee Information Saved';
            }

            //dependent update
            if ($_POST["formtype"]=="Dependentok")
            {
                HRISDB::getInstance()->update_dependent($_POST["DepLastName"],
                    $_POST["DepFirstName"],
                    $_POST["DepMiddleName"],
                    $_POST["DepDateOfBirth"],
                    $_POST["Relationship"],
                    $taxdep,
                    $_POST["deppkid"]);

                //update family info
                HRISDB::getInstance()->update_employee_family($pkid,$_POST["FatherName"],
                    $_POST["MotherName"],
                    $_POST["PersonToBeNotified"]);

                $message = 'Dependent information updated';
            }

            //dependent delete
            else if ($_POST["formtype"]=="Dependentdelete")
            {
                HRISDB::getInstance()->delete_dependent($_POST["deppkid"]);

                $message = 'Dependent deleted';
            }

            //create new dependent
            else if ($_POST["formtype"]=="Dependentadd")
            {                             
                HRISDB::getInstance()->create_dependent($_POST["DepLastName"],
                    $_POST["DepFirstName"],
                    $_POST["DepMiddleName"],
                    $pkid,
                    $_POST["DepDateOfBirth"],
                    $_POST["Relationship"],
                    '0',
                    $taxdep);

                //update family info
                HRISDB::getInstance()->update_employee_family($pkid,$_POST["FatherName"],
                    $_POST["MotherName"],
                    $_POST["PersonToBeNotified"]);

                $message = 'New dependent added';
            }

            //update disciplinary info
            if ($_POST["formtype"]=="Offensesave")
            {
                HRISDB::getInstance()->update_disciplinary($_POST["Action"],
                    $_POST["NatureOfOffense"],
                    $_POST["FrequencyOfOffense"],
                    $_POST["NotificationDate"],
                    $_POST["EffectivityDate"],
                    $_POST["Offensepkid"]);

                $message = 'Offense saved';
            }

            //delete disciplinary
            else if ($_POST["formtype"]=="Offensedelete")
            {
                HRISDB::getInstance()->delete_disciplinary($_POST["Offensepkid"]);

                $message = 'Offense deleted';
            }

            //create disciplinary info
            else if ($_POST["formtype"]=="Offenseadd")
            {
                HRISDB::getInstance()->create_disciplinary($_POST["Action"],
                    $_POST["NatureOfOffense"],
                    $_POST["FrequencyOfOffense"],
                    $_POST["NotificationDate"],
                    $_POST["EffectivityDate"],
                    $pkid);

                $message = 'Offense added';
            }
			
	
            //create employment history info
            else if ($_POST["formtype"]=="addHistory")
            {
                if(isset($_POST['Present']) && $_POST['Present'] == 'Yes')
                {
                    $_POST["historyTo"] = 'Present';
                }

                HRISDB::getInstance()->add_employee_history($pkid,$_POST["historyDivision"],$_POST["historyDepartment"],$_POST["historySection"],$_POST["historyPosition"],$_POST["historyFrom"],$_POST["historyTo"]);
                $message = 'Employment History Added';
            }

            //create employment history info
            else if ($_POST["formtype"]=="editHistory")
            {
                if(isset($_POST['Present']) && $_POST['Present'] == 'Yes')
                {
                    $_POST["historyTo"] = 'Present';
                }
                HRISDB::getInstance()->update_employee_history($_POST["historypkid"],$_POST["historyDivision"],$_POST["historyDepartment"],$_POST["historySection"],$_POST["historyPosition"],$_POST["historyFrom"],$_POST["historyTo"]);
                $message = 'Employment History edited';
            }

            //create employment history info
            else if ($_POST["formtype"]=="deleteHistory")
            {
                HRISDB::getInstance()->delete_employee_history($_POST["historypkid"]);
                $message = 'Deleted Employment History';
            }
        }
        else //else if no pkid is found create the new employee
        {
            if ($_POST["formtype"]=="SaveBasicInfo")
            {
                //create new employee
                HRISDB::getInstance()->create_employee(
                    $_POST["LastName"],
                    $_POST["FirstName"],
                    $_POST["MiddleName"],
                    $_POST["NickName"],
                    $DOB,
                    $_POST["Gender"],
                    $_POST["CivilStatus"],
                    $_POST["Spouse"],
                    $_POST['TaxExemptionBase'],
                    $_POST["CityAddStreet"],
                    $_POST["CityAddBarangay"],
                    $_POST["CityAdd"],
                    $_POST["ProvAddressBarangay"],
                    $_POST["ProvAddressMunicipality"],
                    $_POST["ProvAddress"],
                    $_POST["Telephone"],
                    $_POST["IncomingShuttle"],
                    $_POST["OutgoingShuttle"],
                    $_POST["Remarks"]);

                //get pkid of the newly inserted account
                $pkid = HRISDB::getInstance()->get_last_inserted_employee_id();

                $message = 'Basic employee information saved';
            }
        }
        
        if($pkid != "")
        {
            //if an account pkid is found, load data

            //get all account data
            $result = HRISDB::getInstance()->get_employee_info_by_id($pkid);
            $EmpInfo = mysqli_fetch_array($result);
            
            //get age using php
            $EmpInfo['Age'] = GetAge($EmpInfo['DOBirth']);
        }
    }
    else //do nothing and leave all fields blank for creation of new employee
    {
        $message = 'Create new employee';
    }
	 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <title>Employee Information</title>
    <!-- CSS -->
    <link href="src/hris.css" type="text/css" rel="stylesheet" media="all" />
    
    <!-- JQUERY -->
    <link rel="stylesheet" type="text/css" href="../global_includes/jquery-1/css/custom-theme/jquery-ui-1.8.19.custom.css" />  
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-ui-1.8.19.custom.min.js"></script>
	<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>

    <!-- table sorter -->
    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>

    <!-- javascript codes -->
    <script language="javascript" type="text/javascript">

    //set page title
    document.title = document.title<?php echo '+": ['.$EmpInfo['EmpNo'].'] '.$EmpInfo['LastName'].', '.$EmpInfo['FirstName'].'"'; ?>;

    //variable for validating fields
    var error = false;

    function validateTextBox(textbox, alerts)
    {
        if(""==document.forms.EmployeeInfo[textbox].value){
            document.getElementById(alerts).innerHTML = '*'; error = true;
        }
        else document.getElementById(alerts).innerHTML = '';
    }

    //validate empty textboxes
    function validateHistoryForm()
    {
        error = false;
        validateTextBox('historyDivision', 'AlertHisDiv');
        validateTextBox('historyDepartment', 'AlertHisDep');
        validateTextBox('historySection', 'AlertHisSec');
        validateTextBox('historyPosition', 'AlertHisPos');
        validateTextBox('historyFrom', 'AlertHisFrom');


        if(document.forms.EmployeeInfo['historyTo'].value == "" && document.forms.EmployeeInfo['Present'].checked==false)
        {
            //validateTextBox('historyTo', 'AlertHisTo');
            document.getElementById('AlertHisTo').innerHTML = '*'; error = true;
        }
        else document.getElementById('AlertHisTo').innerHTML = '';

        if(!error)
        {
            document.getElementById('alertHistory').innerHTML = '';
            return true;
        }
        else
        {
            document.getElementById('alertHistory').innerHTML = 'Please fill up the required fields';
        }
        return false;
    }

	//validate empty textboxes SCANNED 
    function validateEmpFileForm()
    {
        error = false;
        validateTextBox('filecat', 'AlertFileCat');

        if(!error)
        {
            document.getElementById('alertScannedFile').innerHTML = '';
            return true;
        }
        else
        {
            document.getElementById('alertScannedFile').innerHTML = 'Please fill up the required fields';
        }
        return false;
    }
	
    function validateForm()
    {
        error = false;

        //textbox validations
        validateTextBox('LastName', 'AlertLN');
        validateTextBox('FirstName', 'AlertFN');
        validateTextBox('MiddleName', 'AlertMN');
        validateTextBox('DateOfBirth', 'AlertDOB');
        validateTextBox('CityAddStreet', 'AlertCAS');
        validateTextBox('CityAddBarangay', 'AlertCAB');
        validateTextBox('CityAdd', 'AlertCA');
        

        //no need for validation
        //validateTextBox('Telephone', 'AlertT');
        //validateTextBox('Spouse', 'AlertS');
        //validateTextBox('NickName', 'AlertNN');
        //validateTextBox('ProvAddressBarangay', 'AlertPAB');
        //validateTextBox('ProvAddressMunicipality', 'AlertPAM');
        //validateTextBox('ProvAddress', 'AlertPA');
        
        if(!error)
        {
            document.getElementById('Alerts').innerHTML = '';
            document.forms.EmployeeInfo['formtype'].value = 'SaveBasicInfo';
            document.forms.EmployeeInfo.submit();
        }
        else
        {
            document.getElementById('Alerts').innerHTML = 'Please fill up the required fields';
        }
    }

    function getAge()
    {
        //calculate age based on the date
        var birthdate = new Date(document.EmployeeInfo['DateOfBirth'].value);
        var today = new Date();
        var diff = today.getTime() - birthdate.getTime();
        var oneDay = 1000*60*60*24;
        var oneMonth = oneDay * (365.25/12);
        var oneyear = oneDay * 365.25;
        
        var y = Math.floor(diff/oneyear);
        var m = Math.floor((diff/oneMonth)%12);
        var d = (Math.ceil((diff/oneDay)%(365.25/12))-1);

        document.EmployeeInfo['Age'].value = '';

        if(y>1) document.EmployeeInfo['Age'].value += y+' years ';
        else if(y==1) document.EmployeeInfo['Age'].value += y+' year ';

        if(m>1) document.EmployeeInfo['Age'].value += m+' months ';
        else if(m==1) document.EmployeeInfo['Age'].value += m+' month ';

        if(d>1) document.EmployeeInfo['Age'].value += d+' days';
        else if(d==1) document.EmployeeInfo['Age'].value += d+' day';

        //document.EmployeeInfo['Age'].value = Math.floor(diff/oneyear)+' year(s), '+ Math.floor((diff/oneMonth)%12) + ' month(s), ' + (Math.ceil((diff/oneDay)%(365.25/12))-1) + ' day(s) ';
    }

    function getDepAge()
    {
        //calculate dependent age based on the date
        var birthdate = new Date(document.EmployeeInfo['DepDateOfBirth'].value);
        var today = new Date();
        var diff = today.getTime() - birthdate.getTime();
        var oneDay = 1000*60*60*24;
        var oneMonth = oneDay * (365.25/12);
        var oneyear = oneDay * 365.25;
        
        var y = Math.floor(diff/oneyear);
        var m = Math.floor((diff/oneMonth)%12);
        var d = (Math.ceil((diff/oneDay)%(365.25/12))-1);

        document.EmployeeInfo['DepAge'].value = '';

        if(y>1) document.EmployeeInfo['DepAge'].value += y+' years ';
        else if(y==1) document.EmployeeInfo['DepAge'].value += y+' year ';

        if(m>1) document.EmployeeInfo['DepAge'].value += m+' months ';
        else if(m==1) document.EmployeeInfo['DepAge'].value += m+' month ';

        if(d>1) document.EmployeeInfo['DepAge'].value += d+' days';
        else if(d==1) document.EmployeeInfo['DepAge'].value += d+' day';

    }

    //javascript trim function
    String.prototype.trim = function()
    {
        return this.replace(/^\s+|\s+$/g,"");
    }

    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //055 for dash (-)
        //053 for +
        //050 for (
        //051 for )
        //240 for space
        //octal values
        //numbers only and dash
        if ((charCode > 31 && (charCode < 48 || charCode > 57)) && (charCode != 050) && (charCode != 051) && (charCode != 055) && (charCode != 053) && (charCode != 040))
            return false;

        return true;
    }

    function isLetterKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //key code code for small and capital letters
        //46 for period
        //44 for comma
        //241 for ñ
        //209 for Ñ
        //055 for dash (-)
        if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode != 32) && (charCode != 46) && (charCode != 053) && (charCode != 44) && (charCode != 241) && (charCode != 209) && (charCode != 055))
            return false;
        return true;
    }

    function isLetterAndNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //key code code for small and capital letters
        //46 for period
        //44 for comma
        
        if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    $(function() {
        $( "#tabs" ).tabs({
            fxAutoHeight: true
        });

        //get the selected tab
        var selectedTab = <?php 
        if ($_POST["tabIndex"]!="") 
            echo $_POST["tabIndex"];
        else
            echo "0";
        ?>;

        //select the previously selected tab on load
        $( "#tabs" ).tabs( "option", "selected", selectedTab );
        document.EmployeeInfo['tabIndex'].value = selectedTab;
        

        <?php
            //hide tabs when creating a new account
            //show only personal background tab
            if($pkid=="")
            {
            ?>
                // add new created tabs here
                $('#tabs').tabs("option","disabled",[1,2,3,4,5,6]);
            <?php
            }
        ?>

        //put selected tab on tabIndex element
        $( "#tabs" ).tabs({
            show: function(event, ui)
            {
                document.EmployeeInfo['tabIndex'].value = $( "#tabs" ).tabs( "option", "selected" );
            }
        });
        
        $(function() {
        $( "#datepickerBday" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true , maxDate: "-18Y"});
        });
        $(function() {
        $( "#datepickerDepBday" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+0D"});
        //$( "#datepickerDepBday" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerGrad" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+0D"});
        });
        $(function() {
        $( "#datepickerHired" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+0D"});
        });
        $(function() {
        $( "#datepickerNotif" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+0D"});
        //$( "#datepickerNotif" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerEffect" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true});
        //$( "#datepickerEffect" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });

        $(function() {
        $( "#historyTo" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true });
        //$( "#historyTo" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#historyFrom" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true });
        //$( "#historyFrom" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });

        //delete dependent confirmation dialog
        $("#dialog").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open delete dependent dialog 
        $("#opener").click(function() {
            $("#dialog").dialog("open");
            return false;
        });
        //close delete dependent dialog box
        $("#closer").click(function() {
            $("#dialog").dialog("close");
            return false;
        });

        //delete offense confirmation dialog
        $("#dialogDeleteOffense").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open offense dialog 
        $("#OffenseDialogopener").click(function() {
            $("#dialogDeleteOffense").dialog("open");
            return false;
        });
        //close offense dialog box
        $("#OffenseDialogcloser").click(function() {
            $("#dialogDeleteOffense").dialog("close");
            return false;
        });
        var adegreeObtainedTags = [
            <?php
                //tags for the degree field autocomplete
                $result = HRISDB::getInstance()->execute_search_query("SELECT Distinct(Degree) FROM tbl_EmployeeInfo WHERE Degree!=''");
                $list = '';
                while($row = mysqli_fetch_array($result))
                {
                    $list .= '"'.$row['Degree'].'",';
                }
                echo substr($list,0,-1);
            ?>
        ];
        //connect the autocomplete values from above to the degree textbox
        $( "#DegreeObtained" ).autocomplete({
            source: adegreeObtainedTags
        });

        var offenseTags = [
            <?php
                //tags for the degree field autocomplete
                $result = HRISDB::getInstance()->execute_search_query("SELECT DISTINCT(Nature) FROM tbl_Disciplinary ORDER BY NATURE");
                $list = '';
                while($row = mysqli_fetch_array($result))
                {
                    $list .= '"'.$row['Nature'].'",';
                }
                echo substr($list,0,-1);
            ?>
        ];
        //connect the autocomplete values from above to the degree textbox
        $( "#NatureOfOffense" ).autocomplete({
            source: offenseTags
        });

        $("#Present").change(function(){
            if (this.checked === true) {
                $("input:text.historyTo").attr('disabled', true);
            }
            else {
                $("input:text.historyTo").removeAttr("disabled");
            }
        });
    });

    onload = function() {
        //show age of employee
        getAge();

        //hide dependent delete and ok button button on load
        document.EmployeeInfo['deletes'].style.display = 'none';
        document.EmployeeInfo['adds'].style.display = 'inline-block';
        document.EmployeeInfo['oks'].style.display = 'none';

        //hide Offense delete and ok button button on load
        document.EmployeeInfo['Offensedeletes'].style.display = 'none';
        document.EmployeeInfo['Offenseadds'].style.display = 'inline-block';
        document.EmployeeInfo['Offenseoks'].style.display = 'none';

        //make sorted table for dependents
        var myTable = new SortedTable('sorted');


        myTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        myTable.onsort = myTable.colorize;
        myTable.onmove = myTable.colorize;
        myTable.colorize();

        myTable.onselect = function(element)
        {
            //get values on the table and put into row[][] variable
            var $table = $("#sorted"),
              $headerCells = $table.find("thead th"),
              $rows = $table.find("tbody tr");
            var headers = [], rows = [];
            $headerCells.each(function(k,v)
            {
                headers[headers.length] = $(this).text();
            });
            $rows.each(function(row,v)
            {
            $(this).find("td").each(function(cell,v)
            {
                if (typeof rows[cell] === 'undefined') rows[cell] = [];
                rows[cell][row] = $(this).text();
            });
            });

            //insert all values of selected dependent into the textboxes
            document.EmployeeInfo['DepLastName'].value = rows[0][this.elementIndex(element)];
            document.EmployeeInfo['DepFirstName'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['DepMiddleName'].value = rows[2][this.elementIndex(element)];
            document.EmployeeInfo['DepDateOfBirth'].value = rows[3][this.elementIndex(element)];
            document.EmployeeInfo['Relationship'].value = rows[8][this.elementIndex(element)];
            document.EmployeeInfo['TaxDependent'].value = rows[5][this.elementIndex(element)];
            document.EmployeeInfo['DepAge'].value = rows[6][this.elementIndex(element)];
            getDepAge();
            document.EmployeeInfo['deppkid'].value = rows[7][this.elementIndex(element)];

            //hide add button
            document.EmployeeInfo['deletes'].style.display = 'inline-block';
            document.EmployeeInfo['adds'].style.display = 'none';
            document.EmployeeInfo['oks'].style.display = 'inline-block';

            //validateFields();
            validateDependentFields();

        }
        myTable.ondeselect = function(element)
        {
            //remove values
            document.EmployeeInfo['DepLastName'].value = "";
            document.EmployeeInfo['DepFirstName'].value = "";
            document.EmployeeInfo['DepMiddleName'].value = "";
            document.EmployeeInfo['DepDateOfBirth'].value = "";
            document.EmployeeInfo['Relationship'].value = "1";
            document.EmployeeInfo['TaxDependent'].value = "no";
            document.EmployeeInfo['DepAge'].value = "";
            document.EmployeeInfo['deppkid'].value = "";

            //hide delete and ok button, show add button
            document.EmployeeInfo['deletes'].style.display = 'none';
            document.EmployeeInfo['adds'].style.display = 'inline-block';
            document.EmployeeInfo['oks'].style.display = 'none';
        }



        var trainingTable = new SortedTable('sortedTrainings');

        //sorted table colorization
        trainingTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        trainingTable.onselect = function(element)
        {
             //this.cleansort();
             SortedTable.getSortedTable(document.getElementById('row1')).cleanselect();
        }

        trainingTable.onsort = myTable.colorize;
        trainingTable.onmove = myTable.colorize;
        trainingTable.colorize();

		
		 var ScannedTable = new SortedTable('sortedScanned');

        //sorted table colorization
        ScannedTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        ScannedTable.onselect = function(element)
        {
             SortedTable.getSortedTable(document.getElementById('row1')).cleanselect();
        }

        ScannedTable.onsort = myTable.colorize;
        ScannedTable.onmove = myTable.colorize;
        ScannedTable.colorize();

		
        var myOffenseTable = new SortedTable('sortedOffense');
        
        //sorted table colorization
        myOffenseTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        myOffenseTable.onsort = myTable.colorize;
        myOffenseTable.onmove = myTable.colorize;
        myOffenseTable.colorize();

        myOffenseTable.onselect = function(element)
        {
            //get values on the table and put into row[][] variable
            var $table = $("#sortedOffense"),
              $headerCells = $table.find("thead th"),
              $rows = $table.find("tbody tr");
            var headers = [], rows = [];
            $headerCells.each(function(k,v)
            {
                headers[headers.length] = $(this).text();
            });
            $rows.each(function(row,v)
            {
            $(this).find("td").each(function(cell,v)
            {
              if (typeof rows[cell] === 'undefined') rows[cell] = [];
              rows[cell][row] = $(this).text();
            });
            });

            //inser all values of selected dependent into the textboxes
            document.EmployeeInfo['Action'].value = rows[6][this.elementIndex(element)];
            document.EmployeeInfo['NatureOfOffense'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['FrequencyOfOffense'].value = rows[7][this.elementIndex(element)];
            document.EmployeeInfo['EffectivityDate'].value = rows[3][this.elementIndex(element)];
            document.EmployeeInfo['NotificationDate'].value = rows[4][this.elementIndex(element)];
            document.EmployeeInfo['Offensepkid'].value = rows[5][this.elementIndex(element)];

            //hide add button
            document.EmployeeInfo['Offensedeletes'].style.display = 'inline-block';
            document.EmployeeInfo['Offenseadds'].style.display = 'none';
            document.EmployeeInfo['Offenseoks'].style.display = 'inline-block';

            validateOffenseFields();
        }
        myOffenseTable.ondeselect = function(element)
        {
            //remove values
            document.EmployeeInfo['Action'].value = "1";
            document.EmployeeInfo['NatureOfOffense'].value = "";
            document.EmployeeInfo['FrequencyOfOffense'].value = "1";
            document.EmployeeInfo['EffectivityDate'].value = "";
            document.EmployeeInfo['NotificationDate'].value = "";
            document.EmployeeInfo['Offensepkid'].value = "";

            //hide delete and ok button, show add button
            document.EmployeeInfo['Offensedeletes'].style.display = 'none';
            document.EmployeeInfo['Offenseadds'].style.display = 'inline-block';
            document.EmployeeInfo['Offenseoks'].style.display = 'none';
        }

	        
        //for employment history table
        var historyTable = new SortedTable('sortedHistory');
        
        //sorted table colorization
        historyTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        historyTable.onsort = historyTable.colorize;
        historyTable.onmove = historyTable.colorize;
        historyTable.colorize();

        historyTable.onselect = function(element)
        {
            //get values of table and put to fields

            var $table = $("#sortedHistory"),
              $headerCells = $table.find("thead th"),
              $rows = $table.find("tbody tr");
            var headers = [], rows = [];
            $headerCells.each(function(k,v)
            {
                headers[headers.length] = $(this).text();
            });
            $rows.each(function(row,v)
            {
            $(this).find("td").each(function(cell,v)
            {
                if (typeof rows[cell] === 'undefined') rows[cell] = [];
                rows[cell][row] = $(this).text();
            });
            });

            //inser all values of selected dependent into the textboxes
            document.EmployeeInfo['historyPosition'].value = rows[6][this.elementIndex(element)];
            document.EmployeeInfo['historyDivision'].value = rows[7][this.elementIndex(element)];
            updateDepartmentListHistory();
            document.EmployeeInfo['historyDepartment'].value = rows[8][this.elementIndex(element)];
            updateSectionListHistory();
            document.EmployeeInfo['historySection'].value = rows[9][this.elementIndex(element)];
            document.EmployeeInfo['historyFrom'].value = rows[4][this.elementIndex(element)];

            if(rows[5][this.elementIndex(element)]=='Present')
            {
                $("input:text.historyTo").attr('disabled', true);
                $("#Present").attr('checked','checked');
            }
            else
            {
                $("input:text.historyTo").removeAttr("disabled");
                $("#Present").removeAttr("checked");
                document.EmployeeInfo['historyTo'].value = rows[5][this.elementIndex(element)];
            }

            document.EmployeeInfo['historypkid'].value = rows[10][this.elementIndex(element)];

            //show delete and edit, hide add history
            document.EmployeeInfo['deleteHistory'].style.display = 'inline-block';
            document.EmployeeInfo['addHistory'].style.display = 'none';
            document.EmployeeInfo['editHistory'].style.display = 'inline-block';

            validateHistoryForm();
        }
        historyTable.ondeselect = function(element)
        {
            document.EmployeeInfo['historyDivision'].value = "";
            document.EmployeeInfo['historyDepartment'].value = "";
            document.EmployeeInfo['historySection'].value = "";
            document.EmployeeInfo['historyPosition'].value = "";
            document.EmployeeInfo['historyFrom'].value = "";
            document.EmployeeInfo['historyTo'].value = "";
            
            $("input:text.historyTo").removeAttr("disabled");
            $("#Present").removeAttr("checked");

            //hide delete and edit button, show add button
            document.EmployeeInfo['deleteHistory'].style.display = 'none';
            document.EmployeeInfo['addHistory'].style.display = 'inline-block';
            document.EmployeeInfo['editHistory'].style.display = 'none';
        }
		
	
        var appraisalTable = new SortedTable('sortedAppraisal');

        //sorted table colorization
        appraisalTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        appraisalTable.onselect = function(element)
        {
             //this.cleansort();
             SortedTable.getSortedTable(document.getElementById('row1')).cleanselect();
        }

        appraisalTable.onsort = myTable.colorize;
        appraisalTable.onmove = myTable.colorize;
        appraisalTable.colorize();
    }

    function validateDependentFields()
    {
        //trim textbox spaces
        document.EmployeeInfo['DepLastName'].value = document.EmployeeInfo['DepLastName'].value.trim();
        document.EmployeeInfo['DepFirstName'].value = document.EmployeeInfo['DepFirstName'].value.trim();
        document.EmployeeInfo['DepMiddleName'].value = document.EmployeeInfo['DepMiddleName'].value.trim();

        //javascript validation of fields
        error = false;

        validateTextBox('DepLastName', 'AlertLastName');
        validateTextBox('DepFirstName', 'AlertFirstName');
        validateTextBox('DepMiddleName', 'AlertMiddleName');
        validateTextBox('DepDateOfBirth', 'AlertDOBirth');
        validateTextBox('DepLastName', 'AlertLastName');
        //validateTextBox('DepAge', 'AlertDepAge');

        if(error)
        {
            document.getElementById('Alert').innerHTML = 'Please fill up the required fields';
        }
        else
        {
            document.getElementById('Alert').innerHTML = '';
        }

        return error;
    }

    function Dependentok()
    {
        if(!validateDependentFields())
        {
            document.EmployeeInfo['formtype'].value = "Dependentok";
            document.EmployeeInfo.submit();
        }
    }

    function Dependentadd()
    {
        if(!validateDependentFields())
        {
            document.EmployeeInfo['formtype'].value = "Dependentadd";
            document.EmployeeInfo.submit();
        }
    }

    function Dependentdelete()
    {
        document.EmployeeInfo['formtype'].value = "Dependentdelete";
        document.EmployeeInfo.submit();
    }


    function validateOffenseFields()
    {
        error = false;
        
        validateTextBox('NotificationDate', 'AlertNotificationDate');
        validateTextBox('NatureOfOffense', 'AlertNatureOfOffense');
        validateTextBox('EffectivityDate', 'AlertEffectivityDate');

        //validateTextBox('Action', 'AlertAction');
        //validateTextBox('FrequencyOfOffense', 'AlertAction');
        if(error)
        {
            document.getElementById('AlertOffense').innerHTML = 'Please fill up the required fields';
        }
        else
        {
            document.getElementById('AlertOffense').innerHTML = '';
        }
        return error;   
    }

    function Offenseok()
    {
        if(!validateOffenseFields())
        {
            document.EmployeeInfo['formtype'].value = "Offensesave";
            document.EmployeeInfo.submit();
        }
    }

    function Offenseadd()
    {
        if(!validateOffenseFields())
        {
            document.EmployeeInfo['formtype'].value = "Offenseadd";
            document.EmployeeInfo.submit();
        }
    }

    function Offensedelete()
    {
        document.EmployeeInfo['formtype'].value = "Offensedelete";
        document.EmployeeInfo.submit();
    }

    //add option on selection dropdownbox
    //used for changing values of departments and sections dropdownbox
    function appendOptionLast(id,text,value)
    {
      var elOptNew = document.createElement('option');
      elOptNew.text = text;
      elOptNew.value = value;
      var elSel = document.getElementById(id);

      try {
        elSel.add(elOptNew, null); 
      }
      catch(ex) {
        elSel.add(elOptNew); 
      }
    }

// function showCat(pkid) {
function showCat(fkCategory) {
// function showCat(fkCategory,fkEmployee) {

  // if (pkid == "") {
  if (fkCategory == "") {
    document.getElementById("sortedScanned").innerHTML="";
    return;
  } 
     
  if (window.XMLHttpRequest) {
    xmlhttp=new XMLHttpRequest();
  } else { 
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      document.getElementById("sortedScanned").innerHTML=xmlhttp.responseText;
    }
  }
  // xmlhttp.open("GET","searchcategory.php?pkid="+pkid,true);
  xmlhttp.open("GET","searchcategory.php?fkCategory="+fkCategory,true);
  // xmlhttp.open("GET","searchcategory.php?fkCategory="+fkCategory+"&fkEmployee="+fkEmployee,true);
  xmlhttp.send();
}
	
    var Departments = new Array (<?php
        //get all departments and put in javascript array
        $result = HRISDB::getInstance()->execute_search_query("SELECT * FROM tbl_Department Where isActive = 1 ORDER BY Department");

        $array = '';
        while($row = mysqli_fetch_array($result))
        {
            $DepartmentName = str_replace('"', '\"', $row['Department']);
            $array .= 'new Array("'.$row['pkid'].'","'.$DepartmentName.'","'.$row['fkDivision'].'"),';
        }
        $array = substr_replace($array ,"",-1);
        echo $array;
        ?>);

    var Sections = new Array(<?php
        //get all sections and put in sections array
        $result = HRISDB::getInstance()->execute_search_query("SELECT * FROM tbl_Section Where isActive = 1 ORDER BY Section");

        $array = '';
        while($row = mysqli_fetch_array($result))
        {
            $SectionName = str_replace('"', '\"', $row['Section']);
            $array .= 'new Array("'.$row['pkid'].'","'.$SectionName.'","'.$row['fkDepartment'].'"),';
        }
        $array = substr_replace($array ,"",-1);
        echo $array;
        ?>);

 function updateDepartmentList()
{
    //update options in Departments combo box
    removeOptions('Department');

    var Divvalue = document.getElementById('Division').value;

    for (var i = 0; i < Departments.length; i++)
    {
        if(Departments[i][2] == Divvalue)
        {
            appendOptionLast('Department',Departments[i][1],Departments[i][0]);
        }
    }
    updateSectionList();
}

function updateSectionList()
{
    //update options in Section combo box
    removeOptions('Section');

    var Depvalue = document.getElementById('Department').value;

    for (var i = 0; i < Sections.length; i++)
    {
        if(Sections[i][2] == Depvalue)
        {
            appendOptionLast('Section',Sections[i][1],Sections[i][0]);
        }
    }
}

    function updateDepartmentListHistory()
    {
        //update options in Departments combo box
        removeOptions('historyDepartment');

        var Divvalue = document.getElementById('historyDivision').value;

        for (var i = 0; i < Departments.length; i++)
        {
            if(Departments[i][2] == Divvalue)
            {
                appendOptionLast('historyDepartment',Departments[i][1],Departments[i][0]);
            }
        }
        updateSectionListHistory();
    }

    function updateSectionListHistory()
    {
        //update options in Section combo box
        removeOptions('historySection');

        var Depvalue = document.getElementById('historyDepartment').value;

        for (var i = 0; i < Sections.length; i++)
        {
            if(Sections[i][2] == Depvalue)
            {
                appendOptionLast('historySection',Sections[i][1],Sections[i][0]);
            }
        }
    }

    function removeOptions(id)
    {
        for(j=document.getElementById(id).options.length-1;j>=0;j--)
        {
            document.getElementById(id).remove(j);
        }
    }
	
	$(document).ready(function () {
		$('#scannedFile').on('submit',(function(e) {	
		e.preventDefault();
		var data_1 =  new FormData(this);
			data_1.append('fkEmployee', $('#fkEmployee').val());
			data_1.append('fkCategory', $('#filecat').val());
	$.ajax({
		   url: "uploads.php",    	
		   // url: "convert_pdf_to_jpg.php",    	
		   type: "POST",            
		   data: data_1, 	
		   contentType: false,      	
		   cache: false,          	
		   processData:false,       	
		   success: function(data)  	
		   {
				alert(data);
				window.location.reload();
			   }, error : function (data) {
				alert('Error found!');
			   }
		});
	}));
}); // end document 

	</script>
	<script type = 'text/javascript'>
		function toggleResignation() {
			var e = document.getElementById("toggler");
			var strUser = e.options[e.selectedIndex].text;
			if (strUser == 'Active' || strUser == 'Rehired') {
				document.getElementById('resignInfo').style.visibility = 'hidden';
			} else {
				document.getElementById('resignInfo').style.visibility = 'visible';
			}
		}
	</script>
</head>

<body>

    <h2>HRIS System</h2>
    <h3>Employee Information  <?php echo "(" . $EmpInfo['LastName'] . ", " . $EmpInfo['FirstName'] . " " . $EmpInfo['MiddleName'] . ") [" . $EmpInfo['EmpNo'] . "]"; ?></h3>

    <div class="transbox">

        <?php if($message!="")echo '<div class="alertMessage"><p><h4>&nbsp'.$message.'</p></h4></div>'; ?>

        <div id ="Personal Background">
            <!--<form name="EmployeeInfo" action="employeeinfo.php" method="POST" onSubmit="validateForm();">-->
            <form name="EmployeeInfo" method="POST">

            <div id="tabs">
                    <ul>
                        <?
                        //tabs to be implemented
                        // employment history - ok
                        // Social Security
                        // Phil Health
                        // Pag Ibig
                        // Medical History
                        // Leave Monitoring
                        // Appraisal
                        // Loans
                        ?>
                        <li><a href="#tabs-1">Personal Background</a></li>
                        <li><a href="#tabs-2">Family Information</a></li>
                        <li><a href="#tabs-3">Educational Background</a></li>
                        <li><a href="#tabs-4">Employment Information</a></li>
                        <li><a href="#tabs-5">Disciplinary History</a></li>
                        <li><a href="#tabs-6">Training Records</a></li>
                        <li><a href="#tabs-7">Employment History</a></li>
                        <li><a href="#tabs-8">Appraisal</a></li>
                        <li><a href="#tabs-9">Scanned 201 File</a></li>
                    </ul>
                    <!-- Personal Background -->
                    <div id="tabs-1">
                        <h4 class="group_title">Personal Information</h4>
                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td rowspan="5"><img src="<?php if($EmpInfo['Picturepath']==''){echo 'pictures/default.jpg';}else echo $EmpInfo['Picturepath']; ?>" border="2" width="150" height="150"></td>
                                            <td rowspan="5" width="50px"></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td>Lastname:</td>
                                            <td>
                                                <input type="text" name="LastName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['LastName'] ?>" />
                                                <font color="red"><text id='AlertLN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Firstname:</td>
                                            <td>
                                                <input type="text" name="FirstName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['FirstName'] ?>"/>
                                                <font color="red"><text id='AlertFN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Middlename:</td>
                                            <td>
                                                <input type="text" name="MiddleName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['MiddleName'] ?>"/>
                                                <font color="red"><text id='AlertMN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <?php if($EmpInfo['EmpStatus']==2) { echo '<td colspan="2"><font color="red" size="18">RESIGNED</font></td>'; } 
													else if($EmpInfo['EmpStatus']==3) { echo '<td colspan="2"><font color="red" size="18">REHIRED</font></td>'; } ?>
                                        </tr>

                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td>Nickname:</td>
                                            <td>
                                                <input type="text" name="NickName" maxlength="20" style="width:250px" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['NickName'] ?>"/>
                                                <font color="red"><text id='AlertNN'></text></font>
                                            </td>
                                            <td>Gender:</td>
                                            <td>
                                                <select name="Gender" style="width:200px">
                                                    <option value="1" <?php if($EmpInfo['Gender']==1) echo 'selected="true"'; ?>>Male</option>
                                                    <option value="2" <?php if($EmpInfo['Gender']==2) echo 'selected="true"'; ?>>Female</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Date of Birth:</td>
                                            <td>
                                                <!-- id="datepickerBday" -->
                                                <input type="text" name="DateOfBirth" style="width:250px" id="datepickerBday" onchange="getAge()" maxlength="10"
                                                value="<?php if($pkid!="")
                                                        {
                                                            if ($EmpInfo['DOBirth']!='0000-00-00'){
                                                            $date = new DateTime($EmpInfo['DOBirth']);
                                                            echo $date->format('m/d/Y');}
                                                        }
                                                        ?>"/>
                                                <font color="red"><text id='AlertDOB'></text></font>
                                            </td>
                                            <td>Civil Status:</td>
                                            <td>
                                                <select name="CivilStatus"  style="width:200px">
                                                    <option value="1" <?php if($EmpInfo['CivilStatus']==1) echo 'selected = "true"' ?>>Single</option>
                                                    <option value="2" <?php if($EmpInfo['CivilStatus']==2) echo 'selected = "true"' ?>>Married</option>
                                                    <option value="3" <?php if($EmpInfo['CivilStatus']==3) echo 'selected = "true"' ?>>Widowed</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Age:</td>
                                            <td width="200px">
                                                <input disabled="disabled" type="text" name="Age" readonly="readonly" size="29" value="<?php echo $EmpInfo['Age']; ?>"/><!-- age is dynamically calculated through javascript -->
                                                <font color="red"><text id='AlertA'></text></font>
                                            </td>
                                            <td>Spouse:</td>
                                            <td>
                                                <input type="text" name="Spouse"  style="width:200px" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['Spouse'] ?>"/>
                                                <font color="red"><text id='AlertS'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Tax Exemption Base:</td>
                                            <td>
                                                <select name="TaxExemptionBase">
                                                    <option value="1" <?php if($EmpInfo['ExemptBaseCode']==1)echo 'selected="true"' ?>>S</option>
                                                    <option value="2" <?php if($EmpInfo['ExemptBaseCode']==2)echo 'selected="true"' ?>>ME</option>
                                                    <option value="3" <?php if($EmpInfo['ExemptBaseCode']==3)echo 'selected="true"' ?>>HF</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <h4 class="group_title">Address Information</h4>
                
                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>
                                    City Address
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td>Street:</td>
                                            <td>
                                                <input type="text" name="CityAddStreet" maxlength="50" size="30" value="<?php echo $EmpInfo['CityAddress1'] ?>"/>
                                                <font color="red"><text id='AlertCAS'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Barangay:</td>
                                            <td>
                                                <input type="text" name="CityAddBarangay" maxlength="50" size="30" value="<?php echo $EmpInfo['CityAddress2'] ?>"/>
                                                <font color="red"><text id='AlertCAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>City:</td>
                                            <td>
                                                <input type="text" name="CityAdd" maxlength="50" size="30" value="<?php echo $EmpInfo['CityAddress3'] ?>"/>
                                                <font color="red"><text id='AlertCA'></text></font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    Provincial Address
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td>Barangay:</td>
                                            <td>
                                                <input type="text" name="ProvAddressBarangay" maxlength="50" size="30" value="<?php echo $EmpInfo['ProvAddress1'] ?>"/>
                                                <font color="red"><text id='AlertPAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Municipality:</td>
                                            <td>
                                                <input type="text" name="ProvAddressMunicipality" maxlength="50" size="30" value="<?php echo $EmpInfo['ProvAddress2'] ?>"/>
                                                <font color="red"><text id='AlertPAM'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Province:</td>
                                            <td>
                                                <input type="text" name="ProvAddress" maxlength="50" size="30" value="<?php echo $EmpInfo['ProvAddress3'] ?>"/>
                                                <font color="red"><text id='AlertPA'></text></font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">

                                    <table border="<?php echo $border ?>">

                                        <tr>
                                            <td>Telephone No:</td>
                                            <td colspan="2">
                                                <input type="text" name="Telephone" maxlength="15" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['TelephoneNo'] ?>"/>
                                                <font color="red"><text id='AlertT'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Incoming Shuttle Service:</td>
                                            <td>
                                                <select name="IncomingShuttle" value="<?php echo $EmpInfo['Incoming'] ?>">
                                                    <option value="0" <?php if($EmpInfo['Incoming']==0)echo 'selected="true"' ?>>Not Availing Service</option>
                                                    <option value="1" <?php if($EmpInfo['Incoming']==1)echo 'selected="true"' ?>>Lower Bicutan</option>
                                                    <option value="2" <?php if($EmpInfo['Incoming']==2)echo 'selected="true"' ?>>FTI</option>
                                                    <option value="3" <?php if($EmpInfo['Incoming']==3)echo 'selected="true"' ?>>Sucat</option>
                                                    <option value="4" <?php if($EmpInfo['Incoming']==4)echo 'selected="true"' ?>>Alabang</option>
                                                    <option value="5" <?php if($EmpInfo['Incoming']==5)echo 'selected="true"' ?>>Mamatid</option>
                                                    <option value="6"<?php if($EmpInfo['Incoming']==6)echo 'selected="true"' ?>>Mayapa/Real</option>
                                                    <!-- 
                                                        <option value="7" <?php if($EmpInfo['Incoming']==7)echo 'selected="true"' ?>>Calamba</option>
                                                     -->
                                                </select>
                                            </td>
                                            <td>Outgoing Shuttle Service:</td>
                                            <td>
                                                <select name="OutgoingShuttle">
                                                    <option value="0" <?php if($EmpInfo['Outgoing']==0)echo 'selected="true"' ?>>Not Availing Service</option>
                                                    <option value="1" <?php if($EmpInfo['Outgoing']==1)echo 'selected="true"' ?>>Lower Bicutan</option>
                                                    <option value="2" <?php if($EmpInfo['Outgoing']==2)echo 'selected="true"' ?>>FTI</option>
                                                    <option value="3" <?php if($EmpInfo['Outgoing']==3)echo 'selected="true"' ?>>Sucat</option>
                                                    <option value="4" <?php if($EmpInfo['Outgoing']==4)echo 'selected="true"' ?>>Alabang</option>
                                                    <option value="5" <?php if($EmpInfo['Outgoing']==5)echo 'selected="true"' ?>>Mamatid</option>
                                                    <option value="6" <?php if($EmpInfo['Outgoing']==6)echo 'selected="true"' ?>>Mayapa/Real</option>
                                                    <!-- 
                                                        <option value="7" <?php if($EmpInfo['Outgoing']==7)echo 'selected="true"' ?>>Calamba</option>
                                                     -->
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Remarks:</td>
                                            <td colspan="3">
                                                <!-- <input type="text" name="Remarks" value="<?php echo $EmpInfo['Remarks'] ?>"/> -->
                                                <textarea cols="74" maxlength="250" rows="2" name="Remarks"><?php echo $EmpInfo['Remarks'] ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <br>
                    </div>
                    <!-- Family Infornation -->
                    <div id="tabs-2">
                        <h4 class="group_title">Immediate Family</h4>

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Father's Name:</td>
                                <td>
                                    <input type="text" name="FatherName" maxlength="50" size="25" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['FatherName'] ?>"/>
                                    <font color="red"><text id='AlertFather'></text></font>
                                </td>
                                <td rowspan="2">Person to be notified <br>in case of emergency:</td>
                                <td rowspan="2">
                                    <textarea cols="40" rows="3" maxlength="150" name="PersonToBeNotified"><?php echo $EmpInfo['Emergency']; ?></textarea>

                                    <font color="red"><text id='AlertPersonNotify'></text></font>
                                </td>
                            </tr>

                            <tr>
                                <td>Mother's Name:</td>
                                <td>
                                    <input type="text" name="MotherName" maxlength="50" size="25" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['MotherName'] ?>"/>
                                    <font color="red"><text id='AlertMother'></text></font>
                                </td>
                            </tr>
                        </table>

                        <h4 class="group_title">Dependent Family</h4>

                        <!-- delete dialog box -->
                        <div id="dialog" title="Delete Dependent">
                            <p>Are you sure you want to delete this dependent?</p>
                            <input type="button" id="button" name="YesDeleteDependent" value="Yes" onclick="Dependentdelete()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                            <input type="button" id="closer" name="no" value="No" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                        </div>

                        <table class="transbox" border="<?php echo $border ?>">
                            
                            <tr>
                                <td>
                                    <!-- code for listing of dependents -->

                                    <table class="sorted" id="sorted" border="1">
                                        <thead>
                                            <tr>
                                                <th id="ln" class="sortedplus" title="click to sort by last name">Last Name</th>
                                                <th id="fn" title="click to sort by first name">First Name</th>
                                                <th id="mn" title="click to sort by middle name">Middle Name</th>
                                                <th id="dob" title="click to sort by birthday">Birthday</th>
                                                <th id="rel" title="click to sort by relationship">Relationship</th>
                                                <th id="dep" title="click to sort by tex exemption">Tax Exempt</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php

                                            //$EmpNo = HRISDB::getInstance()->get_employee_no_by_pkid($pkid);
                                            $result = HRISDB::getInstance()->get_dependents_by_employee_id($pkid);
                                            $Depcounter = 0;
                                            while($row = mysqli_fetch_array($result)):
                                                $Depcounter++;
                                                ?>
                                                <tr>
                                                <td axis="string" headers="ln"><?php echo $row['LastName'] ?></td>
                                                <td axis="string" headers="fn"><?php echo $row['FirstName'] ?></td>
                                                <td axis="string" headers="mn"><?php echo $row['MiddleName'] ?></td>
                                                <td axis="number" headers="dob"><?php 
                                                    if($pkid!="")
                                                    {
                                                        if ($EmpInfo['DOBirth']!='0000-00-00'){
                                                        $date = new DateTime($row['DoBirth']);
                                                        echo $date->format('m/d/Y');}
                                                    }
                                                    ?><?php //echo $row['DoBirth'] ?></td>

                                                <?php
                                                //get corresponding value for relationship
                                                switch ($row['Relationship']) {
                                                    case '1':
                                                        $relationship = 'Parent';
                                                        break;
                                                    case '2':
                                                        $relationship = 'Husband';
                                                        break;
                                                    case '3':
                                                        $relationship = 'Wife';
                                                        break;
                                                    case '4':
                                                        $relationship = 'Brother';
                                                        break;
                                                    case '5':
                                                        $relationship = 'Sister';
                                                        break;
                                                    case '6':
                                                        $relationship = 'Son';
                                                        break;
                                                    case '7':
                                                        $relationship = 'Daughter';
                                                        break;
                                                    case '8':
                                                        $relationship = 'Adopted Child';
                                                        break;
                                                    default:
                                                        $relationship = '';
                                                        break;
                                                }

                                                ?>
                                                <td axis="string" headers="rel"><?php echo $relationship ?></td>

                                                <?php
                                                //get corresponding value for tax dependent
                                                if($row['TaxDependent']==1)
                                                    $taxdep = yes;
                                                else
                                                    $taxdep = no;
                                                ?>

                                                <td axis="string" headers="dep" align="center"><?php echo $taxdep ?></td>
                                                <td id="age" style="display:none"><?php 
                                                    if($pkid!="")
                                                    {
                                                        if ($EmpInfo['DOBirth']!='0000-00-00'){
                                                        $date = new DateTime($row['DoBirth']);
                                                        echo $date->format('m/d/Y');}
                                                    }
                                                    ?><?php //echo GetAge($row['DoBirth']) ?></td>
                                                <td style="display:none" id="deppkid"><?php echo $row['pkid'] ?></td>
                                                <td style="display:none" id="relationshipNo"><?php echo $row['Relationship'] ?></td>
                                                <td style="display:none" id="fkemployee"><input type="text" name="fkEmpNo" value="<?php echo $row['fkEmpNo'] ?>"/></td>
                                                </tr>
                                                <?php
                                            endwhile;

                                            if($Depcounter==0)
                                            {
                                              ?><tfoot><td colspan="6" align="center">No Dependents</td></tfoot><?php
                                            }

                                            //mysql_free_result($result);
                                            ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- <form name="Dependent" method="POST"> -->
                                <tr>
                                    <td>
                                        <table border="<?php echo $border ?>">
                                            <tr>
                                                <td>Lastname:</td>
                                                <td>
                                                    <input type="text" maxlength="50" onkeypress="return isLetterKey(event)" name="DepLastName"/>
                                                    <font color="red"><text id='AlertLastName'></text></font>
                                                </td>
                                                <td>Date of Birth:</td>
                                                <td>
                                                    <input type="text" maxlength="10" style="width:250px" name="DepDateOfBirth" id="datepickerDepBday" onchange="getDepAge()"/>
                                                    <font color="red"><text id='AlertDOBirth'></text></font>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Firstname:</td>
                                                <td>
                                                    <input type="text" maxlength="50" onkeypress="return isLetterKey(event)" name="DepFirstName"/>
                                                    <font color="red"><text id='AlertFirstName'></text></font>
                                                </td>
                                                <td>Age:</td>
                                                <td>
                                                    <input disabled="disabled" type="text" name="DepAge" readonly="readonly" size="29"/>
                                                    <font color="red"><text id='AlertDepAge'></text></font>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Middlename:</td>
                                                <td>
                                                    <input type="text" maxlength="50" onkeypress="return isLetterKey(event)" name="DepMiddleName"/>
                                                    <font color="red"><text id='AlertMiddleName'></text></font>
                                                </td>
                                                <td>Relationship:</td>
                                                <td>
                                                    <select name="Relationship" style="width:250px">
                                                        <option value="1">Parent</option>
                                                        <option value="2">Husband</option>
                                                        <option value="3">Wife</option>
                                                        <option value="4">Brother</option>
                                                        <option value="5">Sister</option>
                                                        <option value="6">Son</option>
                                                        <option value="7">Daughter</option>
                                                        <option value="8">Adopted Child</option>
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Tax Dependent:</td>
                                                <td>
                                                    <select name="TaxDependent">
                                                        <option>no</option>
                                                        <option>yes</option>
                                                    </select>
                                                </td>
                                                <td colspan="2">
                                                    <font color="red"><text id='Alert'></text></font>
                                                    <input type="hidden" name="deppkid" value="">
                                                    <!-- <input type="hidden" name="formtype" value=""> -->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <!-- ok add and delete are not valid names -->
                                        <input type="button" id="button" name="oks" value="Update" onclick="Dependentok()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                        <input type="button" id="button" name="adds" value="Add" onclick="Dependentadd()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                        <input type="button" id="opener" name="deletes" value="Delete" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    </td>
                                </tr>
                            <!-- </form> -->
                        </table>
                    </div>
                    <!-- Educational Background -->
                    <div id="tabs-3">
                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Highest Educational Attainment:</td>
                                <td>
                                    <select name="HighestEducationalAttainment" style="width:420px">
                                        <option value="1" <?php if($EmpInfo['HighestEduc']==1)echo 'selected="true"' ?>>High School Graduate</option>
                                        <option value="2" <?php if($EmpInfo['HighestEduc']==2)echo 'selected="true"' ?>>Vocational Graduate</option>
                                        <option value="6" <?php if($EmpInfo['HighestEduc']==6)echo 'selected="true"' ?>>Vocational Level</option>
										<option value="3" <?php if($EmpInfo['HighestEduc']==3)echo 'selected="true"' ?>>College Level</option>
                                        <option value="4" <?php if($EmpInfo['HighestEduc']==4)echo 'selected="true"' ?>>Post Graduate</option>
                                        <option value="5" <?php if($EmpInfo['HighestEduc']==5)echo 'selected="true"' ?>>College Graduate</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>School/Address:</td>
                                <td>
                                    <textarea cols="50" maxlength="250" rows="2" name="SchoolAddress"><?php echo $EmpInfo['School']; ?></textarea>
                                    <!-- <input type="text" name="SchoolAddress" size="50" value=""/> -->
                                </td>
                            </tr>

                            <tr>
                                <td>Date Graduated:</td>
                                <td> <input type="text" name="DateGraduated" size="50" id="datepickerGrad" class="date" maxlength="10"
                                    value="<?php

                                                if ($EmpInfo['DOGraduated']!='0000-00-00'){
                                                $date = new DateTime($EmpInfo['DOGraduated']);
                                                echo $date->format('m/d/Y');}
                                            ?>"/></td>
                            </tr>

                            <tr>
                                <td>Degree Obtained:</td>
                                <td> <input type="text" maxlength="100" name="DegreeObtained" id="DegreeObtained" size="50" value="<?php echo $EmpInfo['Degree'] ?>"/></td>
                            </tr>

                            <tr>
                                <td>Honors/Awards:</td>
                                <td>
                                    <textarea cols="50" maxlength="250" rows="2" name="HonorsAwards"><?php echo $EmpInfo['Honors']; ?></textarea>
                                    <!-- <input type="text" name="HonorsAwards" value=""/> -->
                                </td>
                                    
                            </tr>

                            <tr>
                                <td>Government Examination Taken:</td>
                                <td>
                                    <textarea cols="50" maxlength="250" rows="2" name="GovernmentExams"><?php echo $EmpInfo['GovtExams']; ?></textarea>
                                    <!-- <input type="text" name="GovernmentExams" value=""/> -->
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- Employment Information -->
                    <div id="tabs-4">
                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Employee#:</td>
                                <td><input type="text" maxlength="8" onkeypress="return isLetterAndNumberKey(event)" name="EmployeeNo" style="width:300px" value="<?php echo $EmpInfo['EmpNo'] ?>"/></td>
                                <td>Salary Type:</td>
                                <td>
                                    <select name="EmploymentStatus" style="width:250px">
                                        <option value="1" <?php if($EmpInfo['HiringStatus']==1)echo 'selected="true"' ?>>Contractual</option>
                                        <option value="2" <?php if($EmpInfo['HiringStatus']==2)echo 'selected="true"' ?>>Probationary</option>
                                        <option value="3" <?php if($EmpInfo['HiringStatus']==3)echo 'selected="true"' ?>>Regular</option>
                                    </select>
                                    </select>
                                </td>
                            </tr>

                            <!-- container for updating options query -->
                            <div id="texter" style="display: none;"></div>
                            
                            <tr>
                                <td>Division:</td>
                                <td>
                                    <select name="Division" id="Division" onchange="updateDepartmentList(document.getElementById('Division').value);" size="1" style="width:300px">
                                        <?php
                                            //if creating a new employee, initialize to admin division
                                            if($EmpInfo['fkDivision']=='0' || $pkid == "")
                                            {
                                                $EmpInfo['fkDivision'] = 1;
                                                $EmpInfo['fkDepartment'] = 5;
                                            }
                                            //get list of divisions and select current value
                                            $result = HRISDB::getInstance()->get_divisions();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid'] ?>" <?php if($EmpInfo['fkDivision']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID = $row['division']) ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <script type="text/javascript">
                                        updateDepartmentList();
                                    </script>
                                </td>
                                <td>Employment Status:</td>
								<?php 
									if ($EmpInfo['EmpStatus'] == '1') {
										echo "<td>";
										echo "<select name='Status' id = 'toggler' onchange = 'toggleResignation();' style='width:250px'>";
										echo "<option value = '1' selected>Active</option>";
										echo "<option value = '2'>Resigned</option>";
										echo "<option value = '3'>Rehired</option>";
										echo "</select>";
										echo "</td>";
									} else if ($EmpInfo['EmpStatus'] == '2') {
										echo "<td>";
										echo "<select name='Status' id = 'toggler' onchange = 'toggleResignation();' style='width:250px'>";
										echo "<option value = '1'>Active</option>";
										echo "<option value = '2' selected>Resigned</option>";
										echo "<option value = '3'>Rehired</option>";
										echo "</select>";
										echo "</td>";
									} else if ($EmpInfo['EmpStatus'] == '3') {
										echo "<td>";
										echo "<select name='Status' id = 'toggler' onchange = 'toggleResignation();' style='width:250px'>";
										echo "<option value = '1'>Active</option>";
										echo "<option value = '2'>Resigned</option>";
										echo "<option value = '3' selected>Rehired</option>";
										echo "</select>";
										echo "</td>";
									} else {
										echo "<td>";
										echo "<select name='Status' id = 'toggler' onchange = 'toggleResignation();' style='width:250px'>";
										echo "<option value = '1' selected>Active</option>";
										echo "<option value = '2'>Resigned</option>";
										echo "<option value = '3'>Rehired</option>";
										echo "</select>";
										echo "</td>";
									}
								?>
                            </tr>

                            <tr>
                                <td>Department:</td>
                                <td>
                                    <select name="Department" id="Department" onchange="updateSectionList();" size="1" style="width:300px">
                                        <?php
                                            //get list of departments and select current value

                                            $result = HRISDB::getInstance()->get_departments_by_division($EmpInfo['fkDivision']);
                                            //$result = HRISDB::getInstance()->get_departments();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid']?>" <?php if($EmpInfo['fkDepartment']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID = $row['Department']) ?></option>
                                               <?php
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>Salary Type:</td>
                                <td>
                                    <select name="SalaryType" style="width:250px">
                                        <?php
                                            $count = HRISDB::getInstance()->get_salary_type_by_id('length');
                                            //echo $count;
                                            for ($i=1; $i <= $count; $i++) {
                                                ?>
                                                <option value="<?php echo $i ?>" <?php if($EmpInfo['SalaryType'] == $i)echo 'selected="true"' ?>><? echo HRISDB::getInstance()->get_salary_type_by_id($i);?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Section:</td>
                                <td>
                                    <select name="Section" id="Section" style="width:300px">
                                        <?php
                                            //get section
                                            $result = HRISDB::getInstance()->get_sections_by_department($EmpInfo['fkDepartment']);
                                            //$result = HRISDB::getInstance()->get_sections();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid']?>" <?php if($EmpInfo['fkSection']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID = $row['Section']) ?></option>
                                                <?php                       
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>SSS #:</td>
                                <td>
                                    <input type="text" maxlength="20" name="SSS" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['SSN'] ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td>Position:</td>
                                <td>
                                    <select name="Position" style="width:300px">
                                        <option></option>
                                        <?php
                                            //get positions and select the value
                                            $result = HRISDB::getInstance()->get_positions();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid']?>" <?php if($EmpInfo['fkPosition']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID = $row['Position']) ?></option>
                                                <?php  
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>TIN #:</td>
                                <td>
                                    <input type="text"  maxlength="20" name="TIN" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['TIN'] ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td>Date Hired:</td>
                                <td>
                                    <input type="text" name="DateHired" id="datepickerHired" class="date" maxlength="10"
                                    value="<?php 
                                            if ($EmpInfo['DateHired']!='0000-00-00'){
                                            $date = new DateTime($EmpInfo['DateHired']);
                                            echo $date->format('m/d/Y');}
                                            ?>" style="width:300px"/>
                                </td>
                                <td>PagIBIG #:</td>
                                <td>
                                    <input type="text" name="PagIbig" maxlength="25" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['PagIbig'] ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td>Blood Type:</td>
                                <td><input type="text" maxlength="3" name="BloodType" style="width:300px" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['BloodType'] ?>"/></td>
                                <td>PhilHealth #:</td>
                                <td>
                                    <input type="text" maxlength="20" name="PhilHealth" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['PhilHealth'] ?>"/>
                                </td>
                            </tr>
                        </table>
						<br />
						<?php 
							if ($EmpInfo['EmpStatus'] == '1') {
								echo "<table class = 'transbox' id = 'resignInfo' style = 'visibility: hidden;'>";
							} else if ($EmpInfo['EmpStatus'] == '2') {
								echo "<table class = 'transbox' id = 'resignInfo' style = 'visibility: visible;'>";
							} else if ($EmpInfo['EmpStatus'] == '3') {
								echo "<table class = 'transbox' id = 'resignInfo' style = 'visibility: hidden;'>";
							} 
						?>
							<tr>
								<th colspan = '2'>Resignation Information</th>
							</tr>
							<tr>
								<td>Effectivity Date:</td>
								<td><input type = 'date' name = 'ResEffectDate' value = '<?php echo $EmpInfo['Effectivity']; ?>'></td>
							</tr>
							<tr>
								<td>Reason:</td>
								<td><textarea cols = '98px' name = 'ResReason' rows = '5px'><?php echo $EmpInfo['Reason']; ?></textarea></td>
							</tr>
						</table>
                    </div>
                    <!-- Disciplinary History -->
                    <div id="tabs-5">
                        <!-- yes no delete dialog -->
                        <div id="dialogDeleteOffense" title="Delete Offense">
                            <p>Are you sure you want to delete this Offense?</p>
                            <input type="button" id="button" name="YesDeleteOffense" value="Yes" onclick="Offensedelete()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                            <input type="button" id="OffenseDialogcloser" name="no" value="No" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                        </div>

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>
                                    <!-- list of offense of Offense -->

                                    <table class="sorted" id="sortedOffense" border="1">
                                        <thead>
                                            <tr>
                                                <th id="action" title="click to sort by last name">Action</th>
                                                <th id="nature" title="click to sort by nature of offense">Nature</th>
                                                <th id="freq" title="click to sort by frequency of offense">Frequency of Offense</th>
                                                <th id="effect" title="click to sort by effectivity date">Effectivity</th>
                                                <th id="notif" title="click to sort by notification">Notification</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php

                                            //$EmpNo = HRISDB::getInstance()->get_employee_no_by_pkid($pkid);
                                            $result = HRISDB::getInstance()->get_disciplinary_by_employee_id($pkid);
                                            $Offcounter = 0;
                                            while($row = mysqli_fetch_array($result)):
                                                $Offcounter++;
                                                ?>
                                                <tr>
                                                <td axis="string" headers="action"><?php 

                                                switch ($row['Action']) {
                                                    case '1': echo 'Written Warning'; break;
                                                    case '2': echo '3 days suspension'; break;
                                                    case '3': echo '6 days suspension'; break;
                                                    case '4': echo '12 days suspension'; break;
                                                    case '5': echo '4 days suspension'; break;
                                                    case '6': echo '5 days suspension'; break;
                                                    case '7': echo '7 days suspension'; break;
                                                    case '8': echo '8 days suspension'; break;
                                                    case '9': echo '9 days suspension'; break;
                                                    case '10': echo '10 days suspension'; break;
                                                    case '11': echo '11 days suspension'; break;
                                                    case '12': echo '13 days suspension'; break;
                                                    case '13': echo '14 days suspension'; break;
                                                    case '14': echo '15 days suspension'; break;
                                                    case '15': echo '16 days suspension'; break;
                                                    case '16': echo '17 days suspension'; break;
                                                    case '17': echo '18 days suspension'; break;
                                                    case '18': echo '19 days suspension'; break;
                                                    case '19': echo '20 days suspension'; break;
                                                    case '20': echo '21 days suspension'; break;
                                                    case '21': echo '22 days suspension'; break;
                                                    case '22': echo '23 days suspension'; break;
                                                    case '23': echo '24 days suspension'; break;
                                                    case '24': echo '25 days suspension'; break;
                                                    case '25': echo '26 days suspension'; break;
                                                    case '26': echo '27 days suspension'; break;
                                                    case '27': echo '28 days suspension'; break;
                                                    case '28': echo '29 days suspension'; break;
                                                    case '29': echo '30 days suspension'; break;
                                                    case '30': echo 'dismissal'; break;
                                                    default: echo ''; break;
                                                }
                                                ?></td>
                                                <td axis="string" headers="nature"><?php echo $row['Nature'] ?></td>
                                                <td axis="string" headers="freq"><center><?php 

                                                switch ($row['Frequency'] ) {
                                                    case '1': echo '1st'; break;
                                                    case '2': echo '2nd'; break;
                                                    case '3': echo '3rd'; break;
                                                    case '4': echo '4th'; break;
                                                    case '5': echo '5th'; break;
                                                    case '6': echo '6th'; break;
                                                    default: break;
                                                }
                                                ?></center></td>
                                                <td axis="string" headers="effect" title="<?php $date = new DateTime($row['Effectivity']); echo $date->format('F j, Y'); ?>"><?php 
                                                    if($pkid!="")
                                                    {
                                                        if ($row['Effectivity']!='0000-00-00'){
                                                        $date = new DateTime($row['Effectivity']);
                                                        echo $date->format('m/d/Y');}
                                                    }
                                                    ?><?php //echo $row['Effectivity'] ?></td>
                                                <td axis="string" headers="notif" title="<?php $date = new DateTime($row['Notification']); echo $date->format('F j, Y'); ?>"><?php 
                                                    if($pkid!="")
                                                    {
                                                        if ($row['Notification']!='0000-00-00'){
                                                        $date = new DateTime($row['Notification']);
                                                        echo $date->format('m/d/Y');}
                                                    }
                                                    ?><?php //echo $row['Notification'] ?></td>
                                                <td style="display:none" id="pkid"><?php echo $row['pkid'] ?></td>
                                                <td style="display:none" id="actionNo"><?php echo $row['Action'] ?></td>
                                                <td style="display:none" id="frequenqyNo"><?php echo $row['Frequency'] ?></td>
                                                <td style="display:none" id="fkemployee"><input type="text" name="fkEmpNo" value="<?php echo $row['fkEmpNo'] ?>"/></td>
                                                
                                                </tr>
                                                <?php
                                            endwhile;

                                            if($Offcounter==0)
                                            {
                                              ?><tfoot><td colspan="5" align="center">No Offense</td></tfoot><?php
                                            }

                                            //mysql_free_result($result);
                                            ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <table border="2">
                                        <tr>
                                            <td>Action:</td>
                                            <td>
                                                <select name="Action">
                                                    <option value = "1">Written Warning</option>
                                                    <option value = "2">3 days suspension</option>
                                                    <option value = "3">6 days suspension</option>
                                                    <option value = "4">12 days suspension</option>
                                                    <option value = "5">4 days suspension</option>
                                                    <option value = "6">5 days suspension</option>
                                                    <option value = "7">7 days suspension</option>
                                                    <option value = "8">8 days suspension</option>
                                                    <option value = "9">9 days suspension</option>
                                                    <option value = "10">10 days suspension</option>
                                                    <option value = "11">11 days suspension</option>
                                                    <option value = "12">13 days suspension</option>
                                                    <option value = "13">14 days suspension</option>
                                                    <option value = "14">15 days suspension</option>
                                                    <option value = "15">16 days suspension</option>
                                                    <option value = "16">17 days suspension</option>
                                                    <option value = "17">18 days suspension</option>
                                                    <option value = "18">19 days suspension</option>
                                                    <option value = "19">20 days suspension</option>
                                                    <option value = "20">21 days suspension</option>
                                                    <option value = "21">22 days suspension</option>
                                                    <option value = "22">23 days suspension</option>
                                                    <option value = "23">24 days suspension</option>
                                                    <option value = "24">25 days suspension</option>
                                                    <option value = "25">26 days suspension</option>
                                                    <option value = "26">27 days suspension</option>
                                                    <option value = "27">28 days suspension</option>
                                                    <option value = "28">29 days suspension</option>
                                                    <option value = "29">30 days suspension</option>
                                                    <option value = "30">dismissal</option>
                                                </select>
                                            </td>
                                            <td>Notification Date:</td>
                                            <td>
                                                <input type="text" name="NotificationDate" id="datepickerNotif" class="date" maxlength="10"/>
                                                <font color="red"><text id='AlertNotificationDate'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Nature of Offense:</td>
                                            <td>
                                                <input type="text"  maxlength="50" name="NatureOfOffense" id="NatureOfOffense"/>
                                                <font color="red"><text id='AlertNatureOfOffense'></text></font>
                                            </td>
                                            <td>Effectivity Date:</td>
                                            <td>
                                                <input type="text" name="EffectivityDate" id="datepickerEffect" class="date" maxlength="10"/>
                                                <font color="red"><text id='AlertEffectivityDate'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Frequency of Offense:</td>
                                            <td>
                                                <select name="FrequencyOfOffense">
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="3">3rd</option>
                                                    <option value="4">4th</option>
                                                    <option value="5">5th</option>
                                                    <option value="6">6th</option>
                                                </select>
                                            </td>
                                            <td colspan="2">
                                                <font color="red"><text id='AlertOffense'></text></font><br>
                                                <input type="hidden" name="Offensepkid" value="">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="button" id="button" name="Offenseoks" value="Update" onclick="Offenseok()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    <input type="button" id="button" name="Offenseadds" value="Add" onclick="Offenseadd()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    <input type="button" id="OffenseDialogopener" name="Offensedeletes" value="Delete" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- training records tab -->
                    <div id="tabs-6">
                        <table class="sorted" id="sortedTrainings" border="1">
                            <thead>
                                <tr>
                                    <th id="period" class="sortedplus" title="click to sort by period">Period</th>
                                    <th id="title" title="click to sort by nature of title">Title</th>
                                    <th id="objective" title="click to sort by objective">Objective</th>
                                    <th id="venue" title="click to sort by venue">Venue</th>
                                    <th id="trainor" title="click to sort by trainor">Trainor</th>
                                    <th id="mechanics" title="click to sort by mechanics">Mechanics</th>
                                    <th id="result" title="click to sort by result">Result</th>
                                    <th id="remarks" title="click to sort by remarks">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                //$EmpNo = HRISDB::getInstance()->get_employee_no_by_pkid($pkid);
                                $result = HRISDB::getInstance()->get_trainings_by_employee_id($pkid, $username,1);
                                $TrainingCounter = 0;
                                while($row = mysqli_fetch_array($result)):
                                    $TrainingCounter++;
                                    ?>
                                    <tr>
                                        <td axis="string" headers="period" title="<?php

                                        $date = new DateTime($row['PeriodFrom']);
                                        echo $date->format('F j, Y');

                                        ?>"><?php
                                        
                                        // $date = new DateTime($row['Period']);
                                        // echo $date->format('Y/m/d');
										echo date('Y/m/d', strtotime($row['PeriodFrom'])).'-'.date('Y/m/d', strtotime($row['PeriodTo']));
                                         ?></td>
                                        <td axis="string" headers="title" ><?php echo $row['Title'] ?></td>
                                        <td axis="string" headers="objective" ><?php echo $row['Objective'] ?></td>
                                        <td axis="string" headers="venue" ><?php echo $row['Venue'] ?></td>
                                        <td axis="string" headers="trainor" ><?php echo $row['Trainor'] ?></td>
                                        <td axis="string" headers="mechanics" ><?php echo $row['Mechanics'] ?></td>
                                        <td axis="string" headers="result" ><?php echo $row['Result'] ?></td>
                                        <td axis="string" headers="remarks" ><?php echo $row['Remarks'] ?></td>
                                    </tr>
                                    <?php
                                endwhile;

                                if($TrainingCounter==0)
                                {
                                  ?><tfoot><td colspan="8" align="center">No Training Records</td></tfoot><?php
                                }

                                //mysql_free_result($result);
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Employment History -->
                    <div id="tabs-7">

                        <table class="sorted" id="sortedHistory">
                            <thead>
                                <tr>
                                    <th id="div">Division</th>
                                    <th id="dep">Department</th>
                                    <th id="sec">Section</th>
                                    <th id="pos">Position</th>
                                    <th id="from">From Date</th>
                                    <th id="to">To Date</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                //die("SELECT * FROM tbl_EmploymentHistory WHERE fkEmployee = ".$EmpInfo['pkid']);
                                $result = HRISDB::getInstance()->execute_search_query("SELECT * FROM tbl_EmploymentHistory WHERE fkEmployee = ".$EmpInfo['pkid']." ORDER BY pkid DESC ");//DateStart DESC

                                $counter = 0;
                                while($row = mysqli_fetch_array($result))
                                {
                                    $counter++;
                                    echo '<tr>';
                                    echo '<td headers="div">'.HRISDB::getinstance()->get_division_name_by_id($row['fkDivision']).'</td>';
                                    echo '<td headers="dep">'.HRISDB::getinstance()->get_department_name_by_id($row['fkDepartment']).'</td>';
                                    echo '<td headers="sec">'.HRISDB::getinstance()->get_section_name_by_id($row['fkSection']).'</td>';
                                    echo '<td headers="pos">'.HRISDB::getinstance()->get_position_name_by_id($row['fkPosition']).'</td>';
                                    echo '<td headers="from">'.convertDateFromatViewing($row['DateStart']).'</td>';
                                    echo '<td headers="to">'.$row['DateEnd'].'</td>';
                                    echo '<td style="display:none" id="fkPos">'.$row['fkPosition'].'</td>';
                                    echo '<td style="display:none" id="fkDiv">'.$row['fkDivision'].'</td>';
                                    echo '<td style="display:none" id="fkDep">'.$row['fkDepartment'].'</td>';
                                    echo '<td style="display:none" id="fkSec">'.$row['fkSection'].'</td>';
                                    echo '<td style="display:none" id="pkid">'.$row['pkid'].'</td>';
                                    echo '</tr>';
                                }
                                if($counter==0)
                                {
                                    echo '<tr>';
                                    echo '<td>'.HRISDB::getinstance()->get_division_name_by_id($EmpInfo['fkDivision']).'</td>';
                                    echo '<td>'.HRISDB::getinstance()->get_department_name_by_id($EmpInfo['fkDepartment']).'</td>';
                                    echo '<td>'.HRISDB::getinstance()->get_section_name_by_id($EmpInfo['fkSection']).'</td>';
                                    echo '<td>'.HRISDB::getinstance()->get_position_name_by_id($EmpInfo['fkPosition']).'</td>';
                                    echo '<td>'.convertDateFromatViewing($EmpInfo['DateHired']).'</td>';
                                    echo '<td>Present</td>';
                                    echo '<td style="display:none" id="fkPos">'.$EmpInfo['fkPosition'].'</td>';
                                    echo '<td style="display:none" id="fkDiv">'.$EmpInfo['fkDivision'].'</td>';
                                    echo '<td style="display:none" id="fkDep">'.$EmpInfo['fkDepartment'].'</td>';
                                    echo '<td style="display:none" id="fkSec">'.$EmpInfo['fkSection'].'</td>';
                                    echo '<td style="display:none" id="pkid"></td>';
                                    echo '</tr>';
                                }

                                ?>
                            </tbody>
                        </table>
                        <br>
                        <table border="2">
                            <tr>
                                <td>
                                Division:
                                </td>
                                <td>
                                    <select name="historyDivision" id="historyDivision" size="1" onchange="updateDepartmentListHistory();" style="width:300px">
                                        <option></option>
                                        <?php
                                            //get list of divisions and select current value
                                            $result = HRISDB::getInstance()->get_divisions();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid'] ?>"><?php echo htmlentities($systemID = $row['division']) ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <font color="red"><text id='AlertHisDiv'></text></font>
                                <!--<input type="text" name="historyDivision"><br>-->
                                </td>
                            </tr>
                            <tr>
                                <td>
                                Department:
                                </td>
                                <td>
                                    <select name="historyDepartment" id="historyDepartment" onchange="updateSectionListHistory();" style="width:300px">
                                        <option></option>
                                    </select>
                                    <font color="red"><text id='AlertHisDep'></text></font>
                                <!--<input type="text" name="historyDepartment"><br>-->
                                </td>
                            </tr>
                            <tr>
                                <td>
                                Section:
                                </td>
                                <td>
                                    <select name="historySection" id="historySection" style="width:300px">
                                        <option></option>
                                    </select>
                                    <font color="red"><text id='AlertHisSec'></text></font>
                                <!--<input type="text" name="historySection"><br>-->
                                </td>
                            </tr>
                            <tr>
                                <td>
                                Position:
                                </td>
                                <td>
                                    <select name="historyPosition" id="historyPosition" style="width:300px">
                                        <option></option>
                                        <?php
                                            //get positions and select the value
                                            $result = HRISDB::getInstance()->get_positions();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo $row['pkid']?>" ><?php echo htmlentities($systemID = $row['Position']) ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <font color="red"><text id='AlertHisPos'></text></font>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                From Date:
                                </td>
                                <td>
                                <input type="text" name="historyFrom" id="historyFrom"  maxlength="10">
                                <font color="red"><text id='AlertHisFrom'></text></font>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                To Date:
                                </td>
									<td>
									<input type="text" name="historyTo" id="historyTo" class="historyTo" maxlength="10">
									<font color="red"><text id='AlertHisTo'></text></font>
									<input type="checkbox" id="Present" name="Present" value="Yes" title="use present"/> Present 
									</td>
                            </tr>
                            <tr>
                                <input type="hidden" name="historypkid" id="historypkid">
                                <td colspan="2">
                                <font color="red"><text id='alertHistory'></text></font>
                                </td>
                            </tr>
                        </table>
                        
                        <input type="button" name="addHistory" value="Add" onclick="if(validateHistoryForm()){document.EmployeeInfo['formtype'].value = 'addHistory';document.EmployeeInfo.submit();}" class="ui-button ui-widget ui-state-default ui-corner-all">
                        <input type="button" name="editHistory" style="display: none; " value="Edit" onclick="if(validateHistoryForm()){document.EmployeeInfo['formtype'].value = 'editHistory';document.EmployeeInfo.submit();}" class="ui-button ui-widget ui-state-default ui-corner-all">
                        <input type="button" name="deleteHistory" style="display: none; " value="Delete" onclick="document.EmployeeInfo['formtype'].value = 'deleteHistory';document.EmployeeInfo.submit();" class="ui-button ui-widget ui-state-default ui-corner-all">
                        <br>
                    </div>
                    
					<!-- Appraisal History tab -->
                    <div id="tabs-8">
                        <table class="sorted" id="sortedTrainings" border="1" style="width:100%">
                            <thead>
                                <tr>
                                    <th id="rating" class="sortedplus" title="click to sort by rating" style="width:20%">Rating</th>
                                    <th id="position" title="click to sort by position" style="width:15%">Position</th>
                                    <th id="from" title="click to sort by evaluation date from" style="width:13%">From</th>
                                    <th id="to" title="click to sort by evaluation date to" style="width:13%">To</th>
                                    <th id="effectivity_date" title="click to sort by effectivity date" style="width:13%">Effectivity Date</th>
                                    <th id="purpose" title="click to sort by purpose" style="width:15%">Purpose</th>
                                    <th id="rated_by" title="click to sort by rater" style="width:25%">Rated by</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result_appraisal = PAS::getInstance()->get_appraisal_record($EmpInfo['EmpNo']);
                                while($row = mysqli_fetch_array($result_appraisal)):
                                    ?>
                                    <tr>                                        
                                        <td axis="string" headers="rating" style="width:20%"><center><?php echo $row['rating']; ?></center></td>
                                        <td axis="string" headers="position" style="width:15%"><center><?php echo $row['position']; ?></center></td>
                                        <td axis="string" headers="from" style="width:13%"><center><?php echo date('M d, Y',strtotime($row['evaluation_from'])); ?></center></td>
                                        <td axis="string" headers="to" style="width:13%"><center><?php echo date('M d, Y',strtotime($row['evaluation_to'])); ?></center></td>
                                        <td axis="string" headers="effectivity_date" style="width:13%"><center><?php echo date('M d, Y',strtotime($row['effectivity_date'])); ?></center></td>
                                        <td axis="string" headers="purpose" style="width:15%"><center><?php echo $row['purpose']; ?></center></td>
                                        <td axis="string" headers="rated_by" style="width:20%"><center><?php echo $row['rated_by']; ?></center></td>
                                    </tr>
                                    <?php
                                endwhile;

                                if($result_appraisal->num_rows == 0)
                                {
                                  ?><tfoot><td colspan="7" align="center"><i>No Appraisal Record Found</i></td></tfoot><?php
                                }

                                //mysql_free_result($result);
                                ?>
                            </tbody>
                        </table>
                    </div>
				<!-- Employee 201 File -->
                    <div id="tabs-9">
				<!-- Search 201 Category -->
						 <table class="transbox" border="<?php echo $border ?>" style="width: 50%;">
						   <tr>
								<td rowspan="2">Search For 201 File Category: </td>
								<td rowspan="2">
									<input type="hidden" id="fkEmployee" value="<?php echo $pkid?>"> 
									<select name="search_fileCat" id="search_fileCat" size="1" onchange="showCat(this.value)" style="width:280px">
										<option></option>
										<?php
											//get list of divisions and select current value
											$result = HRISDB::getInstance()->get_201FileCategory();
											while($row = mysqli_fetch_array($result))
											{
												?>
												<option value="<?php echo $row['pkid'] ?>"><?php echo htmlentities($systemID = $row['empfilecategory']) ?></option>
												<?php
											}
										?>
									</select>
								</td>
							</tr>
							</table>                                
						 <br>	
				<!-- 201 Table -->	
						<button type="button" id="upload_file">Upload File</button>
						<br>
						<br>
						<table class="sorted" id="sortedScanned" style="width: 50%;">
                            <thead>
                                <tr align="center">
				                    <th id="fileCat">201 File Category</th>
                                    <th id="file_Name">File Name</th>
                                    <th id="fileView">View</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
									$result = HRISDB::getInstance()->get_uploaded_file($EmpInfo);
									$FileCatCounter = 0;
									while($row = mysqli_fetch_array($result)):
									$FileCatCounter++;
									{
										echo '<tr align="center">';
										echo '<td style="display:none" id="pkid">'.$row['pkid'].'</td>';
										echo '<td headers="fileCat">'.$row['empfilecategory'].'</td>';
										echo '<td headers="file_Name">'.$row['file_name'].'</td>';
										echo '<td><a href="pdfuploads/'.$row['pkid'].'" target="_blank">View File</a></td>';
										echo '</tr>';
									}
                                ?>
                                <?php
									endwhile;

									if($FileCatCounter==0)
									{
									  ?><tfoot><td colspan="3" align="center">No 201 File Records</td></tfoot><?php
									}
                                ?>
                            </tbody>
							</table>

                    </div>	
                    
			   </div>
            </div>
                <input type="hidden" name="formtype" value="">
                <input type="hidden" name="tabIndex" value="">
                <input type="hidden" name="pkid" value="<?php echo $pkid ?>">
                <font color="red"><text id='Alerts'></text></font><br>

                &nbsp; <input type="button" id="button" value="Save" onClick="validateForm();" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                <input type="button" id="button" value="Employee List" onClick="window.location = 'index.php'" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                
            </form>
        </div>
	
<div class="wrap" id="uploadfile_modal" title="Upload Employee 201 File">
	<form id="scannedFile" method="post" enctype="multipart/form-data">
	<!--	<input type="text" id="fkUploadFile" value=""> 
		<input type="hidden" id="fkUploadFile" value="<?php //echo $pkid?>">-->
		<input type="hidden" id="fkEmployee" value="<?php echo $pkid?>">
		
		<br>		
		<label for="fileCat">Select Employee 201 Category: </label>
			<select name="filecat" id="filecat" size="1" style="width:300px" size="1" class="text ui-widget-content ui-corner-all" required>
				<option></option>
				<?php
					$result = HRISDB::getInstance()->get_201FileCategory();
					while($row = mysqli_fetch_array($result))
					{
					?>
						<option value="<?php echo $row['pkid'] ?>"><?php echo htmlentities($systemID = $row['empfilecategory']) ?></option>
					<?php
					}
				?>
			</select>
			<br>
			<br>
			<br>
	<input type="file" name="files[]" multiple="multiple">
	<br>
	<br>
	<br>
	<input type="submit" value="Upload">
	</form>
</div>

<script>
	$("#uploadfile_modal").dialog({
	   autoOpen: false,
	   show: "blind",
	   hide: "fade",
   	   width:"600px",
	   position: ["bottom",150],
	   dialogClass: 'no-close'
	 });
	  
	$(function() {
		$( "#upload_file" )
		.button()
		.click(function() {
			$( "#uploadfile_modal" ).dialog( "open" );
		});
	});
</script>

</body>
</html>
