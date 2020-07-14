<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "lead_master";
$table_id = 'lead_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST);

    $validator = array(

        'rules' => array(
            'email' => array('email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
            'state_id' => array('required' => true),
            'city_id' => array('required' => true),
            'lead_name' => array('required' => true),
            'address' => array('required' => true),
            'category_id' => array('required' => true),
           ),
        'messages' => array(
            'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'landmark' => array('required' => "Please enter landmark"),
            'lead_name' => array('required' => "Please enter client name"),
            'address' => array('required' => "Please enter address"),
            'category_id' => array('required' => "Please select category"),
           )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $data['customer_id'] = Utility::addOrFetchFromTable("customer_master",
            array(
                "mobile_no"=>$data['mobile_no'],
                "customer_name"=>$data['lead_name'],
                "email"=>$data['email'],
                "customer_from" => "app",
                "pincode" => $data['pincode'],
                "state_id" => $data['state_id'],
                "city_id" => $data['city_id'],
                "address" => $data['address']
            ),"customer_id","mobile_no = '{$data['mobile_no']}'");

        $data['birth_date'] = isset($data['birth_date']) ? core::DMYToYMD($data['birth_date']) : "";
        $id = (isset($data['lead_id']) && $data['lead_id'] != '') ? $data['lead_id'] : 0;


        $data['is_close'] = isset($data['is_close']) ? 1 : 0;

        $response = array();
        $exist_condition = "mobile_no = '{$data['mobile_no']}'";

        if($id > 0){

            $exist_condition .= " && lead_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);
                $leadData = $db->FetchToArray($table,"*","$table_id = $id");
                $db->Insert("lead_history",$leadData[0]);
                $statusClose = $db->FetchCellValue("status_master","status_id","is_close = 1 and status_id = '{$data['status_id']}'");
                if($statusClose == ''){
                    if(isset($data['partner_id']) && $data['partner_id'] != '') {

//                        Utility::sendPushNotificationForLead($id, $data['lead_name'], $data['partner_id']);

                        $db->Insert("partner_lead",array(
                            "lead_id" => $id,
                            "partner_id" => $data['partner_id'],
                            "assign_at"=>date('Y-m-d'),
                            "assign_from"=>"admin",
                        ));
                        $partnerDetails = Utility::getPartnerForLead($data['partner_id']);
                        $customerDetails = $db->FetchToArray("customer_master",array("mobile_no","customer_name"),"customer_id = '{$data['customer_id']}'",null,array(0,1));
                        $customerMobile = isset($customerDetails['mobile_no']) ? $customerDetails['mobile_no'] : "";
                        $customerName = isset($customerDetails['customer_name']) ? $customerDetails['customer_name'] : "";
                        $partnerMobile = isset($partnerDetails[0]['mobile_no']) ? $partnerDetails[0]['mobile_no'] : "";
                        $partnerName = isset($partnerDetails[0]['partner_name']) ? $partnerDetails[0]['partner_name'] : "";
                        $customerMessage = "Hello ".$customerName." - ".$partnerName." is assigned to help you in your Capita World Application.";
                        $message = "New lead available - {$data['lead_name']}";
                        //$status = Utility::sendSMS($partnerMobile,$message);
                        //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$data['partner_id'],"",$message,$partnerMobile,'sms');
                        //$status = Utility::sendSMS($customerMobile,$customerMessage);
                        //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$data['customer_id'],"",$customerMessage,$customerMobile,'sms');
                    }
                }
                /*
                $calPrice = $db->FunctionFetch("status_master", 'count', array("status_id"), "cal_price = 1 and status_id = '{$data['status_id']}'", array(0,1));
                if($calPrice == 1) {
                    $catPrice = $db->FetchCellValue("category_master","commission","category_id = '{$data['category_id']}'");

                    $commData = array(
                        "partner_id" => $data['partner_id'],
                        "amount" => $catPrice,
                        "category_id" => $data['category_id'],
                        "lead_id" => $id,
                    );
                    $commData = array_merge($commData,$db->TimeStampAtCreate($user_id));
                    Utility::addOrFetchFromTable("partner_commission",$commData,"partner_commission_id","lead_id = '{$id}'");

                    $ledgerData = array(
                        "partner_id" => $data['partner_id'],
                        "amount" => $catPrice,
                        "ledger_type" => 'C',
                        "type_id" => $id,
                        "ledger_from" => "lead",
                    );
                    $ledgerData = array_merge($ledgerData,$db->TimeStampAtCreate($user_id));
                    Utility::addOrFetchFromTable("partner_ledger",$ledgerData,"partner_ledger_id","type_id = '{$id}' and ledger_from = 'lead'");
                }
                */
                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "client with  mobile: {$data['mobile']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            if($userType == UT_BD)
            {
                $data['bd_id']=$user_id;

            }else{
                $data['bd_id']=Utility::getBdForLead($data['city_id']);

            }




            $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$data['category_id']}'");
            $tableId = $db->GetNextAutoIncreamentValue($table);
            $data['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){
                $data['lead_manager'] = $user_id;
                $data['is_new'] = 1;
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);
                $data['lead_id'] = $insertId;
                $db->Insert("lead_history",$data);
                if(isset($data['bd_id']) && $data['bd_id']!= '') {
                    $oldLeadBd = $db->FetchToArray("lead_users", "user_id", "lead_id = '{$insertId}' and user_type = 'bd' and is_latest = 1");
                        $db->UpdateWhere("lead_users", array("is_latest" => 0), "lead_id = '{$insertId}' and user_type = 'bd'");
                        $db->Insert("lead_users", array(
                            "lead_id" => $insertId,
                            "user_id" => $data['bd_id'],
                            "user_type" => "bd",
                            "user_type_id" => UT_BD,
                            "is_latest" => "1",
                            "created_at" => DATE_TIME_DATABASE,
                            "created_by" => $user_id,
                        ));

                }
                if(isset($data['partner_id']) && $data['partner_id'] != '') {

                    //Utility::sendPushNotificationForLead($insertId, $data['lead_name'], $data['partner_id']);

                    $db->Insert("partner_lead",array(
                        "lead_id" => $insertId,
                        "partner_id" => $data['partner_id'],
                        "assign_at"=>date('Y-m-d'),
                        "assign_from"=>"admin",
                    ));
                    $partnerDetails = Utility::getPartnerForLead($data['partner_id']);
                    $customerDetails = $db->FetchToArray("customer_master",array("mobile_no","customer_name"),"customer_id = '{$data['customer_id']}'",null,array(0,1));
                    $customerMobile = isset($customerDetails[0]['mobile_no']) ? $customerDetails[0]['mobile_no'] : "";
                    $customerName = isset($customerDetails[0]['customer_name']) ? $customerDetails[0]['customer_name'] : "";
                    $partnerMobile = isset($partnerDetails[0]['mobile_no']) ? $partnerDetails[0]['mobile_no'] : "";
                    $partnerName = isset($partnerDetails[0]['partner_name']) ? $partnerDetails[0]['partner_name'] : "";
                    $customerMessage = "Hello ".$customerName." - ".$partnerName." is assigned to help you in your Capita World Application.";
                    //$customerMessage = "lead ".$data['lead_code']." is assign to ".$partnerName."";
                    $message = "New lead available - {$data['lead_name']}";
                    //$status = Utility::sendSMS($partnerMobile,$message);
                    //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$data['partner_id'],"",$message,$partnerMobile,'sms');
                    //$status = Utility::sendSMS($customerMobile,$customerMessage);
                    //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$data['customer_id'],"",$customerMessage,$customerMobile,'sms');
                }else{
//                    //$partnerDetails = Utility::getPartnersForLead($insertId);
//                    if(isset($data['customer_id']) && $data['customer_id']!="") {
//
//                        $partnerMobiles = (count($partnerDetails) > 0) ? Utility::array_column($partnerDetails,"mobile_no") : array();
//                    $message = "New lead available - {$data['lead_name']}";
//                    //$status = Utility::sendSMS($partnerMobiles,$message);
//                         $customerMobile = $db->FetchCellValue("customer_master", "mobile_no", "customer_id = '{$data['customer_id']}'");
//
//                        $customerMessage = "lead " . $data['lead_code'] . " is created";
//                    }
                    //$status = Utility::sendSMS($customerMobile,$customerMessage);
                    //Utility::sendPushNotificationForLead($insertId, $data['lead_name']);
                }

                /*
                $calPrice = $db->FunctionFetch("status_master", 'count', array("status_id"), "cal_price = 1 and status_id = '{$data['status_id']}'", array(0,1));
                if($calPrice == 1) {
                    $catPrice = $db->FetchCellValue("category_master","commission","category_id = '{$data['category_id']}'");
                    $commData = array(
                        "partner_id" => $data['partner_id'],
                        "amount" => $catPrice,
                        "category_id" => $data['category_id'],
                        "lead_id" => $insertId,
                    );
                    $ledgerData = array_merge($commData,$db->TimeStampAtCreate($user_id));
                    $db->Insert("partner_commission",$commData);

                    $ledgerData = array(
                        "partner_id" => $data['partner_id'],
                        "amount" => $catPrice,
                        "ledger_type" => 'C',
                        "type_id" => $insertId,
                        "ledger_from" => "lead",
                    );
                    $ledgerData = array_merge($ledgerData,$db->TimeStampAtCreate($user_id));
                    $db->Insert("partner_ledger",$ledgerData);
                }
                */

                $response['success'] = true;
                $response['act'] = 'added';
                $response['lead_id'] = $insertId;
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully and Lead Number is '.$data['lead_code'].'!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "lead with mobile: {$data['mobile_no']} already exist";
            }
        }
        echo json_encode($response);
    }
} elseif($action == 'addcomment'){
    $data = $db->FilterParameters($_POST);
    $validator = array(

        'rules' => array(
            'lead_id' => array('required' => true),
            'lead_comment' => array('required' => true),
        ),
        'messages' => array(
            'lead_id' => array('required' => "Please select lead"),
            'lead_comment' => array('required' => "Please enter lead comment"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
        exit;
    }
    if($data['lead_id'] != ''){
        $data = array_merge($data,$db->TimeStampAtCreate($user_id));
        $db->Insert("lead_comment",$data);
        $response['success'] = true;
        $response['title'] = 'Success';
        $response['msg'] = "Comment added successfully";
    } else {
        $response['success'] = false;
        $response['title'] = 'unsuccessfully';
        $response['msg'] = "Comment added unsuccessfully";
    }
    echo json_encode($response);
} elseif($action == 'commentview'){
    $data = $db->FilterParameters($_POST);
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";
    if($leadId != ''){
        $leadHistory = $db->FetchToArray("lead_comment","*","lead_id = '{$leadId}' and is_delete = 0",array('created_at'=>"desc"));
        if(is_array($leadHistory) and count($leadHistory) > 0){
            $lead_html_div = "";
            $lead_html_div .= "<table class='table table-condensed table-bordered table-hover'>";
            $lead_html_div .= "<tr>";
            $lead_html_div .= "<td><b>Comment</b></td>";
            $lead_html_div .= "<td><b>Created On</b></td>";
            $lead_html_div .= "<tr>";
            foreach($leadHistory as $id => $history){
                $lead_html_div .= "<tr>";
                $lead_html_div .= "<td>".$history['lead_comment']."</td>";
                $lead_html_div .= "<td>".core::YMDToDMY($history['created_at'],true)."</td>";
                $lead_html_div .= "<tr>";

            }
            $lead_html_div .= "</table>";
            echo $lead_html_div;
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
}
include_once 'footer.php';