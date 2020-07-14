<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "partner_master";
$table_id = 'partner_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table pm";

    $columns = array(
        'pm.partner_id','pm.partner_code','concat(pm.first_name," ",pm.last_name) as full_name','concat(ppm.first_name," ",ppm.last_name) as parent_partner_name',
        'pm.email','pm.mobile_no','pm.partner_type','c.city_name','pm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by',
        'pm.is_active'

    );
    $seach_columns = array(
        'concat(pm.first_name," ",pm.last_name) as full_name',
        'pm.email','pm.mobile_no','pm.created_at',
        'concat(au.first_name," ",au.last_name)',
    );

    $joins = " left join admin_user au on (au.user_id = pm.created_by)";
    $joins .= " left join partner_master as ppm on (ppm.partner_id = pm.parent_partner_id)";
    $joins .= " left join city c on (c.city_id = pm.city_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1";
    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "concat(pm.first_name,' ',pm.last_name)" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "pm.email" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "pm.mobile_no" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "c.city_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_7']) && $_GET['sSearch_7'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "pm.partner_code" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_7'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    // ordering
    $sql_order = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sql_order = "ORDER BY  ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
//        echo $sql_order;
        $sql_order = substr_replace( $sql_order, "", -2 );
    }

    // group by
    // If you are not using then put it blank otherwise mention it
    $sql_group = "";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

// 	echo $sql;exit;
    $main_query = mysql_query($sql) or die(mysql_error());

    // get the number of filtered rows
    $filtered_rows_query = mysql_query("SELECT FOUND_ROWS()") or die(mysql_error());

    $row = mysql_fetch_array($filtered_rows_query);
    $response['iTotalDisplayRecords'] = $row[0];
    $response['iTotalRecords'] = $row[0];

    $response['sEcho'] = intval($_GET['sEcho']);
    $response['aaData'] = array();
    $db->UpdateWhere("partner_master",array("is_new"=>1),"1=1");
    while ($row = $db->MySqlFetchRow($main_query))
    {
        $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
        $row['partner_type'] = strtoupper($row['partner_type']);
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}elseif($action == 'delete'){
    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();
    if(is_array($idArray)) {
        foreach ($idArray as $typeId) {
            $existingCheckR = $db->FetchCellValue("partner_ledger", "$table_id", "partner_id = '{$typeId}'");
            $existingCheckT = $db->FetchCellValue("partner_payout", "$table_id", "partner_id = '{$typeId}'");

            $existingCheckC = count($existingCheckR);
            $existingCheckCount = count($existingCheckT);
            if ($existingCheckC > 0 || $existingCheckCount > 0) {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete partner because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $db->DeleteWhere($table, $condition);
                $db->DeleteWhere("partner_education", $condition);
                $db->DeleteWhere("partner_education", $condition);
                //$db->UpdateWhere($table,array("is_active"=>0),$condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = ' partner (s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $partnerId = isset($data['partner_id']) ? $data['partner_id'] : "";
    if(isset($partnerId) and $partnerId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and partner_id != '{$partnerId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email'");
    }
    $emailCount = $db->CountResultRows($emailRes);
    if($emailCount > 0){
        $flag = "false";
    }
    echo $flag;
} elseif ($action == 'getparnter'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("concat(first_name,' ',last_name)","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("partner_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}
include_once 'footer.php';
