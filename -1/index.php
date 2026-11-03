<?php

session_start();

require_once("../global_includes/hris-oop.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>HRIS System</title>

    <!-- CSS -->
    <link href="src/hris.css" type="text/css" rel="stylesheet" media="all" />

    <!-- JQUERY -->
    <link rel="stylesheet" type="text/css" href="../global_includes/jquery-1/css/custom-theme/jquery-ui-1.8.19.custom.css" />  
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-ui-1.8.19.custom.min.js"></script>

    <!-- table sorter -->
    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>
    <script type="text/javascript">
    
	    function loadjscssfile(filename)
	    {//loads javascript to apply sort table on every result
	      var fileref=document.createElement('script');
	      fileref.setAttribute("type","text/javascript");
	      fileref.setAttribute("src", filename);
	      
	      document.getElementsByTagName("head")[0].appendChild(fileref)
	    }

      function exportResult()
      {
        //alert(document.getElementById("query").innerHTML);

        document.getElementById("SQL").value = document.getElementById("query").innerHTML;
        
        //alert(document.getElementById("SQL").value);

        document.forms["export"].submit();
      }

	  function returnResult()
	  {//ajax
      //alert();
      var field = document.getElementById('field').value;
      var search = document.getElementById('find').value;
      var status = document.getElementById('EmpStatus').value;

      //loading image
      $('#returnValues').html('<p><div align="center"><img src="src/ajax-loader.gif" /><br>Loading...</div></p>');

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
	        document.getElementById("returnValues").innerHTML=xmlhttp.responseText;

	        //reload the javascript
	        loadjscssfile("js.js");
	      }
	    }
	    xmlhttp.open("GET","search.php?search="+search+"&field="+field+"&status="+status,true);
	    xmlhttp.send();
	  }
	 
	//initial search result
	//document.onload=returnResult();


  </script>
</head>

<body onload="returnResult();">
    
    <h2><font color='#1A3380'>HRIS System</font></h2>
    <h3><font color='gray'>Employee List</font></h3>

	<div class="transbox">

    <form name="search" method="post" action="">
    	<br>&nbsp
      Search for: <input type="text" class="search" name="find" id="find" onkeyup="returnResult();"> by
        <select name="field" id="field" onchange="returnResult();">
          <option value="EmpNo">Employee No</option>
          <option value="LastName">Last Name</option>
          <option value="FirstName">First Name</option>
          <option value="MiddleName">Middle Name</option>
          <option value="fkposition">Position</option>
          <option value="fkdivision">Division</option>
          <option value="fkdepartment">Department</option>
          <option value="fksection">Section</option>
        </select>

        &nbsp Employee status: <select id="EmpStatus" name="status" onchange="returnResult(document.getElementById('field').value,document.getElementById('find').value,document.getElementById('EmpStatus').value);">
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="resigned">Resigned</option>
                      </select>

          <input type="button" value="export" onclick="exportResult();">



      <!--
      <input type="hidden" name="searching" value="yes">
      <input type="submit" name="search" id="cmdSearch" value="Search" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
    	-->

    	<br>
    	<br>
    </form>

    <form name="export" action="exportExcel.php" method="POST">
          <input type="hidden" value="" id="SQL" name="SQL">
        </form>

   <div id="returnValues">
   </div>
	</div><!-- end of tansbox -->
</body>
</html>

