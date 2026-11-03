<?php

	session_start();
	require_once("../global_includes/hris-oop.php");
	if(!isset($_SESSION['username'])){
		echo "Please login to systemone!";
		exit;
	}
	
	//1112024 by Nessa
	$username = $_SESSION['username'];
	// echo $username;
	$valid_username = HRISDB::getInstance()->get_valid_ip($username);
		
	if(!$valid_username){
        echo '<span style="color:red;"><br><br><br><br><br><br><br><br><br><br><br><br><center><h1>WARNING!<br> YOU ARE NOT ALLOWED TO VIEW THIS PAGE!</h1></center></span';
        exit;
    }

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
    
    <!-- dropdown menu -->
    <link rel="stylesheet" type="text/css" href="jsmenu/ddsmoothmenu.css" />
    <link rel="stylesheet" type="text/css" href="jsmenu/ddsmoothmenu-v.css" />
    <script type="text/javascript" src="jsmenu/ddsmoothmenu.js"></script>

    <!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script> -->
  
    <!-- table sorter -->
    <script type="text/javascript" src="includes/jquery-ui.min.js"></script>
    <script type="text/javascript" src="includes/jquery.min.js"></script>
    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>
    <script type="text/javascript">

      //for report dropdown menu
      ddsmoothmenu.init({
        mainmenuid: "smoothmenu1",
        orientation: 'h', 
        classname: 'ddsmoothmenu', 
        contentsource: "markup" 
      })

      ddsmoothmenu.init({
        mainmenuid: "smoothmenu2", 
        orientation: 'v', 
        classname: 'ddsmoothmenu-v',
        contentsource: "markup" 
      })

      jQuery.noConflict();
    
	    function loadjscssfile(filename)
	    {
        //loads javascript to apply sort table on every result
	      var fileref=document.createElement('script');
	      fileref.setAttribute("type","text/javascript");
	      fileref.setAttribute("src", filename);
	      
	      document.getElementsByTagName("head")[0].appendChild(fileref)
	    }

      //get field and status through php
      var field = '<?php echo $_SESSION['field'] ?>';
      var status = '<?php echo $_SESSION['status'] ?>';

      function exportResult()
      {
        document.getElementById("SQL").value = document.getElementById("query").innerHTML;
        document.forms["export"].submit();
      }

	  function returnResult()
	  {
      //ajax get result
      var field = document.getElementById('field').value;
      var search = document.getElementById('find').value;
      var status = document.getElementById('EmpStatus').value;

      //loading circular image
      $('#returnValues').html('<p><div align="center"><img src="src/ajax-loader.gif" /><br>Loading...</div></p>');

	    if (window.XMLHttpRequest)
	    {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
	    }
	    else
	    {
        // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    xmlhttp.onreadystatechange=function()
	    {
	      if (xmlhttp.readyState==4 && xmlhttp.status==200)
	      {
	        document.getElementById("returnValues").innerHTML=xmlhttp.responseText;

          // console.log(xmlhttp.responseText);
	        //reload the javascript to reapply the table styles
	        loadjscssfile("js/js.js");
	      }
	    }
	    xmlhttp.open("GET","search.php?search="+search+"&field="+field+"&status="+status,true);
	    xmlhttp.send();
	  }

    function firstLoad()
    {
      if (field!='')
      {
        document.getElementById("field").value = field;
      }
      if (status!='')
      {
        document.getElementById("EmpStatus").value = status;
      }
    }


    $(document).ready(function() {
        $("#dialog").dialog({
            autoOpen: true,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
    });

       
  $(document).ready(function() {
    $("#dialog").dialog();
  });


  /*

  $(function() {
    $("#monthInputdialog").dialog({ disabled: true });
    $("#resignedEmployeedialog").dialog();
  });


  //form for date to and from report


  <div id="resignedEmployeedialog" title="Select Date start and End">
    <p>date start, date end</p>
      <input type="button" id="button" name="from date" value="input Date from" class="ui-button ui-widget ui-state-default ui-corner-all"/>
      <input type="button" id="closer" name="to date" value="input Date to" class="ui-button ui-widget ui-state-default ui-corner-all"/>
  </div>

  <div id="dialog" title="Delete Dependent">
      
  </div>


  */
  </script>
</head>

<!-- search on document load -->
<body onload="firstLoad();returnResult();">
   
    <h2><font color='#1A3380'>HRIS System</font></h2>
    <h3><font color='gray'>Employee List</font></h3>

	<div class="transbox">

    <div class="searchdiv">

    <form name="search" method="post" action="">
    	<br>&nbsp;
      Search for: <input type="text" class="search" name="find" id="find" onkeyup="returnResult();" value="<?php echo $_SESSION['search']; ?>"> by
        
        <select name="field" class="comboBox" id="field" onchange="returnResult();">
          <option value="EmpNo">Employee No</option>
          <option value="FullName">Full Name</option>
          <option value="LastName">Last Name</option>
          <option value="FirstName">First Name</option>
          <option value="MiddleName">Middle Name</option>
          <option value="fkposition">Position</option>
          <option value="fkdivision">Division</option>
          <option value="fkdepartment">Department</option>
          <option value="fksection">Section</option>
        </select>

        &nbsp; Employee status: <select class="comboBox" id="EmpStatus" name="status" onchange="returnResult(document.getElementById('field').value,document.getElementById('find').value,document.getElementById('EmpStatus').value);">
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="resigned">Resigned</option>
          <option value="rehired">Rehired</option>
        </select>

    </form>
    </div>
    <div class="reportdiv">
      <!-- list for reports -->
          <div id="smoothmenu1" class="ddsmoothmenu">
            <ul>
                <li>
                    <a>Reports</a>
                    <ul>
                      <li>
                            <a href="excel.php?Custom=exportResult">Export This Result</a>
                      </li>
                      <li>
                            <a>Company Profile</a>
                            <ul>
                                <li>
                                    <a href="excel.php?Custom=Age">Employee Age</a>
                                </li>
                                <li>
                                    <a href="excel.php?Custom=CivilStatus">Civil Status</a>
                                </li>
                                <li>
                                    <a href="excel.php?Custom=EducAttain">Employee Educational Attainment</a>
                                </li>
                                <li>
                                    <a href="excel.php?Custom=LengthOfService">Employee Length of Service (Default)</a>
                                </li>
								<li>
                                    <a href="excel.php?Custom=LengthOfServiceCO">Employee Length of Service (Cut-off)</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="excel.php?Custom=Philhealth">Employee Philhealth Number</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=TIN">Employee Tax Identification Number</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=SSS">Employee SSS Number</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=Pagibig">Employee Pag-Ibig Number</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=Address">Employee Address</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=MealStub">Meal Stub Recieving</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=MonthlyBirthday">Monthly Birthday Celebrant</a>
                            <ul>
                              <?php
                                //print individual months
                                for ($i=1; $i < 13; $i++)
                                {
                                  //get months
                                  $timestamp = mktime(0, 0, 0, $i, 1, 2012);
                                  $month = date("M", $timestamp);
                                  echo '<li><a href="excel.php?Custom=MonthlyBirthday&Month='.$i.'">'.$month.'</a></li>';
                                }
                              ?>
                            </ul>
                        </li>
                        <li>
                            <a href="excel.php?Custom=PerformanceRating">Performance Rating Summary</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=ResignedEmployees">Resigned Employees</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=RaffleStub">Raffle Stub</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=ShuttleService">Shuttle Service Survey</a>
                        </li>
                        <li>
                            <a href="excel.php?Custom=ManpowerCountdown">Manpower Countdown</a>
                        </li>
								<li>
                            <a href="excel.php?Custom=ManpowerRoster">Manpower Roster</a>
                        </li>
                    </ul>
                </li>
            </ul>
            </div>
          </div>


   <div id="returnValues" class="returnValues">
   </div>

	</div>
  <!-- end of tansbox -->
  <form name="export" action="excel.php" method="POST">
    <input type="hidden" value="" id="SQL" name="SQL">
  </form>

</body>
</html>
