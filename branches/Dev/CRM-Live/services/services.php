<?php
//staus:
//1- posted
//2- accepted
//3- approved
//4- done
header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");

$log_flag = true;
$log_table = 'api_request';

include_once 'header.php';
//$file_name = date('d_m_Y_H_i_s') . '.txt';
$json = file_get_contents("php://input");

//$json = file_get_contents("test.json");

// Code to log request starts
//$data = json_decode($json, true);
$action = isset($_GET['action']) ? $_GET['action'] : "" ;
$request_log['request_time'] = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$request_log['request_data'] = json_encode($_GET);
$request_log['request_type'] = $action;
//echo $action;
if(isset($action) && $action == 'loginvalidate'){

    //    print_r($_GET);
    $data = $_GET;
    $device_id = isset($_GET['device_id'])?$_GET['device_id']:0;

    $response = array();
    $data['pass'] = isset($_GET['password']) ? md5($_GET['password']) : "";
    $data['email'] = isset($_GET['email']) ? $_GET['email'] : "";
    $condition = " ( email='{$data['email']}' || mobile_no='{$data['email']}' ) && pass='{$data['pass']}'";
    $res = $db->FetchRowWhere('partner_master', array('partner_id','email','pass','is_active', 'first_name', 'last_name'), $condition);
    $counter = $db->CountResultRows($res);
    if(1 == $counter) {

        $userData = mysql_fetch_assoc($res);

//        print_r($userData);

        if($userData['is_active'] == 1){
            // user is allowed to access
            $info["partner_id"] = $userData["partner_id"];
            $info["email"] = $userData["email"];
//            $info["pass"] = $userData['pass'];
            $info['displayname'] = $userData['first_name']." ".$userData['last_name'];

            if(isset($_GET['deviceuid']) && $_GET['deviceuid']!="")
            {
                $updateDeviceInfo = array(
                    "device_uid" => isset($_GET['deviceuid']) ? $_GET['deviceuid'] : "",
                    "device_model" => isset($_GET['devicemodel']) ? $_GET['devicemodel'] : "",
                    "device_version" => isset($_GET['deviceversion']) ? $_GET['deviceversion'] : "",
                    "device_platform" => isset($_GET['deviceplatform']) ? $_GET['deviceplatform'] : "",
                    "gcm_reg_id" => isset($_GET['gcm_reg_id']) ? $_GET['gcm_reg_id'] : ""
                );

                $updateId = $db->UpdateWhere("partner_master",$updateDeviceInfo,"partner_id = '{$info["partner_id"]}'");
            }

            $response = array('success' => true, 'data' => $info);
        } else {
            $response = array('success' => false, 'error' => 'The user is Inactive from system');
        }
    } else {
        $response = array('success' => false, 'error' => 'Invalid User Login');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'forgotpassword'){
    $data = $_GET;

    $response = array();
    $data['email'] = isset($_GET['email']) ? $_GET['email'] : "";
    $condition = "email='{$data['email']}'";
    $res = $db->FetchRowWhere('partner_master', array('partner_id','email','pass','is_active', 'first_name', 'last_name'), $condition);
    $counter = $db->CountResultRows($res);
    if(1 == $counter) {

        $userData = mysql_fetch_assoc($res);

//        print_r($userData);

        if($userData['is_active'] == 1){

            $requestTime = DATE_TIME_DATABASE;
            $token = Core::GenRandomStr(30);
            $dataInsert['token'] = $token;
            $dataInsert['email'] = $data['email'];
            $dataInsert['created_at'] = date('Y-m-d H:i:s');
            $dataInsert['is_used'] = 0;
            $dataInsert['user_type'] = "partner";
            $dataInsert['type_id'] = $userData['partner_id'];

            $insert = $db->Insert('password_reset', $dataInsert);

            if($insert){
                $redirect_url = "".SITE_ROOT."reset_password.php?token=$token";
                // Sending Mail
                $message = "Your Password Reset Link : <br/>"
                    . "<a href='$redirect_url'>Click Here To Reset<a><br/><br/>"
                    . "Or<br/><br/>"
                    . "Go to URL : $redirect_url";
                $to      = array($userData['first_name']." ".$userData['last_name']=>$data['email']);
                $subject = "Alliance Partner : Your password reset link";

                $status = true;//sMail($to,"Alliance Partner",$subject,$message,"Alliance Partner",USER_NAME);

                Utility::insertEMailLog($requestTime,$status,$userData['partner_id'],"",$message,$data['email']);
            }

            $response = array('success' => true, 'message' => 'Reset Password information has been sent to your registered E-mail Id!');
        } else {
            $response = array('success' => false, 'error' => 'The user is Inactive from system');
        }
    } else {
        $response = array('success' => false, 'error' => 'Email id not exist. Please register OR contact Administrator!');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'myaccount'){

    $data = $_GET;

    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $condition = "partner_id = {$partnerId}";
            $res = $db->FetchToArray('partner_master pm', array('first_name', 'last_name', 'email', 'mobile_no'), $condition);
            if (count($res) > 0) {
                $response = array('success' => true, 'data' => $res);
            } else {
                $response = array('success' => false, 'error' => 'User not found!');
            }
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    }else{
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'register'){

    $flag = false;
    $res = array();
//    $data = $_GET;
    $data = $_GET;
    $response = array();
    $existCondition = "email='{$_GET['email']}'";
    $emailExist = $db->FunctionFetch("partner_master", 'count', array('partner_id'), $existCondition, array(0,1));
    $mobileExist = $db->FunctionFetch("partner_master", 'count', array('partner_id'), "mobile_no='{$_GET['mobile_no']}'", array(0,1));
    if($emailExist == 0)
    {
        if($mobileExist == 0){
            if(isset($_GET['deviceuid']) && $_GET['deviceuid']!="")
            {
                $updateDeviceInfo = array(
                    "device_uid" => isset($_GET['deviceuid']) ? $_GET['deviceuid'] : "",
                    "device_model" => isset($_GET['devicemodel']) ? $_GET['devicemodel'] : "",
                    "device_version" => isset($_GET['deviceversion']) ? $_GET['deviceversion'] : "",
                    "device_platform" => isset($_GET['deviceplatform']) ? $_GET['deviceplatform'] : "",
                    "gcm_reg_id" => isset($_GET['gcm_reg_id']) ? $_GET['gcm_reg_id'] : ""
                );
                $data = array_merge($data,$updateDeviceInfo);
            }
            $data['partner_code'] = Utility::GenerateNo("partner_master","partner");
            $data['pass'] = md5($data['pass']);
            $data["is_active"] = 0;
            $data = array_merge($data,$db->TimeStampAtCreate(APP_USER_ID));
            $insertId = $db->Insert("partner_master",$data,1);

            $response = array('success' => true, 'msg' => 'Registration Successful!', 'partner_id' => $insertId);
        }else{
            $response = array('success' => false,'error' => "User with the mobile no: {$data['mobile_no']} already exist");
        }
    } else {
        $response = array('success' => false,'error' => "User with the email: {$data['email']} already exist");
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';

}elseif(isset($action) && $action == 'updateaccount'){

    $data = $_GET;

    if(
        isset($data['partner_id']) && $data['partner_id'] != ""
    ) {

        $partnerId = isset($data['partner_id']) ? $data['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            $existCondition = "email='{$data['email']}' && partner_id!='{$partnerId}' ";
            $emailExist = $db->FunctionFetch("partner_master", 'count', array('partner_id'), $existCondition, array(0,1));
            if($emailExist == 0) {

                if(trim($data['pass']) == ""){
                    unset($data['pass']);
                }else{
                    $data['pass'] = md5($data['pass']);
                }

                $data = array_merge($data,$db->TimeStampAtUpdate(APP_USER_ID));
                $udpate = $db->Update("partner_master", $data, "partner_id", $partnerId);

                $response = array('success' => true, 'msg' => 'Account updated successful!', 'partner_id' => $partnerId);

            }else{
                $response = array('success' => false,'error' => "User with the email: {$data['email']} already exist");
            }

        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    }else{
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'withdrawrequest'){

    $data = $_GET;

    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            $data = array_merge($data,$db->TimeStampAtCreate(APP_USER_ID));
            $insertId = $db->Insert("partner_withdrawal", $data,1);

            if($insertId){
                $response = array('success' => true, 'msg' => 'Withdrawal request submitted successfully!', 'partner_id' => $partnerId);
            }

        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    }else{
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'getdashboardleads'){

    $data = $_GET;

    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";
        $filterStatus = isset($_GET['filter']) ? $_GET['filter'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            if($filterStatus == "all") {
//                $statusId = $db->FetchCellValue( "status_master", "status_id", " is_active=1 ");
                $statusClause = "1=1";
            }else{
                $statusId = $db->FetchCellValue( "status_master", "status_id", " status_name LIKE  '%{$filterStatus}%' ");
                $statusId = $statusId ? $statusId:-999;
                $statusClause = "lm.status_id = {$statusId}";
            }

            $condition = "{$statusClause} && lm.partner_id = {$partnerId} && lm.customer_id = cm.customer_id && sm.status_id = lm.status_id && lm.emergency_id = em.emergency_id ";
            $res = $db->FetchToArray('lead_master lm, partner_master pm, customer_master cm, status_master sm, emergency_master em', array('distinct(lm.lead_id)', 'lm.lead_name', 'cm.customer_name', 'lm.lead_code', 'cm.mobile_no', 'lcase(sm.status_name) as status_name', 'lm.address', 'lm.landmark','em.emergency_name'), $condition);
            if (count($res) > 0) {
                $response = array('success' => true, 'data' => $res);
            } else {
                $response = array('success' => true, 'data' => $res);
//                $response = array('success' => true, 'data' => $res);
            }
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    }else{
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'viewledger'){

    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";
        $start_index = (isset($_GET['start_index'])) ? intval($_GET['start_index']) : 0;
        $page_size = (isset($_GET['page_size'])) ? intval($_GET['page_size']) : PER_PAGE_LIMIT;

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $info["partner_id"] = $data["partner_id"];
            $info["start_index"] = $start_index;
            $info["page_size"] = $page_size;
            $mainTable = array("partner_ledger as pl",array('IF(pl.ledger_from = "lead",lm.lead_name,"withdrawal") as particular','IF(pl.ledger_type = "D",pl.amount,0) as debit_amount','IF(pl.ledger_type = "C",pl.amount,0) as credit_amount','pl.ledger_balance','lm.lead_id','pl.ledger_from'));
            $joinTable = array(
                array("left","partner_master as pm","pm.partner_id = pl.partner_id",array('concat(pm.first_name," ",pm.last_name) as partner_name')),
                array("left","lead_master as lm","lm.lead_id = pl.type_id",array('DATE_FORMAT(pl.created_at, \'%m-%d-%Y\') as created_date')),
            );
            $ledgerQ = $db->JoinFetch($mainTable,$joinTable,"pm.partner_id = '{$data['partner_id']}'",array("pl.created_at"=>"desc"),array($start_index,$page_size));
            $ledgerR = $db->FetchToArrayFromResultset($ledgerQ);
            $info["ledger_data"] = $ledgerR;
            $response = array('success' => true, 'data' => $info);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }

    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'viewnewleads'){
    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            $leadQuery = "
SELECT distinct(lm.lead_id), lm.lead_name, cm.customer_name, lm.lead_code, cm.mobile_no, lm.address, lm.landmark, em.emergency_name
FROM lead_master lm, partner_master pm, status_master sm, partner_category pc, customer_master cm, emergency_master em
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
-- AND lm.sub_locality_id = psl.sub_locality_id AND psl.partner_id = pm.partner_id
AND lm.city_id = pm.city_id
AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
AND pm.partner_id = {$partnerId}
AND cm.customer_id = lm.customer_id
AND em.emergency_id = lm.emergency_id
AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
-- AND lm.lead_id = 1
ORDER BY lm.lead_id DESC
";

            $leads = $db->FetchToArrayFromResultset($db->Query($leadQuery));

            if(count($leads) == 0){
                $leadQuery = "
SELECT distinct(lm.lead_id), lm.lead_name, cm.customer_name, lm.lead_code, cm.mobile_no, lm.address, lm.landmark, em.emergency_name
FROM lead_master lm, partner_master pm, status_master sm, partner_category pc, customer_master cm, emergency_master em
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
AND lm.city_id = pm.city_id
AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
AND pm.partner_id = {$partnerId}
AND cm.customer_id = lm.customer_id
AND em.emergency_id = lm.emergency_id
-- AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
ORDER BY lm.lead_id DESC
";

                $leads = $db->FetchToArrayFromResultset($db->Query($leadQuery));
            }

            if(count($leads) == 0){
                $leadQuery = "
SELECT distinct(lm.lead_id), lm.lead_name, cm.customer_name, lm.lead_code, cm.mobile_no, lm.address, lm.landmark, em.emergency_name
FROM lead_master lm, partner_master pm, status_master sm, customer_master cm, emergency_master em
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
AND lm.city_id = pm.city_id
-- AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
AND pm.partner_id = {$partnerId}
AND cm.customer_id = lm.customer_id
AND em.emergency_id = lm.emergency_id
-- AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
ORDER BY lm.lead_id DESC
";

                $leads = $db->FetchToArrayFromResultset($db->Query($leadQuery));
            }

//            $leadArr = array($leads);
            $response = array('success' => true, 'data' => $leads);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'viewlead'){
    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $info["partner_id"] = $data["partner_id"];
            $info["start_index"] = $start_index;
            $info["page_size"] = $page_size;
            $mainTable = array("lead_master as l",array('l.lead_id','l.lead_code','l.lead_name','l.email','l.mobile_no'));
            $joinTable = array(
                array("left","partner_master as pm","pm.partner_id = l.partner_id",array('concat(pm.first_name," ",pm.last_name) as partner_name')),
                array("left","customer_master as cm","cm.customer_id = l.customer_id",array('cm.customer_name','l.customer_check','l.check_time')),
                array("left","status_master as sm","sm.status_id = l.status_id",array('sm.status_name','sm.is_close')),
                array("left","category_master as cam","cam.category_id = l.category_id",array('cam.category_name')),
            );
            $leadCondition = "l.partner_id = '{$data['partner_id']}'";
            if($leadId != ""){
                $leadCondition .= " and l.lead_id = '{$leadId}'";
            }
            if($statusId != ""){
                $leadCondition .= " and sm.status_id = '{$statusId}'";
            }
            $leadQ = $db->JoinFetch($mainTable,$joinTable,$leadCondition,array("l.created_at"=>"desc"),array($start_index,$page_size));
            $leadR = $db->FetchToArrayFromResultset($leadQ);
            $info["data"] = $leadR;
            $response = array('success' => true, 'data' => $info);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'acceptlead'){

    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != "" &&
        isset($_GET['lead_id']) && $_GET['lead_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";
        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $info = array();
            $info["partner_id"] = $data["partner_id"];
            $info["lead_id"] = $data['lead_id'];

            $leadAcceptedStatus = Utility::checkLeadAccepted($data['lead_id']);

            if($leadAcceptedStatus == 1){
                $info["status_id"] = $db->FetchCellValue( "status_master", "status_id", " is_dashboard=1 ");

                $udpate = $db->Update("lead_master", $info, "lead_id", $data['lead_id']);
                $leadData = $db->FetchToArray("lead_master","*","lead_id = '{$data['lead_id']}'");
                $db->Insert("lead_master_history",$leadData[0]);
                $response = array('success' => true, 'msg' => "Lead accepted successfully!");

            }else{
                $response = array('success' => false, 'error' => 'Lead already accepted!');
            }
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'changelead'){

    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != "" &&
        isset($_GET['lead_id']) && $_GET['lead_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";
        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $info = array();
            $info["partner_id"] = $data["partner_id"];
            $info["lead_id"] = $data['lead_id'];

            if($data['status'] == "reject"){
                $info["partner_id"] = 0;
                $info["status_id"] = $db->FetchCellValue( "status_master", "status_id", " is_dashboard=1 ");
            }else{
                $info["status_id"] = $db->FetchCellValue( "status_master", "status_id", " status_name LIKE '%{$data['status']}%' ");
            }

            $successMsg = "Lead status changed successfully!";

            $udpate = $db->Update("lead_master", $info, "lead_id", $data['lead_id']);
            $leadData = $db->FetchToArray("lead_master","*","lead_id = '{$data['lead_id']}'");
            $db->Insert("lead_master_history",$leadData[0]);

            if($data['status'] == "reject"){
                $successMsg = "We have accepted your request for lead rejection.";
                Utility::sendPushNotificationForLead($data['lead_id'], $data['lead_name']);
            }else if($data['status'] == "complete"){
                $successMsg = "We have accepted your request for lead completion.";
            }

            $response = array('success' => true, 'msg' => $successMsg);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }
    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'viewearnings'){

    //    print_r($_GET);
    $data = $_GET;

    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){
            $info["partner_id"] = $data["partner_id"];

            $mainTable = array("lead_master as l",array('count(l.lead_id) as status_count'));
            $joinTable = array(
                array("left","partner_master as pm","pm.partner_id = l.partner_id"),
                array("left","status_master as sm","sm.status_id = l.status_id",array('sm.status_name')),
            );
            $leadCondition = "l.partner_id = '{$data['partner_id']}'";
            $leadQ = $db->JoinFetch($mainTable,$joinTable,$leadCondition,array("l.created_at"=>"desc"),null,"sm.status_id");
            $leadR = $db->FetchToArrayFromResultset($leadQ);
            $info['amount_data'] = $db->FetchToArray("partner_ledger",array("sum(amount) as amount","ledger_type"),"partner_id = '{$data['partner_id']}' group by ledger_type");
            $info['status_data'] = $leadR;

            $sendArr = array(
                "earningTotalConfirmed" => 0.00,
                "earningInProcessLeads" => 0,
                "earningFinishedLeads" => 0,
                "earningAmountToEarn" => 0.00,
                "earningRejectedLeads" => 0,
                "earningAmountWithdrawTillNow" => 0.00,
                "withdrawAmountBtn" => 0.00,
                "canWithdraw" => -1,
            );

            foreach($info['amount_data'] as $amountInfo){
                if(strtolower($amountInfo['ledger_type']) == "c"){
                    $sendArr['earningTotalConfirmed'] = $amountInfo['amount'];
                }
                if(strtolower($amountInfo['ledger_type']) == "d"){
                    $sendArr['earningAmountWithdrawTillNow'] = $amountInfo['amount'];
                }
            }

            $sendArr['withdrawAmountBtn'] = $sendArr['earningTotalConfirmed'] - $sendArr['earningAmountWithdrawTillNow'];
            $sendArr['canWithdraw'] = $sendArr['withdrawAmountBtn'] >0 ? 1: 0;

            foreach ($info['status_data'] as $statusInfo){
//                Core::PrintArray($statusInfo);
                if(strtolower($statusInfo['status_name']) == "in process"){
                    $sendArr['earningInProcessLeads'] = $statusInfo['status_count'];
                }
                if(strtolower($statusInfo['status_name']) == "complete"){
                    $sendArr['earningFinishedLeads'] = $statusInfo['status_count'];
                }
                if(strtolower($statusInfo['status_name']) == "rejected"){
                    $sendArr['earningRejectedLeads'] = $statusInfo['status_count'];
                }
            }

            $response = array('success' => true, 'data' => $sendArr);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }

    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
    //echo json_encode($response);

}elseif(isset($action) && $action == 'getservices'){
    $data = $_GET;
        $condition = "status = 1";
        $res = $db->FetchToArray('app_services',array('app_service_id','service_name','description','filepath'), $condition);
        if(count($res) > 0) {
            $bannerData = $res;
            foreach($bannerData as $key => $value){
                if(isset($bannerData[$key]['filepath'])) {
                    $bannerData[$key]['filepath'] = UPLOADS_PATH_REL .'/services/'.$bannerData[$key]['filepath'];
                } else {
                    $bannerData[$key]['filepath'] = UPLOADS_PATH_REL .'/services/'."no-image.jpg" ;
                }
            }
            $response = array('success' => true, 'data' => $bannerData);
        } else {
            $response = array('success' => false, 'data' => 'no service available');
        }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'getnotification'){
    $data = $_GET;
    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";
        $start_index = (isset($_GET['start_index'])) ? intval($_GET['start_index']) : 0;
        $page_size = (isset($_GET['page_size'])) ? intval($_GET['page_size']) : PER_PAGE_LIMIT;

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            $notificationDataSend = array();

            $notificationData = $db->FetchToArray("updates","*",null,array("created_at"=>"desc"),array($start_index,$page_size));
//            core::PrintArray($notificationData);
            foreach ($notificationData as $notData){
                $notificationTime=strtotime($notData['created_at']);
                $arr = array(
                    "notification_id" => $notData['update_id'],
                    "notification_title" => $notData['update_title'],
                    "notification_message" => $notData['message'],
//                    "notification_time" => date("dd-mm-yyyy H:i:s",$notificationTime),
                    "created_at_date" => date("d-m-Y H:i:s",$notificationTime),
                    "created_at_month" => date("M",$notificationTime),
                    "created_at_year" => date("Y",$notificationTime),
                    "created_at_hour" => date("H",$notificationTime),
                    "created_at_min" => date("i",$notificationTime),
                    "created_at_sec" => date("s",$notificationTime),
                );
                $notificationDataSend[] = $arr;
            }
//            for($i=0; $i<10; $i++){
//                $arr = array(
//                    "notification_id" => 1,
//                    "notification_title" => "Hi there!",
//                    "notification_message" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and",
//                    "created_at_date" => "10",
//                    "created_at_month" => "09",
//                    "created_at_year" => "2014",
//                    "created_at_hour" => "13",
//                    "created_at_min" => "44",
//                    "created_at_sec" => "55",
//                );
//                $notificationData[] = $arr;
//            }

            $response = array('success' => true, 'data' => $notificationDataSend);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }

    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';

//	$data = $db->FilterParameters($_GET);
//    $deviceId = isset($data['device_uid']) ? $data['device_uid'] : "";
//    if(isset($deviceId) && $deviceId != ''){
//        $data['time'] = $db->FetchCellValue("devices","updated_at","device_id = '{$deviceId}'");
//        $time = (isset($data['time'])) ? $data['time'] : date("Y-m-d H:i:s");
//        $condition = "created_at >= '{$time}'";
//        $res = $db->FetchToArray('push_notification',array('notification_id','notification_title','notification_message','category_id','created_at'), $condition,array("created_at"=>"desc"));
//        if(count($res) > 0) {
//            $notificationData = $res;
//            foreach($notificationData as $key => $value){
//                $notificationData[$key]['created_at'] = date("h i a, d M Y",strtotime($notificationData[$key]['created_at']));
//            }
//            $response = array('success' => true, 'data' => $notificationData);
//        } else {
//            $response = array('success' => true, 'data' => 'No Notification Available');
//        }
//    }else {
//        $response = array('success' => false, 'data' => 'device id not available');
//    }
//    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'gettutorials'){
    $data = $_GET;
    $response = array();
    if(
        isset($_GET['partner_id']) && $_GET['partner_id'] != ""
    ) {

        $partnerId = isset($_GET['partner_id']) ? $_GET['partner_id'] : "";

        $userStatus = Utility::checkPartnerActive($partnerId);

        if($userStatus == 1){

            $tutorialData = array();
            $tutorialDataArr = $db->FetchToArray("tutorial","*","is_active = 1");
            foreach($tutorialDataArr as $tut){
                $arr = array(
                    "tutorial_id" => $tut['tutorial_id'],
                    "tutorial_title" => $tut['tutorial_title'],
                    "tutorial_youtube_id" => $tut['youtube_id'],
                    "tutorial_message" => $tut['message'],
                    "created_at" => date("jS F Y",strtotime($tut['created_at'])),
                );
                $tutorialData[] = $arr;
            }
            $response = array('success' => true, 'data' => $tutorialData);
        }else{
            $response = array('success' => false, 'error' => 'Invalid user!', 'response_code' => $userStatus);
        }

    } else {
        $response = array('success' => false, 'error' => 'Invalid parameters');
    }

    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'push_notification'){
    $data = $_GET;
	$data = $db->FilterParameters($_GET);
    $deviceId = isset($data['deviceuid']) ? $data['deviceuid'] : "";
	$flag = isset($data['flag']) ? $data['flag'] : 1;
    $flag = $flag?1:0;
    if(isset($deviceId) && $deviceId != ''){

        $deviceData["push_notification_flag"] = $flag;
        $deviceData["updated_at"] = date('Y-m-d H:i:s');
        $updateFlag = $db->UpdateWhere("devices",$deviceData,"device_uid = '{$deviceId}'");

        if($updateFlag){
            $response = array('success' => true);
        }else{
            $response = array('success' => false, 'data' => 'Unable to save preferences!');
        }
    }else {
        $response = array('success' => false, 'data' => 'device id not available');
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'addlead'){

    $data = $db->FilterParameters($_GET);
    if(
        isset($_GET['lead_name']) && $_GET['lead_name'] != "" &&
        isset($_GET['auth_token']) && $_GET['auth_token'] != "" &&
        isset($_GET['mobile']) && $_GET['mobile'] != "" &&
        isset($_GET['pincode']) && $_GET['pincode'] != "" &&
        isset($_GET['landmark']) && $_GET['landmark'] != "" &&
        isset($_GET['state']) && $_GET['state'] != "" &&
        isset($_GET['city']) && $_GET['city'] != "" &&
        isset($_GET['category']) && $_GET['category'] != ""
    ) {
        $getData = $db->FilterParameters($_GET);
        $categoryId = Utility::addOrFetchFromTable("category_master",array("category_name"=>$getData['category']),"category_id","category_name = '{$getData['category']}'");
        $subLocalityId = Utility::addOrFetchFromTable("sub_locality",array("sub_locality_name"=>$getData['landmark']),"sub_locality_id","lower(sub_locality_name) = '{$getData['landmark']}'");
        $cityId = Utility::addOrFetchFromTable("city",array("city_name"=>$getData['city']),"city_id","lower(city_name) = '{$getData['city']}'");
        $stateId = Utility::addOrFetchFromTable("state",array("state_name"=>$getData['state']),"state_id","lower(state_name) = '{$getData['state']}'");
        $customerId = Utility::addOrFetchFromTable("customer_master",
            array(
                "mobile_no"=>$getData['mobile'],
                "customer_name"=>$getData['lead_name'],
                "customer_from" => "app",
                "pincode" => $getData['pincode'],
                "state_id" => $stateId,
                "city_id" => $cityId,
                "address" => $getData['address']
            ),"customer_id","mobile_no = '{$getData['mobile']}'");
        $data = array(
            "lead_name" => $getData['lead_name'],
            "mobile_no" => $getData['mobile'],
            "address" => $getData['address'],
            "pincode" => $getData['pincode'],
            "state_id" => $stateId,
            "city_id" => $cityId,
            "sub_locality_id" => $subLocalityId,
            "customer_id" => $customerId,
            "emergency_id" => isset($getData['emergency_id']) ? $getData['emergency_id'] : "",
            "status_id" => $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1"),
            "category_id" => $categoryId,
            "time_slot" => isset($getData['time_slot']) ? $getData['time_slot'] : "",
            "remarks" => isset($getData['remarks']) ? $getData['remarks'] : "",
        );
        $data = array_merge($data,$db->TimeStampAtCreate(0));
        $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$categoryId}'");
        $tableId = $db->GetNextAutoIncreamentValue("lead_master");
        $data['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
        $insertId = $db->Insert("lead_master", $data,1);
        $data['lead_id'] = $insertId;
        $db->Insert("lead_master_history",$data);
        if($insertId != '') {
            Utility::sendPushNotificationForLead($insertId, $getData['lead_name']);
            $response = array('success' => true, 'data' => $data,"response_code"=>SUCCESS);
        } else {
            $response = array('success' => false, 'error' => 'Currently no leads available!',"response_code"=>INVALID_REQUEST);
        }
    }else {
        $response = array('success' => false, 'error' => 'Invalid parameter',"response_code"=>INVALID_PARAMETER);
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}elseif(isset($action) && $action == 'addprospect'){

    $data = $db->FilterParameters($_GET);
    if(
        isset($_GET['lead_name']) && $_GET['lead_name'] != "" &&
        isset($_GET['auth_token']) && $_GET['auth_token'] != "" &&
        isset($_GET['mobile']) && $_GET['mobile'] != "" &&
        isset($_GET['pincode']) && $_GET['pincode'] != "" &&
        isset($_GET['state']) && $_GET['state'] != "" &&
        isset($_GET['city']) && $_GET['city'] != "" &&
        isset($_GET['category']) && $_GET['category'] != ""
    ) {
        $getData = $db->FilterParameters($_GET);
        $categoryId = Utility::addOrFetchFromTable("category_master",array("category_name"=>$getData['category']),"category_id","category_name = '{$getData['category']}'");
        $subLocalityId = Utility::addOrFetchFromTable("sub_locality",array("sub_locality_name"=>$getData['landmark']),"sub_locality_id","lower(sub_locality_name) = '{$getData['landmark']}'");
        $cityId = Utility::addOrFetchFromTable("city",array("city_name"=>$getData['city']),"city_id","lower(city_name) = '{$getData['city']}'");
        $stateId = Utility::addOrFetchFromTable("state",array("state_name"=>$getData['state']),"state_id","lower(state_name) = '{$getData['state']}'");
        $customerId = Utility::addOrFetchFromTable("customer_master",
            array(
                "mobile_no"=>$getData['mobile'],
                "customer_name"=>$getData['lead_name'],
                "customer_from" => "app",
                "pincode" => $getData['pincode'],
                "state_id" => $stateId,
                "city_id" => $cityId,
                "address" => $getData['address']
            ),"customer_id","mobile_no = '{$getData['mobile']}'");
        $data = array(
            "lead_name" => $getData['lead_name'],
            "mobile_no" => $getData['mobile'],
            "address" => $getData['address'],
            "pincode" => $getData['pincode'],
            "state_id" => $stateId,
            "city_id" => $cityId,
            "sub_locality_id" => $subLocalityId,
            "customer_id" => $customerId,
            "emergency_id" => isset($getData['emergency_id']) ? $getData['emergency_id'] : "",
            "status_id" => $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1"),
            "category_id" => $categoryId,
            "time_slot" => isset($getData['time_slot']) ? $getData['time_slot'] : "",
            "remarks" => isset($getData['remarks']) ? $getData['remarks'] : "",
        );
        $data = array_merge($data,$db->TimeStampAtCreate(0));
        $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$categoryId}'");
        $tableId = $db->GetNextAutoIncreamentValue("lead_master");
        $data['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
        $insertId = $db->Insert("lead_master", $data,1);
        $data['lead_id'] = $insertId;
        $db->Insert("lead_master_history",$data);
        if($insertId != '') {
            Utility::sendPushNotificationForLead($insertId, $getData['lead_name']);
            $response = array('success' => true, 'data' => $data,"response_code"=>SUCCESS);
        } else {
            $response = array('success' => false, 'error' => 'Currently no leads available!',"response_code"=>INVALID_REQUEST);
        }
    }else {
        $response = array('success' => false, 'error' => 'Invalid parameter',"response_code"=>INVALID_PARAMETER);
    }
    echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
}
else {
        $response = array('success' => false, 'error' => 'invalid request',"response_code"=>INVALID_REQUEST);
        echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
//        echo $_GET['jsoncallback'] . '(' . json_encode($response).');';
		//echo json_encode($response);
    }

if(isset($log_flag) && $log_flag)
{
    $request_log['response_time'] = date('Y-m-d H:i:s');
    $request_log['response_data'] = json_encode($response);
    $db->Insert($log_table, $request_log);
}
// Code to log request starts
//$response_path = realpath('response');
//$response_file_path = $file_name;
//@file_put_contents($response_file_path, json_encode($response));
include_once 'footer.php';

