<?php
$log_flag = true;

$log_table = 'api_request';

include_once 'header.php';

$json = file_get_contents("php://input");

$data = json_decode($json, true);
//core::PrintArray($data);
$action = isset($data['action']) ? $data['action'] : "" ;

$request_log['request_time'] = date('Y-m-d H:i:s');

$today = date('Y-m-d');

$request_log['request_data'] = $json;

$request_log['request_type'] = $action;

$user_id = isset($data['user_id']) ? $data['user_id'] : "";

$json_response = '';


$jsonCallBack = 0;
$tokenSuccess = 0;
$cwStages = array("mcq","gst","itr","bank_statement","director_cibil","one_form","matches","payment","in_principal_letter");
if(is_array($data) && count ($data) > 0) {
    if(array_key_exists("token",$data)){
        if($data['token'] != ''){
            $tokenCheck = $db->FetchRowWhere("token_master", array('token',"token_validity"),"token='{$data['token']}' and is_active = 1");
            if($db->CountResultRows($tokenCheck) == 1){
                $tokenData = $db->FetchRowInAssocArray($tokenCheck);
                if($tokenData['token_validity'] >= DATE_TIME_DATABASE){
                    $tokenSuccess = 1;
                } else {
                    $tokenSuccess = 0;
                    $response = array('success' => false, 'error' => 'Auth token Expired',"response_code"=>AUTH_EXPIRE);
                    $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
                    echo $jsonResponse;
                    exit;
                }

            } else {
                $tokenSuccess = 0;
                $response = array('success' => false, 'error' => 'Invalid Auth Token',"response_code"=>INVALID_AUTH);
                $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
                echo $jsonResponse;
                exit;
            }
        }  else {
            $tokenSuccess = 0;
            $response = array('success' => false, 'error' => 'Auth Token Missing',"response_code"=>AUTH_MISSING);
            $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
            echo $jsonResponse;
            exit;
        }
    } else {
        $tokenSuccess = 0;
        $response = array('success' => false, 'error' => 'Auth Token Missing',"response_code"=>AUTH_MISSING);
        $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
        echo $jsonResponse;
        exit;
    }

} else {
    $tokenSuccess = 1;
}

if(isset($action) && $action == 'addprospecttemp' && $tokenSuccess == 1){

    $data = $db->FilterParameters($data);
    $response = array();

    if(
        isset($data['email']) && $data['email'] != "" &&
        isset($data['mobile']) && $data['mobile'] != "" &&
        isset($data['user_type']) && $data['user_type'] != "" &&
        isset($data['loan_type']) && $data['loan_type'] != "" &&
        isset($data['first_name']) && $data['first_name'] != "" &&
        isset($data['last_name']) && $data['last_name'] != "" &&
        isset($data['city']) && $data['city'] != "" &&
        isset($data['state']) && $data['state'] != ""
    )
    {
        $stateId = '';
        $categoryId = '';
        $emails = array();
        $phoneNumber = array();
        $category = $data['loan_type'];
        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($category));
            $categoryId = Utility::addOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_name)='{$category}'");
        }
        $state = $data['state'];
        if(!empty($state)){
            $state_data['state_name'] = trim(strtolower($state));
            $state_data['country_id'] = 1;
            $stateId = Utility::addOrFetchFromTable('state', $state_data, 'state_id', "lower(state_name)='$state'");
        }
        $city = $data['city'];
        $cityId = '';
        if(!empty($city)){
            $city_data['city_name'] = trim(strtolower($city));
            $city_data['state_id'] = $stateId;
            $city_data['country_id'] = 1;
            $cityId = Utility::addOrFetchFromTable('city', $city_data, 'city_id', "lower(city_name)='$city' and state_id = '{$stateId}'");
        }


        $prospect_data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $prospect_data['last_name'] = isset($data['last_name']) ?  ucwords($data['last_name']) : "";
        $prospect_data['state_id'] = $stateId;
        $prospect_data['city_id'] = $cityId;
        $prospect_data['pincode'] = isset($data['pincode']) ? $data['pincode'] : "";
        $prospect_data['user_type'] = isset($data['user_type']) ? $data['user_type'] : "";
        $prospect_data['campaign_id'] = ONLINE_CAMPAIGN_ID;
        $prospect_data['category_id'] = $categoryId;
        $prospect_data['prospect_via'] = 'ws';

        //$client_person_cond="person_name='$person_name'";
        $prospectCondition = "pm.category_id = '{$categoryId}' && " . "pm.campaign_id = '".ONLINE_CAMPAIGN_ID."'";

        $emailCheck = is_array($data['email']) ? implode("','",$data['email'])  : $data['email'];
        $prospectCondition .= " and pc.contact in ('$emailCheck')";

        $mobileCheck = is_array($data['mobile']) ? implode("','",$data['mobile'])  : $data['mobile'];
        $prospectCondition .= " and pc1.contact in ('$mobileCheck')";

        $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
        $joinTable = array(
            array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id"),
            array("left","prospect_contact as pc1","pc1.prospect_id = pm.prospect_id")
        );
        $prospectRes = $db->JoinFetch($mainTable,$joinTable,$prospectCondition,null,array(0,1),"pm.prospect_id");
        $prospectCount = $db->CountResultRows($prospectRes);
       // core::PrintArray($prospectData);
        $prospect_data = array_merge($prospect_data,$db->TimeStampAtCreate($user_id));
