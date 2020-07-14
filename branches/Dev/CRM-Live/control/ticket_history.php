<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "ticket_history";
$table_id = 'ticket_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);

if('fetch' === $action){

    $ticketCondition = Utility::ticketCondition();

    $table = "$table t";

    $campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';
    $status = (isset($_GET['status']) && !empty($_GET['status'])) ? $db->FilterParameters($_GET['status']) : '';
    $reasonId = (isset($_GET['reason_id']) && !empty($_GET['reason_id'])) ? intval($db->FilterParameters($_GET['reason_id'])) : '';
    $customerId = (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) ? intval($db->FilterParameters($_GET['customer_id'])) : '';
    $dispositionId = (isset($_GET['disposition_id']) && !empty($_GET['disposition_id'])) ? intval($db->FilterParameters($_GET['disposition_id'])) : '';
    $userId = (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? intval($db->FilterParameters($_GET['user_id'])) : '';
    $getUserId = (isset($_GET['get_user_id']) && !empty($_GET['get_user_id'])) ? intval($db->FilterParameters($_GET['get_user_id'])) : '';
    $getUserLevel = (isset($_GET['get_user_level']) && !empty($_GET['get_user_level'])) ? $_GET['get_user_level'] : '';
    $ticketNumber = (isset($_GET['ticket_id']) && !empty($_GET['ticket_id'])) ? $_GET['ticket_id'] : '';

    // to show only merged tickets
    $showMerged = (isset($_GET['show_merged']) && !empty($_GET['show_merged'])) ? true : false;

    $columns = array(
        't.ticket_id','t.ticket_number','cm.customer_name','bm.bank_name','cm.email','ltm.loan_type_name','cms.category_name',
        'cm.mobile_no','rm.reason_name','t.call_from',
        'qsm.query_stage_name','qtm.query_type_name','sqsm.sub_query_stage_name', 't.comment as short_comment','t.resolve_date_time',
        'dm.disposition_name','sm.status_name',
        'concat(cu.first_name," ",cu.last_name) as created_by',
        'concat(au.first_name," ",au.last_name) as assign_to',
        'concat(ae2.first_name," ",ae2.last_name) as escalate_2',
        'concat(ae3.first_name," ",ae3.last_name) as escalate_3',
        't.created_at','t.updated_at',
        'concat(uu.first_name," ",uu.last_name) as updated_by','t.comment',
        'dm.is_close','dm.is_callback','cm.customer_id','t.is_latest',"dm.is_meeting",'dm.disposition_id','sm.status_id','sm.is_close','qtm.query_color',
        't.escalate_to_2','t.escalate_to_3','t.message_no','t2.ticket_number as merged_ticket_number'
    );
    $seach_columns = array(
        'pm.ticket_name','cm.campaign_name','sub_query_stage_name'
    );

    $joins = " left join bank_master as bm on (t.bank_id = bm.bank_id)";
    $joins .= " left join loan_type_master as ltm on (t.loan_type_id = ltm.loan_type_id)";
    $joins .= " left join category_master as cms on (t.product_type_id = cms.category_id)";
    $joins .= " left join customer_master as cm on (t.customer_id = cm.customer_id)";
    $joins .= " left join reason_master as rm on (t.reason_id = rm.reason_id)";
    $joins .= " left join query_stage_master as qsm on (t.query_stage_id = qsm.query_stage_id)";
    $joins .= " left join sub_query_stage_master as sqsm on (t.sub_query_stage_id = sqsm.sub_query_stage_id)";
    $joins .= " left join query_type_master as qtm on (t.query_type_id = qtm.query_type_id)";
    $joins .= " left join disposition_master as dm on (t.disposition_id = dm.disposition_id)";
    $joins .= " left join status_master as sm on (t.status_id = sm.status_id)";
    $joins .= " left join admin_user as cu on (t.created_by = cu.user_id) ";
    $joins .= " left join admin_user as ae2 on (t.escalate_to_2 = ae2.user_id) ";
    $joins .= " left join admin_user as ae3 on (t.escalate_to_3 = ae3.user_id) ";
    //$joins .= " left join ticket_users as tu on (t.ticket_id = tu.ticket_id) and tu.is_latest = 1";
    $joins .= " left join tickets as t2 on (t2.ticket_id = t.merged_id) ";
    $joins .= " left join admin_user as au on (t.assign_to = au.user_id) ";
    $joins .= " left join admin_user as uu on (t.updated_by = uu.user_id) ";

    // filtering
// filtering

    if(isset($showMerged) && $showMerged != '' && $showMerged === true){
        $sql_where = "WHERE 1=1 and ".$ticketCondition." and t.is_merged = 1";
    }
    else{
        $sql_where = "WHERE 1=1 and ".$ticketCondition." and t.is_merged != 1";
    }

    $sql_having = "having 1=1";


    if($customerId != ''){
        $sql_where .= " and c.customer_id = '{$customerId}'";
    }

    if($ticketNumber != ''){
        $sql_where .= " and t.ticket_id = '{$ticketNumber}'";
    }

    if($dispositionId != ''){
        $sql_where .= " and dm.disposition_id = '{$dispositionId}'";
    }

    if($reasonId != ''){
        $sql_where .= " and rm.reason_id = '{$reasonId}'";
    }
    if($userId != ''){
        $sql_where .= " and t.created_by = '{$userId}'";
    }



    if ($getUserLevel != '') {

        if ($getUserLevel == 'level1') {
            $sql_where .= " and assign_to = $getUserId";
        } elseif ($getUserLevel == 'level2') {
            $sql_where .= " and escalate_to_2 = $getUserId";
        } elseif ($getUserLevel == 'level3') {
            $sql_where .= " and escalate_to_3 = $getUserId";
        }
    }

    if($status != ''){
        if($status == 'close'){
            $sql_where .= " and sm.is_close = 1";
        } elseif($status == 'open'){
            $sql_where .= " and sm.is_close != 1";
        } elseif($status == 'unassign'){
            $sql_where .= " and t.assign_to = 0";
        }

    }

    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 't.ticket_number' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }


    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'cm.customer_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
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
        $seach_condition .= 'sm.status_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
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

    if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'cm.mobile_no' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_having .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_7']) && $_GET['sSearch_7'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 't.call_from' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_7'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_8']) && $_GET['sSearch_8'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'qsm.query_stage_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_8'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_9']) && $_GET['sSearch_9'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'qtm.query_type_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_9'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_10']) && $_GET['sSearch_10'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 't.comment' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_10'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_11']) && $_GET['sSearch_11'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'cm.email' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_11'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_12']) && $_GET['sSearch_12'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'sqsm.sub_query_stage_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_12'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_13']) && $_GET['sSearch_13'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'ltm.loan_type_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_13'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_14']) && $_GET['sSearch_14'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'cms.category_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_14'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }


    include_once 'ticket_filter.php';
    // ordering
    $sql_order = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $_GET['iSortCol_0'] = $_GET['iSortCol_0'] - 1;
        $sql_order = "ORDER BY  ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
        //echo $sql_order;
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

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_having} {$sql_order} {$sql_limit}";

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
        $row['created_at'] = ($row['created_at'] != '0000-00-00 00:00:00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
        $row['updated_at'] = ($row['updated_at'] != '0000-00-00 00:00:00' && $row['updated_at'] != '') ? core::YMDToDMY($row['updated_at'],true) : "";
        $row['comment'] = ($row['message_no'] != '') ? "Click here to see" : "<span>".Utility::getCutString($row['comment'],40)."</span>";
        $row['short_comment'] = ($row['message_no'] != '') ? "Click here to see" : $row['short_comment'];
//        $row['short_comment'] = base64_decode($row['short_comment']);
        //$row['short_comment'] = "<span>".Utility::getCutString($row['comment'],40)."</span>";
        $response['aaData'][] = $row;
    }
    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}
include_once 'footer.php';
