<?php

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

    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>

    <script type="text/javascript">
    
	$(function() 
	{
		$( "#cmdSearch").button();
	});
	function returnResult(field,search)
	{
		if (window.XMLHttpRequest)
		{// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
            xmlhttp2=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            xmlhttp2=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				document.getElementById("returnValues").innerHTML=xmlhttp.responseText;
                //document.write(xmlhttp.responseText);

                var headID = document.getElementsByTagName("head")[0];  
                var newScript = document.createElement('script');
                newScript.type = 'text/javascript';
                newScript.onload=scriptLoaded;
                newScript.src = 'includes/table.js';
                headID.appendChild(newScript);
			}
		}
		xmlhttp.open("GET","search.php?search="+search+"&field="+field,true);
		xmlhttp.send();

        // xmlhttp2.onreadystatechange=function()
        // {
        //     if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
        //     {
        //         var headID = document.getElementsByTagName("head")[0];
        //         var cssNode = document.createElement('script');
        //         cssNode.type = 'text/javascript';
        //         cssNode.text = xmlhttp2.responseText;
        //         headID.appendChild(cssNode);
        //         //document.getElementById("returnValues").innerHTML=xmlhttp2.responseText;
        //         //document.write(xmlhttp.responseText);


        //         var headID = document.getElementsByTagName("head")[0];         
        //         var newScript = document.createElement('script');
        //         newScript.type = 'text/javascript';
        //         newScript.onload=scriptLoaded;
        //         newScript.src = 'includes/table.js';
        //         headID.appendChild(newScript);

        //     }
        // }
        // xmlhttp2.open("GET","js.php",true);
        // xmlhttp2.send();
	}

    function alertContents() {
        if (http_request.readyState == 4)
        {
            //alert(http_request.responseText);
            
            result = http_request.responseText;
            document.getElementById('myspan').innerHTML = result; 
            var ob = document.getElementsByTagName("script");
            for(var i=0; i<ob.length-1; i++)
            {
                if(ob[i+1].text!=null) eval(ob[i+1].text);
            }
        }
    }




	returnResult('EmpNo','');


//fix adding javascript source every ajax request




	</script>
    <style type="text/css">

        tr {border:0;padding:0;margin:0;}
        td, th {border:0;padding:2px 6px;margin:0;border-right:0px solid #336;border-bottom:0px solid #336;}
        td[axis='number'], td[axis='date'] {text-align:right;}
        th {white-space:no-wrap;background-color:#B4C4D1;padding:2px 20px;}
        tfoot td {border-top:1px solid #003;}
        thead th {border-bottom:2px solid #003;}
        .odd td {background-color:#E8ECF1;}
        .even td {background-color:#DDE5EB;}
        .hover td {background-color:#A5B3C9;}
        .sortedminus {background-color:#ecc;}
        .sortedplus {background-color:#cec;}
        .selrow td {background-color:#879AB7;}

    </style>
</head>

<body>
    
    <h2 class="title_header"><hr><u>HRIS System(Module)</u><hr></h2>



	 <div class="styled-select">
		<form name="search" method="post" action="<?=$PHP_SELF?>">
			Search for: <input type="text" class="tb12" name="find" id="find" onkeyup="returnResult(document.getElementById('field').value,document.getElementById('find').value);"/> by
				<Select name="field" id="field" onchange = "returnResult(document.getElementById('field').value,document.getElementById('find').value);">
					<Option VALUE="EmpNo">Employee No</option>
					<Option VALUE="LastName">Last Name</option>
					<Option VALUE="FirstName">First Name</option>
					<Option VALUE="MiddleName">Middle Name</option>
					<Option VALUE="EmpNo">Employee No</option>
				</Select>
			<input type="hidden" name="searching" value="yes" />
			<input type="submit" name="search" id="cmdSearch" value="Search" />
		</form>
	 </div>
	 <div id="returnValues">
	 </div>
	 
</body>
</html>