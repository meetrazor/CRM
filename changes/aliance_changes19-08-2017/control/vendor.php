<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "vendor_master";
$table_id = 'vendor_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table pm";

    $columns = array(
        'pm.vendor_id','vendor_code','vendor_name',
        'pm.email','pm.mobile_no','c.city_name','pm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by',
        'pm.is_active'

    );
    $seach_columns = array(
        'concat(pm.first_name," ",pm.last_name) as full_name',
        'pm.email','pm.mobile_no','pm.created_at',
        'concat(au.first_name," ",au.last_name)',
    );

    $joins = " left join admin_user au on (au.user_id = pm.created_by)";
    $joins .= " left join city c on (c.city_id = pm.city_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1";
    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "pm.vendor_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
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
        $seach_condition .= "pm.vendor_code" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_7'] ) . "%' OR ";
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
    while ($row = $db->MySqlFetchRow($main_query))
    {
        $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
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
            $existingCheckR = $db->Fetch("campaign_master", $table_id, "vendor_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if ($existingCheckC > 0) {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete vendor because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $db->DeleteWhere($table, $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = ' vendor (s) deleted successfully';
            }
        }
    }

    echo json_encode($response);
}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $vendorId = isset($data['vendor_id']) ? $data['vendor_id'] : "";
    if(isset($vendorId) and $vendorId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and vendor_id != '{$vendorId}'");
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
    $leadInfo = $db->FetchToArray($table,array("vendor_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}
include_once 'footer.php';
