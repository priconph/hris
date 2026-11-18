<?php

// session first, before any include or output
session_start();

//for layout testing
$border = 0;

require_once __DIR__ . '/../global_includes/hris-oop.php';
include_once 'db_hris.php';

ob_start(); 

// same guard and message as index.php
if(!isset($_SESSION['username'])){
    echo "Please login to systemone!";
    exit;
}
$username = $_SESSION['username'];

// escape a value for HTML text and attributes (null-safe)
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// format a date for display; '' for empty, null, 0000-00-00 or invalid values
function formatDateForView($value, $fmt = 'm/d/Y')
{
    $value = trim((string)$value);
    if ($value === '' || strncmp($value, '0000-00-00', 10) === 0)
    {
        return '';
    }
    try
    {
        $date = new DateTime($value);
    }
    catch (Exception $e)
    {
        return '';
    }
    return $date->format($fmt);
}

//validations done in javascript
function GetAge($bday)
{
    $bday = formatDateForView($bday, 'Y-m-d');
    if ($bday === '')
    {
        return '';
    }
    $date = new DateTime($bday);
    $diff = $date->diff(new DateTime());

    $age = ($diff->y!=0) ? $diff->y.' years ':'';
    $age .= ($diff->m!=0) ? $diff->m.' months ':'';
    $age .= ($diff->d!=0) ? $diff->d.' days ':'';

    return $age;
}

function convertDateFromat($date)
{
    //change format of date
    return formatDateForView($date, 'Y-m-d');
}

// date for the database: null (not '') when empty or invalid
function dateOrNull($date)
{
    $converted = convertDateFromat($date);
    return ($converted === '') ? null : $converted;
}

function convertDateFromatViewing($date)
{
    return formatDateForView($date, 'm/d/Y');
}

// every key the template reads from $EmpInfo, '' by default (new or missing employee)
function employeeDefaults()
{
    $keys = array('pkid', 'Age', 'EmpNo', 'LastName', 'FirstName', 'MiddleName', 'NickName', 'Picturepath',
        'Gender', 'DOBirth', 'CivilStatus', 'Spouse', 'ExemptBaseCode',
        'CityAddress1', 'CityAddress2', 'CityAddress3', 'ProvAddress1', 'ProvAddress2', 'ProvAddress3',
        'TelephoneNo', 'Incoming', 'Outgoing', 'Remarks', 'FatherName', 'MotherName', 'Emergency',
        'HighestEduc', 'School', 'DOGraduated', 'Degree', 'Honors', 'GovtExams',
        'HiringStatus', 'EmpStatus', 'fkDivision', 'fkDepartment', 'fkSection', 'fkPosition', 'SalaryType',
        'SSN', 'TIN', 'PagIbig', 'PhilHealth', 'BloodType', 'DateHired', 'Effectivity', 'Reason');
    return array_fill_keys($keys, '');
}

// load the employee row by pkid over the defaults; defaults only when pkid is empty or not found
function loadEmployeeDetails($pkid)
{
    $info = employeeDefaults();
    $pkid = (int)$pkid;
    if ($pkid > 0)
    {
        $result = HRISDB::getInstance()->get_employee_info_by_id($pkid);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        if (is_array($row))
        {
            foreach ($row as $key => $value)
            {
                $info[$key] = ($value === null) ? '' : $value;
            }
        }
    }
    $info['Age'] = GetAge($info['DOBirth']);
    return $info;
}


