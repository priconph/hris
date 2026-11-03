
document.title = document.title<?php echo '+": ['.$EmpInfo['EmpNo'].'] '.$EmpInfo['LastName'].', '.$EmpInfo['FirstName'].'"'; ?>;

    //variable for validating fields
    var error = false;

    function validateTextBox(textbox, alerts)
    {
        if(""==document.forms.EmployeeInfo[textbox].value){
            document.getElementById(alerts).innerHTML = '*'; error = true;
        }
        else document.getElementById(alerts).innerHTML = '';
    }

    function validateHistoryForm()
    {
        error = false;
        validateTextBox('historyDivision', 'AlertHisDiv');
        validateTextBox('historyDepartment', 'AlertHisDep');
        validateTextBox('historySection', 'AlertHisSec');
        validateTextBox('historyPosition', 'AlertHisPos');
        validateTextBox('historyFrom', 'AlertHisFrom');


        if(document.forms.EmployeeInfo['historyTo'].value == "" && document.forms.EmployeeInfo['Present'].checked==false)
        {
            //validateTextBox('historyTo', 'AlertHisTo');
            document.getElementById('AlertHisTo').innerHTML = '*'; error = true;
        }
        else document.getElementById('AlertHisTo').innerHTML = '';

        if(!error)
        {
            document.getElementById('alertHistory').innerHTML = '';
            return true;
        }
        else
        {
            document.getElementById('alertHistory').innerHTML = 'Please fill up the required fields';
        }
        return false;
    }

    function validateForm()
    {
        error = false;

        //textbox validations
        validateTextBox('LastName', 'AlertLN');
        validateTextBox('FirstName', 'AlertFN');
        validateTextBox('MiddleName', 'AlertMN');
        validateTextBox('DateOfBirth', 'AlertDOB');
        validateTextBox('CityAddStreet', 'AlertCAS');
        validateTextBox('CityAddBarangay', 'AlertCAB');
        validateTextBox('CityAdd', 'AlertCA');
        

        //no need for validation
        //validateTextBox('Telephone', 'AlertT');
        //validateTextBox('Spouse', 'AlertS');
        //validateTextBox('NickName', 'AlertNN');
        //validateTextBox('ProvAddressBarangay', 'AlertPAB');
        //validateTextBox('ProvAddressMunicipality', 'AlertPAM');
        //validateTextBox('ProvAddress', 'AlertPA');
        
        if(!error)
        {
            document.getElementById('Alerts').innerHTML = '';
            document.forms.EmployeeInfo['formtype'].value = 'SaveBasicInfo';
            document.forms.EmployeeInfo.submit();
        }
        else
        {
            document.getElementById('Alerts').innerHTML = 'Please fill up the required fields';
        }
    }

    function getAge()
    {
        //calculate age based on the date
        var birthdate = new Date(document.EmployeeInfo['DateOfBirth'].value);
        var today = new Date();
        var diff = today.getTime() - birthdate.getTime();
        var oneDay = 1000*60*60*24;
        var oneMonth = oneDay * (365.25/12);
        var oneyear = oneDay * 365.25;
        
        var y = Math.floor(diff/oneyear);
        var m = Math.floor((diff/oneMonth)%12);
        var d = (Math.ceil((diff/oneDay)%(365.25/12))-1);

        document.EmployeeInfo['Age'].value = '';

        if(y>1) document.EmployeeInfo['Age'].value += y+' years ';
        else if(y==1) document.EmployeeInfo['Age'].value += y+' year ';

        if(m>1) document.EmployeeInfo['Age'].value += m+' months ';
        else if(m==1) document.EmployeeInfo['Age'].value += m+' month ';

        if(d>1) document.EmployeeInfo['Age'].value += d+' days';
        else if(d==1) document.EmployeeInfo['Age'].value += d+' day';

        //document.EmployeeInfo['Age'].value = Math.floor(diff/oneyear)+' year(s), '+ Math.floor((diff/oneMonth)%12) + ' month(s), ' + (Math.ceil((diff/oneDay)%(365.25/12))-1) + ' day(s) ';
    }

    function getDepAge()
    {
        //calculate age based on the date
        var birthdate = new Date(document.EmployeeInfo['DepDateOfBirth'].value);
        var today = new Date();
        var diff = today.getTime() - birthdate.getTime();
        var oneDay = 1000*60*60*24;
        var oneMonth = oneDay * (365.25/12);
        var oneyear = oneDay * 365.25;
        
        var y = Math.floor(diff/oneyear);
        var m = Math.floor((diff/oneMonth)%12);
        var d = (Math.ceil((diff/oneDay)%(365.25/12))-1);

        document.EmployeeInfo['DepAge'].value = '';

        if(y>1) document.EmployeeInfo['DepAge'].value += y+' years ';
        else if(y==1) document.EmployeeInfo['DepAge'].value += y+' year ';

        if(m>1) document.EmployeeInfo['DepAge'].value += m+' months ';
        else if(m==1) document.EmployeeInfo['DepAge'].value += m+' month ';

        if(d>1) document.EmployeeInfo['DepAge'].value += d+' days';
        else if(d==1) document.EmployeeInfo['DepAge'].value += d+' day';

    }

    //javascript trim function
    String.prototype.trim = function()
    {
        return this.replace(/^\s+|\s+$/g,"");
    }

    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //55 for dash
        //numbers only and dash
        if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode != 055))
            return false;

        return true;
    }

    function isLetterKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //key code code for small and capital letters
        //46 for period
        //44 for comma
        if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode != 32) && (charCode != 46) && (charCode != 44))
            return false;
        return true;
    }

    function isLetterAndNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        //key code code for small and capital letters
        //46 for period
        //44 for comma
        
        if ((charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    $(function() {
        $( "#tabs" ).tabs({
            fxAutoHeight: true
        });

        //get the selected tab
        var selectedTab = <?php 
        if ($_POST["tabIndex"]!="") 
            echo $_POST["tabIndex"];
        else
            echo "0";
        ?>;

        //select the previously selected tab on load
        $( "#tabs" ).tabs( "option", "selected", selectedTab );
        document.EmployeeInfo['tabIndex'].value = selectedTab;
        

        <?php
            //hide tabs when creating a new account
            //show only personal background tab
            if($pkid=="")
            {
            ?>
                $('#tabs').tabs("option","disabled",[1,2,3,4,5,6]);
            <?php
            }
        ?>

        //put selected tab on tabIndex element
        $( "#tabs" ).tabs({
            show: function(event, ui)
            {
                document.EmployeeInfo['tabIndex'].value = $( "#tabs" ).tabs( "option", "selected" );
            }
        });
        
        $(function() {
        $( "#datepickerBday" ).datepicker({ changeMonth: true, changeYear: true , maxDate: "-18Y"});
        });
        $(function() {
        $( "#datepickerDepBday" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        $( "#datepickerDepBday" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerGrad" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        });
        $(function() {
        $( "#datepickerHired" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        //$( "#datepickerHired" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerNotif" ).datepicker({ changeMonth: true, changeYear: true, maxDate: "+0D"});
        $( "#datepickerNotif" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#datepickerEffect" ).datepicker({ changeMonth: true, changeYear: true});
        $( "#datepickerEffect" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });

        $(function() {
        $( "#historyTo" ).datepicker({ changeMonth: true, changeYear: true });
        $( "#historyTo" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });
        $(function() {
        $( "#historyFrom" ).datepicker({ changeMonth: true, changeYear: true });
        $( "#historyFrom" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        });

        //delete dependent confirmation dialog
        $("#dialog").dialog({
            autoOpen: false,
            show: "blind",
            hide: "fade",
            width:"300px"
        });
        //open delete dependent dialog 
        $("#opener").click(function() {
            $("#dialog").dialog("open");
            return false;
        });
        //close delete dependent dialog box
        $("#closer").click(function() {
            $("#dialog").dialog("close");
            return false;
        });

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
        var availableTags = [
            <?php
                //tags for the degree field autocomplete
                $result = HRISDB::getInstance()->execute_search_query("SELECT Distinct(Degree) FROM tbl_EmployeeInfo WHERE Degree!=''");
                $list = '';
                while($row = mysqli_fetch_array($result))
                {
                    $list .= '"'.$row['Degree'].'",';
                }
                echo substr($list,0,-1);
            ?>
        ];
        //connect the autocomplete values from above to the degree textbox
        $( "#DegreeObtained" ).autocomplete({
            source: availableTags
        });

        $("#Present").change(function(){
        if (this.checked === true) {
            $("input:text.historyTo").attr('disabled', true);
        }
        else {
            $("input:text.historyTo").removeAttr("disabled");
        }
    });
    });

    onload = function() {
        //show age of employee
        getAge();

        //hide dependent delete and ok button button on load
        document.EmployeeInfo['deletes'].style.display = 'none';
        document.EmployeeInfo['adds'].style.display = 'inline-block';
        document.EmployeeInfo['oks'].style.display = 'none';

        //hide Offense delete and ok button button on load
        document.EmployeeInfo['Offensedeletes'].style.display = 'none';
        document.EmployeeInfo['Offenseadds'].style.display = 'inline-block';
        document.EmployeeInfo['Offenseoks'].style.display = 'none';

        //make table for dependents a sorted table
        var myTable = new SortedTable('sorted');


        myTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        myTable.onsort = myTable.colorize;
        myTable.onmove = myTable.colorize;
        myTable.colorize();

        myTable.onselect = function(element)
        {
            //get values on the table and put into row[][] variable
            var $table = $("#sorted"),
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

            //insert all values of selected dependent into the textboxes
            document.EmployeeInfo['DepLastName'].value = rows[0][this.elementIndex(element)];
            document.EmployeeInfo['DepFirstName'].value = rows[1][this.elementIndex(element)];
            document.EmployeeInfo['DepMiddleName'].value = rows[2][this.elementIndex(element)];
            document.EmployeeInfo['DepDateOfBirth'].value = rows[3][this.elementIndex(element)];
            document.EmployeeInfo['Relationship'].value = rows[8][this.elementIndex(element)];
            document.EmployeeInfo['TaxDependent'].value = rows[5][this.elementIndex(element)];
            document.EmployeeInfo['DepAge'].value = rows[6][this.elementIndex(element)];
            getDepAge();
            document.EmployeeInfo['deppkid'].value = rows[7][this.elementIndex(element)];

            //hide add button
            document.EmployeeInfo['deletes'].style.display = 'inline-block';
            document.EmployeeInfo['adds'].style.display = 'none';
            document.EmployeeInfo['oks'].style.display = 'inline-block';

            //validateFields();
            validateDependentFields();

        }
        myTable.ondeselect = function(element)
        {
            //remove values
            document.EmployeeInfo['DepLastName'].value = "";
            document.EmployeeInfo['DepFirstName'].value = "";
            document.EmployeeInfo['DepMiddleName'].value = "";
            document.EmployeeInfo['DepDateOfBirth'].value = "";
            document.EmployeeInfo['Relationship'].value = "1";
            document.EmployeeInfo['TaxDependent'].value = "no";
            document.EmployeeInfo['DepAge'].value = "";
            document.EmployeeInfo['deppkid'].value = "";

            //hide delete and ok button, show add button
            document.EmployeeInfo['deletes'].style.display = 'none';
            document.EmployeeInfo['adds'].style.display = 'inline-block';
            document.EmployeeInfo['oks'].style.display = 'none';
        }



        var trainingTable = new SortedTable('sortedTrainings');

        //sorted table colorization
        trainingTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        trainingTable.onselect = function(element)
        {
             //this.cleansort();
             SortedTable.getSortedTable(document.getElementById('row1')).cleanselect();
        }

        trainingTable.onsort = myTable.colorize;
        trainingTable.onmove = myTable.colorize;
        trainingTable.colorize();

        var myOffenseTable = new SortedTable('sortedOffense');
        
        //sorted table colorization
        myOffenseTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        myOffenseTable.onsort = myTable.colorize;
        myOffenseTable.onmove = myTable.colorize;
        myOffenseTable.colorize();

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

        var historyTable = new SortedTable('sortedHistory');
        
        //sorted table colorization
        historyTable.colorize = function() {
        for (var i=0;i<this.elements.length;i++) {
            if (i%2) {
              this.changeClass(this.elements[i],'even','odd');
            } else {
              this.changeClass(this.elements[i],'odd','even');
            }
          }
        }

        historyTable.onsort = historyTable.colorize;
        historyTable.onmove = historyTable.colorize;
        historyTable.colorize();



        historyTable.onselect = function(element)
        {
            //get values of table and put to fields

            var $table = $("#sortedHistory"),
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
            document.EmployeeInfo['historyPosition'].value = rows[6][this.elementIndex(element)];
            document.EmployeeInfo['historyDivision'].value = rows[7][this.elementIndex(element)];
            //updateDepartmentList();
            document.EmployeeInfo['historyDepartment'].value = rows[8][this.elementIndex(element)];
            //updateSectionList();
            document.EmployeeInfo['historySection'].value = rows[9][this.elementIndex(element)];
            document.EmployeeInfo['historyFrom'].value = rows[4][this.elementIndex(element)];
            document.EmployeeInfo['historyTo'].value = rows[5][this.elementIndex(element)];
            document.EmployeeInfo['historypkid'].value = rows[10][this.elementIndex(element)];

            document.EmployeeInfo['deleteHistory'].style.display = 'inline-block';
            document.EmployeeInfo['addHistory'].style.display = 'none';
            document.EmployeeInfo['editHistory'].style.display = 'inline-block';

            validateHistoryForm();
            
        }
        historyTable.ondeselect = function(element)
        {
            document.EmployeeInfo['historyDivision'].value = "";
            document.EmployeeInfo['historyDepartment'].value = "";
            document.EmployeeInfo['historySection'].value = "";
            document.EmployeeInfo['historyPosition'].value = "";
            document.EmployeeInfo['historyFrom'].value = "";
            document.EmployeeInfo['historyTo'].value = "";

            //hide delete and ok button, show add button
            document.EmployeeInfo['deleteHistory'].style.display = 'none';
            document.EmployeeInfo['addHistory'].style.display = 'inline-block';
            document.EmployeeInfo['editHistory'].style.display = 'none';
        }
    }

    function validateDependentFields()
    {
        //trim spaces
        document.EmployeeInfo['DepLastName'].value = document.EmployeeInfo['DepLastName'].value.trim();
        document.EmployeeInfo['DepFirstName'].value = document.EmployeeInfo['DepFirstName'].value.trim();
        document.EmployeeInfo['DepMiddleName'].value = document.EmployeeInfo['DepMiddleName'].value.trim();
        //javascript validation of fields
        error = false;

        validateTextBox('DepLastName', 'AlertLastName');
        validateTextBox('DepFirstName', 'AlertFirstName');
        validateTextBox('DepMiddleName', 'AlertMiddleName');
        validateTextBox('DepDateOfBirth', 'AlertDOBirth');
        validateTextBox('DepLastName', 'AlertLastName');
        //validateTextBox('DepAge', 'AlertDepAge');

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

    function Dependentok()
    {
        if(!validateDependentFields())
        {
            document.EmployeeInfo['formtype'].value = "Dependentok";
            document.EmployeeInfo.submit();
        }
    }

    function Dependentadd()
    {
        if(!validateDependentFields())
        {
            document.EmployeeInfo['formtype'].value = "Dependentadd";
            document.EmployeeInfo.submit();
        }
    }

    function Dependentdelete()
    {
        document.EmployeeInfo['formtype'].value = "Dependentdelete";
        document.EmployeeInfo.submit();
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
            document.EmployeeInfo.submit();
        }
    }

    function Offenseadd()
    {
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

    //add option on selection box
    function appendOptionLast(id,text,value)
    {
      var elOptNew = document.createElement('option');
      elOptNew.text = text;
      elOptNew.value = value;
      var elSel = document.getElementById(id);

      try {
        elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
      }
      catch(ex) {
        elSel.add(elOptNew); // IE only
      }
    }

    //update departmentbox on division select
    function updateDepartmentList(fkdiv)
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
                document.getElementById("texter").innerHTML = xmlhttp.responseText;
                var myarray = eval(document.getElementById("texter").innerHTML);


                for(j=document.getElementById('Department').options.length-1;j>=0;j--)
                {
                    document.getElementById('Department').remove(j);
                }

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Department',myarray[i][1],myarray[i][0]);
                }

                updateSectionList();
                
            }
        }

        var url = "updateDepartment.php?fkDivision="+fkdiv;//document.getElementById("Division").value;
        xmlhttp.open("GET",url,true);
        xmlhttp.send();

    }

    //update selection box on department select
    function updateSectionList()
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
                document.getElementById("texter").innerHTML = xmlhttp.responseText;
                var myarray = eval(document.getElementById("texter").innerHTML);


                for(j=document.getElementById('Section').options.length-1;j>=0;j--)
                {
                    document.getElementById('Section').remove(j);
                }

                appendOptionLast('Section','','');

                for (var i = 0; i < myarray.length; i++)
                {
                    appendOptionLast('Section',myarray[i][1],myarray[i][0]);
                }
            }
        }

        var url = "updateSection.php?fkDepartment="+document.getElementById("Department").value;
        //alert(url);
        xmlhttp.open("GET",url,true);
        xmlhttp.send();
    }