//        $prospectResEmail = $db->FetchRowWhere("prospect_contact", array("prospect_id"), $prospectEmailCondition);
//        $prospectCountEmail = $db->CountResultRows($prospectResEmail);
//        $prospectResMobile = $db->FetchRowWhere("prospect_contact", array("prospect_id"), $prospectMobileCondition);
//        $prospectCountMobile = $db->CountResultRows($prospectResMobile);
        //echo $prospectCountEmail."<br/>";
        if($prospectCount > 0) {
            $prospectRow = $db->FetchRowInAssocArray($prospectRes);
            $prospectId = $prospectRow['prospect_id'];
            $response = array('success' => true, 'prospect_id' => $prospectId,"response_code"=>SUCCESS);
        } else {
            $prospectId = $db->Insert("prospect_master", $prospect_data, true);
            if($prospectId != ''){
                // add prospect in queue
                $db->Insert("prospect_queue",array(
                        "prospect_id" => $prospectId,
                        "campaign_id" => ONLINE_CAMPAIGN_ID,
                        "created_at"=>DATE_TIME_DATABASE,
                        "created_by"=>$user_id,
                        "updated_at"=>DATE_TIME_DATABASE,
                        "updated_by"=>$user_id,
                        "date_queued"=>DATE_TIME_DATABASE,
                    )

                );
                $emails = is_array($data['email']) ? $data['email']  : array($data['email']);
                if(count($emails) > 0){
                    foreach($emails as $ekey => $email) {
                        $primary = ($ekey == 0) ? 1 : 0;
                        $emailData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $email,
                            "contact_type" => "email",
                            "is_wrong" =>0,
                            "is_primary" =>$primary

                        );
                        $db->Insert('prospect_contact', $emailData);
                    }
                }

                $phoneNumber = is_array($data['mobile']) ? $data['mobile']  : array($data['mobile']);
                if(count($phoneNumber) > 0){
                    foreach($phoneNumber as $pnkey => $number) {
                        $primary = ($pnkey == 0) ? 1 : 0;
                        $phoneNumberData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $number,
                            "contact_type" => "phone",
                            "is_wrong" =>0,
                            "is_primary" =>$primary

                        );
                        $db->Insert('prospect_contact', $phoneNumberData);
                    }
                }
                $response = array('success' => true, 'prospect_id' => $prospectId,"response_code"=>SUCCESS);
            }  else {
                $response = array('success' => false, 'error' => 'Data Added Unsuccessfully',"response_code"=>NO_DATE_FOUND);
            }
        }

    }
    else
    {
        $response = array('success' => false, 'error' => 'Invalid parameters',"response_code"=>INVALID_PARAMETER);
    }
    //print_r($_GET);
    //core::PrintArray($response);
    $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
    echo $jsonResponse;

}elseif(isset($action) && $action == 'addprospect' && $tokenSuccess == 1){

    $data = $db->FilterParameters($data);
    $response = array();

    if(
        isset($data['mobile']) && $data['mobile'] != "" &&
        isset($data['campaign_id']) && intval($data['campaign_id']) != "" &&
        isset($data['first_name']) && $data['first_name'] != "" &&
        isset($data['last_name']) && $data['last_name'] != ""
    )
    {
        $stateId = '';
        $categoryId = '';
        $emails = array();
        $phoneNumber = array();
        $category = isset($data['loan_type']) ? $data['loan_type'] : "";
        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($category));
            $categoryId = Utility::addOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_name)='{$category}'");
        }

        $campaignId = $data['campaign_id'];

        if(!empty($campaignId)){
            $campaign_data['campaign_id'] = trim(strtolower($campaignId));
            $campaignId = Utility::checkOrFetchFromTable('campaign_master', $campaign_data, 'campaign_id', "campaign_id = '{$campaignId}'");
            if($campaignId == ''){
                $response = array('success' => false, 'error' => 'Invalid Campaign Id',"response_code" => INVALID_PARAMETER);
                $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
                echo $jsonResponse;
                exit;
            }
        } else {
            $response = array('success' => false, 'error' => 'Invalid Campaign Id',"response_code" => INVALID_PARAMETER);
            $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
            echo $jsonResponse;
            exit;
        }


        $prospect_data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $prospect_data['last_name'] = isset($data['last_name']) ?  ucwords($data['last_name']) : "";

        $prospect_data['user_type'] = isset($data['user_type']) ? $data['user_type'] : "";
        $prospect_data['campaign_id'] = $campaignId;
        //$prospect_data['category_id'] = $categoryId;
        $prospect_data['prospect_via'] = 'ws';

        //$client_person_cond="person_name='$person_name'";
        $prospectCondition = "pm.campaign_id = '".$campaignId."'";

        //$emailCheck = is_array($data['email']) ? implode("','",$data['email'])  : $data['email'];
        //$prospectCondition .= " and pc.contact in ('$emailCheck')";

        $mobileCheck = is_array($data['mobile']) ? implode("','",$data['mobile'])  : $data['mobile'];
        $prospectCondition .= " and pc1.contact in ('$mobileCheck')";

        $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
        $joinTable = array(
            //array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id"),
            array("left","prospect_contact as pc1","pc1.prospect_id = pm.prospect_id")
        );
        $prospectRes = $db->JoinFetch($mainTable,$joinTable,$prospectCondition,null,array(0,1),"pm.prospect_id");
        $prospectCount = $db->CountResultRows($prospectRes);
        // core::PrintArray($prospectData);
        $prospect_data = array_merge($prospect_data,$db->TimeStampAtCreate($user_id));
