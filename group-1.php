
<?php
	require_once("../global_includes/hris-oop.php");
?>


<script type="text/javascript">

var Departments = new Array (<?php
	//get all departments and put in javascript array
	$result = HRISDB::getInstance()->get_active_departments_all_columns();

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
	$result = HRISDB::getInstance()->get_active_sections_all_columns();

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

</script>


<select name="Division" id="Division" onchange="updateDepartmentList();" size="1" style="width:300px">                                  
    <?php //get list of divisions and select current value 
    $result=HRISDB::getInstance()->get_divisions(); 
    while($row = mysqli_fetch_array($result)) 
        { ?>
        <option value="<?php echo $row['pkid'] ?>" <?php if($_POST['DivId']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID=$row[ 'division']) ?></option>
    <?php } ?>
</select>
<br>
<select name="Department" id="Department" onchange="updateSectionList();" size="1" style="width:300px">
</select>
<br>
<select name="Section" id="Section" style="width:300px">
</select>
<br><br><br><br>



<select name="historyDivision" id="historyDivision" onchange="updateDepartmentListHistory();" size="1" style="width:300px">                                  
    <?php //get list of divisions and select current value 
    $result=HRISDB::getInstance()->get_divisions(); 
    while($row = mysqli_fetch_array($result)) 
        { ?>
        <option value="<?php echo $row['pkid'] ?>" <?php if($_POST['DivId']==$row['pkid']){echo 'selected="true"';} ?>><?php echo htmlentities($systemID=$row[ 'division']) ?></option>
    <?php } ?>
</select>
<br>
<select name="historyDepartment" id="historyDepartment" onchange="updateSectionListHistory();" size="1" style="width:300px">
</select>
<br>
<select name="historySection" id="historySection" style="width:300px">
</select>
<br>