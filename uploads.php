<?php
include_once 'db_hris.php';
require_once("../global_includes/hris-oop.php");

$valid_formats = array("pdf", "PDF");
$max_file_size = 1000000*2; // 2MB 
// $max_file_size = 1024*10000; // 2MB 
$pdf_file = 'pdfuploads/';
$count = 0;

if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
	// Loop $_FILES to execute all files
	foreach ($_FILES['files']['name'] as $f => $name) {     
	    if ($_FILES['files']['error'][$f] == 4) {
	        continue; // Skip file if any error found
	    }	       
	    if ($_FILES['files']['error'][$f] == 0) {	           
	        if ($_FILES['files']['size'][$f] > $max_file_size) {
				echo "$name is too large! File size must not exceed 2 MB";
	            continue; // Skip large files
	        }
			
			elseif( ! in_array(pathinfo($name, PATHINFO_EXTENSION), $valid_formats) ){
				echo "$name is not a valid format! \n Format must in .PDF only!";			
				continue; // Skip invalid file formats
			}
				 
			 elseif (file_exists("pdfuploads/" . $name))
				{
				// file already exists error
				echo "$name have already uploaded!";			
				continue; // Skip existing file 
				}  
				
			 else{ // No error found! Move uploaded files 
      
			if(move_uploaded_file($_FILES["files"]["tmp_name"][$f], $pdf_file.$name)) {
			$sql="INSERT INTO tbl_upload_file(file_name,fkEmployee,fkCategory) VALUES('$name','".$_POST['fkEmployee']."','".$_POST['fkCategory']."')";
			mysql_query($sql);
			
				 $query = HRISDB::getInstance()->get_renamefile($name, $fkEmployee, $fkCategory);
				 while($row = mysqli_fetch_array($query)){
				 	$pkid = $row['pkid'];

				
				rename($pdf_file.$name,  $pdf_file.$pkid.'.pdf');
								
				$count++; // Number of successfully uploaded files
				echo "$name File successfully uploaded!  \n\n";
				} 
			} 
		 
	        } 
	    }
	}
}

?>