//
        if($prospectCount > 0) {
            $prospectRow = $db->FetchRowInAssocArray($prospectRes);
            $prospectId = $prospectRow['prospect_id'];
            $response = array('success' => true, 'prospect_id' => $prospectId,"response_code"=>SUCCESS);
        } else {
            $prospectId = $db->Insert("prospect_master", $prospect_data, true);
            if($prospectId != ''){
                // add prospect in queue
                $db->Insert("prospect_queue",array(
                        "prospect_id" => $prospectId,
                        "campaign_id" => $campaignId,
                        "created_at"=>DATE_TIME_DATABASE,
                        "created_by"=>$user_id,
                        "updated_at"=>DATE_TIME_DATABASE,
                        "updated_by"=>$user_id,
                        "date_queued"=>DATE_TIME_DATABASE,
                    )

                );

                /*
                $emails = is_array($data['email']) ? $data['email']  : array($data['email']);
                if(count($emails) > 0){
                    foreach($emails as $ekey => $email) {
                        $primary = ($ekey == 0) ? 1 : 0;
                        $emailData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $email,
                            "contact_type" => "email",
                            "is_wrong" =>0,
                            "is_primary" =>$primary

                        );
                        $db->Insert('prospect_contact', $emailData);
                    }
                }
                */

                $phoneNumber = is_array($data['mobile']) ? $data['mobile']  : array($data['mobile']);
                if(count($phoneNumber) > 0){
                    foreach($phoneNumber as $pnkey => $number) {
                        $primary = ($pnkey == 0) ? 1 : 0;
                        $phoneNumberData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $number,
                            "contact_type" => "phone",
                            "is_wrong" =>0,
                            "is_primary" =>$primary

                        );
                        $db->Insert('prospect_contact', $phoneNumberData);
                    }
                }
                $response = array('success' => true, 'prospect_id' => $prospectId,"response_code"=>SUCCESS);
            }  else {
                $response = array('success' => false, 'error' => 'Data Added Unsuccessfully',"response_code"=>NO_DATE_FOUND);
            }
        }

    }
    else
    {
        $response = array('success' => false, 'error' => 'Invalid parameters',"response_code"=>INVALID_PARAMETER);
    }
    //print_r($_GET);
    //core::PrintArray($response);
    $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
    echo $jsonResponse;

}elseif(isset($action) && $action == 'addprospectdetails' && $tokenSuccess == 1){

    $data = $db->FilterParameters($data);
    $response = array();
    if(
        isset($data['mobile']) && $data['mobile'] != "" &&
        isset($data['stage_name']) && $data['stage_name'] != "" &&
        isset($data['status']) &&
        isset($data['application_id']) && $data['application_id'] != ""
      )
    {
        $stateId = '';
        $categoryId = '';
        $emails = array();
        $phoneNumber = array();
        $loanAmount = isset($data['loan_amount']) ? $data['loan_amount'] : 0;
        $category = isset($data['product_type']) ? $data['product_type'] : "";
        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($category));
            $categoryId = Utility::checkOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_code)='{$category}'");
            if($categoryId == ''){
                $response = array('success' => false, 'error' => 'Invalid Loan Type',"response_code" => INVALID_PARAMETER);
                $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
                echo $jsonResponse;
                exit;
            }
        }

        $data['stage_name'] = strtolower($data['stage_name']);
        if(!in_array($data['stage_name'],$cwStages)){
            $response = array('success' => false, 'error' => 'Invalid Stage Name');
            $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
            echo $jsonResponse;
            exit;
        }
        //$exist_condition = "contact = '{$data['mobile']}' and contact_type = 'phone'";
        //$res = $db->Fetch("prospect_contact", array("prospect_id"), $exist_condition,null,array(0,1));
        //$counter = mysql_num_rows($res);
        $prospectCondition = "pm.campaign_id = '".SIDBI_CAMPAIGN_ID."'";

        //$emailCheck = is_array($data['email']) ? implode("','",$data['email'])  : $data['email'];
        //$prospectCondition .= " and pc.contact in ('$emailCheck')";

        $mobileCheck = is_array($data['mobile']) ? implode("','",$data['mobile'])  : $data['mobile'];
        $prospectCondition .= " and pc1.contact in ('$mobileCheck')";

        $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
        $joinTable = array(
            //array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id"),
            array("left","prospect_contact as pc1","pc1.prospect_id = pm.prospect_id")
        );
        $prospectRes = $db->JoinFetch($mainTable,$joinTable,$prospectCondition,null,array(0,1),"pm.prospect_id");
        $prospectCount = $db->CountResultRows($prospectRes);
        // core::PrintArray($prospectData);
