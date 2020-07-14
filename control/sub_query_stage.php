<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "sub_query_stage_master";
$table_id = "sub_query_stage_id";
$response = '';

if ('fetch' == $action) {

    $table = "sub_query_stage_master sqsm";

    $columns = array(
        'sqsm.sub_query_stage_id','ltm.loan_type_name','cm.category_name','rm.reason_name','qsm.query_stage_name',
        'sqsm.sub_query_stage_name', 'sqsm.sub_query_stage_description','sqsm.is_active','sqsm.is_default',
        'qsm.reason_id','cm.loan_type_id','rm.category_id'
    );
    $seach_columns = array(
        'sqsm.sub_query_stage_name','query_stage_name','reason_name','sub_query_stage_description','loan_type_name','category_name'
    );

    $joins = " left join query_stage_master as qsm on (sqsm.query_stage_id = qsm.query_stage_id)";
    $joins .= " left join reason_master as rm on (qsm.reason_id = rm.reason_id)";
    $joins .= " left join category_master as cm on (rm.category_id = cm.category_id)";
    $joins .= " left join loan_type_master as ltm on (cm.loan_type_id = ltm.loan_type_id)";

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
    $sql_group = "";

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
            $existingCheckR = $db->Fetch("tickets", $table_id, "$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if ($existingCheckC > 0) {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete sub query stage because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'record(s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
} elseif ($action == 'get_sub_query_stage_description') {
    $data = $db->FilterParameters($_POST);
//    Core::PrintArray($data); die();
    $subQueryStageId = isset($data['id']) ? $data['id'] : "";
    if ($subQueryStageId != '') {

        $descriptionInfo = $db->FetchCellValue("sub_query_stage_master","sub_query_stage_description","sub_query_stage_id = '".$subQueryStageId."'");

        //Core::PrintArray($descriptionInfo); die();
        $html = '';

        if($descriptionInfo != ''){
            //$html .= '<textarea disabled>'. $descriptionInfo .'</textarea>';
            $html .= $descriptionInfo;
        }
        echo $html;
    } else {
        echo NO_RESULT;
    }

} else {
    echo NO_RESULT;
}
include_once 'footer.php';

