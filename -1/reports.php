<?php

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$_POST[''];
}

?>


<link rel="stylesheet" type="text/css" href="jsmenu/ddsmoothmenu.css" />
<link rel="stylesheet" type="text/css" href="jsmenu/ddsmoothmenu-v.css" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="jsmenu/ddsmoothmenu.js">

</script>

<script type="text/javascript">

ddsmoothmenu.init({
	mainmenuid: "smoothmenu1", //menu DIV id
	orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu', //class added to menu's outer DIV
	//customtheme: ["#1c5a80", "#18374a"],
	contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
})

ddsmoothmenu.init({
	mainmenuid: "smoothmenu2", //Menu DIV id
	orientation: 'v', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu-v', //class added to menu's outer DIV
	//customtheme: ["#804000", "#482400"],
	contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
})

</script>


<head>
<script type="text/javascript">


</script>
</head>

<body>



<a href="http://users.tpg.com.au/j_birch/plugins/superfish/#sample5">link</a>

<div id="smoothmenu1" class="ddsmoothmenu">
    <ul>
        <li>
            <a>Reports</a>
            <ul>
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
                            <a href="excel.php?Custom=LengthOfService">Employee Length of Service</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="excel.php?Custom=Philhealth">Employee Philhealth number</a>
                </li>
                <li>
                    <a href="excel.php?Custom=TIN">Employee Tax Identification Number</a>
                </li>
                <li>
                    <a href="excel.php?Custom=SSS">Employee SSS number</a>
                </li>
                <li>
                    <a href="excel.php?Custom=Pagibig">Employee Pag-Ibig Number</a>
                </li>
                <li>
                    <a href="excel.php?Custom=Address">Employee Address</a>
                </li>
                <li>
                    <a href="excel.php?Custom=MealStub">Meal Stub recieving</a>
                </li>
                <li>
                    <a href="excel.php?Custom=MonthlyBirthday">Monthly birthday Celebrant</a>
                </li>
                <li>
                    <a href="excel.php?Custom=PerformanceRating">Performance rating Summary</a>
                </li>
                <li>
                    <a href="excel.php?Custom=ResignedEmployees">Rsigned employees</a>
                </li>
                <li>
                    <a href="excel.php?Custom=RaffleStub">Raffle Stub</a>
                </li>
                <li>
                    <a href="excel.php?Custom=ShuttleService">Shuttle Service Survey</a>
                </li>
                <li>
                    <a href="excel.php?Custom=ManpowerClassification">Manpower classification</a>
                </li>
                <li>
                    <a href="CustomReport.php">Custom Reports</a>
                </li>
            </ul>
        </li>
    </ul>
    </divs>



</body>