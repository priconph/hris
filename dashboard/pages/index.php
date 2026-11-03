<?php
/* Include employee information */
require_once('get_employee_username.php');
$directory = explode("/",dirname(__FILE__));

?>
<input type="hidden" id="hidden_username" value="<?php echo $username; ?>">
<input type="hidden" id="hidden_date_today" value="<?php echo date('Y-m-d'); ?>">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>PMI HRIS</title>
	
	<!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../../../media/font_awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>

<body>	
	<div id="swrapper">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Home</h1>
			</div>
			<!-- /.col-lg-12 -->
		</div>
		<!-- /.row -->
		<?php 
			require_once('../class/oop.php');
            
            $array_fields  = array('modname');
			$table 	   	= 'vw_access_list';
			$joins 	   	= '';
			$sql_where 	= 'WHERE system_folder="'.$directory[3].'" AND NOT modname LIKE "%Dashboard%" AND username="'.$username.'"';
			$sql_order 	= 'ORDER BY modname';
			$sql_limit 	= '';
			$result 	= OOP::getInstance()->select_query($array_fields,$table,$joins,$sql_where,$sql_order,$sql_limit);
			// echo OOP::getInstance()->select_query_script($array_fields,$table,$joins,$sql_where,$sql_order,$sql_limit);
			while($row = mysqli_fetch_array($result)) {
				$array_fields_app_mods  = array('modname', 'system', 'mod_link','dashboard_name','total_tooltip','link_tooltip','system_icon');
				$table_app_mods 	   	= 'vw_mod_list';
				$joins_app_mods 	   	= '';
				$sql_where_app_mods 	= 'WHERE modname="'.$row['modname'].'"';
				$sql_order_app_mods 	= 'ORDER BY dashboard_name';
				$sql_limit_app_mods 	= '';
				$result_app_mods = OOP::getInstance()->select_query($array_fields_app_mods,$table_app_mods,$joins_app_mods,$sql_where_app_mods,$sql_order_app_mods,$sql_limit_app_mods);
				// echo OOP::getInstance()->select_query_script($array_fields_app_mods,$table_app_mods,$joins_app_mods,$sql_where_app_mods,$sql_order_app_mods,$sql_limit_app_mods);
				while($row_mods=mysqli_fetch_array($result_app_mods)) {
					// echo $row_mods['mod_link'].'<br>';
					echo '
						<div class="col-sm-3" onclick="location.href=\''.$row_mods['mod_link'].'\';">
							<div class="panel panel-primary" data-toggle="tooltip" data-placement="bottom" title="'.$row_mods['link_tooltip'].'" id="div_srs_pending_approver">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-3">
											<i class="fa fa-'.$row_mods['system_icon'].' fa-5x"></i>
										</div>
										<div class="col-xs-9 text-right">
											<div class="huge" data-placement="bottom" title="'.$row_mods['total_tooltip'].'">&nbsp;</div>
											<div>'.$row_mods['dashboard_name'].'</div>
										</div>
									</div>
								</div>
								<a href="'.$row_mods['mod_link'].'" id="">
									<div class="panel-footer">
										<span class="pull-left">View Request Details</span>
										<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
					';
				}
			}
			
			
			
		?>
	</div>
</body>
<!-- Call user defined functions common functions -->
<script src="../../../common_functions/common_functions.js" type="text/javascript"></script>
<script src="../../../media/js/jquery.js"></script>

<script type="text/javascript">
	var username 				= $('#hidden_username').val();
	var handler 				= '../handler/handler.php';
</script>	
	
	
	
	