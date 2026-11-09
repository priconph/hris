<?php
	require_once("includes.php");
	require_once("global_includes/hris-oop.php");
	require_once 'db_hris.php';
	
?>
<div id="Viewpage"></div>

<iframe src="<?php echo 'pdfuploads/'. $_GET['pkid'] .'.pdf' ?>#toolbar=0&navpanes=0&scrollbar=0"  width="100%" height="100%" ></iframe> 

<script type="text/javascript">
	var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	load_dimmer(w,h);
	function load_dimmer(w,h){
		var width = w - (w * 0.02);		
		var height = h - (h * 0.01);
		
		var left = w * .80;
		var top = h * .87;
	   // $('#Viewpage').attr("style","position:fixed;padding:0;margin:0;top:"+'15px'+";left: "+'0'+";width: "+width+"px;height: "+(height-20)+"px;background:rgba(255,255,100,0.5);opacity: 0;overflow-x:auto;overflow-y:auto;");
	   $('#Viewpage').attr("style","position:fixed;padding:0;margin:0;top:"+'9px'+";left: "+'5px'+";width: "+width+"px;height: "+height+"px;background:rgba(105,255,255,0.5);opacity: 0;overflow-x:auto;overflow-y:auto;");
	}
			
	$('#Viewpage').click(function(){
		//alert("SAVING AND PRINTING IS NOT ALLOWED!");
	});
	
	/* Create a validation if javascript is not active on browser */
	$(window).resize(function() {
		var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
		var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
		load_dimmer(w,h);
	});
	
	(function() {
		var beforePrint = function() {
			console.log('Functionality to run before printing.');
			alert("PRINTING IS NOT ALLOWED!");
			window.location.reload();
		};
		
		var afterPrint = function() {
			console.log('Functionality to run after printing');
		};

		if (window.matchMedia) {
			var mediaQueryList = window.matchMedia('print');
			mediaQueryList.addListener(function(mql) {
				if (mql.matches) {
					beforePrint();
				} else {
					afterPrint();
				}
			});
		}
		window.onbeforeprint = beforePrint;
		window.onafterprint = afterPrint;
	}());

	/* function to disable right click */
	$(this).bind("contextmenu", function(e) {
        e.preventDefault();
    });

</script>