<?php

$border = 0;

require_once("../global_includes/hris-oop.php");

//validations done in javascript

function GetAge($bday)
{
    $date = new DateTime($bday);
    $diff = $date->diff(new DateTime());

    return $diff->y.' years, '.$diff->m.' months, '.$diff->d.' days';
}

$pkid = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        //validate date fields
        if($_POST["DateOfBirth"]!='')
        {
            $date = new DateTime($_POST["DateOfBirth"]);
            $DOB = $date->format('Y-m-d');
        }
        else
            $DOB = '';

        if($_POST["DateGraduated"]!='')
        {
            $dateGad = new DateTime($_POST["DateGraduated"]);
            $DOGrad = $dateGad->format('Y-m-d');
        }
        else
            $DOGrad = '';

        if($_POST["DateHired"]!='')
        {
            $dateHired = new DateTime($_POST["DateHired"]);
            $DOHired = $dateHired->format('Y-m-d');
        }
        else
            $DOHired = '';

        strlen($_POST["EmployeeNo"]);


        //put leading zeros
        if(($_POST["EmployeeNo"] != "") && (strlen($_POST["EmployeeNo"]) < 4))
        {
            for ($i=4-strlen($_POST["EmployeeNo"]); $i > 0; $i--) { 
                $_POST["EmployeeNo"] = '0'.$_POST["EmployeeNo"];
            }
        }

        //for dependents form
        //get value of tax dependent
        if($_POST["TaxDependent"]=='yes') $taxdep = 1; else $taxdep = 0;

        if($_POST["pkid"]!="")//if there is a pkid passed on to the form
        {
            $pkid = $_POST["pkid"];

            //update employee
            if ($_POST["formtype"]=="SaveBasicInfo")
            {
                //update basic info
                HRISDB::getInstance()->update_employee($pkid,
                    $_POST["LastName"],
                    $_POST["FirstName"],
                    $_POST["MiddleName"],
                    $_POST["NickName"],
                    $DOB,
                    $_POST["Gender"],
                    $_POST["CivilStatus"],
                    $_POST["Spouse"],
                    $_POST['TaxExemptionBase'],
                    $_POST["CityAddStreet"],
                    $_POST["CityAddBarangay"],
                    $_POST["CityAdd"],
                    $_POST["ProvAddressBarangay"],
                    $_POST["ProvAddressMunicipality"],
                    $_POST["ProvAddress"],
                    $_POST["Telephone"],
                    $_POST["IncomingShuttle"],
                    $_POST["OutgoingShuttle"],
                    $_POST["Remarks"]);

                //update educational background
                HRISDB::getInstance()->update_employee_educ($pkid,
                    $_POST["HighestEducationalAttainment"],
                    $_POST["SchoolAddress"],
                    $DOGrad,
                    $_POST["DegreeObtained"],
                    $_POST["HonorsAwards"],
                    $_POST["GovernmentExams"]);

                //update employee employment status
                HRISDB::getInstance()->update_employee_employment_info($pkid,
                    $_POST["EmployeeNo"],
                    $_POST["EmploymentStatus"],
                    $_POST["Division"],
                    $_POST["Status"],
                    $_POST["Department"],
                    $_POST["SalaryType"],
                    $_POST["Section"],
                    $_POST["SSS"],
                    $_POST["Position"],
                    $_POST["TIN"],
                    $DOHired,
                   $_POST["PhilHealth"]);

                //update family info
                HRISDB::getInstance()->update_employee_family($pkid,$_POST["FatherName"],
                    $_POST["MotherName"],
                    $_POST["PersonToBeNotified"]);

                $message = 'Employee Information Saved';
            }

            //dependent update
            if ($_POST["formtype"]=="Dependentok")
            {
                HR