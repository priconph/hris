<?php
    
    require_once("../global_includes/hris-oop.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // echo $_POST["submit"];
        // echo $_POST["type"].' '.$_POST['DivId'].' '.$_POST['text'];
        if($_POST['type']=="Change Division Name")
        {
            if($_POST['DivId']!="")
            {
                if($_POST['text']!="")
                {
                    //update division name with id $_POST['DivId']
                    HRISDB::getInstance()->update_division_name($_POST['text'],$_POST['DivId']);
                    $message = "Changed division name to '".$_POST['text']."'";
                }
                else
                    $message = "Please type a Division name";
            }
            else
                $message = "Please select a Division first";
        }
        else if($_POST['type']=="Create New Division")
        {
            if($_POST['text']!="")
            {
                //create new division
                HRISDB::getInstance()->create_division($_POST['text']);
                $message = "Created new division named '".$_POST['text']."'";
            }
            else
                $message = "Please type a Division name";
        }
        else if($_POST['type']=="Create New Department")
        {
            if($_POST['DivId']!="")
            {
                if($_POST['text']!="")
                {
                    //create department under division with id $_POST['DivId']
                    HRISDB::getInstance()->create_department($_POST['text'],$_POST['DivId']);
                    $message = "Created new Department named '".$_POST['text']."'";
                }
                else
                    $message = "Please type a Department name";
            }
            else
                $message = "Please select a Division first";
        }
        else if($_POST['type']=="Change Department Name")
        {
            if($_POST['DepId']!="")
            {
                if($_POST['text']!="")
                {
                    //update department name with id $_POST['DepId']
                    HRISDB::getInstance()->update_department_name($_POST['text'],$_POST['DepId']);
                    $message = "Changed department name to '".$_POST['text']."'";
                }
                else
                    $message = "Please type a Department name";
            }
            else
                $message = "Please select a department first";
        }
        else if($_POST['type']=="Change Department Status")
        {
            if($_POST['DepId']!="")
            {
                //update Department Status with id $_POST['DepId']
                $result = HRISDB::getInstance()->update_department_status($_POST['DepId'],$_POST['text']);
                if($result)
                {
                    $message = "Changed department status ";
                }
                else
                {
                    $message = "Unable to change status, an active employee is assigned to this Department ";
                }
                //$message = "updated department status";
            }
            else
                $message = "Please select a department first";
        }
        else if($_POST['type']=="Create New Section")
        {
            if($_POST['DepId']!="")
            {
                if($_POST['text']!="")
                {
                    //creaate new section under department with id $_POST['DepId']
                    HRISDB::getInstance()->create_section($_POST['text'],$_POST['DepId']);
                    $message = "Created new section named '".$_POST['text']."'";
                }
                else
                    $message = "Please type a section name";
            }
            else
                $message = "please select a department first";
        }
        else if($_POST['type']=="Change Section Name")
        {
            if($_POST['SecId']!="")
            {
                //change section name with id $_POST['SecId']
                HRISDB::getInstance()->update_section_name($_POST['text'],$_POST['SecId']);
                $message = "Changed section name to '".$_POST['text']."'";
            }
            else
                $message = "Please select a section first";
        }
        else if($_POST['type']=="Change Section Status")
        {
            if($_POST['SecId']!="")
            {
                //change section status with id $_POST['SecId']
                $result = HRISDB::getInstance()->update_section_status($_POST['SecId'],$_POST['text']);
                if($result)
                {
                    $message = "Updated section status ";
                }
                else
                {
                    $message = "Unable to change status, an active employee is assigned to this Section ";
                }
                //$message = "Changed section status ";
            }
            else
                $message = "Please select a section first";
        }
        
    }

?>



<head>

    <title>Division, Department and Section Manager</title>
    <!-- CSS -->
    <link href="src/hris.css" type="text/css" rel="stylesheet" media="all" />

    <script type="text/javascript">


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

    var div = '<?php echo $_POST["DivId"]; ?>';
    var dep = '<?php echo $_POST["DepId"]; ?>';
    var sec = '<?php echo $_POST["SecId"]; ?>';

    var DepArray = new Array();
    var SecArray = new Array();

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
                DepArray = myarray;

                for(j=document.getElementById('Department').options.length-1;j>=0;j--)
                {
                    document.getElementById('Department').remove(j);
                }

                // document.getElementById("SectionName").value = "";
                // document.getElementById("SecId").value = "";

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Department',myarray[i][1],myarray[i][0]);
                    updateSectionList();
                }



                var ddl = document.getElementById('Department');

                var i = 0;
                for(i=0; i < ddl.options.length; i++)
                {
                    if(ddl.options[i].value==dep)
                    {
                        ddl.selectedIndex = i;
                        document.getElementById("DepartmentName").value = ddl.options[i].text;
                        document.getElementById("DepId").value = ddl.options[i].value;
                        updateSectionList();
                        break;
                    }
                }
            }
        }

        var url = "updateDepartment.php?fkDivision=" + document.getElementById("Division").value+"&all=true";
        xmlhttp.open("GET",url,true);
        xmlhttp.send();

        var x=document.getElementById("Division").selectedIndex;
        var y=document.getElementById("Division").options;

        document.getElementById("DivisionName").value = y[x].text;
        document.getElementById("DivId").value = y[x].value;

        document.getElementById("DepartmentName").value = "";
        document.getElementById("DepId").value = "";

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
                SecArray = myarray;

                for(j=document.getElementById('Section').options.length-1;j>=0;j--)
                {
                    document.getElementById('Section').remove(j);
                }

                document.getElementById("SectionName").value = "";
                document.getElementById("SecId").value = "";

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Section',myarray[i][1],myarray[i][0]);
                }

                sectionChange();
            }
        }

        var url = "updateSection.php?fkDepartment="+document.getElementById("Department").value+"&all=true";
        //alert(url);
        //document.write(url);
        xmlhttp.open("GET",url,true);
        xmlhttp.send();

        var x=document.getElementById("Department").selectedIndex;
        var y=document.getElementById("Department").options;

        document.getElementById("DepartmentName").value = y[x].text;
        document.getElementById("DepId").value = y[x].value;
        document.getElementById("DepStatus").value = DepArray[x][2];
    }

    function sectionChange()
    {
        var x=document.getElementById("Section").selectedIndex;
        var y=document.getElementById("Section").options;

        document.getElementById("SectionName").value = y[x].text;
        document.getElementById("SecId").value = y[x].value;

        document.getElementById("SecStatus").value = SecArray[x][2];
    }


    function submitForm(formName,text)
    {
        document.getElementById("text").value = text;
        document.getElementById("type").value = formName;
        document.mainForm.submit();
    }

    </script>
