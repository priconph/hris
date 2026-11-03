
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
		}
		else
		{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","search.php?search="+search+"&field="+field,true);
		xmlhttp.send();
	}

	returnResult('EmpNo','');
	</script>
</head>

<body>
<div class="styled-select">
	Search for: <input type="text" class="tb12" name="find" id="find" onkeyup="returnResult(document.getElementById('field').value,document.getElementById('find').value);"/> by
	<Select name="field" id="field" onchange = "returnResult(document.getElementById('field').value,document.getElementById('find').value);">
		<Option VALUE="EmpNo">Employee No</option>
		<Option VALUE="LastName">Last Name</option>
		<Option VALUE="FirstName">First Name</option>
		<Option VALUE="MiddleName">Middle Name</option>
	</Select>
</div>

<table width="100%" border="1" class="sorted" id="sort" border="1">

<input type="hidden" name="pkid" value="">

<thead>
<tr>
<th id="empNo">Employee #</th>
<th id="EmpN">Employee Name</th>
<th id="Pos">Position</th>
<th id="Div">Division</th>
<th id="Dep">Department</th>
<th id="Sec">Section</th>
<tr>
</thead>

<tbody>

<div id="txtHint">

	
</div>

</tbody>

</table>
</body>
</html>