//initialize empkid
$pkid = "";
$message = '';
$empNotUnique = false;

    // posted form fields with '' as default; never written back into $_POST
    $post = array_merge(array_fill_keys(array('pkid', 'formtype', 'DateOfBirth', 'DateGraduated', 'DateHired', 'DepDateOfBirth',
        'EffectivityDate', 'NotificationDate', 'EmployeeNo', 'TaxDependent',
        'LastName', 'FirstName', 'MiddleName', 'NickName', 'Gender', 'CivilStatus', 'Spouse', 'TaxExemptionBase',
        'CityAddStreet', 'CityAddBarangay', 'CityAdd', 'ProvAddressBarangay', 'ProvAddressMunicipality',
        'ProvAddress', 'Telephone', 'IncomingShuttle', 'OutgoingShuttle', 'Remarks',
        'HighestEducationalAttainment', 'SchoolAddress', 'DegreeObtained', 'HonorsAwards', 'GovernmentExams',
        'Division', 'Department', 'Section', 'Position', 'EmploymentStatus', 'Status', 'SalaryType',
        'SSS', 'TIN', 'PagIbig', 'PhilHealth', 'BloodType', 'ResEffectDate', 'ResReason',
        'FatherName', 'MotherName', 'PersonToBeNotified',
        'DepLastName', 'DepFirstName', 'DepMiddleName', 'Relationship', 'deppkid',
        'Action', 'NatureOfOffense', 'DefinitionOfOffense', 'FrequencyOfOffense', 'Offensepkid',
        'historyDivision', 'historyDepartment', 'historySection', 'historyPosition',
        'historyFrom', 'historyTo', 'historypkid', 'tabIndex', 'Present'), ''), $_POST);
   
    // only these form types are handled
    $formtype = in_array($post['formtype'], array('SaveBasicInfo', 'Dependentok', 'Dependentdelete', 'Dependentadd',
        'Offensesave', 'Offensedelete', 'Offenseadd', 'addHistory', 'editHistory', 'deleteHistory'), true) ? $post['formtype'] : '';

    // pkid is resolved once here: POST (employees form in search.php), GET as fallback
    $pkidInput = ($post['pkid'] !== '') ? $post['pkid'] : (isset($_GET['pkid']) ? $_GET['pkid'] : '');
    $pkid = (is_scalar($pkidInput) && (int)$pkidInput > 0) ? (int)$pkidInput : "";

    $EmpInfo = employeeDefaults();
    try
    {
        //get gor testing
        if ($_SERVER["REQUEST_METHOD"] == "POST")// || $_SERVER["REQUEST_METHOD"] == "GET")
        {
            //validate date fields (null when empty, so the database gets NULL)
            $DOB = dateOrNull($post["DateOfBirth"]);
            $DOGrad = dateOrNull($post["DateGraduated"]);
            $DOHired = dateOrNull($post["DateHired"]);
            $DepDOB = dateOrNull($post["DepDateOfBirth"]);
            $EffectivityDate = dateOrNull($post["EffectivityDate"]);
            $NotificationDate = dateOrNull($post["NotificationDate"]);

            //put leading zeros in employee number
            $EmployeeNo = (string)$post["EmployeeNo"];
            if(($EmployeeNo != "") && (strlen($EmployeeNo) < 4))
            {
                $EmployeeNo = str_pad($EmployeeNo, 4, '0', STR_PAD_LEFT);
            }

            //for dependents form
            //get value of tax dependent
            if($post["TaxDependent"]=='yes') $taxdep = 1; else $taxdep = 0;

            if($pkid!="")
            {
                if($EmployeeNo!="")
                {
                    //check if the employee number is already existing
                    $empNotUnique = HRISDB::getInstance()->verify_employee_number($EmployeeNo,$pkid);
                    if($empNotUnique)
                    {
                        $message = 'unable to save, employee number '.$EmployeeNo.' is already existing';
                    }
                }
                else
                    $empNotUnique = false;

                //update employee
                if ($formtype=="SaveBasicInfo" && !$empNotUnique)
                {
                    //update basic info
                    HRISDB::getInstance()->update_employee($pkid,
                        $post["LastName"],
                        $post["FirstName"],
                        $post["MiddleName"],
                        $post["NickName"],
                        $DOB,
                        $post["Gender"],
                        $post["CivilStatus"],
                        $post["Spouse"],
                        $post['TaxExemptionBase'],
                        $post["CityAddStreet"],
                        $post["CityAddBarangay"],
                        $post["CityAdd"],
                        $post["ProvAddressBarangay"],
                        $post["ProvAddressMunicipality"],
                        $post["ProvAddress"],
                        $post["Telephone"],
                        $post["IncomingShuttle"],
                        $post["OutgoingShuttle"],
                        $post["Remarks"]);

                    //update educational background
                    HRISDB::getInstance()->update_employee_educ($pkid,
                        $post["HighestEducationalAttainment"],
                        $post["SchoolAddress"],
                        $DOGrad,
                        $post["DegreeObtained"],
                        $post["HonorsAwards"],
                        $post["GovernmentExams"]);

                    //check for employment history changes
                    if(HRISDB::getInstance()->change_employement_history($pkid,
                        $post["Division"],
                        $post["Department"],
                        $post["Section"],
                        $post["Position"]
                        ))
                    {
                    //insert into employment history table if there is change detected
                    HRISDB::getInstance()->add_employee_history($pkid,$post["Division"],$post["Department"],$post["Section"],$post["Position"],date("Y-m-d"),'Present');
                    }

                    //update employee employment status
                    HRISDB::getInstance()->update_employee_employment_info($pkid,
                        $EmployeeNo,
                        $post["EmploymentStatus"], //employment category
                        $post["Division"],
                        $post["Status"],  //active resigned
                        $post["Department"],
                        $post["SalaryType"],
                        $post["Section"],
                        $post["SSS"],
                        $post["Position"],
                        $post["TIN"],
                        $post["PagIbig"],
                        $DOHired,
                        $post["PhilHealth"],
                        $post["BloodType"],
                        $post["ResEffectDate"],
                        $post["ResReason"]);

                    //update family info
                    HRISDB::getInstance()->update_employee_family($pkid,$post["FatherName"],
                        $post["MotherName"],
                        $post["PersonToBeNotified"]);

                    $message = 'Employee Information Saved';
                }

                //dependent update
                if ($formtype=="Dependentok")
                {
                    HRISDB::getInstance()->update_dependent($post["DepLastName"],
                        $post["DepFirstName"],
                        $post["DepMiddleName"],
                        $DepDOB,
                        $post["Relationship"],
                        $taxdep,
                        $post["deppkid"]);

                    //update family info
                    HRISDB::getInstance()->update_employee_family($pkid,$post["FatherName"],
                        $post["MotherName"],
                        $post["PersonToBeNotified"]);

                    $message = 'Dependent information updated';
                }

                //dependent delete
                else if ($formtype=="Dependentdelete")
                {
                    HRISDB::getInstance()->delete_dependent($post["deppkid"]);

                    $message = 'Dependent deleted';
                }

                //create new dependent
                else if ($formtype=="Dependentadd")
                {                             
                    HRISDB::getInstance()->create_dependent($post["DepLastName"],
                        $post["DepFirstName"],
                        $post["DepMiddleName"],
                        $pkid,
                        $DepDOB,
                        $post["Relationship"],
                        '0',
                        $taxdep);

                    //update family info
                    HRISDB::getInstance()->update_employee_family($pkid,$post["FatherName"],
                        $post["MotherName"],
                        $post["PersonToBeNotified"]);

                    $message = 'New dependent added';
                }

                //update disciplinary info
                if ($formtype=="Offensesave")
                {
                    HRISDB::getInstance()->update_disciplinary($post["Action"],
                        $post["NatureOfOffense"],
                        $post["DefinitionOfOffense"],
                        $post["FrequencyOfOffense"],
                        $NotificationDate,
                        $EffectivityDate,
                        $post["Offensepkid"]);

                    $message = 'Offense saved';
                }

                //delete disciplinary
                else if ($formtype=="Offensedelete")
                {
                    HRISDB::getInstance()->delete_disciplinary($post["Offensepkid"]);

                    $message = 'Offense deleted';
                }

                //create disciplinary info
                else if ($formtype=="Offenseadd")
                {
                    HRISDB::getInstance()->create_disciplinary($post["Action"],
                        $post["NatureOfOffense"],
                        $post["DefinitionOfOffense"],                    
                        $post["FrequencyOfOffense"],
                        $NotificationDate,
                        $EffectivityDate,
                        $pkid);

                    $message = 'Offense added';
                }
                
        
                //create employment history info
                else if ($formtype=="addHistory")
                {
                    $historyTo = ($post['Present'] == 'Yes') ? 'Present' : $post["historyTo"];

                    HRISDB::getInstance()->add_employee_history($pkid,$post["historyDivision"],$post["historyDepartment"],$post["historySection"],$post["historyPosition"],$post["historyFrom"],$historyTo);
                    $message = 'Employment History Added';
                }

                //create employment history info
                else if ($formtype=="editHistory")
                {
                    $historyTo = ($post['Present'] == 'Yes') ? 'Present' : $post["historyTo"];
                    HRISDB::getInstance()->update_employee_history($post["historypkid"],$post["historyDivision"],$post["historyDepartment"],$post["historySection"],$post["historyPosition"],$post["historyFrom"],$historyTo);
                    $message = 'Employment History edited';
                }

                //create employment history info
                else if ($formtype=="deleteHistory")
                {
                    HRISDB::getInstance()->delete_employee_history($post["historypkid"]);
                    $message = 'Deleted Employment History';
                }
            }
            else //else if no pkid is found create the new employee
            {
                if ($formtype=="SaveBasicInfo")
                {
                    //create new employee
                    HRISDB::getInstance()->create_employee(
                        $post["LastName"],
                        $post["FirstName"],
                        $post["MiddleName"],
                        $post["NickName"],
                        $DOB,
                        $post["Gender"],
                        $post["CivilStatus"],
                        $post["Spouse"],
                        $post['TaxExemptionBase'],
                        $post["CityAddStreet"],
                        $post["CityAddBarangay"],
                        $post["CityAdd"],
                        $post["ProvAddressBarangay"],
                        $post["ProvAddressMunicipality"],
                        $post["ProvAddress"],
                        $post["Telephone"],
                        $post["IncomingShuttle"],
                        $post["OutgoingShuttle"],
                        $post["Remarks"]);

                    //get pkid of the newly inserted account
                    $pkid = HRISDB::getInstance()->get_last_inserted_employee_id();

                    $message = 'Basic employee information saved';
                }
            }
        }
        else if ($pkid == "") //do nothing and leave all fields blank for creation of new employee
        {
            $message = 'Create new employee';
        }

        //load the employee by pkid after any write, so the form shows fresh data
        if($pkid != "")
        {
            $EmpInfo = loadEmployeeDetails($pkid);
        }
    }
    catch (Throwable $ex)
    {
        //never show SQL or exception text in the browser
        error_log('employeeinfo.php: ' . get_class($ex) . ': ' . $ex->getMessage() . ' in ' . basename($ex->getFile()) . ':' . $ex->getLine());
        $message = 'A database error occurred. Please try again or contact your administrator.';
        echo $message;
        echo $ex->getMessage();
        exit;
        // local debugging only: set APP_ENV=dev in the environment to see the real error on screen (off by default)
        if (getenv('APP_ENV') === 'dev')
        {
            $message .= ' [' . get_class($ex) . ': ' . $ex->getMessage() . ' in ' . basename($ex->getFile()) . ':' . $ex->getLine() . ']';
        }
        $EmpInfo = employeeDefaults();
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
    document.title = document.title<?php echo '+' . json_encode(': ['.$EmpInfo['EmpNo'].'] '.$EmpInfo['LastName'].', '.$EmpInfo['FirstName'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

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
        if ($post["tabIndex"]!="") 
            echo (int)$post["tabIndex"];
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
        // $( "#datepickerHired" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+0D"}); // Commented as of 3/20/2025 by whyRainbowDoesntHaveBlack
        $( "#datepickerHired" ).datepicker({ yearRange: "-100:+0", changeMonth: true, changeYear: true, maxDate: "+14D"});
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
                $result = HRISDB::getInstance()->get_distinct_degrees();
                $list = array();
                while($row = mysqli_fetch_array($result))
                {
                    $list[] = json_encode((string)$row['Degree'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
                }
                echo implode(',', $list);
            ?>
        ];
        //connect the autocomplete values from above to the degree textbox
        $( "#DegreeObtained" ).autocomplete({
            source: adegreeObtainedTags
        });

        var offenseTags = [
            <?php
                //tags for the degree field autocomplete
                $result = HRISDB::getInstance()->get_distinct_offense_natures();
                $list = array();
                while($row = mysqli_fetch_array($result))
                {
                    $list[] = json_encode((string)$row['Nature'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
                }
                echo implode(',', $list);
            ?>
        ];

        var offenseDefTags = [
            <?php
                //tags for the degree field autocomplete
                $result = HRISDB::getInstance()->get_distinct_offense_definitions();
                $list = array();
                while($row = mysqli_fetch_array($result))
                {
                    $list[] = json_encode((string)$row['Definition'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
                }
                echo implode(',', $list);
            ?>
        ];
        console.log('offenseDefTags', offenseDefTags);
        //connect the autocomplete values from above to the degree textbox
        $( "#NatureOfOffense" ).autocomplete({
            source: offenseTags
        });
        $( "#DefinitionOfOffense" ).autocomplete({
            source: offenseDefTags
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
            // document.EmployeeInfo['Action'].value = rows[6][this.elementIndex(element)];
            // document.EmployeeInfo['NatureOfOffense'].value = rows[1][this.elementIndex(element)];
            // document.EmployeeInfo['FrequencyOfOffense'].value = rows[7][this.elementIndex(element)];
            // document.EmployeeInfo['EffectivityDate'].value = rows[3][this.elementIndex(element)];
            // document.EmployeeInfo['NotificationDate'].value = rows[4][this.elementIndex(element)];
            // document.EmployeeInfo['Offensepkid'].value = rows[5][this.elementIndex(element)];


            document.EmployeeInfo['Action'].value = rows[7][this.elementIndex(element)];
            document.EmployeeInfo['NatureOfOffense'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['DefinitionOfOffense'].value = rows[2][this.elementIndex(element)];
            document.EmployeeInfo['FrequencyOfOffense'].value = rows[8][this.elementIndex(element)];
            document.EmployeeInfo['EffectivityDate'].value = rows[4][this.elementIndex(element)];
            document.EmployeeInfo['NotificationDate'].value = rows[5][this.elementIndex(element)];
            document.EmployeeInfo['Offensepkid'].value = rows[6][this.elementIndex(element)];

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
            document.EmployeeInfo['DefinitionOfOffense'].value = "";
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
        validateTextBox('DefinitionOfOffense', 'AlertDefinitionOfOffense');
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
        $result = HRISDB::getInstance()->get_active_departments_all_columns();

        $array = array();
        while($row = mysqli_fetch_array($result))
        {
            $array[] = 'new Array('.implode(',', array_map(function ($v) { return json_encode((string)$v, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); }, array($row['pkid'], $row['Department'], $row['fkDivision']))).')';
        }
        echo implode(',', $array);
        ?>);

    var Sections = new Array(<?php
        //get all sections and put in sections array
        $result = HRISDB::getInstance()->get_active_sections_all_columns();

        $array = array();
        while($row = mysqli_fetch_array($result))
        {
            $array[] = 'new Array('.implode(',', array_map(function ($v) { return json_encode((string)$v, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); }, array($row['pkid'], $row['Section'], $row['fkDepartment']))).')';
        }
        echo implode(',', $array);
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
    <h3>Employee Information  <?php echo e("(" . $EmpInfo['LastName'] . ", " . $EmpInfo['FirstName'] . " " . $EmpInfo['MiddleName'] . ") [" . $EmpInfo['EmpNo'] . "]"); ?></h3>

    <div class="transbox">

        <?php if($message!="")echo '<div class="alertMessage"><p><h4>&nbsp'.e($message).'</p></h4></div>'; ?>

        <div id ="Personal Background">
            <!--<form name="EmployeeInfo" action="employeeinfo.php" method="POST" onSubmit="validateForm();">-->
            <form name="EmployeeInfo" method="POST">

            <div id="tabs">
                    <ul>
                        <?php
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
                                            <td rowspan="5"><img src="<?php if($EmpInfo['Picturepath']==''){echo 'pictures/default.jpg';}else echo e($EmpInfo['Picturepath']); ?>" border="2" width="150" height="150"></td>
                                            <td rowspan="5" width="50px"></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td>Lastname:</td>
                                            <td>
                                                <input type="text" name="LastName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['LastName']) ?>" />
                                                <font color="red"><text id='AlertLN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Firstname:</td>
                                            <td>
                                                <input type="text" name="FirstName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['FirstName']) ?>"/>
                                                <font color="red"><text id='AlertFN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Middlename:</td>
                                            <td>
                                                <input type="text" name="MiddleName" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['MiddleName']) ?>"/>
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
                                                <input type="text" name="NickName" maxlength="20" style="width:250px" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['NickName']) ?>"/>
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
                                                            echo e(formatDateForView($EmpInfo['DOBirth']));
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
                                                <input disabled="disabled" type="text" name="Age" readonly="readonly" size="29" value="<?php echo e($EmpInfo['Age']); ?>"/><!-- age is dynamically calculated through javascript -->
                                                <font color="red"><text id='AlertA'></text></font>
                                            </td>
                                            <td>Spouse:</td>
                                            <td>
                                                <input type="text" name="Spouse"  style="width:200px" maxlength="50" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['Spouse']) ?>"/>
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
                                                <input type="text" name="CityAddStreet" maxlength="50" size="30" value="<?php echo e($EmpInfo['CityAddress1']) ?>"/>
                                                <font color="red"><text id='AlertCAS'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Barangay:</td>
                                            <td>
                                                <input type="text" name="CityAddBarangay" maxlength="50" size="30" value="<?php echo e($EmpInfo['CityAddress2']) ?>"/>
                                                <font color="red"><text id='AlertCAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>City:</td>
                                            <td>
                                                <input type="text" name="CityAdd" maxlength="50" size="30" value="<?php echo e($EmpInfo['CityAddress3']) ?>"/>
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
                                                <input type="text" name="ProvAddressBarangay" maxlength="50" size="30" value="<?php echo e($EmpInfo['ProvAddress1']) ?>"/>
                                                <font color="red"><text id='AlertPAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Municipality:</td>
                                            <td>
                                                <input type="text" name="ProvAddressMunicipality" maxlength="50" size="30" value="<?php echo e($EmpInfo['ProvAddress2']) ?>"/>
                                                <font color="red"><text id='AlertPAM'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Province:</td>
                                            <td>
                                                <input type="text" name="ProvAddress" maxlength="50" size="30" value="<?php echo e($EmpInfo['ProvAddress3']) ?>"/>
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
                                                <input type="text" name="Telephone" maxlength="15" onkeypress="return isNumberKey(event)" value="<?php echo e($EmpInfo['TelephoneNo']) ?>"/>
                                                <font color="red"><text id='AlertT'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Incoming Shuttle Service:</td>
                                            <td>
                                                <select name="IncomingShuttle" value="<?php echo e($EmpInfo['Incoming']) ?>">
                                                    <option value="0" <?php if((int)$EmpInfo['Incoming']===0)echo 'selected="true"' ?>>Not Availing Service</option>
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
                                                    <option value="0" <?php if((int)$EmpInfo['Outgoing']===0)echo 'selected="true"' ?>>Not Availing Service</option>
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
                                                <textarea cols="74" maxlength="250" rows="2" name="Remarks"><?php echo e($EmpInfo['Remarks']) ?></textarea>
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
                                    <input type="text" name="FatherName" maxlength="50" size="25" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['FatherName']) ?>"/>
                                    <font color="red"><text id='AlertFather'></text></font>
                                </td>
                                <td rowspan="2">Person to be notified <br>in case of emergency:</td>
                                <td rowspan="2">
                                    <textarea cols="40" rows="3" maxlength="150" name="PersonToBeNotified"><?php echo e($EmpInfo['Emergency']); ?></textarea>

                                    <font color="red"><text id='AlertPersonNotify'></text></font>
                                </td>
                            </tr>

                            <tr>
                                <td>Mother's Name:</td>
                                <td>
                                    <input type="text" name="MotherName" maxlength="50" size="25" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['MotherName']) ?>"/>
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
                                                <td axis="string" headers="ln"><?php echo e($row['LastName']) ?></td>
                                                <td axis="string" headers="fn"><?php echo e($row['FirstName']) ?></td>
                                                <td axis="string" headers="mn"><?php echo e($row['MiddleName']) ?></td>
                                                <td axis="number" headers="dob"><?php 
                                                    if($pkid!="")
                                                    {
                                                        //checks the dependent's own DoBirth, not the employee's DOBirth
                                                        echo e(formatDateForView($row['DoBirth']));
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
                                                    $taxdep = 'yes';
                                                else
                                                    $taxdep = 'no';
                                                ?>

                                                <td axis="string" headers="dep" align="center"><?php echo $taxdep ?></td>
                                                <td id="age" style="display:none"><?php 
                                                    if($pkid!="")
                                                    {
                                                        echo e(formatDateForView($row['DoBirth']));
                                                    }
                                                    ?><?php //echo GetAge($row['DoBirth']) ?></td>
                                                <td style="display:none" id="deppkid"><?php echo e($row['pkid']) ?></td>
                                                <td style="display:none" id="relationshipNo"><?php echo e($row['Relationship']) ?></td>
                                                <td style="display:none" id="fkemployee"><input type="text" name="fkEmpNo" value="<?php echo e($row['fkEmpNo']) ?>"/></td>
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
                                    <textarea cols="50" maxlength="250" rows="2" name="SchoolAddress"><?php echo e($EmpInfo['School']); ?></textarea>
                                    <!-- <input type="text" name="SchoolAddress" size="50" value=""/> -->
                                </td>
                            </tr>

                            <tr>
                                <td>Date Graduated:</td>
                                <td> <input type="text" name="DateGraduated" size="50" id="datepickerGrad" class="date" maxlength="10"
                                    value="<?php echo e(formatDateForView($EmpInfo['DOGraduated'])); ?>"/></td>
                            </tr>

                            <tr>
                                <td>Degree Obtained:</td>
                                <td> <input type="text" maxlength="100" name="DegreeObtained" id="DegreeObtained" size="50" value="<?php echo e($EmpInfo['Degree']) ?>"/></td>
                            </tr>

                            <tr>
                                <td>Honors/Awards:</td>
                                <td>
                                    <textarea cols="50" maxlength="250" rows="2" name="HonorsAwards"><?php echo e($EmpInfo['Honors']); ?></textarea>
                                    <!-- <input type="text" name="HonorsAwards" value=""/> -->
                                </td>
                                    
                            </tr>

                            <tr>
                                <td>Government Examination Taken:</td>
                                <td>
                                    <textarea cols="50" maxlength="250" rows="2" name="GovernmentExams"><?php echo e($EmpInfo['GovtExams']); ?></textarea>
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
                                <td><input type="text" maxlength="8" onkeypress="return isLetterAndNumberKey(event)" name="EmployeeNo" style="width:300px" value="<?php echo e($EmpInfo['EmpNo']) ?>"/></td>
                                <td>Salary Type:</td>
                                <td>
                                    <select name="EmploymentStatus" style="width:250px">
                                        <option value="1" <?php if($EmpInfo['HiringStatus']==1)echo 'selected="true"' ?>>Contractual</option>
                                        <option value="2" <?php if($EmpInfo['HiringStatus']==2)echo 'selected="true"' ?>>Probationary</option>
                                        <option value="3" <?php if($EmpInfo['HiringStatus']==3)echo 'selected="true"' ?>>Regular</option>
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
                                            if($EmpInfo['fkDivision']=='0' || $EmpInfo['fkDivision']==='' || $pkid == "")
                                            {
                                                $EmpInfo['fkDivision'] = 1;
                                                $EmpInfo['fkDepartment'] = 5;
                                            }
                                            //get list of divisions and select current value
                                            $result = HRISDB::getInstance()->get_divisions();
                                            while($row = mysqli_fetch_array($result))
                                            {
                                                ?>
                                                <option value="<?php echo e($row['pkid']) ?>" <?php if($EmpInfo['fkDivision']==$row['pkid']){echo 'selected="true"';} ?>><?php echo e($row['division']) ?></option>
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
                                                <option value="<?php echo e($row['pkid'])?>" <?php if($EmpInfo['fkDepartment']==$row['pkid']){echo 'selected="true"';} ?>><?php echo e($row['Department']) ?></option>
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
                                                <option value="<?php echo $i ?>" <?php if($EmpInfo['SalaryType'] == $i)echo 'selected="true"' ?>><?php echo e(HRISDB::getInstance()->get_salary_type_by_id($i));?></option>
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
                                                <option value="<?php echo e($row['pkid'])?>" <?php if($EmpInfo['fkSection']==$row['pkid']){echo 'selected="true"';} ?>><?php echo e($row['Section']) ?></option>
                                                <?php                       
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>SSS #:</td>
                                <td>
                                    <input type="text" maxlength="20" name="SSS" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo e($EmpInfo['SSN']) ?>"/>
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
                                                <option value="<?php echo e($row['pkid'])?>" <?php if($EmpInfo['fkPosition']==$row['pkid']){echo 'selected="true"';} ?>><?php echo e($row['Position']) ?></option>
                                                <?php  
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>TIN #:</td>
                                <td>
                                    <input type="text"  maxlength="20" name="TIN" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo e($EmpInfo['TIN']) ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td>Date Hired:</td>
                                <td>
                                    <input type="text" name="DateHired" id="datepickerHired" class="date" maxlength="10"
                                    value="<?php echo e(formatDateForView($EmpInfo['DateHired'])); ?>" style="width:300px"/>
                                </td>
                                <td>PagIBIG #:</td>
                                <td>
                                    <input type="text" name="PagIbig" maxlength="25" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo e($EmpInfo['PagIbig']) ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td>Blood Type:</td>
                                <td><input type="text" maxlength="3" name="BloodType" style="width:300px" onkeypress="return isLetterKey(event)" value="<?php echo e($EmpInfo['BloodType']) ?>"/></td>
                                <td>PhilHealth #:</td>
                                <td>
                                    <input type="text" maxlength="20" name="PhilHealth" style="width:250px" onkeypress="return isNumberKey(event)" value="<?php echo e($EmpInfo['PhilHealth']) ?>"/>
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
							} else {
								//unknown status: keep the table hidden so the closing tag below always has an opening tag
								echo "<table class = 'transbox' id = 'resignInfo' style = 'visibility: hidden;'>";
							}
						?>
							<tr>
								<th colspan = '2'>Resignation Information</th>
							</tr>
							<tr>
								<td>Effectivity Date:</td>
								<td><input type = 'date' name = 'ResEffectDate' value = '<?php echo e(formatDateForView($EmpInfo['Effectivity'], 'Y-m-d')); ?>'></td>
							</tr>
							<tr>
								<td>Reason:</td>
								<td><textarea cols = '98px' name = 'ResReason' rows = '5px'><?php echo e($EmpInfo['Reason']); ?></textarea></td>
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
                                                <th id="definition" title="click to sort by definition of offense">Definition</th>
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
                                                <td axis="string" headers="nature"><?php echo e($row['Nature']) ?></td>
                                                <td axis="string" headers="definition"><?php echo e($row['Definition']) ?></td>
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
                                                <td axis="string" headers="effect" title="<?php echo e(formatDateForView($row['Effectivity'], 'F j, Y')); ?>"><?php 
                                                    if($pkid!="")
                                                    {
                                                        echo e(formatDateForView($row['Effectivity']));
                                                    }
                                                    ?><?php //echo $row['Effectivity'] ?></td>
                                                <td axis="string" headers="notif" title="<?php echo e(formatDateForView($row['Notification'], 'F j, Y')); ?>"><?php 
                                                    if($pkid!="")
                                                    {
                                                        echo e(formatDateForView($row['Notification']));
                                                    }
                                                    ?><?php //echo $row['Notification'] ?></td>
                                                <td style="display:none" id="pkid"><?php echo e($row['pkid']) ?></td>
                                                <td style="display:none" id="actionNo"><?php echo e($row['Action']) ?></td>
                                                <td style="display:none" id="frequenqyNo"><?php echo e($row['Frequency']) ?></td>
                                                <td style="display:none" id="fkemployee"><input type="text" name="fkEmpNo" value="<?php echo e($row['fkEmpNo']) ?>"/></td>
                                                
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
                                        <!-- 08022025 by Nessa -->
                                        <tr>
                                            <td>Definition of Offense:</td>
                                            <td>
                                                <!-- <input type="text"  maxlength="1000" name="DefinitionOfOffense" id="DefinitionOfOffense"/> -->
                                                <textarea cols="50" maxlength="1000" rows="2" name="DefinitionOfOffense"></textarea>
                                                <font color="red"><text id='AlertDefinitionOfOffense'></text></font>
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

                                        echo e(formatDateForView($row['PeriodFrom'], 'F j, Y'));

                                        ?>"><?php
                                        
                                        // $date = new DateTime($row['Period']);
                                        // echo $date->format('Y/m/d');
										echo e(formatDateForView($row['PeriodFrom'], 'Y/m/d').'-'.formatDateForView($row['PeriodTo'], 'Y/m/d'));
                                         ?></td>
                                        <td axis="string" headers="title" ><?php echo e($row['Title']) ?></td>
                                        <td axis="string" headers="objective" ><?php echo e($row['Objective']) ?></td>
                                        <td axis="string" headers="venue" ><?php echo e($row['Venue']) ?></td>
                                        <td axis="string" headers="trainor" ><?php echo e($row['Trainor']) ?></td>
                                        <td axis="string" headers="mechanics" ><?php echo e($row['Mechanics']) ?></td>
                                        <td axis="string" headers="result" ><?php echo e($row['Result']) ?></td>
                                        <td axis="string" headers="remarks" ><?php echo e($row['Remarks']) ?></td>
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

                                //bound query in HRISDB; pkid 0 (new employee) returns no rows
                                $result = HRISDB::getInstance()->get_employment_history_by_employee((int)$EmpInfo['pkid']);

                                $counter = 0;
                                while($row = mysqli_fetch_array($result))
                                {
                                    $counter++;
                                    echo '<tr>';
                                    echo '<td headers="div">'.e(HRISDB::getinstance()->get_division_name_by_id($row['fkDivision'])).'</td>';
                                    echo '<td headers="dep">'.e(HRISDB::getinstance()->get_department_name_by_id($row['fkDepartment'])).'</td>';
                                    echo '<td headers="sec">'.e(HRISDB::getinstance()->get_section_name_by_id($row['fkSection'])).'</td>';
                                    echo '<td headers="pos">'.e(HRISDB::getinstance()->get_position_name_by_id($row['fkPosition'])).'</td>';
                                    echo '<td headers="from">'.e(convertDateFromatViewing($row['DateStart'])).'</td>';
                                    echo '<td headers="to">'.e($row['DateEnd']).'</td>';
                                    echo '<td style="display:none" id="fkPos">'.e($row['fkPosition']).'</td>';
                                    echo '<td style="display:none" id="fkDiv">'.e($row['fkDivision']).'</td>';
                                    echo '<td style="display:none" id="fkDep">'.e($row['fkDepartment']).'</td>';
                                    echo '<td style="display:none" id="fkSec">'.e($row['fkSection']).'</td>';
                                    echo '<td style="display:none" id="pkid">'.e($row['pkid']).'</td>';
                                    echo '</tr>';
                                }
                                if($counter==0)
                                {
                                    echo '<tr>';
                                    echo '<td>'.e(HRISDB::getinstance()->get_division_name_by_id($EmpInfo['fkDivision'])).'</td>';
                                    echo '<td>'.e(HRISDB::getinstance()->get_department_name_by_id($EmpInfo['fkDepartment'])).'</td>';
                                    echo '<td>'.e(HRISDB::getinstance()->get_section_name_by_id($EmpInfo['fkSection'])).'</td>';
                                    echo '<td>'.e(HRISDB::getinstance()->get_position_name_by_id($EmpInfo['fkPosition'])).'</td>';
                                    echo '<td>'.e(convertDateFromatViewing($EmpInfo['DateHired'])).'</td>';
                                    echo '<td>Present</td>';
                                    echo '<td style="display:none" id="fkPos">'.e($EmpInfo['fkPosition']).'</td>';
                                    echo '<td style="display:none" id="fkDiv">'.e($EmpInfo['fkDivision']).'</td>';
                                    echo '<td style="display:none" id="fkDep">'.e($EmpInfo['fkDepartment']).'</td>';
                                    echo '<td style="display:none" id="fkSec">'.e($EmpInfo['fkSection']).'</td>';
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
                                                <option value="<?php echo e($row['pkid']) ?>"><?php echo e($row['division']) ?></option>
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
                                                <option value="<?php echo e($row['pkid'])?>" ><?php echo e($row['Position']) ?></option>
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
                                //PAS is a separate database: a failure here must not break the whole page
                                $result_appraisal = null;
                                try
                                {
                                    $result_appraisal = PAS::getInstance()->get_appraisal_record($EmpInfo['EmpNo']);
                                }
                                catch (Throwable $ex)
                                {
                                    error_log('employeeinfo.php appraisal: ' . $ex->getMessage());
                                }
                                while($result_appraisal && ($row = mysqli_fetch_array($result_appraisal))):
                                    ?>
                                    <tr>                                        
                                        <td axis="string" headers="rating" style="width:20%"><center><?php echo e($row['rating']); ?></center></td>
                                        <td axis="string" headers="position" style="width:15%"><center><?php echo e($row['position']); ?></center></td>
                                        <td axis="string" headers="from" style="width:13%"><center><?php echo e(formatDateForView($row['evaluation_from'], 'M d, Y')); ?></center></td>
                                        <td axis="string" headers="to" style="width:13%"><center><?php echo e(formatDateForView($row['evaluation_to'], 'M d, Y')); ?></center></td>
                                        <td axis="string" headers="effectivity_date" style="width:13%"><center><?php echo e(formatDateForView($row['effectivity_date'], 'M d, Y')); ?></center></td>
                                        <td axis="string" headers="purpose" style="width:15%"><center><?php echo e($row['purpose']); ?></center></td>
                                        <td axis="string" headers="rated_by" style="width:20%"><center><?php echo e($row['rated_by']); ?></center></td>
                                    </tr>
                                    <?php
                                endwhile;

                                if(!$result_appraisal || $result_appraisal->num_rows == 0)
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
									<input type="hidden" id="fkEmployee" value="<?php echo e($pkid)?>"> 
									<select name="search_fileCat" id="search_fileCat" size="1" onchange="showCat(this.value)" style="width:280px">
										<option></option>
										<?php
											//get list of divisions and select current value
											$result = HRISDB::getInstance()->get_201FileCategory();
											while($row = mysqli_fetch_array($result))
											{
												?>
												<option value="<?php echo e($row['pkid']) ?>"><?php echo e($row['empfilecategory']) ?></option>
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
										echo '<td style="display:none" id="pkid">'.e($row['pkid']).'</td>';
										echo '<td headers="fileCat">'.e($row['empfilecategory']).'</td>';
										echo '<td headers="file_Name">'.e($row['file_name']).'</td>';
										echo '<td><a href="pdfuploads/'.e($row['pkid']).'" target="_blank">View File</a></td>';
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
                <input type="hidden" name="pkid" value="<?php echo e($pkid) ?>">
                <font color="red"><text id='Alerts'></text></font><br>

                &nbsp; <input type="button" id="button" value="Save" onClick="validateForm();" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                <input type="button" id="button" value="Employee List" onClick="window.location = 'index.php'" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                
            </form>
        </div>
	
<div class="wrap" id="uploadfile_modal" title="Upload Employee 201 File">
	<form id="scannedFile" method="post" enctype="multipart/form-data">
	<!--	<input type="text" id="fkUploadFile" value=""> 
		<input type="hidden" id="fkUploadFile" value="<?php //echo $pkid?>">-->
		<input type="hidden" id="fkEmployee" value="<?php echo e($pkid)?>">
		
		<br>		
		<label for="fileCat">Select Employee 201 Category: </label>
			<select name="filecat" id="filecat" size="1" style="width:300px" size="1" class="text ui-widget-content ui-corner-all" required>
				<option></option>
				<?php
					$result = HRISDB::getInstance()->get_201FileCategory();
					while($row = mysqli_fetch_array($result))
					{
					?>
						<option value="<?php echo e($row['pkid']) ?>"><?php echo e($row['empfilecategory']) ?></option>
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
