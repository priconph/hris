<?php

$border = 0;
$empno = 1;

require_once("../global_includes/hris-oop.php");

function GetAge($bday)
{
    $date = new DateTime($bday);
    $diff = $date->diff(new DateTime());

    echo $diff->y.' years, '.$diff->m.' months, '.$diff->d.' days';
}

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        //get value of tax dependent
        if($_POST["TaxDependent"]=='yes') $taxdep = 1; else $taxdep = 0;

        //get value of relationship
        switch ($_POST["Relationship"]) {
            case 'Parent':
                $relationship = 1;
                break;
            case 'Husband':
                $relationship = 2;
                break;
            case 'Wife':
                $relationship = 3;
                break;
            case 'Brother':
                $relationship = 4;
                break;
            case 'Sister':
                $relationship = 5;
                break;
            case 'Son':
                $relationship = 6;
                break;
            case 'Daughter':
                $relationship = 7;
                break;
            case 'Adopted Child':
                $relationship = 8;
                break;
            default:
                $relationship = 0;
                break;
        }//end case

        if ($_POST["formtype"]=="Dependentok")
        {
            HRISDB::getInstance()->update_dependent($_POST["DepLastName"],
                $_POST["DepFirstName"],
                $_POST["DepMiddleName"],
                $_POST["DepDateOfBirth"],
                $relationship,
                $taxdep,
                $_POST["pkid"]);
        }
        else if ($_POST["formtype"]=="Dependentdelete")
        {
            HRISDB::getInstance()->delete_dependent($_POST["pkid"]);
        }
        else if ($_POST["formtype"]=="Dependentadd")
        {
            HRISDB::getInstance()->create_dependent($_POST["DepLastName"],
                $_POST["DepFirstName"],
                $_POST["DepMiddleName"],
                $empno,
                $_POST["DepDateOfBirth"],
                $relationship, '0',
                $taxdep);
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
        $("#dialog").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open dialog confirmation of deletion
        $("#opener").click(function() {
            $("#dialog").dialog("open");
            return false;
        });
        //close dialog box
        $("#closer").click(function() {
            $("#dialog").dialog("close");
            return false;
        });
        //datetpicker
        $("#datepickerDepBday").datepicker();//"option", "dateFormat", "yy-mm-dd");
        $( "#datepickerDepBday" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
    });
    
    onload = function() {
        var myTable = new SortedTable();

        document.Dependent['deletes'].style.display = 'none';
        document.Dependent['adds'].style.display = 'inline-block';
        document.Dependent['oks'].style.display = 'none';

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

            //inser all values into the textboxes
            document.Dependent['DepLastName'].value = rows[0][this.elementIndex(element)];
            document.Dependent['DepFirstName'].value = rows[1][this.elementIndex(element)];
            document.Dependent['DepMiddleName'].value = rows[2][this.elementIndex(element)];
            document.Dependent['DepDateOfBirth'].value = rows[3][this.elementIndex(element)];
            document.Dependent['Relationship'].value = rows[4][this.elementIndex(element)];
            document.Dependent['TaxDependent'].value = rows[5][this.elementIndex(element)];
            document.Dependent['DepAge'].value = rows[6][this.elementIndex(element)];
            document.Dependent['pkid'].value = rows[7][this.elementIndex(element)];

            document.Dependent['deletes'].style.display = 'inline-block';
            document.Dependent['adds'].style.display = 'none';
            document.Dependent['oks'].style.display = 'inline-block';

            validateFields();
        }
        myTable.ondeselect = function(element)
        {
            //remove values
            document.Dependent['DepLastName'].value = "";
            document.Dependent['DepFirstName'].value = "";
            document.Dependent['DepMiddleName'].value = "";
            document.Dependent['DepDateOfBirth'].value = "";
            document.Dependent['Relationship'].value = "Parent";
            document.Dependent['TaxDependent'].value = "no";
            document.Dependent['DepAge'].value = "";
            document.Dependent['pkid'].value = "";

            document.Dependent['deletes'].style.display = 'none';
            document.Dependent['adds'].style.display = 'inline-block';
            document.Dependent['oks'].style.display = 'none';

        }
    }
    //javascript trim function
    String.prototype.trim = function()
    {
        return this.replace(/^\s+|\s+$/g,"");
    }

    function validateDependentFields()
    {
        //trim spaces
        document.Dependent['DepLastName'].value = document.Dependent['DepLastName'].value.trim();
        document.Dependent['DepFirstName'].value = document.Dependent['DepFirstName'].value.trim();
        document.Dependent['DepMiddleName'].value = document.Dependent['DepMiddleName'].value.trim();
        //javascript validation of fields
        var error = false;
        if (document.Dependent['DepLastName'].value == "")
        {
            document.getElementById('AlertLastName').innerHTML = '*';
            error = true;
        }
        else
            document.getElementById('AlertLastName').innerHTML = '';
        if (document.Dependent['DepFirstName'].value == "")
        {
            document.getElementById('AlertFirstName').innerHTML = '*';
            error = true;
        }
        else
            document.getElementById('AlertFirstName').innerHTML = '';
        if (document.Dependent['DepMiddleName'].value == "")
        {
            document.getElementById('AlertMiddleName').innerHTML = '*';
            error = true;
        }
        else
            document.getElementById('AlertMiddleName').innerHTML = '';
        if (document.Dependent['DepDateOfBirth'].value == "")
        {
            document.getElementById('AlertDOBirth').innerHTML = '*';
            error = true;
        }
        else
            document.getElementById('AlertDOBirth').innerHTML = '';

        if(error)
        {
            document.getElementById('Alert').innerHTML = 'Please fill up the required fields';
        }
        else
        {
            document.getElementById('Alert').innerHTML = '';
        }

        return error;
    }

    function ok()
    {
        if(!validateDependentFields())
        {
            document.Dependent['formtype'].value = "Dependentok";
            document.Dependent.submit();
        }
    }

    function add()
    {
        if(!validateDependentFields())
        {
            document.Dependent['formtype'].value = "Dependentadd";
            document.Dependent.submit();
        }
    }

    function deletes()
    {
        document.Dependent['formtype'].value = "Dependentdelete";
        document.Dependent.submit();
    }

    function onlyNumbers(evt)
    {
        var e = event || evt; 
        var charCode = e.which || e.keyCode;

        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }

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
    
    <!-- yes no dialog -->
    <div id="dialog" title="Delete Dependent">
        <p>Are you sure you want to delete this dependent?</p>
        <input type="button" id="button" name="YesDeleteDependent" value="Yes" onclick="deletes()"/>
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
                                <th id="ln" class="sortedplus">Last Name</th>
                                <th id="fn">First Name</th>
                                <th id="mn">Middle Name</th>
                                <th id="dob">Birthday</th>
                                <th id="rel">Relationship</th>
                                <th id="dep">Tax Exempt</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            
                            $result = HRISDB::getInstance()->get_dependents_by_employee_id($empno);
                            
                            while($row = mysqli_fetch_array($result)):
                                ?>
                                <tr>
                                <td axis="string" headers="ln"><?php echo $row['LastName'] ?></td>
                                <td axis="string" headers="fn"><?php echo $row['FirstName'] ?></td>
                                <td axis="string" headers="mn"><?php echo $row['MiddleName'] ?></td>
                                <td axis="number" headers="dob"><?php echo $row['DoBirth'] ?></td>

                                <?php
                                //get corresponding value for relationship
                                switch ($row['Relationship']) {
                                    case '1':
                                        $relationship = 'Parent';
                                        break;
                                    case '2':
                                        $relationship = 'Husband';
                                        break;
                                    case '3':
                                        $relationship = 'Wife';
                                        break;
                                    case '4':
                                        $relationship = 'Brother';
                                        break;
                                    case '5':
                                        $relationship = 'Sister';
                                        break;
                                    case '6':
                                        $relationship = 'Son';
                                        break;
                                    case '7':
                                        $relationship = 'Daughter';
                                        break;
                                    case '8':
                                        $relationship = 'Adopted Child';
                                        break;
                                    default:
                                        $relationship = '';
                                        break;
                                }

                                ?>
                                <td axis="string" headers="rel"><?php echo $relationship ?></td>

                                <?php
                                //get corresponding value for tax dependent
                                if($row['TaxDependent']==1)
                                    $taxdep = yes;
                                else
                                    $taxdep = no;
                                ?>

                                <td axis="string" headers="dep"><?php echo $taxdep ?></td>
                                <td id="age" style="display:none"><?php echo GetAge($row['DoBirth']) ?></td>
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

            <form name="Dependent" method="POST">
            <tr>
                <td>
                    <table border="<?php echo $border ?>">
                        <tr>
                            <td>Lastname:</td>
                            <td>
                                <input type="text" name="DepLastName"/>
                                <font color="red"><text id='AlertLastName'></text></font>
                            </td>
                            <td>Date of Birth</td>
                            <td>
                                <input type="text" name="DepDateOfBirth" id="datepickerDepBday"/>
                                <font color="red"><text id='AlertDOBirth'></text></font>
                            </td>
                        </tr>

                        <tr>
                            <td>Firstname:</td>
                            <td>
                                <input type="text" name="DepFirstName"/>
                                <font color="red"><text id='AlertFirstName'></text></font>
                            </td>
                            <td>Age</td>
                            <td><input type="text" name="DepAge" readonly="readonly" size="30"/></td>
                        </tr>

                        <tr>
                            <td>Middlename:</td>
                            <td>
                                <input type="text" name="DepMiddleName"/>
                                <font color="red"><text id='AlertMiddleName'></text></font>
                            </td>
                            <td>Relationship</td>
                            <td>
                                <select name="Relationship">
                                    <option>Parent</option>
                                    <option>Husband</option>
                                    <option>Wife</option>
                                    <option>Brother</option>
                                    <option>Sister</option>
                                    <option>Son</option>
                                    <option>Daughter</option>
                                    <option>Adopted Child</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>Tax Dependent</td>
                            <td>
                                <select name="TaxDependent">
                                    <option>no</option>
                                    <option>yes</option>
                                </select>
                            </td>
                            <td colspan="2">
                                <font color="red"><text id='Alert'></text></font>
                                <input type="hidden" name="pkid" value="">
                                <input type="hidden" name="formtype" value="">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- ok add and delete are not valid names -->
                    <input type="button" id="button" name="oks" value="Ok" onclick="ok()"/>
                    <input type="button" id="button" name="adds" value="Add" onclick="add()"/>
                    <input type="button" id="opener" name="deletes" value="Delete"/>
                </td>
            </tr>
        </form>
    </table>
</body>
</html>