</head>


<body onload="updateDepartmentList();">


<!-- container for updating options query -->
<div id="texter" style="display: none;"></div>

<form name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" value="" name="DivId" id="DivId">
<input type="hidden" value="" name="DepId" id="DepId">
<input type="hidden" value="" name="SecId" id="SecId">
<input type="hidden" value="" name="type" id="type">
<input type="hidden" value="" name="text" id="text">
</form>

<div class="alertMessage"><p></p><h4><?php echo $message; ?><p></p></h4></div>


<div class="transbox">
    <table border="0">
        <tr>
            <td colspan="2">
                <b>Divisions:</b>
                
                <select name="Division" id="Division" onchange="updateDepartmentList();" size="1">
                    <?php //get list of divisions and select current value 
                    $result=HRISDB::getInstance()->get_divisions(); 
                    while($row = mysqli_fetch_array($result)) 
                        { ?>
                        <option value="<?php echo $row['pkid'] ?>" <?php if($_POST['DivId']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID=$row[ 'division']) ?></option>
                    <?php } ?>
                </select>
                <br>
                <br>
            </td>
        </tr>
        <tr>
            <td>
                Change Division Name:<br>
                <input type="text" name="text" id="DivisionName" size="40" maxlength="40">
                <br>
                <input type="button" value="Edit Name" name="submit" onclick="submitForm('Change Division Name',document.getElementById('DivisionName').value)">
                <br>
            </td>
            <td>
                Create New Division:<br>
                <input type="text" name="text" id="NewDivisionName" size="40" maxlength="40">
                <br>
                <input type="submit" value="Create" name="submit" onclick="submitForm('Create New Division',document.getElementById('NewDivisionName').value)">
                <br>
            </td>
        </tr>
        <tr>
            <td>
                Create New Department under this Division:<br>
                <input type="text" name="text" id="NewDepartment" size="40" maxlength="40">
                <br>
                <input type="submit" value="Create New Department" name="submit" onclick="submitForm('Create New Department',document.getElementById('NewDepartment').value)">
                <br>
            </td>
        </tr>
    </table>
</div>
<br>


<div class="transbox">
    <table border="0">
        <tr>
            <td colspan="2">
                <b>Departments:</b>
                <select name="Department" id="Department" onchange="updateSectionList();"></select>
                <br>
                <br>

            </td>
        </tr>
        <tr>
            <td>
                Change Department Name:<br>
                <input type="text" name="DepartmentName" id="DepartmentName" size="40" maxlength="40">
                <br>
                <input type="button" value="Edit Name" name="edit Department Name" onclick="submitForm('Change Department Name',document.getElementById('DepartmentName').value)">
            </td>
            <td>
                Status:<br>
                <select name="DepStatus" id="DepStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <br>
                <input type="button" value="Change Status" name="ChangeStatus" onclick="submitForm('Change Department Status',document.getElementById('DepStatus').value)">
                <br>
            </td>
        </tr>

        <tr>
            <td>
                Create New Section under this Department:<br>
                <input type="text" name="CreateSectionName" id="CreateSectionName" size="40" maxlength="40">
                <br>
                <input type="button" value="Create New Section" name="edit Division Name" onclick="submitForm('Create New Section',document.getElementById('CreateSectionName').value)">
                <br>
            </td>
            <td>
            </td>
        </tr>
    </table>
</div>

<br>


<div class="transbox">
    <table border="0">
        <tr>
            <td colspan="2">
                <b>Sections:</b>
                <select name="Section" id="Section" onchange="sectionChange();"></select>
                <br><br>
            </td>
        </tr>
        <tr>
            <td>
                Change Section Name:<br>
                <input type="text" id="SectionName" id="SectionName" size="40" maxlength="40">
                <br>
                <input type="button" value="Edit Name" name="edit Section Name" onclick="submitForm('Change Section Name',document.getElementById('SectionName').value)">
                
            </td>
            <td>
                Status:<br>
                <select name="SecStatus" id="SecStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <br>
                <input type="button" value="Change Status" name="inactiveDepartment" onclick="submitForm('Change Section Status',document.getElementById('SecStatus').value)">
                <br>
            </td>
        </tr>
    </table>
</div>

<div id="adlink" class="adlink">
</div>

</body>