//
        if($prospectCount > 0) {
            $prospectRow = $db->FetchRowInAssocArray($prospectRes);
            $prospectId = $prospectRow['prospect_id'];
            $stageColumnKey = strtolower($data['stage_name']);

            $updateData = array(
                "$stageColumnKey" => $data['status'],
                "cw_application_id" => $data['application_id'],
                "category_id" => $categoryId,
                "amount" => $loanAmount,
                "actual_amount" => $loanAmount,
            );
            $isUpdate = $db->UpdateWhere("prospect_master",$updateData,"prospect_id = '{$prospectId}'");
            if($isUpdate){
                $response = array('success' => true, 'prospect_id' => $prospectId,"response_code"=>SUCCESS);
            }else {
                $response = array('success' => false, 'error' => 'Data Updated Unsuccessfully',"response_code"=>NO_DATE_FOUND);
            }
        }  else {
            $response = array('success' => false, 'error' => "Mobile Number not found","response_code"=>INVALID_PARAMETER);
        }


    }
    else
    {
        $response = array('success' => false, 'error' => 'Invalid parameters',"response_code"=>INVALID_PARAMETER);
    }
    //print_r($_GET);
    //core::PrintArray($response);
    $jsonResponse = ($jsonCallBack == 1) ? $_GET['jsoncallback'] . '(' . json_encode($response).');' : json_encode($response);
    echo $jsonResponse;

}   else {

    $response = array('success' => false, 'error' => 'Invalid Request',"response_code" => INVALID_REQUEST);

    $jsonResponse = json_encode($response);

    echo $jsonResponse;

}




if(isset($log_flag) && $log_flag){


    $request_log['response_time'] = date('Y-m-d H:i:s');

    $request_log['response_data'] = $jsonResponse;

    $request_log['user_id'] = $user_id;

    $db->Insert($log_table, $request_log);

}

// Code to log request starts

//$response_path = realpath('response');

//$response_file_path = $file_name;

//@file_put_contents($response_file_path, json_encode($response));

include_once 'footer.php';