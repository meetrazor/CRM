<?php
include_once 'header.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "lead_master";
$table_id = "lead_id";
$response='';
if(1==1) {
//if(core::isAjax() && isset($_GET['etoken']) && $token == $_GET['etoken']) {
}
if('fetch' == $action){
    $childIds = Utility::getUserChild($user_id,"user");
    $table = "$table as l";
    $leadId = isset($_GET['lead_id']) ? $db->FilterParameters($_GET['lead_id']) : "";
    $columns = array(
        'l.lead_id','l.lead_code','l.lead_name','l.email','l.remarks','am.activity_type','sm.status_name',
        'l.mobile_no','cam.category_name','NULL as cw_status_name',
        'concat(pm.first_name," ",pm.last_name) as partner_name',
        'IF(lu.user_type = "bd",group_concat(concat(bu.first_name," ",bu.last_name)), NULL) as bd_name',
        'IF(lu.user_type = "kc", group_concat(concat(bu.first_name," ",bu.last_name)), NULL) as kc_name',
        'cm.customer_name',
        'l.created_at','l.updated_at','l.customer_check','l.check_time','s.state_name','c.city_name'

    );
    $seach_columns = array(
        'l.lead_name','l.email','l.mobile_no',
        'IF(lu.user_type = "bd", concat(bu.first_name," ",bu.last_name), NULL)',
        'IF(lu.user_type = "kc", concat(bu.first_name," ",bu.last_name), NULL)',
        'concat(pm.first_name," ",pm.last_name)','cm.customer_name','s.state_name','c.city_name'
    );

    $joins = " LEFT JOIN customer_master as cm ON cm.customer_id = l.customer_id";
    $joins .= " LEFT JOIN partner_master as pm ON pm.partner_id = l.partner_id";
    $joins .= " left join activity_master am ON (l.lead_id = am.type_id)";
    $joins .= " LEFT JOIN status_master as sm ON sm.status_id = am.status_id";
    $joins .= " left join lead_users lu ON (lu.lead_id = l.lead_id)";
    $joins .= " LEFT JOIN admin_user as bu ON bu.user_id = lu.user_id";
    $joins .= " LEFT JOIN category_master as cam ON cam.category_id = l.category_id";
    $joins .= " LEFT JOIN state as s ON (l.state_id = s.state_id)";
    $joins .= " LEFT JOIN city as c ON (c.city_id = l.city_id)";

    $sql_where = "WHERE 1=1 and lu.is_latest = 1 and am.is_latest = 1 and am.activity_on = 'lead'";

    if($leadId != ''){
        $sql_where .= " and l.lead_id  = '{$leadId}'";
    }

    if($userType == UT_BD || $userType == UT_KC || $userType == UT_IA){
        $res = Utility::getReportingUserId(array("$user_id"),array(),$userType);
        $childIds = implode(",",Utility::getUniqueArray($res));
        $sql_where .= " and lu.user_id in ($childIds) and lu.is_latest = 1 and user_type_id in (".$userType.")";

    }



    if ($_GET['sSearch'] != "")
    {

        $seach_condition = "";
        foreach ($seach_columns as $column)
        {
            $column = strtolower($column);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, $index + 4);
            }

            $seach_condition .= $column . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch'] ) . "%' OR ";
        }
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    include_once 'lead_filter.php';


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
        $sql_order = substr_replace( $sql_order, "", -2 );
    }
    $sql_group = "group by lu.lead_id,lu.user_type";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

    //echo $sql;exit;
    $main_query = mysql_query($sql) or die(mysql_error());

    // get the number of filtered rows
    $filtered_rows_query = mysql_query("SELECT FOUND_ROWS()") or die(mysql_error());

    $row = mysql_fetch_array($filtered_rows_query);
    $response['iTotalDisplayRecords'] = $row[0];
    $response['iTotalRecords'] = $row[0];

    $response['sEcho'] = intval($_GET['sEcho']);
    $response['aaData'] = array();
    $db->UpdateWhere("lead_master",array("is_new"=>1),"1=1");
    while ($row = $db->MySqlFetchRow($main_query))
    {
        $row['created_at'] = ($row['created_at'] != '' && $row['created_at'] != '0000-00-00') ? Core::YMDToDMY($row['created_at'],true) : "";
        $row['updated_at'] = ($row['updated_at'] != '' && $row['updated_at'] != '0000-00-00') ? Core::YMDToDMY($row['updated_at'],true) : "";
        $row['check_time'] = ($row['check_time'] != '' && $row['check_time'] != '0000-00-00') ? Core::YMDToDMY($row['check_time'],true) : "";
        //$row['follow_up_date_time'] = ($row['follow_up_date_time'] != '0000-00-00 00:00:00' && $row['follow_up_date_time'] != '') ? core::YMDToDMY($row['follow_up_date_time'],true) : "";
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}elseif($action == 'delete'){
    $is_delete = true;
    $ids = $db->FilterParameters($_POST['id']);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    if(is_array($ids)){
        foreach($ids as $delId){
            $deleteR = $db->FetchRowWhere("partner_lead", array('lead_id'),"$table_id = '{$delId}'");
            $deleteC = $db->CountResultRows($deleteR);
            if($deleteC > 0){
                $is_delete = false;
                break;
            }
        }
    } else {
        $deleteR = $db->FetchRowWhere("partner_lead", array('lead_id'),"$table_id = '{$ids}'");
        $deleteC = $db->CountResultRows($deleteR);
        if($deleteC > 0){
            $is_delete = false;
        }
    }
    if($is_delete) {
        $condition = "$table_id in ('$id')";
        $result = $db->DeleteWhere($table, $condition);
        $db->DeleteWhere("partner_lead", $condition);
        $response['success'] = true;
        $response['title'] = "Records Deleted";
        $response['msg'] = ' lead (s) deleted successfully';
    } else {
        $response['success'] = false;
        $response['title'] = "Can't delete Records";
        $response['msg'] = "already assign lead to partner";
    }

    echo json_encode($response);
}elseif($action == 'assign'){
    $data = $db->FilterParameters($_POST);
    $leadIds = $data['assign_lead_id'];
    $leadIdArray = explode(",",$leadIds);
    $assignPartnerId = $data['partner_id'];
    foreach($leadIdArray as $lead => $id){
        $db->Insert("partner_lead",array(
            "lead_id" => $id,
            "partner_id" => $assignPartnerId,
            "assign_at"=>date('Y-m-d'),
            "assign_from"=>"admin",
        ));
        $mainTable = array("lead_master as l",array('l.lead_id','l.lead_code','l.lead_name','l.email','l.mobile_no'));
        $joinTable = array(
            array("left","partner_master as pm","pm.partner_id = l.partner_id",array('concat(pm.first_name," ",pm.last_name) as partner_name','pm.partner_id')),
            array("left","customer_master as cm","cm.customer_id = l.customer_id",array('cm.customer_name','cm.mobile_no as customer_number','cm.customer_id')),
            array("left","status_master as sm","sm.status_id = l.status_id",array('sm.status_name')),
            array("left","category_master as cam","cam.category_id = l.category_id",array('cam.category_name')),
        );
        $leadCondition = "l.lead_id = '{$id}'";
        $leadQ = $db->JoinFetch($mainTable,$joinTable,$leadCondition,array("l.created_at"=>"desc"));
        $leadR = $db->FetchToArrayFromResultset($leadQ);
        if(count($leadR) > 0) {
            Utility::sendPushNotificationForLead($insertId, $leadR[0]['lead_name'], $leadR[0]['partner_id']);
            $partnerDetails = Utility::getPartnerForLead($leadR[0]['partner_id']);
            $partnerMobile = isset($partnerDetails[0]['mobile_no']) ? $partnerDetails[0]['mobile_no'] : "";
            $partnerName = isset($partnerDetails[0]['partner_name']) ? $partnerDetails[0]['partner_name'] : "";
            $customerMobile = isset($leadR[0]['customer_number']) ? $leadR[0]['customer_number'] : "";
            $customerName = isset($leadR[0]['customer_name']) ? $leadR[0]['customer_name'] : "";
            $customerId = isset($leadR[0]['customer_id']) ? $leadR[0]['customer_id'] : "";
            $message = "New lead available - {$leadR[0]['lead_name']}";
            $status = Utility::sendSMS($partnerMobile,$message);
            Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$leadR[0]['partner_id'],"",$message,$partnerMobile,'sms',"partner",$leadR[0]['partner_id']);
            $customerMessage = "Hello ".$customerName." - ".$partnerName." is assigned to help you in your Capita World Application.";
            $status = Utility::sendSMS($customerMobile,$customerMessage);
            Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$customerId,"",$customerMessage,$customerMobile,'sms','customer',$customerId);
        }
    }
    $leadUpdate = $db->UpdateWhere($table,array("partner_id"=>$assignPartnerId),"lead_id IN ($leadIds)");
    $response['success'] = true;
    $response['title'] = "Lead Assign";
    $response['msg'] = 'Lead Assign successfully';
    echo json_encode($response);

}
elseif($action == 'changebd'){
    $data = $db->FilterParameters($_POST);
    $leadIds =  isset($data['bd_lead_id']) ? $data['bd_lead_id'] : "";
    $newBd =  isset($data['bd_user_id']) ? $data['bd_user_id'] : "";
    if($leadIds == '' || $newBd == ''){
        $response['success'] = false;
        $response['title'] = "Lead Not Assign";
        $response['msg'] = 'Lead Assign unsuccessfully';
        echo json_encode($response);
        exit;
    }
    $leadIdArray = explode(",",$leadIds);
    $newBdArray = explode(",",$newBd);
    foreach($leadIdArray as $lead => $id){
        foreach($newBdArray as $bdId) {
            $oldLeadBd = $db->FetchToArray("lead_users","user_id","lead_id = '{$id}' and user_type = 'bd' and is_latest = 1");
            if(!in_array($bdId,$oldLeadBd)){
                $db->UpdateWhere("lead_users",array("is_latest"=>0),"lead_id = '{$id}' and user_type = 'bd'");
                $db->Insert("lead_users",array(
                    "lead_id" => $id,
                    "user_id" => $bdId,
                    "user_type" => "bd",
                    "user_type_id" => UT_BD,
                    "is_latest" => "1",
                    "created_at"=>DATE_TIME_DATABASE,
                    "created_by"=>$user_id,
                ));
            }
        }
    }
    $response['success'] = true;
    $response['title'] = "Lead Assign";
    $response['msg'] = 'Lead Assign successfully';
    echo json_encode($response);

}elseif($action == 'leadinfod'){
    $leadId = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : "";
    if($leadId == ''){
        return $leadDetails = "no record found";
    }
    $mainTable = array("lead_master as lm",array("lm.*"));
    $joinTable = array(
        array("left","state as s","s.state_id = lm.state_id",array("s.state_name")),
        array("left","city as c","c.city_id = lm.city_id",array("c.city_name")),
        array("left","partner_master as pm","pm.partner_id = lm.partner_id",array('concat(pm.first_name," ",pm.last_name) as partner_name')),
        array("left","customer_master as cm","cm.customer_id = lm.customer_id",array("cm.customer_name")),
    );
    $res = $db->JoinFetch($mainTable,$joinTable,"lead_id = '{$leadId}'");
    $leadInfo = $db->FetchToArrayFromResultset($res);
    $leadDetails = "";
    if(count($leadInfo) > 0){
        $leadDetails .= "<table class='table table-bordered'>";
        $leadDetails .= "<tr><th>Lead Name</th><td>";
        $leadDetails .= $leadInfo[0]['lead_name'];
        $leadDetails .= "</td></tr>";
        //        $leadDetails .= "<tr><th>Email Address</th><td>";
        //        $leadDetails .= $leadInfo[0]['email'];
        //        $leadDetails .= "</td></tr>";
        //        $leadDetails .= "<tr><th>Phone Number</th><td>";
        //        $leadDetails .= $leadInfo[0]['mobile_no'];
        //        $leadDetails .= "</td></tr>";
        $leadDetails .= "<tr><th>Address</th><td>";
        $leadDetails .= $leadInfo[0]['address'];
        $leadDetails .= "</td></tr>";
        $leadDetails .= "<tr><th>City</th><td>";
        $leadDetails .= $leadInfo[0]['city_name'];
        $leadDetails .= "</td></tr>";
        $leadDetails .= "<tr><th>State</th><td>";
        $leadDetails .= $leadInfo[0]['state_name'];
        $leadDetails .= "</td></tr>";
        $leadDetails .= "<tr><th>Partner</th><td>";
        $leadDetails .= $leadInfo[0]['partner_name'];
        $leadDetails .= "</td></tr>";
        $leadDetails .= "<tr><th>Customer</th><td>";
        $leadDetails .= $leadInfo[0]['customer_name'];
        $leadDetails .= "</td></tr>";
        $leadDetails .= "</table>";
    }
    echo $leadDetails;

} elseif ($action == 'getleads'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("lead_name","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("lead_id as value","concat(lead_name,' ',mobile_no) as text"),$condition,array("lead_name"=>"asc"));

    echo json_encode($leadInfo);
} elseif($action == 'updatestatus'){
    $data = $db->FilterParameters($_POST);
    if($data['lead_id'] != ''){
        $status = $db->UpdateWhere($table,array_merge(array("status_id"=>$data['value']),$db->TimeStampAtUpdate($user_id)),"lead_id = '{$data['lead_id']}'");
    }
}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";
    if(isset($leadId) and $leadId != ''){
        $emailRes = $db->FetchRowWhere($table, array($table_id),"email='$email' and lead_id != '{$leadId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array($table_id),"email='$email'");
    }
    $emailResC = $db->CountResultRows($emailRes);
    if($emailResC > 0){
        $flag = "false";
    }
    echo $flag;
}elseif($action == 'checkmobile'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $mobile = $data['mobile'];
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";
    if(isset($leadId) and $leadId != ''){
        $mobileRes = $db->FetchRowWhere($table, array($table_id),"mobile='$mobile' and lead_id != '{$leadId}' and lead_manager = '{$user_id}'");
    } else {
        $mobileRes = $db->FetchRowWhere($table, array($table_id),"mobile='$mobile' and lead_manager = '{$user_id}'");
    }
    $mobileResC = $db->CountResultRows($mobileRes);
    if($mobileResC > 0){
        $flag = "false";
    }
    echo $flag;
} elseif ($action == 'leadreport'){
    $data = $db->FilterParameters($_POST);
    $leadInfoChart = array();
    $groupBy = isset($data['group_by']) ? $data['group_by'] : "source";
    $groupByColumnArray = array(
        "source" => 'ls.source_name',
        "month" => "concat(MONTHNAME(l.created_at),'-',YEAR(l.created_at))",
        "region" => "r.region_name",
        "zone" => "z.zone_name",
        "branch" => "b.branch_name",
    );
    $groupByColumn = $groupByColumnArray[$groupBy];
    $groupByArray = array(
        "source" => 'ls.source_name',
        "month" => "MONTHNAME(l.created_at),year(l.created_at)",
        "region" => "r.region_name",
        "zone" => "z.zone_name",
        "branch" => "b.branch_name",
    );
    $groupBy = $groupByArray[$groupBy];
    $sql_where = '1=1';
    if(isset($data['days']) && $data['days'] != ''){
        $date_range_str = $data['days'];
        $date_range_arr = explode(" to ", $date_range_str);
        $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
        $rangeTo = Core::DMYToYMD($date_range_arr[1]);
        if($rangeFrom == $rangeTo){
            $range_condition = " && (DATE_FORMAT(l.created_at,'%Y-%m-%d') = '$rangeFrom')";
        } else {
            $range_condition = " && (DATE_FORMAT(l.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(l.created_at,'%Y-%m-%d') <= '$rangeTo')";
        }
        $sql_where .= $range_condition;
    } else {
        $sql_where .= " && (DATE_FORMAT(l.created_at,'%Y-%m-%d') >= '".date('Y-m-d',strtotime("-6 days"))."' AND DATE_FORMAT(l.created_at,'%Y-%m-%d') <= '".date('Y-m-d')."')";
    }
    if($ses->Get("user_type") == UT_TELECALLER){
        $sql_where .= " and l.telecaller_id  = '{$user_id}'";
    } else {
        $childIds = implode(",",Utility::getChildIds($user_id,$ses->Get("user_type")));
        $sql_where .= " and l.lead_manager in ($childIds)";
    }
    $mainTable = array("$table as l",array("count(l.lead_id) as count","$groupByColumn AS xasix"));
    $joinTable = array(
        array("right","lead_sources as ls","ls.source_id = l.source_id",array("ls.source_id")),
        array("left","admin_user as u","u.user_id = l.lead_manager"),
        array("left","branches as b","u.branch_id = b.branch_id"),
        array("left","regions as r","b.region_id = r.region_id"),
        array("left","zones as z","b.zone_id = z.zone_id")
    );
    $leadInfoR = $db->JoinFetch($mainTable,$joinTable,$sql_where,null,null,$groupBy);
    $leadInfo = $db->FetchToArrayFromResultset($leadInfoR);
    if(count($leadInfo) > 0){
        foreach($leadInfo as $key => $info){
            $leadInfoChart[$key]['label'] = $info['xasix'];
            $leadInfoChart[$key]['data'] = $info['count'];
        }
    }
    echo json_encode($leadInfoChart);
} elseif ($action == 'completelead') {
    $data = $db->FilterParameters($_POST);
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";

    if($leadId != '') {
        $leadData = $db->FetchRowForForm($table,"*","lead_id = '{$leadId}'");
        $tierId = $db->FetchCellValue("city","tier_id","city_id = '{$leadData['city_id']}'");
        $calPrice = $db->FunctionFetch("status_master", 'count', array("status_id"), "cal_price = 1 and status_id = '{$leadData['status_id']}'", array(0,1));
        if($calPrice == 1) {
            $db->UpdateWhere($table,array(
                "check_by" => $user_id,
                "customer_check" => 1,
                "check_time" => DATE_TIME_DATABASE
            ),"lead_id = '{$leadId}'");
            $catPrice = $db->FetchCellValue("tier_category","commission","category_id = '{$leadData['category_id']}' and tier_id = '{$tierId}'");
            $commData = array(
                "partner_id" => $leadData['partner_id'],
                "amount" => $catPrice,
                "category_id" => $leadData['category_id'],
                "lead_id" => $leadId,
            );
            $commData = array_merge($commData,$db->TimeStampAtCreate($user_id));
            Utility::addOrFetchFromTable("partner_commission",$commData,"partner_commission_id","lead_id = '{$leadId}'");
            $oldBalance = $db->FetchCellValue("partner_ledger", "ledger_balance","partner_id = '{$leadData['partner_id']}' order by updated_at desc");
            $newBalance = intval($oldBalance) + intval($catPrice);
            $ledgerData = array(
                "partner_id" => $leadData['partner_id'],
                "amount" => $catPrice,
                "ledger_type" => 'C',
                "type_id" => $leadId,
                "ledger_from" => "lead",
                "ledger_balance" => $newBalance,
            );
            $ledgerData = array_merge($ledgerData,$db->TimeStampAtCreate($user_id));
            Utility::addOrFetchFromTable("partner_ledger",$ledgerData,"partner_ledger_id","type_id = '{$leadId}' and ledger_from = 'lead'");
        }
        $response['success'] = true;
        $response['title'] = "Successful";
        $response['msg'] = "Record updated successfully";
    } else {
        $response['success'] = false;
        $response['title'] = "Unsuccessful";
        $response['msg'] = "Record updated Unsuccessfully";
    }

    echo json_encode($response);
} elseif($action == 'leadinfo'){
    $data = $db->FilterParameters($_POST);
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";
    if($leadId != ''){
        $mainTable = array("lead_master as lm",array("lm.*"));
        $joinTable = array(
            array("left","state as s","s.state_id = lm.state_id",array("s.state_name")),
            array("left","city as c","c.city_id = lm.city_id",array("c.city_name")),
            array("left","partner_master as pm","pm.partner_id = lm.partner_id",array('concat(pm.first_name," ",pm.last_name) as partner_name')),
            array("left","customer_master as cm","cm.customer_id = lm.customer_id",array("cm.customer_name")),
        );
        $leadR = $db->JoinFetch($mainTable,$joinTable,"lm.lead_id in ($leadId)");
        $leadInfo = $db->FetchToArrayFromResultset($leadR);
        $html = '';
        if(count($leadInfo) > 0){
            $html .= '<table class="table table-bordered">';
            $html .= '<tr><th>Lead Code</th>';
            $html .= '<td>'.$leadInfo[0]['lead_code'].'</td><tr>';
            $html .= '<tr><th>Lead Name</th>';
            $html .= '<td>'.$leadInfo[0]['lead_name'].'</td><tr>';
            $html .= '<tr><th>Email</th>';
            $html .= '<td>'.$leadInfo[0]['email'].'</td><tr>';
            $html .= '<tr><th>Mobile</th>';
            $html .= '<td>'.$leadInfo[0]['mobile_no'].'</td><tr>';
            $html .= '<tr><th>Amount</th>';
            $html .= '<td>'.$leadInfo[0]['amount'].'</td><tr>';
            $html .= '<tr><th>Actual Amount</th>';
            $html .= '<td>'.$leadInfo[0]['actual_amount'].'</td><tr>';
            $html .= '<tr><th>Address</th>';
            $html .= '<td>'.$leadInfo[0]['address'].'</td><tr>';
            $html .= '<tr><th>State</th>';
            $html .= '<td>'.$leadInfo[0]['state_name'].'</td><tr>';
            $html .= '<tr><th>City</th>';
            $html .= '<td>'.$leadInfo[0]['city_name'].'</td><tr>';
            $html .= '<tr><th>Pincode</th>';
            $html .= '<td>'.$leadInfo[0]['pincode'].'</td><tr>';

            $html .= '</table>';
            echo $html;
        } else {
            echo NO_RESULT;
        }

    } else {
        echo NO_RESULT;
    }
} elseif($action == "notificationupdate"){
    $notificationId = isset($_POST['notification_id']) ? $_POST['notification_id'] : "";
    return $nf->markSubjectNotificationsSeen($user_id,$notificationId);
} elseif($action == "getundisplaynotification"){
    $notification = $nf->getUnDisplayNotifications($user_id);
    echo json_encode($notification);
}
//} elseif($action == "notificationupdate"){
//    $notificationId = isset($_POST['notification_id']) ? $_POST['notification_id'] : "";
//    return $nf->markSubjectNotificationsSeen($user_id,$notificationId);
//} elseif($action == "getundisplaynotification"){
//
//        if($user_id != '') {
//            $response = array();
//            $main_table = array("lead_user as lu",array("lu.*"));
//            $join_table = array(
//                array("INNER","lead_master as lm","lu.lead_id = lm.lead_us",array("lm.lead_name")),
//                array("INNER","admin_user as au","au.user_id = lu.created_by",array("CONCAT(lu.first_name,' ',lu.last_name) AS created_by")));
//            $res = $db->JoinFetch($main_table, $join_table,"user_id = '{$user_id}' and is_display = 0",array("created_at"=>"DESC"),"LIMIT 0, 1");
//            $result = $db->FetchToArrayFromResultset($res);
//            if(count($result) > 0){
//                foreach ($result as $data) {
//                    $response = array(
//                        'message' => $data['created_by']."assign you new Lead".$data['lead_name'],
//                        'lead_user_id' => $data['lead_user_id'],
//                    );
//                }
//            }
//        } else {
//            $response = array();
//        }
//    echo json_encode($response);
//}
include_once 'footer.php';

