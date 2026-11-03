<?php

session_start();

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

  $_SESSION['search'] = $_GET["search"];
  $_SESSION['field'] = $_GET["field"];
  $_SESSION['status'] = $_GET["status"];

  $select = "SELECT *, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName FROM tbl_EmployeeInfo ";
  
  if($_GET["field"]=="fkposition")
  {
    //get all pkids of positions
    $result = HRISDB::getInstance()->search_position($_GET["search"]);

    if(mysqli_num_rows($result)>0)
    {
      $row = mysqli_fetch_array($result);

      //append all found pkids to the new search query
      $where = " WHERE (";
      $where .= " ".$_GET["field"]."=".$row['pkid'];

      while($row = mysqli_fetch_array($result))
      {
        $where .= " or ".$_GET["field"]."=".$row['pkid'];
      }
      $where .= ') ';
    }
    else
      $where = " WHERE (".$_GET["field"]."='-1') ";

  }
  else if($_GET["field"]=="fkdivision")
  {
    $SearchQuery = "SELECT * FROM tbl_Division WHERE Division LIKE '%".$_GET["search"]."%'";

    //get all pkids of divisions
    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    if(mysqli_num_rows($result)>0)
    {
      $row = mysqli_fetch_array($result);

      //append all found pkids to the new search query
      $where = " WHERE (";
      $where .= " ".$_GET["field"]."=".$row['pkid'];

      while($row = mysqli_fetch_array($result))
      {
        $where .= " or ".$_GET["field"]."=".$row['pkid'];
      }
      $where .= ') ';
    }
    else
    $where = " WHERE (".$_GET["field"]."='-1') ";
  }
  else if($_GET["field"]=="fkdepartment")
  { 
    $SearchQuery = "SELECT * FROM tbl_Department WHERE Department LIKE '%".$_GET["search"]."%'";

    //get all pkids of divisions
    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    if(mysqli_num_rows($result)>0)
    {
      $row = mysqli_fetch_array($result);

      //append all found pkids to the new search query
      $where = " WHERE (";
      $where .= " ".$_GET["field"]."=".$row['pkid'];

      while($row = mysqli_fetch_array($result))
      {
        $where .= " or ".$_GET["field"]."=".$row['pkid'];
      }
      $where .= ') ';
    }
    else
    $where = " WHERE (".$_GET["field"]."='-1') ";
  }
  else if($_GET["field"]=="fksection")
  {
    $SearchQuery = "SELECT * FROM tbl_Section WHERE Section LIKE '%".$_GET["search"]."%'";

    //get all pkids of divisions
    $result = HRISDB::getInstance()->execute_search_query($SearchQuery);
    if(mysqli_num_rows($result)>0)
    {
      $row = mysqli_fetch_array($result);

      //append all found pkids to the new search query
      $where = " WHERE (";
      $where .= " ".$_GET["field"]."=".$row['pkid'];

      while($row = mysqli_fetch_array($result))
      {
        $where .= " or ".$_GET["field"]."=".$row['pkid'];
      }
      $where .= ') ';
    }
    else
    $where = " WHERE (".$_GET["field"]."='-1') ";
  }
  else if($_GET["field"]=="FullName")
  {
    $where = " WHERE (firstname like '%".$_GET["search"]."%' OR lastname like '%".$_GET["search"]."%' or middlename like '%".$_GET["search"]."%')";
    $Query = "SELECT * FROM tbl_EmployeeInfo WHERE (firstname like '%".$_GET["search"]."%' OR lastname like '%".$_GET["search"]."%' or middlename like '%".$_GET["search"]."%')";
    //$Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]." LIKE '%".$_GET["search"]."%')";
  }
  else
  {
    //$Query = "SELECT * FROM tbl_EmployeeInfo WHERE (".$_GET["field"]." LIKE '%".$_GET["search"]."%')";
    $where = " WHERE (".$_GET["field"]." LIKE '%".$_GET["search"]."%') ";
    //echo $Query;
    //$result = HRISDB::getInstance()->search_employeeInfo_by_field($_GET["field"], $_GET["search"]);
  }


  if($_GET['status']=='active')
  {
    $status = ' and EmpStatus = 1';
  }
  else if($_GET['status']=='resigned')
  {
    $status = ' and EmpStatus = 2';
  }
  else if($_GET['status']=='rehired')
  {
    $status = ' and EmpStatus = 3';
  }
  else
  {
    $status = '';
  }

  $Query = $select . $where . $status." ORDER BY EmpNo ASC";

  //echo $Query; //uncomment to view the query every search result

  session_start();
  $_SESSION['SQL'] = $Query;
  //die($_SESSION['SQL']);
  $result = HRISDB::getInstance()->execute_search_query($Query);

  $resultCounter = 0;
  try
  {
    while($row = mysqli_fetch_array($result))
    {
      $position = HRISDB::getInstance()->get_position_name_by_id($row['fkPosition']);
      $department = HRISDB::getInstance()->get_department_name_by_id($row['fkDepartment']);
      $division = HRISDB::getInstance()->get_division_name_by_id($row['fkDivision']);
      $section = HRISDB::getInstance()->get_section_name_by_id($row['fkSection']);


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
  ?><tfoot><td colspan="7" align="center"><font color="red">no results for '<?php echo $_GET["search"] ?>'</font></td></tfoot><?php
}
else
{
  ?><tfoot><td colspan="7" align="center"><?php echo $resultCounter ?> result<?php if($resultCounter>1) echo 's'; ?></td></tfoot><?php
}
?>

</table>
