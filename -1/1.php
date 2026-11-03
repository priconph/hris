    <html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>HRIS System</title>

    <!-- CSS -->
    <link href="src/hris.css" type="text/css" rel="stylesheet" media="all">

    <!-- JQUERY -->
    <link rel="stylesheet" type="text/css" href="../global_includes/jquery-1/css/custom-theme/jquery-ui-1.8.19.custom.css">
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-ui-1.8.19.custom.min.js"></script>

    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>

    <script type="text/javascript">
    
    function loadjscssfile(filename)
    {
      var fileref=document.createElement('script');
      fileref.setAttribute("type","text/javascript");
      fileref.setAttribute("src", filename);
      

      document.getElementsByTagName("head")[0].appendChild(fileref)
    }

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
        document.getElementById("returnValues").innerHTML=xmlhttp.responseText;
        loadjscssfile("js.js");
                //document.write(xmlhttp.responseText);
      }
    }
    xmlhttp.open("GET","search.php?search="+search+"&field="+field,true);
    xmlhttp.send();

  }
  
returnResult('EmpNo','');
loadjscssfile("js.js");


  </script>
    <style type="text/css">

        tr {border:0;padding:0;margin:0;}
        td, th {border:0;padding:2px 6px;margin:0;border-right:0px solid #336;border-bottom:0px solid #336;}
        td[axis='number'], td[axis='date'] {text-align:right;}
        th {white-space:no-wrap;background-color:#B4C4D1;padding:2px 20px;}
        tfoot td {border-top:1px solid #003;}
        thead th {border-bottom:2px solid #003;}
        .odd td {background-color:#E8ECF1;}
        .even td {background-color:#DDE5EB;}
        .hover td {background-color:#A5B3C9;}
        .sortedminus {background-color:#ecc;}
        .sortedplus {background-color:#cec;}
        .selrow td {background-color:#879AB7;}

    </style>
  </head>

<body>
    
    <h2 class="title_header"><hr><u>HRIS System(Module)</u><hr></h2>


    <form name="search" method="post" action="">
      Search for: <input type="text" class="tb12" name="find" id="find" onkeyup="returnResult(document.getElementById('field').value,document.getElementById('find').value);"> by
        <select name="field" id="field" onchange="returnResult(document.getElementById('field').value,document.getElementById('find').value);">
          <option value="EmpNo">Employee No</option>
          <option value="LastName">Last Name</option>
          <option value="FirstName">First Name</option>
          <option value="MiddleName">Middle Name</option>
          <option value="EmpNo">Employee No</option>
        </select>
      <input type="hidden" name="searching" value="yes">
      <input type="submit" name="search" id="cmdSearch" value="Search" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
    </form>


   <div id="returnValues">
   </div>
   

</body>
</html>