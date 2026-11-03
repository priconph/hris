<?php

$border = 1;
$empno = 1;

require_once("../global_includes/hris-oop.php");


    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if ($_POST["formtype"]=="Offensesave")
        {
            HRISDB::getInstance()->update_disciplinary($_POST["DepLastName"],
                $_POST["DepFirstName"],
                $_POST["DepMiddleName"],
                $_POST["DepDateOfBirth"],
                $_POST["pkid"]);
        }
        else if ($_POST["formtype"]=="Offensedelete")
        {
            HRISDB::getInstance()->delete_disciplinary($_POST["pkid"]);
        }
        else if ($_POST["formtype"]=="Offenseadd")
        {
            HRISDB::getInstance()->create_disciplinary($_POST["DepLastName"],
                $_POST["DepFirstName"],
                $_POST["DepMiddleName"],
                $_POST["DepDateOfBirth"],
                $EmployeeNumber);
        }
    }
?>

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

    $(function()
    {

        $("#dialogDeleteOffense").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open dialog confirmation of deletion
        $("#openerDeleteOffense").click(function() {
            $("#dialog").dialog("open");
            return false;
        });
        //close dialog box
        $("#closerDeleteOffense").click(function() {
            $("#dialog").dialog("close");
            return false;
        });

        $(function() {
        $( "#datepickerNotif" ).datepicker();
        $( "#datepickerNotif" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });

        $(function() {
        $( "#datepickerEffect" ).datepicker();
        $( "#datepickerEffect" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
    
    onload = function() {

        alert('dgsd');
        var myTable = new SortedTable();

        document.Dependent['Offensedelete'].style.display = 'none';
        document.Dependent['Offenseadd'].style.display = 'inline-block';
        document.Dependent['Offenseok'].style.display = 'none';

        myTable.onselect = function(element)
        {
            var $table = $("#sorted"),
              $headerCells = $table.find("thead th"),
              $rows = $table.find("tbody tr");
            var headers = [],
              rows = [];
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

            //insert all values into the textboxes, while selecting dependents
            document.Dependent['Action'].value = rows[0][this.elementIndex(element)];
            document.Dependent['NotificationDate'].value = rows[1][this.elementIndex(element)];
            document.Dependent['NatureOfOffense'].value = rows[2][this.elementIndex(element)];
            document.Dependent['EffectivityDate'].value = rows[3][this.elementIndex(element)];
            document.Dependent['FrequencyOfOffense'].value = rows[4][this.elementIndex(element)];
            document.Dependent['pkid'].value = rows[7][this.elementIndex(element)];

            document.Dependent['Offensedelete'].style.display = 'inline-block';
            document.Dependent['Offenseadd'].style.display = 'none';
            document.Dependent['Offenseok'].style.display = 'inline-block';

            validateFields();
        }
        myTable.ondeselect = function(element)
        {
            //remove values
            document.Dependent['Action'].value = "";
            document.Dependent['NotificationDate'].value = "";
            document.Dependent['NatureOfOffense'].value = "";
            document.Dependent['EffectivityDate'].value = "";
            document.Dependent['FrequencyOfOffense'].value = "";
            document.Dependent['pkid'].value = "";

            document.Dependent['Offensedelete'].style.display = 'none';
            document.Dependent['Offenseadd'].style.display = 'inline-block';
            document.Dependent['Offenseok'].style.display = 'none';

        }
    }
    function validateTextBox(textbox, alert)
    {
        if(""==document.forms.EmployeeInfo[textbox].value){
            document.getElementById(alert).innerHTML = '*'; error = true;
        }
        else document.getElementById(alert).innerHTML = '';
    }

    var error = false;

    function validateDependentFields()
    {
        
        //validation of fields
        error = false;
        validateTextBox('NotificationDate', 'AlertNotificationDate');
        validateTextBox('NatureOfOffense', 'AlertNatureOfOffense');
        validateTextBox('EffectivityDate', 'AlertEffectivityDate');

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

        if(!validateDependentFields())
        {
            document.Dependent['formtype'].value = "Offenseok";
            document.Dependent.submit();
        }
    }

    function Offenseadd()
    {
        if(!validateDependentFields())
        {
            document.Dependent['formtype'].value = "Offenseadd";
            document.Dependent.submit();
        }
    }

    function Offensedelete()
    {
        document.Dependent['formtype'].value = "Offensedelete";
        document.Dependent.submit();
    }

    </script>
    <style type="text/css">

        /*
        document styles 
        .disclaimer {border-top:1px solid #ccc;color:#879AB7;padding-top:.5em;font-size:.9em;}
        dl {margin:0 1em;padding:0;}
        dl, form {float:left;}
        li {padding:.1em 0;}
        hr {clear:both;width:100%;background:#fff;height:0;border:0;border-bottom:1px solid #fff;margin:0 0 1em;padding:0;}
        */

        /* table styles */

        /*
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
        */

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
    
    <!-- yes no dialog -->
    <div id="dialog" title="Delete Dependent">
        <p>Are you sure you want to delete this dependent?</p>
        <input type="button" id="button" name="YesDeleteDependent" value="Yes" onclick="Offensedelete()"/>
        <input type="button" id="closer" name="no" value="No"/>
    </div>

    <h4 class="group_title">Dependent Family</h4>

        <table class="transbox" border="<?php echo $border ?>">
            
            <tr>
                <td>
                    <!-- code for listing of dependents -->

                    <table class="sorted" id="sorted" border="1">
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
                                <td axis="string" headers="action"><?php echo $row['Action'] ?></td>
                                <td axis="string" headers="nature"><?php echo $row['FirstName'] ?></td>
                                <td axis="string" headers="freq"><?php echo $row['MiddleName'] ?></td>
                                <td axis="string" headers="effect"><?php echo $row['DoBirth'] ?></td>
                                <td axis="string" headers="notif"><?php echo $row['DoBirth'] ?></td>
                                
                                <td axis="string" headers="rel"><?php echo $relationship ?></td>

                                <td axis="string" headers="dep"><?php echo $taxdep ?></td>
                                <td style="display:none" id="pkid"><?php echo $row['pkid'] ?></td>
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
                                <input type="text" name="NotificationDate" id="datepickerNotif"/>
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
                                <input type="text" name="EffectivityDate" id="datepickerEffect"/>
                                <font color="red"><text id='AlertNatureOfOffense'></text></font>
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
                            <td colspan="2"><font color="red"><text id='AlertOffense'></text></font><br></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- ok add and delete are not valid names -->
                    <input type="button" id="button" name="Offenseok" value="Ok" onclick="Offenseok()"/>
                    <input type="button" id="button" name="Offenseadd" value="Add" onclick="Offenseadd()"/>
                    <input type="button" id="openerDeleteOffense" name="Offensedelete" value="Delete"/>
                </td>
            </tr>
        </table>
</body>
</html>