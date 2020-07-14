<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/13/2019
 * Time: 7:08 PM
 */

include_once 'header.php';
$user_id = $ses->Get("user_id");

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "question_master";
$table_id = "question_id";
$response = '';

if ('fetch' == $action) {

    $table = "question_master qm";

    $columns = array(
        'qm.question_id','qm.question_for','qm.question_name','qm.question_short_name','qm.option_type',
        'GROUP_CONCAT(qov.option_value) as option_value',
        'qm.sort_order','qm.is_active','qm.is_required','qm.is_multiple',
    );
    $seach_columns = array(
        'qm.question_name','qm.question_for','GROUP_CONCAT(qov.option_value) as option_value','qm.option_type',
        'qm.sort_order'
    );

    $joins = "left join question_option_value as qov on (qm.question_id = qov.question_id)";

    $sql_where = "WHERE 1=1";

    if ($_GET['sSearch'] != "") {

        $seach_condition = "";
        foreach ($seach_columns as $column) {
            $column = strtolower($column);
            if (false !== ($index = strpos($column, ' as '))) {
                $column = substr($column, $index + 4);
            }

            $seach_condition .= $column . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    $sql_order = "";
    if (isset($_GET['iSortCol_0'])) {
        $sql_order = "ORDER BY  ";
        for ($i = 0; $i < mysql_real_escape_string($_GET['iSortingCols']); $i++) {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if (false !== ($index = strpos($column, ' as '))) {
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
        $sql_order = substr_replace($sql_order, "", -2);
    }
    $sql_group = "GROUP BY qm.question_id";

    // paging
    $sql_limit = "";
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
        $sql_limit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " . mysql_real_escape_string($_GET['iDisplayLength']);
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

//    echo $sql;exit;
    $main_query = mysql_query($sql) or die(mysql_error());

    // get the number of filtered rows
    $filtered_rows_query = mysql_query("SELECT FOUND_ROWS()") or die(mysql_error());

    $row = mysql_fetch_array($filtered_rows_query);
    $response['iTotalDisplayRecords'] = $row[0];
    $response['iTotalRecords'] = $row[0];

    $response['sEcho'] = intval($_GET['sEcho']);
    $response['aaData'] = array();
    while ($row = $db->MySqlFetchRow($main_query)) {
        $row['question_name'] = ($row['question_name'] != '') ? core::StripAllSlashes($row['question_name']) : "";
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);

} elseif ($action == 'delete') {

    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();

    if (is_array($idArray)) {
        foreach ($idArray as $typeId) {
            $existingCheckR = $db->Fetch("call_audit_answer", $table_id, "$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if ($existingCheckC > 0) {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete this question because it has record!";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                if($result){
                    $db->DeleteWhere("question_option_value", $condition);
                }
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'record(s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
}
include_once 'footer.php';

