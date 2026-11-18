<?php
// Shared by excel.php and excel_2.php: bound-parameter query helpers and the error handler.
// Include after global_includes/hris-oop.php and after the session check.

// run a query with bound parameters (values only)
function excelQuery($sql, array $params = array())
{
    return HRISDB::getInstance()->query_params($sql, $params);
}

// header query of a report (column names only); an export query carries its own parameters in $sql[0][2]
function excelHeaderQuery($sql)
{
    return excelQuery($sql[0][0], isset($sql[0][2]) ? $sql[0][2] : array());
}

// group query: BASE where col1 = ? [and col2 = ? ...] SUFFIX. $conds = array(array(column, value), ...)
// columns come from the fixed report definitions below and are checked anyway; values are always bound
function excelGroupQuery($baseSql, $suffix, array $conds)
{
    $parts = array();
    $params = array();
    foreach ($conds as $c)
    {
        if (!preg_match('/^(\'\'|[A-Za-z_][A-Za-z0-9_]*)$/', (string)$c[0]))
        {
            throw new InvalidArgumentException('Invalid column name');
        }
        $parts[] = $c[0] . ' = ?';
        $params[] = (string)$c[1];
    }
    return excelQuery($baseSql . ' where ' . implode(' and ', $parts) . ' ' . $suffix, $params);
}

// main data query: a "complete" query ($sql[0][3] true, used by the search export) is run as is
function excelMainQuery($sql, array $conds)
{
    if (!empty($sql[0][3]))
    {
        return excelQuery($sql[0][0], isset($sql[0][2]) ? $sql[0][2] : array());
    }
    return excelGroupQuery($sql[0][0], $sql[0][1], $conds);
}

// rebuild the search.php query from the validated parameters it stored (same shape as HRISDB::search_employees_list)
function excelBuildSearchQuery(array $p)
{
    $allowedFields = array('EmpNo', 'FullName', 'LastName', 'FirstName', 'MiddleName', 'fkposition', 'fkdivision', 'fkdepartment', 'fksection');
    $field = isset($p['field']) ? $p['field'] : '';
    if (!in_array($field, $allowedFields, true))
    {
        throw new InvalidArgumentException('Invalid column name');
    }
    $search = (string)(isset($p['search']) ? $p['search'] : '');
    $pkids = (isset($p['pkids']) && is_array($p['pkids'])) ? array_map('intval', $p['pkids']) : null;
    $status = isset($p['status']) ? $p['status'] : null;

    $sql = "SELECT *, CONCAT(LastName, ', ', FirstName, ' (', MiddleName,')') as FullName FROM tbl_EmployeeInfo WHERE ";
    if ($field === 'FullName')
    {
        $sql .= "(firstname LIKE ? OR lastname LIKE ? OR middlename LIKE ?)";
        $params = array('%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
    }
    else if ($pkids !== null)
    {
        if (count($pkids) > 0)
        {
            $sql .= "(" . $field . " IN (" . implode(',', array_fill(0, count($pkids), '?')) . "))";
            $params = array_values($pkids);
        }
        else
        {
            $sql .= "(" . $field . " = ?)";
            $params = array('-1');
        }
    }
    else
    {
        $sql .= "(" . $field . " LIKE ?)";
        $params = array('%' . $search . '%');
    }
    if ($status !== null)
    {
        if (!in_array((int)$status, array(1, 2, 3), true))
        {
            throw new InvalidArgumentException('Invalid status');
        }
        $sql .= " AND EmpStatus = ?";
        $params[] = (int)$status;
    }
    $sql .= " ORDER BY EmpNo ASC";
    return array($sql, $params);
}

// never show SQL or exception text in the browser (nothing is output before endExcel())
set_exception_handler(function ($ex) {
    error_log(basename($_SERVER['SCRIPT_NAME']) . ': ' . $ex->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'A database error occurred. Please try again or contact your administrator.';
});
