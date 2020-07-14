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

    $campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';
    $prospectId = (isset($_GET['prospect_id']) && !empty($_GET['prospect_id'])) ? intval($db->FilterParameters($_GET['prospect_id'])) : '';
    $dispositionId = (isset($_GET['disposition_id']) && !empty($_GET['disposition_id'])) ? intval($db->FilterParameters($_GET['disposition_id'])) : '';

    $columns = array(
        'am.activity_id','am.activity_name','concat(pm.first_name," ",pm.last_name) as prospect_name','am.remarks',
        'dm.disposition_name','cm.campaign_name',
        'concat(au.first_name," ",au.last_name) as telecaller','concat(am.start_date_time," ",call_time) as start_date_time',
        'am.duration','dm.is_close','dm.is_callback','pm.prospect_id','am.is_latest'


    );
    $seach_columns = array(
        'pm.activity_name','cm.campaign_name'
    );

    $joins = " left join prospect_master as pm on (am.type_id = pm.prospect_id)";
    $joins .= " left join campaign_master as cm on (cm.campaign_id = pm.campaign_id)";
    $joins .= " left join disposition_master as dm on (dm.disposition_id = am.disposition_id)";
    $joins .= " left join admin_user as au on (am.created_by = au.user_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1 and am.disposition_id is not null and ".$activityCondition;

    if($campaignId != ''){
        $sql_where .= " and cm.campaign_id = '{$campaignId}'";
    }

    if($prospectId != ''){
        $sql_where .= " and pm.prospect_id = '{$prospectId}'";
    }

    if($dispositionId != ''){
        $sql_where .= " and dm.disposition_id = '{$dispositionId}'";
    }

    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'pm.activity_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }


    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'concat(pm.first_name," ",pm.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'dm.disposition_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'cm.campaign_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'concat(au.first_name," ",au.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }


    include_once 'activity_filter.php';
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
        $row['start_date_time'] = ($row['start_date_time'] != '0000-00-00 00:00:00' && $row['start_date_time'] != '') ? core::YMDToDMY($row['start_date_time'],true) : "";
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
    $userType = $ses->Get("user_type");
    $activityOn = isset($_GET['activity_on'])?$db->FilterParameters($_GET['activity_on']):"";

    $condition = $db->LikeSearchCondition($term,array("lm.lead_name","lm.mobile_no"));
    $condition = "(".$condition.")";
    $condition .= " and lu.is_latest = 1";
    if($userType != UT_ADMIN){
        $condition .= " and lu.user_type_id = '{$userType}'";
    }

    //$condition .= " and l.bd_id  = '{$user_id}'";
    $sql = "SELECT l.lead_id AS id, lead_name AS `name`,l.mobile_no FROM lead_master AS l
    WHERE $condition  ORDER BY lead_name ASC LIMIT 0, 10";
    $mainTable = array("lead_master as lm",array("lm.lead_id as id","lm.lead_name as `name`"));
    $joinTable = array(
        array("left","lead_users as lu","lu.lead_id = lm.lead_id")
    );
    $clientInfoR = $db->JoinFetch($mainTable,$joinTable,$condition,null,null,"lm.lead_id");
    $clientInfo = $db->FetchToArrayFromResultset($clientInfoR);
    $response= (count($clientInfo) > 0) ? $clientInfo : array("no result found");


    echo json_encode($response);

} elseif ($action == 'getprospect'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("first_name","last_name"));
    $condition = "(".$condition.")";
    $prospectInfo = $db->FetchToArray('prospect_master',array("prospect_id as value","concat(first_name,' ',last_name) as text"),$condition,array("first_name"=>"asc"));

    echo json_encode($prospectInfo);
}
include_once 'footer.php';
