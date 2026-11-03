<?php

$border = 0;

require_once("../global_includes/hris-oop.php");

//validations done in javascript

function GetAge($bday)
{
    $date = new DateTime($bday);
    $diff = $date->diff(new DateTime());

    return $diff->y.' years, '.$diff->m.' months, '.$diff->d.' days';
}

$pkid = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        //validate date fields
        if($_POST["DateOfBirth"]!='')
        {
            $date = new DateTime($_POST["DateOfBirth"]);
            $DOB = $date->format('Y-m-d');
        }
        else
            $DOB = '';

        if($_POST["DateGraduated"]!='')
        {
            $dateGad = new DateTime($_POST["DateGraduated"]);
            $DOGrad = $dateGad->format('Y-m-d');
        }
        else
            $DOGrad = '';

        if($_POST["DateHired"]!='')
        {
            $dateHired = new DateTime($_POST["DateHired"]);
            $DOHired = $dateHired->format('Y-m-d');
        }
        else
            $DOHired = '';

        //put leading zeros
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
            $pkid = $_POST["pkid"];

            if($_POST["EmployeeNo"]!="")
            {
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

                //update employee employment status
                HRISDB::getInstance()->update_employee_employment_info($pkid,
                    $_POST["EmployeeNo"],
                    $_POST["EmploymentStatus"],
                    $_POST["Division"],
                    $_POST["Status"],
                    $_POST["Department"],
                    $_POST["SalaryType"],
                    $_POST["Section"],
                    $_POST["SSS"],
                    $_POST["Position"],
                    $_POST["TIN"],
                    $DOHired,
                   $_POST["PhilHealth"]);

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
        }
        else //else if no pkid is found
        {
            if ($_POST["formtype"]=="SaveBasicInfo")
            {
                //create the new employee
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
        {//if an account pkid is found, load data

            //get all account data
            $result = HRISDB::getInstance()->get_employee_info_by_id($pkid);
            $EmpInfo = mysqli_fetch_array($result);
            
            //get age using php
            $date = new DateTime($EmpInfo['DOBirth']);
            $diff = $date->diff(new DateTime());
            $EmpInfo['Age'] =  $diff->y.' years, '.$diff->m.' months, '.$diff->d.' days';
        }
    }
    else
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

    <!-- table sorter -->
    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>

     <!-- javascript codes -->
    <script language="javascript" type="text/javascript">

    //variable for validating fields
    var error = false;

    function validateTextBox(textbox, alerts)
    {
        if(""==document.forms.EmployeeInfo[textbox].value){
            document.getElementById(alerts).innerHTML = '*'; error = true;
        }
        else document.getElementById(alerts).innerHTML = '';
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
        validateTextBox('Telephone', 'AlertT');

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
        //calculate age based on the date
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

        //document.EmployeeInfo['Age'].value = Math.floor(diff/oneyear)+' year(s), '+ Math.floor((diff/oneMonth)%12) + ' month(s), ' + (Math.ceil((diff/oneDay)%(365.25/12))-1) + ' day(s) ';
    }

    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }

    function isLetterKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return true;

        return false;
    }

    //alert('<?php echo $_POST["tabIndex"]; ?>');

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
                $('#tabs').tabs("option","disabled",[1,2,3,4]);
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
        $( "#datepickerBday" ).datepicker({ changeMonth: true, changeYear: true , maxDate: "-18Y"});
        });
        $(function() {
        $( "#datepickerDepBday" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        $( "#datepickerDepBday" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerGrad" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        });
        $(function() {
        $( "#datepickerHired" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        //$( "#datepickerHired" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerNotif" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        $( "#datepickerNotif" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerEffect" ).datepicker({ changeMonth: true, changeYear: true});
        $( "#datepickerEffect" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
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

        //make table for dependents a sorted table
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

            //inser all values of selected dependent into the textboxes
            document.EmployeeInfo['DepLastName'].value = rows[0][this.elementIndex(element)];
            document.EmployeeInfo['DepFirstName'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['DepMiddleName'].value = rows[2][this.elementIndex(element)];
            document.EmployeeInfo['DepDateOfBirth'].value = rows[3][this.elementIndex(element)];
            document.EmployeeInfo['Relationship'].value = rows[8][this.elementIndex(element)];
            document.EmployeeInfo['TaxDependent'].value = rows[5][this.elementIndex(element)];
            document.EmployeeInfo['DepAge'].value = rows[6][this.elementIndex(element)];
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

        var myOffenseTable = new SortedTable('sortedOffense');

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
    }

    //javascript trim function
    String.prototype.trim = function()
    {
        return this.replace(/^\s+|\s+$/g,"");
    }


    function validateDependentFields()
    {
        //trim spaces
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
        //validateTextBox('Action', 'AlertAction');
        validateTextBox('NotificationDate', 'AlertNotificationDate');
        validateTextBox('NatureOfOffense', 'AlertNatureOfOffense');
        validateTextBox('EffectivityDate', 'AlertEffectivityDate');
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

    //add option on selection box
    function appendOptionLast(id,text,value)
    {
      var elOptNew = document.createElement('option');
      elOptNew.text = text;
      elOptNew.value = value;
      var elSel = document.getElementById(id);

      try {
        elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
      }
      catch(ex) {
        elSel.add(elOptNew); // IE only
      }
    }

    //update departmentbox on division select
    function updateDepartmentList()
    {
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                document.getElementById("texter").innerHTML = xmlhttp.responseText;
                var myarray = eval(document.getElementById("texter").innerHTML);


                for(j=document.getElementById('Department').options.length-1;j>=0;j--)
                {
                    document.getElementById('Department').remove(j);
                }

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Department',myarray[i][1],myarray[i][0]);
                }
                
            }
        }

        var url = "updateDepartment.php?fkDivision="+document.getElementById("Division").value;
        xmlhttp.open("GET",url,true);
        xmlhttp.send();


    }

    //update selection box on department select
    function updateSectionList()
    {
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                document.getElementById("texter").innerHTML = xmlhttp.responseText;
                var myarray = eval(document.getElementById("texter").innerHTML);


                for(j=document.getElementById('Section').options.length-1;j>=0;j--)
                {
                    document.getElementById('Section').remove(j);
                }

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Section',myarray[i][1],myarray[i][0]);
                }
            }
        }

        var url = "updateSection.php?fkDepartment="+document.getElementById("Department").value;
        //alert(url);
        xmlhttp.open("GET",url,true);
        xmlhttp.send();
    }

    </script>

    <!-- dependent table styles -->
    <!-- put in css file -->
    <style type="text/css">
        tr {border:0;padding:0;margin:0;}
        td, th {border:0;padding:2px 6px;margin:0;border-right:0px solid #336;border-bottom:0px solid #336;}
        td[axis='number'], td[axis='date'] {text-align:right;}
        th {white-space:no-wrap;background-color:#FFFFFF;padding:2px 20px;}
        tfoot td {border-top:1px solid #003;}
        thead th {border-bottom:2px solid #003;}
        .odd td {background-color:white;}
        .even td {background-color:#DDE5EB;}
        .hover td {background-color:#A5B3C9;}
        .sortedminus {background-color:#ecc;}
        .sortedplus {background-color:#cec;}
        .selrow td {background-color:#879AB7;}
    </style>
</head>

<body>

    <h2><font color='#1A3380'>HRIS System</font></h2>
    <h3><font color='gray'>Employee Information</font></h3>

    <div class="transbox">

        <?php if($message!="")echo '<p><h4><font color="green">&nbsp'.$message.'</font></p></h4>'; ?>

        <div id ="Personal Background">
            <!--<form name="EmployeeInfo" action="employeeinfo.php" method="POST" onSubmit="validateForm();">-->
            <form name="EmployeeInfo" method="POST">

            <div id="tabs">
                    <ul>
                        <li><a href="#tabs-1">Personal Background</a></li>
                        <li><a href="#tabs-2">Family Information</a></li>
                        <li><a href="#tabs-3">Educational Background</a></li>
                        <li><a href="#tabs-4">Employment Information</a></li>
                        <li><a href="#tabs-5">Disciplinary History</a></li>
                    </ul>
                    <div id="tabs-1">
                    
                    <!-- Personal Background -->
                        
                        <h4 class="group_title">Personal Information</h4>

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td rowspan="5"><img src="" width="150" height="150"></td>
                                            <td rowspan="5" width="50px"></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td>Lastname:</td>
                                            <td>
                                                <input type="text" name="LastName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['LastName'] ?>" />
                                                <font color="red"><text id='AlertLN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Firstname:</td>
                                            <td>
                                                <input type="text" name="FirstName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['FirstName'] ?>"/>
                                                <font color="red"><text id='AlertFN'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Middlename:</td>
                                            <td>
                                                <input type="text" name="MiddleName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['MiddleName'] ?>"/>
                                                <font color="red"><text id='AlertMN'></text></font>
                                            </td>
                                        </tr>

                                        <tr></tr>

                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="<?php echo $border ?>">
                                        <tr>
                                            <td>Nickname:</td>
                                            <td>
                                                <input type="text" name="NickName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['NickName'] ?>"/>
                                                <font color="red"><text id='AlertNN'></text></font>
                                            </td>
                                            <td>Gender:</td>
                                            <td>
                                                <select name="Gender">
                                                    <option value="1" <?php if($EmpInfo['Gender']==1) echo 'selected = "true"' ?>>Male</option>
                                                    <option value="2" <?php if($EmpInfo['Gender']==2) echo 'selected = "true"' ?>>Female</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Date of Birth:</td>
                                            <td>
                                                <!-- id="datepickerBday" -->
                                                <input type="text" readonly="readonly" name="DateOfBirth" id="datepickerBday" onchange="getAge()"
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
                                                <select name="CivilStatus">
                                                    <option value="1" <?php if($EmpInfo['CivilStatus']==1) echo 'selected = "true"' ?>>Single</option>
                                                    <option value="2" <?php if($EmpInfo['CivilStatus']==2) echo 'selected = "true"' ?>>Married</option>
                                                    <option value="3" <?php if($EmpInfo['CivilStatus']==3) echo 'selected = "true"' ?>>Widowed</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Age:</td>
                                            <td width="200px">
                                                <input disabled="disabled" type="text" name="Age" readonly="readonly" size="29"/><!-- age is dynamically calculated through javascript -->
                                                <font color="red"><text id='AlertA'></text></font>
                                            </td>
                                            <td>Spouse:</td>
                                            <td>
                                                <input type="text" name="Spouse" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['Spouse'] ?>"/>
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
                                                <input type="text" name="CityAddStreet" value="<?php echo $EmpInfo['CityAddress1'] ?>"/>
                                                <font color="red"><text id='AlertCAS'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Barangay:</td>
                                            <td>
                                                <input type="text" name="CityAddBarangay" value="<?php echo $EmpInfo['CityAddress2'] ?>"/>
                                                <font color="red"><text id='AlertCAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>City:</td>
                                            <td>
                                                <input type="text" name="CityAdd" value="<?php echo $EmpInfo['CityAddress3'] ?>"/>
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
                                                <input type="text" name="ProvAddressBarangay" value="<?php echo $EmpInfo['ProvAddress1'] ?>"/>
                                                <font color="red"><text id='AlertPAB'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Municipality:</td>
                                            <td>
                                                <input type="text" name="ProvAddressMunicipality" value="<?php echo $EmpInfo['ProvAddress2'] ?>"/>
                                                <font color="red"><text id='AlertPAM'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Province:</td>
                                            <td>
                                                <input type="text" name="ProvAddress" value="<?php echo $EmpInfo['ProvAddress3'] ?>"/>
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
                                                <input type="text" name="Telephone" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['TelephoneNo'] ?>"/>
                                                <font color="red"><text id='AlertT'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Incoming Shuttle Service:</td>
                                            <td>
                                                <select name="IncomingShuttle" value="<?php echo $EmpInfo['Incoming'] ?>">
                                                    <option value="8" <?php if($EmpInfo['Incoming']==8)echo 'selected="true"' ?>>Not Availing Service</option>
                                                    <option value="1" <?php if($EmpInfo['Incoming']==1)echo 'selected="true"' ?>>Lower/Bicutan</option>
                                                    <option value="2" <?php if($EmpInfo['Incoming']==2)echo 'selected="true"' ?>>FTI/Upper Bicutan</option>
                                                    <option value="3" <?php if($EmpInfo['Incoming']==3)echo 'selected="true"' ?>>Sucat</option>
                                                    <option value="4" <?php if($EmpInfo['Incoming']==4)echo 'selected="true"' ?>>Alabang</option>
                                                    <option value="5" <?php if($EmpInfo['Incoming']==5)echo 'selected="true"' ?>>Magallanes</option>
                                                    <option value="6"<?php if($EmpInfo['Incoming']==6)echo 'selected="true"' ?>>Carmona</option>
                                                    <option value="7" <?php if($EmpInfo['Incoming']==7)echo 'selected="true"' ?>>Calamba</option>
                                                </select>
                                            </td>
                                            <td>Outgoing Shuttle Service:</td>
                                            <td>
                                                <select name="OutgoingShuttle">
                                                    <option value="8" <?php if($EmpInfo['Outgoing']==8)echo 'selected="true"' ?>>Not Availing Service</option>
                                                    <option value="1" <?php if($EmpInfo['Outgoing']==1)echo 'selected="true"' ?>>Lower/Bicutan</option>
                                                    <option value="2" <?php if($EmpInfo['Outgoing']==2)echo 'selected="true"' ?>>FTI/Upper Bicutan</option>
                                                    <option value="3" <?php if($EmpInfo['Outgoing']==3)echo 'selected="true"' ?>>Sucat</option>
                                                    <option value="4" <?php if($EmpInfo['Outgoing']==4)echo 'selected="true"' ?>>Alabang</option>
                                                    <option value="5" <?php if($EmpInfo['Outgoing']==5)echo 'selected="true"' ?>>Magallanes</option>
                                                    <option value="6" <?php if($EmpInfo['Outgoing']==6)echo 'selected="true"' ?>>Carmona</option>
                                                    <option value="7" <?php if($EmpInfo['Outgoing']==7)echo 'selected="true"' ?>>Calamba</option>
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
                    <div id="tabs-2">

                        <!-- Family Infornation -->

                        <h4 class="group_title">Immediate Family</h4>

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Father's Name:</td>
                                <td>
                                    <input type="text" name="FatherName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['FatherName'] ?>"/>
                                    <font color="red"><text id='AlertFather'></text></font>
                                </td>
                                <td rowspan="2">Person to be notified <br>in case of emergency:</td>
                                <td rowspan="2">
                                    <textarea cols="40" rows="3" name="PersonToBeNotified"><?php echo $EmpInfo['Emergency']; ?></textarea>

                                    <font color="red"><text id='AlertPersonNotify'></text></font>
                                </td>
                            </tr>

                            <tr>
                                <td>Mother's Name:</td>
                                <td>
                                    <input type="text" name="MotherName" onkeypress="return isLetterKey(event)" value="<?php echo $EmpInfo['MotherName'] ?>"/>
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
                                                <th id="ln" class="sortedplus">Last Name</th>
                                                <th id="fn">First Name</th>
                                                <th id="mn">Middle Name</th>
                                                <th id="dob">Birthday</th>
                                                <th id="rel">Relationship</th>
                                                <th id="dep">Tax Exempt</th>
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
                                                <td axis="number" headers="dob"><?php echo $row['DoBirth'] ?></td>

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

                                                <td axis="string" headers="dep"><?php echo $taxdep ?></td>
                                                <td id="age" style="display:none"><?php echo GetAge($row['DoBirth']) ?></td>
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
                                                    <input type="text" onkeypress="return isLetterKey(event)" name="DepLastName"/>
                                                    <font color="red"><text id='AlertLastName'></text></font>
                                                </td>
                                                <td>Date of Birth:</td>
                                                <td>
                                                    <input type="text" name="DepDateOfBirth" id="datepickerDepBday" readonly="readonly" onchange="getDepAge()"/>
                                                    <font color="red"><text id='AlertDOBirth'></text></font>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Firstname:</td>
                                                <td>
                                                    <input type="text" onkeypress="return isLetterKey(event)" name="DepFirstName"/>
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
                                                    <input type="text" onkeypress="return isLetterKey(event)" name="DepMiddleName"/>
                                                    <font color="red"><text id='AlertMiddleName'></text></font>
                                                </td>
                                                <td>Relationship:</td>
                                                <td>
                                                    <select name="Relationship">
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
                                        <input type="button" id="button" name="oks" value="Ok" onclick="Dependentok()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                        <input type="button" id="button" name="adds" value="Add" onclick="Dependentadd()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                        <input type="button" id="opener" name="deletes" value="Delete" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    </td>
                                </tr>
                            <!-- </form> -->
                        </table>



                    </div>
                    <div id="tabs-3">

                        <!-- Educational Background -->

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Highest Educational Attainment:</td>
                                <td>
                                    <select name="HighestEducationalAttainment">
                                        <option value="1" <?php if($EmpInfo['HighestEduc']==1)echo 'selected="true"' ?>>High School Graduate</option>
                                        <option value="2" <?php if($EmpInfo['HighestEduc']==2)echo 'selected="true"' ?>>Vocational Graduate</option>
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
                                <td> <input type="text" name="DateGraduated" size="50" id="datepickerGrad" class="date"
                                    value="<?php

                                                if ($EmpInfo['DOGraduated']!='0000-00-00'){
                                                $date = new DateTime($EmpInfo['DOGraduated']);
                                                echo $date->format('m/d/Y');}
                                            ?>"/></td>
                            </tr>

                            <tr>
                                <td>Degree Obtained:</td>
                                <td> <input type="text" name="DegreeObtained" size="50" value="<?php echo $EmpInfo['Degree'] ?>"/></td>
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
                    <div id="tabs-4">

                        <!-- Employment Information -->

                        <table class="transbox" border="<?php echo $border ?>">
                            <tr>
                                <td>Employee #:</td>
                                <td><input type="text" name="EmployeeNo" value="<?php echo $EmpInfo['EmpNo'] ?>"/></td>
                                <td>Employment Category:</td>
                                <td>
                                    <select name="EmploymentStatus">
                                        <option value="1" <?php if($EmpInfo['HiringStatus']==1)echo 'selected="true"' ?>>Contractual</option>
                                        <option value="1" <?php if($EmpInfo['HiringStatus']==1)echo 'selected="true"' ?>>Probationary</option>
                                        <option value="1" <?php if($EmpInfo['HiringStatus']==1)echo 'selected="true"' ?>>Regular</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Division:</td>
                                <td>
                                    <select name="Division" id="Division" onchange="updateDepartmentList();" size="1">
                                        <option></option>
                                        <?php
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
                                </td>
                                <td>Employment Status:</td>
                                <td>
                                    <select name="Status">
                                        <option value="1" <?php if($EmpInfo['Status']==1)echo 'selected="true"' ?>>Active</option>
                                        <option value="2" <?php if($EmpInfo['Status']==2)echo 'selected="true"' ?>>Resigned</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Department:</td>
                                <td>
                                    <select name="Department" id="Department" onchange="updateSectionList();" size="1">
                                        <option></option>
                                        <?php
                                            //get list of departments and select current value
                                            $result = HRISDB::getInstance()->get_departments();
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
                                    <select name="SalaryType">
                                        <option value="1" <?php if($EmpInfo['SalaryType'] == 1)echo 'selected="true"' ?>>Daily Employees</option>
                                        <option value="2" <?php if($EmpInfo['SalaryType'] == 2)echo 'selected="true"' ?>>Monthly Employees</option>
                                        <option value="3" <?php if($EmpInfo['SalaryType'] == 3)echo 'selected="true"' ?>>Confidential Employees</option>
                                        <option value="4" <?php if($EmpInfo['SalaryType'] == 4)echo 'selected="true"' ?>>JS/SS Employees</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Section:</td>
                                <td>
                                    <div id="texter" style="display: none;"></div>
                                    <select name="Section" id="Section">
                                        <option></option>
                                        <?php
                                            //get section
                                            $result = HRISDB::getInstance()->get_sections();
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
                                    <input type="text" name="SSS" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['SSN'] ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td>Position:</td>
                                <td>
                                    <select name="Position">
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
                                    <input type="text" name="TIN" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['TIN'] ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td>Date Hired:</td>
                                <td>
                                    <input type="text" name="DateHired" id="datepickerHired" class="date" readonly="readonly"
                                    value="<?php 
                                            if ($EmpInfo['DateHired']!='0000-00-00'){
                                            $date = new DateTime($EmpInfo['DateHired']);
                                            echo $date->format('m/d/Y');}
                                            ?>"/>
                                </td>
                                <td>PagIBIG #:</td>
                                <td>
                                    <input type="text" name="PagIBIG" onkeypress="return isNumberKey(event)" />
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td>PhilHealth #:</td>
                                <td>
                                    <input type="text" name="PhilHealth" onkeypress="return isNumberKey(event)" value="<?php echo $EmpInfo['PhilHealth'] ?>"/>
                                </td>
                            </tr>
                        </table>


                    </div>
                    <div id="tabs-5">

                        <!-- Disciplinary History -->

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
                                                <th id="action" class="sortedplus">Action</th>
                                                <th id="nature" >Nature</th>
                                                <th id="freq" >Frequency of Offense</th>
                                                <th id="effect" >Effectivity</th>
                                                <th id="notif" >Notification</th>
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
                                                <td axis="string" headers="effect"><?php echo $row['Effectivity'] ?></td>
                                                <td axis="string" headers="notif"><?php echo $row['Notification'] ?></td>
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
                                                <input type="text" name="NotificationDate" id="datepickerNotif" class="date" readonly="readonly"/>
                                                <font color="red"><text id='AlertNotificationDate'></text></font>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Nature of Offense:</td>
                                            <td>
                                                <input type="text" name="NatureOfOffense"/>
                                                <font color="red"><text id='AlertNatureOfOffense'></text></font>
                                            </td>
                                            <td>Effectivity Date:</td>
                                            <td>
                                                <input type="text" name="EffectivityDate" id="datepickerEffect" class="date" readonly="readonly"/>
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
                                    <input type="button" id="button" name="Offenseoks" value="Save" onclick="Offenseok()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    <input type="button" id="button" name="Offenseadds" value="Add" onclick="Offenseadd()" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                    <input type="button" id="OffenseDialogopener" name="Offensedeletes" value="Delete" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
                <input type="hidden" name="formtype" value="">
                <input type="hidden" name="tabIndex" value="">
                <input type="hidden" name="pkid" value="<?php echo $pkid ?>">
                <font color="red"><text id='Alerts'></text></font><br>

                &nbsp<input type="button" id="button" value="Save" onClick="validateForm();" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                <input type="button" id="button" value="Employee List" onClick="window.location = 'index.php'" class="ui-button ui-widget ui-state-default ui-corner-all"/>
                
            </form>
        </div>
    </div>
</body>
</html>