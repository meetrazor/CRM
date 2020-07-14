<?php
$log_flag = true;

$log_table = 'api_request';

include_once 'Dbconfig.php';
include_once 'Db.php';
include_once 'Core.php';

include_once 'session.php';
include_once 'SiteSettings.php';


$db = new Db();
$core = new Core();
$ses = new Session();
$ses->init();
$db->ConnectionOpen();




$json = file_get_contents("php://input");

$data = json_decode($json, true);

$action = isset($data['action']) ? $data['action'] : "" ;

$request_log['request_time'] = date('Y-m-d H:i:s');

$today = date('Y-m-d');

$request_log['request_data'] = $json;

$request_log['request_type'] = $action;

$deviceId = isset($data['device_uid']) ? $data['device_uid'] : "";

$userId = isset($data['user_id']) ? $data['user_id'] : "";

$json_response = '';


$jsonCallBack = 0;
$tokenSuccess = 0;
//$tokenSuccess = 1;

if(is_array($data) && count ($data) > 0) {
    if(array_key_exists("token",$data)){
        if($data['token'] != ''){
            $tokenCheck = $db->FetchRowWhere("token_master", array('token',"token_validity"),"token='{$data['token']}'");
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


if(isset($action) && $action == 'misscalllist' && $tokenSuccess == 1){

    $data = $db->FilterParameters($data);
    $response = array();

    if(
        isset($data['token']) && $data['token'] != ""
    )
    {

        $leadRes = $db->FetchToArray("vicidial_list", array('lead_id','entry_date','status','phone_code','phone_number','city','state'),"status='N'");

        //echo "here".$newsData;
        //exit;
        $leadDataRes = array();
        if(count($leadRes) > 0) {
            //$dealerDataAll = array();
            foreach($leadRes as $lData){
                $leadDataRes[]=$lData;

            }
            $response = array('success' => true, 'data' => $leadDataRes,"response_code"=>SUCCESS);

        } else {
            $response = array('success' => false, 'error' => 'No Data Found',"response_code"=>NO_DATE_FOUND);
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

    $request_log['user_id'] = $userId;

    $db->Insert($log_table, $request_log);

}

// Code to log request starts

//$response_path = realpath('response');

//$response_file_path = $file_name;

//@file_put_contents($response_file_path, json_encode($response));

$db->ConnectionClose();