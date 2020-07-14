<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "tickets";
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

    // to show only merged tickets
    $showMerged = (isset($_GET['show_merged']) && !empty($_GET['show_merged'])) ? true : false;

    $columns = array(
        't.ticket_id','t.ticket_number','t2.ticket_number as merged_ticket_number','cm.customer_name','bm.bank_name','cm.email','ltm.loan_type_name','cms.category_name',
        'cm.mobile_no','cm.personal_mobile_no','rm.reason_name','t.call_from',
        'qsm.query_stage_name','qtm.query_type_name','sqsm.sub_query_stage_name', 't.comment as short_comment','t.resolve_date_time',
        'dm.disposition_name','sm.status_name',
        'concat(cu.first_name," ",cu.last_name) as created_by',
        'concat(au.first_name," ",au.last_name) as assign_to',
        'concat(ae2.first_name," ",ae2.last_name) as escalate_2',
        'concat(ae3.first_name," ",ae3.last_name) as escalate_3',
        't.created_at','t.updated_at',
        'concat(uu.first_name," ",uu.last_name) as updated_by','TIMESTAMPDIFF(second,t.created_at,t.updated_at) as resolve_time','t.comment',
        'dm.is_close','dm.is_callback','cm.customer_id','t.is_latest',"dm.is_meeting",'dm.disposition_id','sm.status_id','sm.is_close','qtm.query_color',
        't.escalate_to_2','t.escalate_to_3','t.message_no'
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
        $sql_order = "ORDER BY ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
        $sql_order = substr_replace( $sql_order, "", -2 );
        //echo $sql_order;
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

    //echo $sql;exit;
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
        //$row['resolve_time'] = ($row['is_close'] == 1) ? Utility::getTimeDifferenceBetweenDate($row['created_at'],$row['updated_at']) : "";
        $row['resolve_time'] = ($row['is_close'] == 1) ? Utility::formatTimeFromSecond($row['resolve_time']) : "";
        $row['created_at'] = ($row['created_at'] != '0000-00-00 00:00:00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
        $row['updated_at'] = ($row['updated_at'] != '0000-00-00 00:00:00' && $row['updated_at'] != '') ? core::YMDToDMY($row['updated_at'],true) : "";
        //$row['comment'] = ($row['message_no'] != '') ? "Click here to see" : "<span>".Utility::getCutString($row['comment'],40)."</span>";
        $row['comment'] = "Click here to see";
        //$row['short_comment'] = ($row['message_no'] != '') ? "Click here to see" : $row['short_comment'];
        $row['short_comment'] = ($row['message_no'] != '') ? "Click here to see" : "Click here to see";
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
}elseif($action == 'delete'){
    $ids = $db->FilterParameters($_POST['id']);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $condition = "$table_id in ('$id')";
    $db->DeleteWhere($table,$condition);
    $db->DeleteWhere("ticket_education",$condition);
    $db->DeleteWhere("ticket_users",$condition);
    //$db->UpdateWhere($table,array("is_active"=>0),$condition);
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' ticket (s) deleted successfully';
    echo json_encode($response);
} elseif ($action == 'getparnter'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("concat(first_name,' ',last_name)","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("ticket_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}elseif ($action == 'checknextcall'){

    $response['next_id'] = Utility::getNextProspect($user_id);
    echo json_encode($response);
}elseif ($action == 'pausecall'){
    $response  = Utility::pauseCall($agentCode);
}elseif ($action == 'call'){
    $number = $db->FilterParameters($_POST['id']);

   $post_data = array(
    'From' => "7016190648",
    'To' => $number,
    'CallerId' => "095-138-86363",
    // 'TimeLimit' => "<time-in-seconds> (optional)",
    // 'TimeOut' => "<time-in-seconds (optional)>",
    'CallType' => "promo" //Can be "trans" for transactional and "promo" for promotional content
);
 
// You can get your $exotel_sid, $api_key and $api_token from: https://my.exotel.com/apisettings/site#api-credentials 
$api_key = "9887b7309f3ce2632b610a6d9e4874f013165c6c34495468"; // Your `API KEY`.
$api_token = "6de1d1bbf6986c25170b3766c622f5f6a5e6e862f5f09031"; // Your `API TOKEN`
$exotel_sid = "esmsys1" ;// Your `Account Sid`
 
$url = "https://".$api_key.":".$api_token."@twilix.exotel.in/v1/Accounts/".$exotel_sid."/Calls/connect";
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FAILONERROR, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
 
$http_result = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
 
curl_close($ch);
 
print "Response = ".print_r($http_result);

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
    $ticketOn = isset($_GET['ticket_on'])?$db->FilterParameters($_GET['ticket_on']):"";

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

} elseif ($action == 'getcustomer'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("first_name","last_name"));
    $condition = "(".$condition.")";
    $prospectInfo = $db->FetchToArray('prospect_master',array("prospect_id as value","concat(first_name,' ',last_name) as text"),$condition,array("first_name"=>"asc"));

    echo json_encode($prospectInfo);
} elseif ($action == 'getticket'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $excludeTicketId = isset($_POST['id'])?$db->FilterParameters($_POST['id']):"";
    $condition = $db->LikeSearchCondition($term,array("ticket_number"));
    $closedTicketStatusId = $db->FetchCellValue("status_master","status_id","status_type = 'support' and status_name = 'closed'");
    $condition = "(".$condition.")";
    $condition .= " and ticket_id != ". $excludeTicketId;
    $condition .= " and is_merged != '1' and status_id != ". $closedTicketStatusId;
    $prospectInfo = $db->FetchToArray('tickets',array("ticket_id as value","ticket_number as text"),$condition,array("created_by"=>"desc"));

    echo json_encode($prospectInfo);
}  elseif($action == 'tickethistory'){
    $data = $db->FilterParameters($_POST);
    $ticketId = isset($data['ticket_id']) ? $data['ticket_id'] : "";
    if($ticketId != ''){

        $mainTable = array("ticket_history as th",array("th.comment","th.updated_at,th.ticket_history_id"));
        $joinTable = array(
            array("left","disposition_master as dm","dm.disposition_id = th.disposition_id",array("dm.disposition_name")),
            array("left","status_master as sm","sm.status_id = th.status_id",array("sm.status_name")),
            array("left","query_type_master qtm","qtm.query_type_id = th.query_type_id",array("qtm.query_type_name")),
            array("left","admin_user as cu","th.updated_by = cu.user_id",array("concat(first_name,' ',last_name) as updated_by")),
        );
        $condition = "th.ticket_id = '{$ticketId}'";
        $ticketInfoR = $db->JoinFetch($mainTable,$joinTable,$condition,array("th.updated_at"=>"desc"));
        $ticketHistory = $db->FetchToArrayFromResultset($ticketInfoR);

        //$ticketHistory = $db->FetchToArray("tickets","*","ticket_id = '{$ticketId}'",array('created_at'=>"desc"));

        if(is_array($ticketHistory) and count($ticketHistory) > 0){
            $ticket_html_div = "";
            $ticket_html_div .= "<table class='table table-condensed table-bordered table-hover'>";
            $ticket_html_div .= "<tr>";
            $ticket_html_div .= "<td><b>Comment</b></td>";
            $ticket_html_div .= "<td><b>Disposition</b></td>";
            $ticket_html_div .= "<td><b>Status</b></td>";
            $ticket_html_div .= "<td><b>Query Type</b></td>";
            $ticket_html_div .= "<td><b>Documents</b></td>";
            $ticket_html_div .= "<td><b>Updated By</b></td>";
            $ticket_html_div .= "<td><b>Updated On</b></td>";
            $ticket_html_div .= "<tr>";
            foreach($ticketHistory as $id => $history){
                $ticketDocument = $db->FetchToArray("ticket_documents","*","ticket_history_id = '{$history['ticket_history_id']}'");
                $imageHtml = '';
                if(count($ticketDocument) > 0){
                    $imageHtml = '';
                    foreach($ticketDocument as $documentData){
                        $fileExt = pathinfo($documentData['filename'],PATHINFO_EXTENSION);
                        $fileabsPath = (!in_array($fileExt,Utility::imageExtensions())) ? "uploads/docimage.png" : TICKET_IMAGE_PATH.$documentData['filename'];

                        if($documentData['filename'] != '' && file_exists(TICKET_IMAGE_PATH_ABS.$documentData['filename'])){

                            $imageHtml .= "<div class='row-fluid inline' id='new_image_".$documentData['ticket_document_id']."'>";
                            $imageHtml .= "<ul class='ace-thumbnails'>";
                            $imageHtml .= " <li>";
                            $imageHtml .= " <a href='javascript:void(0);' class='cboxElement'>";
                            $imageHtml .= "<img src='".$fileabsPath."' alt='".$documentData['real_filename']."' title='".$documentData['real_filename']."' style='width: 150px;height: 150px'>";
                            $imageHtml .= "</a>";

                            $imageHtml .= "<div class='tools tools-bottom'>";
                            $imageHtml .= "<a href='javascript:void(0);' onclick='DeleteImage(".$documentData['ticket_document_id'].")'>";
                            $imageHtml .= "<i class='icon-remove red'></i>";
                            $imageHtml .= "</a>";

                            $imageHtml .= "<a href='".TICKET_IMAGE_PATH.$documentData['filename']."' target='_blank'  data-placement='top' data-rel='tooltip' data-original-title='Download'>";
                            $imageHtml .= "<i class='icon-download'></i>";
                            $imageHtml .= "</a>";
                            $imageHtml .= "</div>";
                            $imageHtml .= "</li>";
                            $imageHtml .= "</ul>";
                            $imageHtml .= " </div>";

                        }
                    }
                }
                $ticket_html_div .= "<tr>";
                $ticket_html_div .= "<td>".$history['comment']."</td>";
                $ticket_html_div .= "<td>".$history['disposition_name']."</td>";
                $ticket_html_div .= "<td>".$history['status_name']."</td>";
                $ticket_html_div .= "<td>".$history['query_type_name']."</td>";
                $ticket_html_div .= "<td>".$imageHtml."</td>";
                $ticket_html_div .= "<td>".$history['updated_by']."</td>";
                $ticket_html_div .= "<td>".core::YMDToDMY($history['updated_at'],true)."</td>";
                $ticket_html_div .= "<tr>";

            }
            $ticket_html_div .= "</table>";
            echo $ticket_html_div;
        } else {
            echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";

        }
    } else {
        echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";
    }
}elseif($action == 'deleteimage'){
    $docId = $_POST['id'];
    $old_image = $db->FetchCellValue("ticket_documents","filename","ticket_document_id = {$docId}");
    $uploadpath = TICKET_IMAGE_PATH_ABS;
    $ref_name_jpg = $uploadpath .$old_image;
    if(file_exists($ref_name_jpg)){
        @unlink($ref_name_jpg);
        $db->DeleteWhere("ticket_documents","ticket_document_id = {$docId}");
    }
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' record (s) deleted successfully';
    echo json_encode($response);
}  elseif($action == 'changesupport'){
    $data = $db->FilterParameters($_POST);
    $ticketIds =  isset($data['support_ticket_id']) ? $data['support_ticket_id'] : "";
    $newSupport =  isset($data['support_user_id']) ? $data['support_user_id'] : "";
    if($ticketIds == '' || $newSupport == ''){
        $response['success'] = false;
        $response['title'] = "Ticket Not Assign";
        $response['msg'] = 'Ticket Assign unsuccessfully';
        echo json_encode($response);
        exit;
    }
    $supportIdArray = explode(",",$ticketIds);
    $newSupportArray = explode(",",$newSupport);
    foreach($supportIdArray as $lead => $ticketId){
        foreach($newSupportArray as $supportId) {

            $ticketData = $db->FetchToArray("tickets","*","ticket_id = $ticketId");
            $db->Insert("ticket_history",$ticketData[0]);

            $updateData = array_merge($db->TimeStampAtUpdate($user_id),array("assign_to"=>$supportId,"assign_to_date"=>DATE_TIME_DATABASE));
            $db->UpdateWhere("tickets",$updateData,"ticket_id = $ticketId");

        }
    }
    $response['success'] = true;
    $response['title'] = "Ticket Assign";
    $response['msg'] = 'Ticket Assign successfully';
    echo json_encode($response);

}elseif($action == 'changetablestate'){
    $data = $db->FilterParameters($_POST);
    $data['column_hidden'] = json_encode($data['column_shown']);
    $data['user_id'] = $user_id;
    $data['last_updated'] = date('Y-m-d H:i:s');
    $res = $db->FetchRowWhere("user_table_state", array('user_table_state_id'),"page='".$_POST['page']."' and user_id = '$user_id'");
    $resCount = $db->CountResultRows($res);
    if($resCount > 0){
        $res = $db->UpdateWhere("user_table_state",$data,"page='".$_POST['page']."' and user_id = '$user_id'");
    } else {
        $res = $db->Insert("user_table_state",$data);
    }
    return $res;

} elseif($action == 'ticketinfo'){
    $data = $db->FilterParameters($_POST);
    $ticketId = isset($data['ticket_id']) ? $data['ticket_id'] : "";
    if($ticketId != ''){
        $mainTable = array("tickets as t",array("t.*"));
        $joinTable = array(
            array("left","customer_master as cm","t.customer_id = cm.customer_id",array('cm.customer_name','cm.email','cm.mobile_no, cm.personal_mobile_no ')),
            array("left","loan_type_master as ltm","t.loan_type_id = ltm.loan_type_id",array("ltm.loan_type_name")),
            array("left","category_master as cms","t.product_type_id = cms.category_id",array("cms.category_name")),
            array("left","reason_master as rm","t.reason_id = rm.reason_id",array("rm.reason_name")),
            array("left","query_stage_master as qsm","t.query_stage_id = qsm.query_stage_id",array("qsm.query_stage_name")),
            array("left","sub_query_stage_master as sqsm","t.sub_query_stage_id = sqsm.sub_query_stage_id",array("sqsm.sub_query_stage_name")),
            array("left","query_type_master as qtm","t.query_type_id = qtm.query_type_id",array("qtm.query_type_name")),
            array("left","disposition_master as dm","t.disposition_id = dm.disposition_id",array("dm.disposition_name")),
            array("left","status_master as sm","t.status_id = sm.status_id",array("sm.status_name")),
            array("left","admin_user as cu","t.created_by = cu.user_id",array("concat(cu.first_name,' ',cu.last_name) as created_by")),
            array("left","admin_user as au","t.assign_to = au.user_id",array('concat(au.first_name," ",au.last_name) as assign_to')),
            array("left","admin_user as ae2","t.escalate_to_2 = ae2.user_id",array('concat(ae2.first_name," ",ae2.last_name) as escalate_2')),
            array("left","admin_user as ae3","t.escalate_to_3 = ae3.user_id",array('concat(ae3.first_name," ",ae3.last_name) as escalate_3')),
            array("left","admin_user as uu","t.updated_by = uu.user_id",array('concat(uu.first_name," ",uu.last_name) as updated_by')),
            array("left","tickets as t2","t2.ticket_id = t.merged_id",array('t2.ticket_number as merged_ticket_number')),
        );
        $ticketR = $db->JoinFetch($mainTable,$joinTable,"t.ticket_id = $ticketId");
        $ticketInfo = $db->FetchRowInAssocArray($ticketR);

        //core::PrintArray($activityHistory);
        $html = '';
        if(count($ticketInfo) > 0){

            $ticketDocument = $db->FetchToArray("ticket_documents","*","ticket_id = '{$ticketInfo['ticket_id']}' order by ticket_history_id desc");
            $imageHtml = '';
            if(count($ticketDocument) > 0){
                $imageHtml = '';
                foreach($ticketDocument as $documentData){
                    $fileExt = pathinfo($documentData['filename'],PATHINFO_EXTENSION);
                    $fileabsPath = (!in_array($fileExt,Utility::imageExtensions())) ? "uploads/docimage.png" : TICKET_IMAGE_PATH.$documentData['filename'];

                    if($documentData['filename'] != '' && file_exists(TICKET_IMAGE_PATH_ABS.$documentData['filename'])){

                        $imageHtml .= "<div class='row-fluid inline' id='new_image_".$documentData['ticket_document_id']."'>";
                        $imageHtml .= "<ul class='ace-thumbnails'>";
                        $imageHtml .= " <li>";
                        $imageHtml .= " <a href='javascript:void(0);' class='cboxElement'>";
                        $imageHtml .= "<img src='".$fileabsPath."' alt='".$documentData['real_filename']."' title='".$documentData['real_filename']."' style='width: 150px;height: 150px'>";
                        $imageHtml .= "</a>";

                        $imageHtml .= "<div class='tools tools-bottom'>";
                        $imageHtml .= "<a href='javascript:void(0);' onclick='DeleteImage(".$documentData['ticket_document_id'].")'>";
                        $imageHtml .= "<i class='icon-remove red'></i>";
                        $imageHtml .= "</a>";

                        $imageHtml .= "<a href='".TICKET_IMAGE_PATH.$documentData['filename']."' target='_blank'  data-placement='top' data-rel='tooltip' data-original-title='Download'>";
                        $imageHtml .= "<i class='icon-download'></i>";
                        $imageHtml .= "</a>";
                        $imageHtml .= "</div>";
                        $imageHtml .= "</li>";
                        $imageHtml .= "</ul>";
                        $imageHtml .= " </div>";

                    }
                }
            }

            $html .= '<table class="table table-bordered">';
            $html .= '<tr><th>Ticket Number</th>';
            $html .= '<td>'.$ticketInfo['ticket_number'].'</td><tr>';

            $html .= '<tr><th>Merged With</th>';
            $html .= '<td>'.$ticketInfo['merged_ticket_number'].'</td><tr>';

            $html .= '<tr><th>Customer Name</th>';
            $html .= '<td>'.$ticketInfo['customer_name'].'</td><tr>';

            $html .= '<tr><th>Email</th>';
            $html .= '<td>'.$ticketInfo['email'].'</td><tr>';

            $html .= '<tr><th>Loan Type</th>';
            $html .= '<td>'.$ticketInfo['loan_type_name'].'</td><tr>';

            $html .= '<tr><th>Product Type</th>';
            $html .= '<td>'.$ticketInfo['category_name'].'</td><tr>';

            $html .= '<tr><th>Mobile</th>';
            $html .= '<td>'.$ticketInfo['mobile_no'].'</td><tr>';
            
            $html .= '<tr><th>Personal Mobile No</th>';
            $html .= '<td>'.$ticketInfo['personal_mobile_no'].'</td><tr>';

            $html .= '<tr><th>Reason</th>';
            $html .= '<td>'.$ticketInfo['reason_name'].'</td><tr>';

            $html .= '<tr><th>Query Received From</th>';
            $html .= '<td>'.$ticketInfo['call_from'].'</td><tr>';

            $html .= '<tr><th>Stage</th>';
            $html .= '<td>'.$ticketInfo['query_stage_name'].'</td><tr>';

            $html .= '<tr><th>Query Type</th>';
            $html .= '<td>'.$ticketInfo['query_type_name'].'</td><tr>';

            $html .= '<tr><th>Sub Query Stage</th>';
            $html .= '<td>'.$ticketInfo['sub_query_stage_name'].'</td><tr>';

            $html .= '<tr><th>Comment</th>';
            $html .= '<td>'.$ticketInfo['comment'].'</td><tr>';


            $html .= '<tr><th>Attachment</th>';
            $html .= '<td>'.$imageHtml.'</td><tr>';

            $html .= '<tr><th>Expected Time</th>';
            $html .= '<td>'.$ticketInfo['resolve_date_time'].'</td><tr>';

            $html .= '<tr><th>Disposition</th>';
            $html .= '<td>'.$ticketInfo['disposition_name'].'</td><tr>';

            $html .= '<tr><th>Status</th>';
            $html .= '<td>'.$ticketInfo['status_name'].'</td><tr>';

            $html .= '<tr><th>Created By</th>';
            $html .= '<td>'.$ticketInfo['created_by'].'</td><tr>';

            $html .= '<tr><th>Created On</th>';
            $html .= '<td>'.core::YMDToDMY($ticketInfo['created_at'],true).'</td><tr>';

            $html .= '<tr><th>Updated By</th>';
            $html .= '<td>'.$ticketInfo['updated_by'].'</td><tr>';

            $html .= '<tr><th>Updated On</th>';
            $html .= '<td>'.core::YMDToDMY($ticketInfo['updated_at'],true).'</td><tr>';

            $html .= '<tr><th>Assign To</th>';
            $html .= '<td>'.$ticketInfo['assign_to'].'</td><tr>';

            $html .= '<tr><th>Assign Date</th>';
            // $html .= '<td>'.core::YMDToDMY($ticketInfo['assign_to_date'],true).'</td><tr>';
             if($ticketInfo['assign_to_date'] != "0000-00-00 00:00:00" && $ticketInfo['assign_to_date'] != null && $ticketInfo['assign_to_date'] != " " ){
            $html .= '<td>'. core::YMDToDMY($ticketInfo['assign_to_date'],true) .'</td>';
            }else{
                $html .= '<td></td>';
            }

            $html .= '<tr><th>Escalate 2</th>';
            $html .= '<td>'.$ticketInfo['escalate_2'].'</td><tr>';

            $html .= '<tr><th>Escalate 2 Date</th>';
            if($ticketInfo['escalate_to_2_date'] != "1970-01-01" && $ticketInfo['escalate_to_2_date'] != null && $ticketInfo['escalate_to_2_date'] != " " ){
            $html .= '<td>'. core::YMDToDMY($ticketInfo['escalate_to_2_date'],true) .'</td>';
            }else{
                $html .= '<td></td>';
            }
            $html .= '<tr>';

            $html .= '<tr><th>Escalate 3</th>';
            $html .= '<td>'.$ticketInfo['escalate_3'].'</td><tr>';

            $html .= '<tr><th>Escalate 3 Date</th>';
            if($ticketInfo['escalate_to_3_date'] != "1970-01-01" && $ticketInfo['escalate_to_3_date'] != null && $ticketInfo['escalate_to_3_date'] != " " ){
                $html .= '<td>'. core::YMDToDMY($ticketInfo['escalate_to_3_date'],true) .'</td>';
            }else{
                $html .= '<td></td>';
            }
            $html .= '<tr>';

            $html .= '</table>';
            echo $html;
        } else {
            echo NO_RESULT;
        }

    } else {
        echo NO_RESULT;
    }
}elseif($action == 'getcomment'){
    $data = $db->FilterParameters($_POST);
    $ticketId = isset($data['ticket_id']) ? $data['ticket_id'] : "";
    $html = '';
    if($ticketId != ''){
        $html = $db->FetchCellValue("tickets","comment","ticket_id = '{$ticketId}'");
    }
    echo $html;

}elseif($action == 'synMail'){
    $data = $db->FilterParameters($_POST);
    $return = Utility::syncEmailCommands();
    if($return){
        $response['success'] = true;
        $response['title'] = "Success";
        $response['msg'] = "Synchronize Done";
    } else {
        $response['success'] = false;
        $response['title'] = "Unsuccess";
        $response['msg'] = "No new mail available";
    }
    echo json_encode($response);
}
include_once 'footer.php';
