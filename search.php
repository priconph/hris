<?php

session_start();

// array values (?search[]=x) would break string functions on PHP 8; treat them as empty
$search = (isset($_GET["search"]) && is_string($_GET["search"])) ? $_GET["search"] : '';
$field = (isset($_GET["field"]) && is_string($_GET["field"])) ? $_GET["field"] : '';
$statusFilter = (isset($_GET["status"]) && is_string($_GET["status"])) ? $_GET["status"] : '';

// escape a value for HTML output (null-safe); prefixed so it cannot clash with global_includes
function search_e($v)
{
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../global_includes/hris-oop.php';

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

  $resultCounter = 0;
  try
  {
    // $field is a column name (cannot be bound): only the columns offered by the dropdown in index.php are accepted
    $allowedFields = array('EmpNo', 'FullName', 'LastName', 'FirstName', 'MiddleName', 'fkposition', 'fkdivision', 'fkdepartment', 'fksection');
    if (!in_array($field, $allowedFields, true))
    {
      throw new InvalidArgumentException('Invalid search field');
    }
    $result = $db->search_employees_list($field, $search, $pkids, $status);
    // validated parameters for excel.php / excel_2.php, which rebuild the export query with bound parameters
    // (the old $_SESSION['SQL'] raw SQL string is no longer written)
    $_SESSION['SQL_PARAMS'] = array(
      'field'  => $field,
      'search' => $search,
      'pkids'  => ($pkids === null) ? null : array_map('intval', $pkids),
      'status' => $status
    );

    while($result && ($row = mysqli_fetch_array($result)))
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
      echo '<td align = "center" axis="string" headers="empNo">'.search_e($row['EmpNo']).'</td>';
      echo '<td align = "center" axis="string" headers="EmpN">'.search_e($row['FullName']).'</td>';
      echo '<td align = "center" axis="string" headers="Pos">'.search_e($position).'</td>';
      echo '<td align = "center" axis="string" headers="Div">'.search_e($division).'</td>';
      echo '<td align = "center" axis="string" headers="Dep">'.search_e($department).'</td>';
      echo '<td align = "center" axis="string" headers="Sec">'.search_e($section).'</td>';
      echo '<td align = "center" style="display:none" id="pkid">'.search_e($row['pkid']).'</td>';
      echo '</tr>';
      ?>

      <?php
      $resultCounter++;
    }
  }
  catch (Throwable $e)
  {
    // never show SQL or exception text in the browser
    error_log('search.php: ' . $e->getMessage());
    echo 'A database error occurred. Please try again or contact your administrator.';
  }
?>

</tbody>

<?php
//if no result is found
if($resultCounter==0)
{
  ?><tfoot><td colspan="7" align="center"><font color="red">no results for '<?php echo search_e($search) ?>'</font></td></tfoot><?php
}
else
{
  ?><tfoot><td colspan="7" align="center"><?php echo $resultCounter ?> result<?php if($resultCounter>1) echo 's'; ?></td></tfoot><?php
}
?>

</table>
