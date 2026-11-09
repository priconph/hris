<?php

session_start();

$search = $_GET["search"] ?? '';
$field = $_GET["field"] ?? '';
$statusFilter = $_GET["status"] ?? '';

require_once("../global_includes/hris-oop.php");

?>

<!-- employee information redirection onclick -->
<form name="employees" action="employeeinfo.php" method="POST">
  <input type="hidden" name="pkid" value="">
</form>


<table width="100%" border="1" class="sorted" id="sort">

<thead>
  <tr>
  <th id="stat" class="status" title="click to sort by status" align = 'center'>Status</th>
  <th id="empNo" class="empNumber" title="click to sort by employee number" align = 'center'>Employee Number</th>
  <th id="EmpN" title="click to sort by employee name" align = 'center'>Employee Name</th>
  <th id="Pos" title="click to sort by position" align = 'center'>Position</th>
  <th id="Div" title="click to sort by division" align = 'center'>Division</th>
  <th id="Dep" title="click to sort by department" align = 'center'>Department</th>
  <th id="Sec" title="click to sort by section" align = 'center'>Section</th>
  </tr>
</thead>

<tbody>


<?php
  //CONCAT(LastName, ', ', firstName, ', (', MiddleName,')') as FullName

  $_SESSION['search'] = $search;
  $_SESSION['field'] = $field;
  $_SESSION['status'] = $statusFilter;

  $db = HRISDB::getInstance();

  // lookup of pkids for the fk* fields (position / division / department / section)
  $lookups = array(
    'fkdivision'   => array('tbl_Division', 'Division'),
    'fkdepartment' => array('tbl_Department', 'Department'),
    'fksection'    => array('tbl_Section', 'Section')
  );

  $pkids = null;
  if($field == "fkposition")
  {
    //get all pkids of positions
    $lookup = $db->search_position($search);
    $pkids = array();
    while($row = mysqli_fetch_array($lookup))
    {
      $pkids[] = $row['pkid'];
    }
  }
  else if(isset($lookups[$field]))
  {
    //get all pkids of divisions / departments / sections
    $lookup = $db->search_lookup_pkids($lookups[$field][0], $lookups[$field][1], $search);
    $pkids = array();
    while($row = mysqli_fetch_array($lookup))
    {
      $pkids[] = $row['pkid'];
    }
  }

  if($statusFilter == 'active')
  {
    $status = 1;
  }
  else if($statusFilter == 'resigned')
  {
    $status = 2;
  }
  else if($statusFilter == 'rehired')
  {
    $status = 3;
  }
  else
  {
    $status = null;
  }

  // legacy executable SQL string kept in the session (read by excel.php / excel_2.php); values are escaped
  $esc = $db->real_escape_string($search);
  if($field == "FullName")
    $where = " WHERE (firstname like '%".$esc."%' OR lastname like '%".$esc."%' or middlename like '%".$esc."%')";
  else if($pkids !== null)
  {
    if(count($pkids) > 0)
      $where = " WHERE (" . implode(' or ', array_map(function($id) use ($field) { return " ".$field."=".(int)$id; }, $pkids)) . ") ";
    else
      $where = " WHERE (".$field."='-1') ";
  }
  else
    $where = " WHERE (".$field." LIKE '%".$esc."%') ";
  $select = "SELECT *, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName FROM tbl_EmployeeInfo ";
  $statusSql = ($status === null) ? '' : ' and EmpStatus = '.$status;
  $legacySql = $select . $where . $statusSql . " ORDER BY EmpNo ASC";

  // throws on an invalid column name, so $legacySql is only stored for a validated $field
  $result = $db->search_employees_list($field, $search, $pkids, $status);
  $_SESSION['SQL'] = $legacySql;

  $resultCounter = 0;
  try
  {
    while($row = mysqli_fetch_array($result))
    {
      $position = $db->get_position_name_by_id($row['fkPosition']);
      $department = $db->get_department_name_by_id($row['fkDepartment']);
      $division = $db->get_division_name_by_id($row['fkDivision']);
      $section = $db->get_section_name_by_id($row['fkSection']);


      if($row['EmpStatus'] == 1){$image = 'active.png'; $toolTip = 'active';}
      else if($row['EmpStatus'] == 2){$image = 'resigned.png';  $toolTip = 'resigned';}
      else if($row['EmpStatus'] == 3){$image = 'active.png';  $toolTip = 'rehired';}
      else {$image = 'resigned.png';  $toolTip = 'incomplete data';}

      echo '<tr>';
      //echo '<td></td>';
      echo '<td align = "center" axis="string" headers="stat" title="'.$toolTip.'"><img src="src/'.$image.'"/><div style="visibility: hidden; display:none">'.$image.'</div></td>';
      echo '<td align = "center" axis="string" headers="empNo">'.$row['EmpNo'].'</td>';
      echo '<td align = "center" axis="string" headers="EmpN">'.$row['FullName'].'</td>';
      echo '<td align = "center" axis="string" headers="Pos">'.$position.'</td>';
      echo '<td align = "center" axis="string" headers="Div">'.$division.'</td>';
      echo '<td align = "center" axis="string" headers="Dep">'.$department.'</td>';
      echo '<td align = "center" axis="string" headers="Sec">'.$section.'</td>';
      echo '<td align = "center" style="display:none" id="pkid">'.$row['pkid'].'</td>';
      echo '</tr>';
      ?>

      <?php
      $resultCounter++;
    }
  }
  catch (Exception $e)
  {
    echo $e->getMessage();
  }
?>

</tbody>

<?php
//if no result is found
if($resultCounter==0)
{
  ?><tfoot><td colspan="7" align="center"><font color="red">no results for '<?php echo htmlspecialchars($search) ?>'</font></td></tfoot><?php
}
else
{
  ?><tfoot><td colspan="7" align="center"><?php echo $resultCounter ?> result<?php if($resultCounter>1) echo 's'; ?></td></tfoot><?php
}
?>

</table>
