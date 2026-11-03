<?php

$border = 1;
$empno = 2;

require_once("../global_includes/hris-oop.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if ($_POST["formtype"]=="Offensesave")
        {
            //update disciplinary info
            HRISDB::getInstance()->update_disciplinary($_POST["Action"],
                $_POST["NatureOfOffense"],
                $_POST["FrequencyOfOffense"],
                $_POST["NotificationDate"],
                $_POST["EffectivityDate"],
                $_POST["Offensepkid"]);
        }
        else if ($_POST["formtype"]=="Offensedelete")
        {
            //delete disciplinary
            HRISDB::getInstance()->delete_disciplinary($_POST["pkid"]);
        }
        else if ($_POST["formtype"]=="Offenseadd")
        {
            //create disciplinary info
            HRISDB::getInstance()->create_disciplinary($_POST["Action"],
                $_POST["NatureOfOffense"],
                $_POST["FrequencyOfOffense"],
                $_POST["NotificationDate"],
                $_POST["EffectivityDate"],
                $empno);
        }
    }
?>


<html>
<head>

    <title>HRIS System</title>
    <!-- CSS -->
    <link href="src/hris.css" type="text/css" rel="stylesheet" media="all" />

    <!-- JQUERY -->
    <link rel="stylesheet" type="text/css" href="../global_includes/jquery-1/css/custom-theme/jquery-ui-1.8.19.custom.css" />  
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../global_includes/jquery-1/js/jquery-ui-1.8.19.custom.min.js"></script>

    <!-- table sorter -->
    <script type="text/javascript" src="includes/Event.js"></script>
    <script type="text/javascript" src="includes/SortedTable.js"></script>

    <!-- javascript codes -->
    <script type="text/javascript">

    $(function() {
        
        //delete offense confirmation dialog
        $("#dialogDeleteOffense").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open offense dialog 
        $("#OffenseDialogopener").click(function() {
            $("#dialogDeleteOffense").dialog("open");
            return false;
        });
        //close offense dialog box
        $("#OffenseDialogcloser").click(function() {
            $("#dialogDeleteOffense").dialog("close");
            return false;
        });
        //datetpicker for dependent
        $("#datepickerDepBday").datepicker();
        $( "#datepickerDepBday" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
    });

    onload = function()
    {
        var myOffenseTable = new SortedTable();

        myOffenseTable.onselect = function(element)
        {
            //get values on the table and put into row[][] variable
            var $table = $("#sortedOffense"),
              $headerCells = $table.find("thead th"),
              $rows = $table.find("tbody tr");
            var headers = [], rows = [];
            $headerCells.each(function(k,v)
            {
                headers[headers.length] = $(this).text();
            });
            $rows.each(function(row,v)
            {
            $(this).find("td").each(function(cell,v)
            {
              if (typeof rows[cell] === 'undefined') rows[cell] = [];
              rows[cell][row] = $(this).text();
            });
            });

            //inser all values of selected dependent into the textboxes
            document.EmployeeInfo['Action'].value = rows[6][this.elementIndex(element)];
            document.EmployeeInfo['NatureOfOffense'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['FrequencyOfOffense'].value = rows[7][this.elementIndex(element)];
            document.EmployeeInfo['EffectivityDate'].value = rows[3][this.elementIndex(element)];
            document.EmployeeInfo['NotificationDate'].value = rows[4][this.elementIndex(element)];
            document.EmployeeInfo['Offensepkid'].value = rows[5][this.elementIndex(element)];

            //hide add button
            document.EmployeeInfo['Offensedeletes'].style.display = 'inline-block';
            document.EmployeeInfo['Offenseadds'].style.display = 'none';
            document.EmployeeInfo['Offenseoks'].style.display = 'inline-block';

            validateOffenseFields();

        }
        myOffenseTable.ondeselect = function(element)
        {
            //remove values
            document.EmployeeInfo['Action'].value = "1";
            document.EmployeeInfo['NatureOfOffense'].value = "";
            document.EmployeeInfo['FrequencyOfOffense'].value = "1";
            document.EmployeeInfo['EffectivityDate'].value = "";
            document.EmployeeInfo['NotificationDate'].value = "";
            document.EmployeeInfo['Offensepkid'].value = "";

            //hide delete and ok button, show add button
            document.EmployeeInfo['Offensedeletes'].style.display = 'none';
            document.EmployeeInfo['Offenseadds'].style.display = 'inline-block';
            document.EmployeeInfo['Offenseoks'].style.display = 'none';
        }
    }

    var error = false;

    function validateTextBox(textbox, alerts)
    {
        if(""==document.forms.EmployeeInfo[textbox].value){
           document.getElementById(alerts).innerHTML = '*'; error = true;
        }
        else document.getElementById(alerts).innerHTML = '';
    }

    function validateOffenseFields()
    {
        error = false;
        //validateTextBox('Action', 'AlertAction');
        validateTextBox('NotificationDate', 'AlertNotificationDate');
        validateTextBox('NatureOfOffense', 'AlertNatureOfOffense');
        validateTextBox('EffectivityDate', 'AlertEffectivityDate');
        //validateTextBox('FrequencyOfOffense', 'AlertAction');
        if(error)
        {
            document.getElementById('AlertOffense').innerHTML = 'Please fill up the required fields';
        }
        else
        {
            document.getElementById('AlertOffense').innerHTML = '';
        }
        return error;   
    }

    function Offenseok()
    {
        if(!validateOffenseFields())
        {

            document.EmployeeInfo['formtype'].value = "Offensesave";
            //alert('sdg');
            document.EmployeeInfo.submit();
            //alert('sdg');
        }
    }

    function Offenseadd()
    {
        //alert('add');
        if(!validateOffenseFields())
        {
            document.EmployeeInfo['formtype'].value = "Offenseadd";
            document.EmployeeInfo.submit();
        }
    }

    function Offensedelete()
    {
        document.EmployeeInfo['formtype'].value = "Offensedelete";
        document.EmployeeInfo.submit();
    }

    </script>

    <style type="text/css">

    table {border:0;padding:0;margin:0 0 1em;border-left:1px solid #336;border-top:1px solid #336;float:left;clear:left;}
    tr {border:0;padding:0;margin:0;}
    td, th {border:0;padding:2px 6px;margin:0;border-right:1px solid #336;border-bottom:1px solid #336;background-color:#EAEEF3;}
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
    
    <!-- yes no dialog -->
    <div id="dialogDeleteOffense" title="Delete Offense">
        <p>Are you sure you want to delete this Offense?</p>
        <input type="button" id="button" name="YesDeleteOffense" value="Yes" onclick="Offensedelete()"/>
        <input type="button" id="OffenseDialogcloser" name="no" value="No"/>
    </div>

    <form name="EmployeeInfo" action="offense.php" method="POST">
        <table class="transbox" border="<?php echo $border ?>">
            <tr>
                <td>
                    <!-- list of offense of Offense -->

                    <table class="sorted" id="sortedOffense" border="1">
                        <thead>
                            <tr>
                                <th id="action" class="sortedplus">Action</th>
                                <th id="nature" >Nature</th>
                                <th id="freq" >Frequency of Offense</th>
                                <th id="effect" >Effectivity</th>
                                <th id="notif" >Notification</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $result = HRISDB::getInstance()->get_disciplinary_by_employee_no($empno);
                            
                            while($row = mysqli_fetch_array($result)):
                                ?>
                                <tr>
                                <td axis="string" headers="action"><?php 

                                switch ($row['Action']) {
                                    case '1': echo 'Written Warning'; break;
                                    case '2': echo '3 days suspension'; break;
                                    case '3': echo '6 days suspension'; break;
                                    case '4': echo '12 days suspension'; break;
                                    case '5': echo '4 days suspension'; break;
                                    case '6': echo '5 days suspension'; break;
                                    case '7': echo '7 days suspension'; break;
                                    case '8': echo '8 days suspension'; break;
                                    case '9': echo '9 days suspension'; break;
                                    case '10': echo '10 days suspension'; break;
                                    case '11': echo '11 days suspension'; break;
                                    case '12': echo '13 days suspension'; break;
                                    case '13': echo '14 days suspension'; break;
                                    case '14': echo '15 days suspension'; break;
                                    case '15': echo '16 days suspension'; break;
                                    case '16': echo '17 days suspension'; break;
                                    case '17': echo '18 days suspension'; break;
                                    case '18': echo '19 days suspension'; break;
                                    case '19': echo '20 days suspension'; break;
                                    case '20': echo '21 days suspension'; break;
                                    case '21': echo '22 days suspension'; break;
                                    case '22': echo '23 days suspension'; break;
                                    case '23': echo '24 days suspension'; break;
                                    case '24': echo '25 days suspension'; break;
                                    case '25': echo '26 days suspension'; break;
                                    case '26': echo '27 days suspension'; break;
                                    case '27': echo '28 days suspension'; break;
                                    case '28': echo '29 days suspension'; break;
                                    case '29': echo '30 days suspension'; break;
                                    case '30': echo 'dismissal'; break;
                                    default: echo ''; break;
                                }
                                ?></td>
                                <td axis="string" headers="nature"><?php echo $row['Nature'] ?></td>
                                <td axis="string" headers="freq"><?php 

                                switch ($row['Frequency'] ) {
                                    case '1': echo '1st'; break;
                                    case '2': echo '2nd'; break;
                                    case '3': echo '3rd'; break;
                                    case '4': echo '4th'; break;
                                    case '5': echo '5th'; break;
                                    case '6': echo '6th'; break;
                                    default: break;
                                }
                                ?></td>
                                <td axis="string" headers="effect"><?php echo $row['Effectivity'] ?></td>
                                <td axis="string" headers="notif"><?php echo $row['Notification'] ?></td>
                                <td style="display:none" id="pkid"><?php echo $row['pkid'] ?></td>
                                <td style="display:none" id="actionNo"><?php echo $row['Action'] ?></td>
                                <td style="display:none" id="frequenqyNo"><?php echo $row['Frequency'] ?></td>
                                <td style="display:none" id="fkemployee"><input type="text" name="fkEmpNo" value="<?php echo $row['fkEmpNo'] ?>"/></td>
                                
                                </tr>
                                <?php
                            endwhile;
                            //mysql_free_result($result);
                            ?>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table border="2">
                        <tr>
                            <td>Action</td>
                            <td>
                                <select name="Action">
                                    <option value = "1">Written Warning</option>
                                    <option value = "2">3 days suspension</option>
                                    <option value = "3">6 days suspension</option>
                                    <option value = "4">12 days suspension</option>
                                    <option value = "5">4 days suspension</option>
                                    <option value = "6">5 days suspension</option>
                                    <option value = "7">7 days suspension</option>
                                    <option value = "8">8 days suspension</option>
                                    <option value = "9">9 days suspension</option>
                                    <option value = "10">10 days suspension</option>
                                    <option value = "11">11 days suspension</option>
                                    <option value = "12">13 days suspension</option>
                                    <option value = "13">14 days suspension</option>
                                    <option value = "14">15 days suspension</option>
                                    <option value = "15">16 days suspension</option>
                                    <option value = "16">17 days suspension</option>
                                    <option value = "17">18 days suspension</option>
                                    <option value = "18">19 days suspension</option>
                                    <option value = "19">20 days suspension</option>
                                    <option value = "20">21 days suspension</option>
                                    <option value = "21">22 days suspension</option>
                                    <option value = "22">23 days suspension</option>
                                    <option value = "23">24 days suspension</option>
                                    <option value = "24">25 days suspension</option>
                                    <option value = "25">26 days suspension</option>
                                    <option value = "26">27 days suspension</option>
                                    <option value = "27">28 days suspension</option>
                                    <option value = "28">29 days suspension</option>
                                    <option value = "29">30 days suspension</option>
                                    <option value = "30">dismissal</option>
                                </select>
                            </td>
                            <td>Notification Date</td>
                            <td>
                                <input type="text" name="NotificationDate" id="datepickerNotif" class="date"/>
                                <font color="red"><text id='AlertNotificationDate'></text></font>
                            </td>
                        </tr>

                        <tr>
                            <td>Nature of Offense</td>
                            <td>
                                <input type="text" name="NatureOfOffense"/>
                                <font color="red"><text id='AlertNatureOfOffense'></text></font>
                            </td>
                            <td>Effectivity Date</td>
                            <td>
                                <input type="text" name="EffectivityDate" id="datepickerEffect" class="date"/>
                                <font color="red"><text id='AlertEffectivityDate'></text></font>
                            </td>
                        </tr>

                        <tr>
                            <td>Frequency of Offense</td>
                            <td>
                                <select name="FrequencyOfOffense">
                                    <option value="1">1st</option>
                                    <option value="2">2nd</option>
                                    <option value="3">3rd</option>
                                    <option value="4">4th</option>
                                    <option value="5">5th</option>
                                    <option value="6">6th</option>
                                </select>
                            </td>
                            <td colspan="2">
                                <font color="red"><text id='AlertOffense'></text></font><br>
                                <input type="hidden" name="Offensepkid" value="">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- remove later -->
                    <input type="hidden" name="formtype" value="">
                    <input type="button" id="button" name="Offenseoks" value="Save" onclick="Offenseok()"/>
                    <input type="button" id="button" name="Offenseadds" value="Add" onclick="Offenseadd()"/>
                    <input type="button" id="OffenseDialogopener" name="Offensedeletes" value="Delete"/>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>


































