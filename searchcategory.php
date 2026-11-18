<?php
$fkCategory = intval($_GET['fkCategory']);
// $fkEmployee = intval($_GET['fkEmployee']);

require_once __DIR__ . '/../global_includes/hris-oop.php';

?>
<!DOCTYPE html>
<html>
<head>
<style>
table {
    width: 50%;
    border-collapse: collapse;
}

th {
text-align: center;
}
</style>
</head>
<body>
<table>
	<thead>
	<tr>
			<th style="display:none;"><center>pkid</center></th>		
			<th><center>201 File Category</center></th>		
			<th><center>File Name</center></th>
			<th><center>View</center></th>
	</tr>
	</thead>
	<tbody>
	<?php			
									
		// $query = HRISDB::getInstance()->get_searchcategory($fkCategory, $fkEmployee);
		// $query = HRISDB::getInstance()->get_searchcategory($fkCategory, $fkEmployee);
		$query = HRISDB::getInstance()->get_searchcategory($fkCategory);
		
		if($query->num_rows == 0) {
			echo '<tr><td colspan="3"><center><i>No Record found.</i></center></td></tr>';
		} else {
			while($row = mysqli_fetch_array($query)) {
				if($row['fkCategory'] == 1) {
					$empfile_category = '<center>Employee Application Form</center>';
				} else if($row['fkCategory'] == 2) {
					$empfile_category = '<center>Examination Result</center>';
				} else if($row['fkCategory'] == 3) {
					$empfile_category = '<center>Pre-Employement Requirements</center>';
				} else if($row['fkCategory'] == 4) {
					$empfile_category = '<center>Medical Result</center>';
				} else if($row['fkCategory'] == 5) {
					$empfile_category = '<center>Appointment Paper</center>';
				} else if($row['fkCategory'] == 6) {
					$empfile_category = '<center>Certificate Internal</center>';
				} else if($row['fkCategory'] == 7) {
					$empfile_category = '<center>Certificate External</center>';
				} else if($row['fkCategory'] == 8) {
					$empfile_category = '<center>Employment History</center>';
				}
			
				echo '<tr align="center">';
				echo '<td style="display:none" id="pkid">'.$row['pkid'].'</td>';
				echo '<td>'.$empfile_category.'</td>';
				echo '<td>'.$row['file_name'].'</td>';
				// echo '<td><a href="pdfuploads/'.$row['pkid'].'" target="_blank">View File</a></td>';
		        echo '<td><a href="pdfuploads/'.$row['pkid'].'" target="_blank">View File</a></td>';
				echo '</tr>';
			}
		}
				

	?>
	</tbody>
</table>

</body>
</html>
