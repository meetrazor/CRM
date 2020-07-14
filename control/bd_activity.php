<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "activity_master";
$table_id = 'activity_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);

if('fetch' === $action){

    $activityCondition = Utility::activityCondition();


    $table = "$table am";

    $leadId = (isset($_GET['lead_id']) && !empty($_GET['lead_id'])) ? intval($db->FilterParameters($_GET['lead_id'])) : '';
    $statusId = (isset($_GET['status_id']) && !empty($_GET['status_id'])) ? intval($db->FilterParameters($_GET['status_id'])) : '';

    $columns = array(
        'am.activity_id','lm.lead_name','am.remarks','am.activity_type',
        'sm.status_name','am.created_at','concat(cu.first_name," ",cu.last_name) as created_by','lm.lead_id','sm.is_close','am.is_latest','sm.is_callback'


    );
    $seach_columns = array(
        'lm.lead_name','am.remarks'
    );

    $joins = " left join lead_master as lm on (am.type_id = lm.lead_id)";
    $joins .= " left join status_master as sm on (sm.status_id = am.status_id)";
    $joins .= " left join admin_user as cu on (cu.user_id = am.created_by)";

    // filtering

    $activityCondition .= " and am.activity_on = 'lead'";

    $sql_where = "WHERE 1=1 and ".$activityCondition;

    if($leadId != ''){
        $sql_where .= " and lm.lead_id = '{$leadId}'";
    }

    if($statusId != ''){
        $sql_where .= " and sm.status_id = '{$statusId}'";
    }


    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'lm.lead_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'am.activity_type' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'sm.status_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    include_once 'bd_activity_filter.php';

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
        //$row['start_date_time'] = ($row['start_date_time'] != '0000-00-00 00:00:00' && $row['start_date_time'] != '') ? core::YMDToDMY($row['start_date_time'],true) : "";
        $row['created_at'] = ($row['created_at'] != '0000-00-00 00:00:00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
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
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $condition = "$table_id in ('$id')";
    $db->DeleteWhere($table,$condition);
    $db->DeleteWhere("activity_education",$condition);
    $db->DeleteWhere("activity_users",$condition);
    //$db->UpdateWhere($table,array("is_active"=>0),$condition);
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' activity (s) deleted successfully';
    echo json_encode($response);
} elseif ($action == 'getparnter'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("concat(first_name,' ',last_name)","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("activity_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}elseif ($action == 'checknextcall'){

    $response['next_id'] = Utility::getNextProspect($user_id);
    echo json_encode($response);
}elseif ($action == 'pausecall'){
    $response  = Utility::pauseCall($agentCode);
}elseif ($action == 'dialcall'){
    $data = $db->FilterParameters($_POST);
    $data['number'] = Utility::format_phone($data['number']);
    core::PrintArray(Utility::dialCall($agentCode,$data['number']));
}elseif ($action == 'hangcall'){
    core::PrintArray(Utility::hangCall($agentCode));
} elseif($action == 'getleads'){
//    $userLeads = $user->getUserLeads();
//    $userLeadsIds = (count($userLeads) > 0) ? implode(",",$userLeads) : "-1";
    $term = isset($_GET['term'])?$db->FilterParameters($_GET['term']):"";
    $activityOn = isset($_GET['activity_on'])?$db->FilterParameters($_GET['activity_on']):"";

    $condition = $db->LikeSearchCondition($term,array("l.lead_name","l.mobile_no"));
    $condition = "(".$condition.")";

    //$condition .= " and l.bd_id  = '{$user_id}'";
    $sql = "SELECT l.lead_id AS id, lead_name AS `name`,l.mobile_no FROM lead_master AS l
    WHERE $condition  ORDER BY lead_name ASC LIMIT 0, 10";
    $clientInfo = $db->FetchToArrayFromResultset($db->Query($sql));
    $response= (count($clientInfo) > 0) ? $clientInfo : array("no result found");


    echo json_encode($response);

}
include_once 'footer.php';
