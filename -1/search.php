<?php
$search=$_GET["search"];

require_once("../global_includes/hris-oop.php");

?>

<!-- employee information redirection onclick -->
<form name="employees" action="employeeinfo.php" method="POST">
  <input type="hidden" name="pkid" value="">
</form>


<table width="100%" border="1" class="sorted" id="sort">

<thead>
  <tr>
  <th id="stat" title="click to sort by status">Status</th>
  <th id="empNo" title="click to sort by employee number">Employee #</th>
  <th id="EmpN" title="click to sort by employee name">Employee Name</th>
  <th id="Pos" title="click to sort by position">Position</th>
  <th id="Div" title="click to sort by division">Division</th>
  <th id="Dep" title="click to sort by department">Department</th>
  <th id="Sec" title="click to sort by section">Section</th>
  </tr>
</thead>

<tbody>


<?php

  if($_GET["field"]=="fkposition")
  {
    $counter = 0;
    $resultCtr = HRISDB::getInstance()->search_position($_GET["search"]);
    while($row = mysqli_fetch_array($resultCtr)) $counter++;

    $result = HRISDB::getInstance()->search_position($_GET["search"]);
    $row = mysqli_fetch_array($result);

    $Query = "SELECT * FROM tbl_EmployeeInfo ";
    $Query .= "WHERE (";
    $Query .= " ".$_GET["field"]."=".$row['pkid'];

    while($row = mysqli_fetch_array($result))
    {
      $Query .= " or ".$_GET["field"]."=".$row['pkid'];
    }
    $Query .= ')';

    if($counter == 0) $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]."='-1')";

    //echo $Query;
  }
  else if($_GET["field"]=="fkdivision")
  {
    $SearchQuery = "SELECT * FROM tbl_Division WHERE Division LIKE '%".$_GET["search"]."%'";

    $counter = 0;
    $resultCtr = HRISDB::getInstance()->execute_search_query($SearchQuery);
    while($row = mysqli_fetch_array($resultCtr)) $counter++;

    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    $row = mysqli_fetch_array($result);

    $Query = "SELECT * FROM tbl_EmployeeInfo ";
    $Query .= "WHERE (";
    $Query .= " ".$_GET["field"]."=".$row['pkid'];

    while($row = mysqli_fetch_array($result))
    {
      $Query .= " or ".$_GET["field"]."=".$row['pkid'];
    }
    $Query .= ')';

    if($counter == 0) $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]."='-1')";

    //echo $Query;
  }
  else if($_GET["field"]=="fkdepartment")
  { 
    $SearchQuery = "SELECT * FROM tbl_Department WHERE Department LIKE '%".$_GET["search"]."%'";

    $counter = 0;
    $resultCtr = HRISDB::getInstance()->execute_search_query($SearchQuery);
    while($row = mysqli_fetch_array($resultCtr)) $counter++;

    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    $row = mysqli_fetch_array($result);

    $Query = "SELECT * FROM tbl_EmployeeInfo ";
    $Query .= "WHERE (";
    $Query .= " ".$_GET["field"]."=".$row['pkid'];

    while($row = mysqli_fetch_array($result))
    {
      $Query .= " or ".$_GET["field"]."=".$row['pkid'];
    }
    $Query .= ')';

    if($counter == 0) $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]."='-1')";

    //echo $Query;
  }
  else if($_GET["field"]=="fksection")
  { 
    $SearchQuery = "SELECT * FROM tbl_Section WHERE Section LIKE '%".$_GET["search"]."%'";

    $counter = 0;
    $resultCtr = HRISDB::getInstance()->execute_search_query($SearchQuery);
    while($row = mysqli_fetch_array($resultCtr)) $counter++;

    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    $row = mysqli_fetch_array($result);

    $Query = "SELECT * FROM tbl_EmployeeInfo ";
    $Query .= "WHERE (";
    $Query .= " ".$_GET["field"]."=".$row['pkid'];

    while($row = mysqli_fetch_array($result))
    {
      $Query .= " or ".$_GET["field"]."=".$row['pkid'];
    }
    $Query .= ')';

    if($counter == 0) $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]."='-1')";

    //echo $Query;
  }
  else
  {
    $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]." LIKE '%".$_GET["search"]."%')";
    //echo $Query;
    //$result = HRISDB::getInstance()->search_employeeInfo_by_field($_GET["field"], $_GET["search"]);
  }


  if($_GET['status']=='active')
    $Query .= ' and EmpStatus = 1';
  else if($_GET['status']=='resigned')
    $Query .= ' and EmpStatus = 2';

  echo '<div style="visibility: hidden; display:none" id="query">'.$Query.'</div>';

  $result = HRISDB::getInstance()->execute_search_query($Query);



  $resultCounter = 0;
  while($row = mysqli_fetch_array($result))
  {
    $position = HRISDB::getInstance()->get_position_name_by_id($row['fkPosition']);
    $department = HRISDB::getInstance()->get_department_name_by_id($row['fkDepartment']);
    $division = HRISDB::getInstance()->get_division_name_by_id($row['fkDivision']);
    $section = HRISDB::getInstance()->get_section_name_by_id($row['fkSection']);


    if($row['EmpStatus'] == 1){$image = 'active.png'; $toolTip = 'active';}
    if($row['EmpStatus'] == 2){$image = 'resigned.png';  $toolTip = 'resigned';}
    
    echo '<tr>';
    echo '<td axis="string" headers="stat" align="center" title="'.$toolTip.'"><img src="src/'.$image.'"/><div style="visibility: hidden; display:none">'.$image.'</div></td>';
    echo '<td axis="string" headers="empNo" align="center">'.$row['EmpNo'].'</td>';
    echo '<td axis="string" headers="EmpN">'.$row['LastName'].', '.$row['FirstName'].' ('.$row['MiddleName'].')</td>';
    echo '<td axis="string" headers="Pos">'.$position. '</td>';
    echo '<td axis="string" headers="Div">' .$division.'</td>';
    echo '<td axis="string" headers="Dep">' .$department. '</td>';
    echo '<td axis="string" headers="Sec">' .$section.'</td>';
    echo '<td style="display:none" id="pkid">' .$row['pkid']. '</td>';
    echo '</tr>';
    ?>

    <?php
    $resultCounter++;
  }
?>

</tbody>

<?php
//if no result is found
if($resultCounter==0)
{
  ?><tfoot><td colspan="7" align="center"><font color="red">no results for '<?php echo $_GET["search"] ?>'</font></td></tfoot><?php
}
else
{
  ?><tfoot><td colspan="7" align="center"><?php echo $resultCounter ?> result<?php if($resultCounter>1) echo 's'; ?></td></tfoot><?php
}
?>

</table>
