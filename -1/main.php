<?php

require_once("../global_includes/hris-oop.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>HRIS System</title>

    <script type="text/javascript">


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



        //updateSectionList();

    }


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
</head>

<body>
    


   <div id="returnValues">
    </div>


<form name="employee">



<table>

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
</tr>

<tr>
    <td>Department:</td>
    <td>
        <select name="Department" id="Department" onchange="updateSectionList();" size="1">
        </select>
    </td>
</tr>
<tr>
    <td>Section:</td>
    <td>
        <select name="Section" id="Section">
        </select>
    </td>
</tr>
</table>
</form>

<div id="texter" style="display: none;"></div>
</body>
</html>
