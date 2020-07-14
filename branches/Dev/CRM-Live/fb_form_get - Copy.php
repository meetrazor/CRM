<?php

include_once 'core/session.php';
$ses = new Session();
$ses->init();

include_once 'core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once 'core/Db.php';
include_once 'core/sMail.php';
$db = new Db();
$db->ConnectionOpen();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

include_once 'core/SiteSettings.php';

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();




// Creating Permission Object
include_once 'core/Permission.php';
$acl = new Permission();
$login_id = $ses->Get('user_id');

$token = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";

$current_page = Core::CurrentPage();

$jsonData = json_decode(file_get_contents('https://graph.facebook.com/v2.8/449622328579373/leadgen_forms?access_token=EAADB5BFuoxkBALN1q0o7EPQ8OnCRKt01e5VoastwD3v6rjq1AgSiLgMjGHIutWh2ZAz7ZCod2N80QuawheIAQoZCH00abhp4V4WZBH1j9Ho6eXICHneoRG4ZBdYniEtUCjAfD7sEtKXPbiRvIVE7TJhOyekqVZA68ZD'));
$jsonData = json_decode(json_encode($jsonData), True);
core::PrintArray($jsonData);

//$jsonData = json_decode(file_get_contents('https://graph.facebook.com/oauth/access_token?client_id=213185289167641&client_secret=d97651b98a89457d6d8dfda7ac82d711&grant_type=fb_exchange_token&fb_exchange_token=EAADB5BFuoxkBALN1q0o7EPQ8OnCRKt01e5VoastwD3v6rjq1AgSiLgMjGHIutWh2ZAz7ZCod2N80QuawheIAQoZCH00abhp4V4WZBH1j9Ho6eXICHneoRG4ZBdYniEtUCjAfD7sEtKXPbiRvIVE7TJhOyekqVZA68ZD'));
//$jsonData = json_decode(json_encode($jsonData), True);
//core::PrintArray($jsonData);

//$jsonData = json_decode(file_get_contents('https://graph.facebook.com/me/accounts?access_token=EAADB5BFuoxkBALN1q0o7EPQ8OnCRKt01e5VoastwD3v6rjq1AgSiLgMjGHIutWh2ZAz7ZCod2N80QuawheIAQoZCH00abhp4V4WZBH1j9Ho6eXICHneoRG4ZBdYniEtUCjAfD7sEtKXPbiRvIVE7TJhOyekqVZA68ZD'));
//$jsonData = json_decode(json_encode($jsonData), True);
//core::PrintArray($jsonData);

//$jsonData = json_decode(file_get_contents('https://graph.facebook.com/oauth/access_token_info?client_id=213185289167641&access_token=EAADB5BFuoxkBAFhBTr10tPRJg5asXSpwe6vgeoDZAebxI4tdNrL05ZANi2ZCplFijXtkRojXjGeZCyK7qnbLLBL0baZAgLoKuGYxsWft3N4IYwPaNBsi5PfE40b1arygW2zBpp2xoflZC5q6PrfCwZCROtZCxVcBQnAZD'));
//$jsonData = json_decode(json_encode($jsonData), True);
//core::PrintArray($jsonData);
//echo ((($jsonData['expires_in']/60)/60)/24);
//exit;

if(count($jsonData['data']) > 0){
    foreach($jsonData['data'] as $data){
        $insertData = array(
            "campaign_name" => $data['name'],
            "phone_number" => $data['id'],
        );
        $campaignId = Utility::addOrFetchFromTable("campaign_master",$insertData,"campaign_id","phone_number = '{$data['id']}'");

        if($campaignId != ''){
            $leadDataJ = json_decode(file_get_contents('https://graph.facebook.com/v2.8/'.$data['id'].'/leads?access_token=EAADB5BFuoxkBALN1q0o7EPQ8OnCRKt01e5VoastwD3v6rjq1AgSiLgMjGHIutWh2ZAz7ZCod2N80QuawheIAQoZCH00abhp4V4WZBH1j9Ho6eXICHneoRG4ZBdYniEtUCjAfD7sEtKXPbiRvIVE7TJhOyekqVZA68ZD&limit=5&pretty=1&date_format=U'));
            $leadData = json_decode(json_encode($leadDataJ), True);
          //  core::PrintArray($leadData);
            foreach($leadData as $data){

            foreach($data as $key => $val){
                if(is_array($val) && array_key_exists("created_time",$val)){
                    $createdTime = date('Y-m-d H:i:s',$val['created_time']);
                }
                if(is_array($val) &&  array_key_exists("field_data",$val)){
                    $userValue = array();
                    foreach($val['field_data'] as $userData){
                       // print_r($userData);
                        $userValue[$userData['name']] =  $userData['values'][0];
                    }
                    $insertData = array(
                        "campaign_id" => $campaignId,
                        "first_name" => $userValue['full_name'],
                        "last_name" => "",
                        "amount" => $userValue['your_desired_loan_amount'],
                        "created_at"=>$createdTime,
                        "updated_at"=>$createdTime,
                        "prospect_via"=>"fb",
                    );
                    $prospectId = Utility::addOrFetchFromTable("prospect_master",$insertData,"prospect_id","campaign_id = '{$campaignId}' and first_name = '{$userValue['full_name']}'");
                    if($prospectId != ''){
                        $db->Insert("prospect_queue",array(
                                "prospect_id" => $prospectId,
                                "campaign_id" => $campaignId,
                                "created_at"=>$createdTime,
                                "updated_at"=>$createdTime,
                                "date_queued"=>$createdTime,
                            )

                        );
                       // core::PrintArray($userValue);

                        if($userValue['email'] != ''){

                            $emailData = array(
                                "prospect_id" => $prospectId,
                                "contact" => $userValue['email'],
                                "contact_type" => "email",
                                "is_wrong" =>0,
                                "is_primary" =>1

                            );
                            $db->Insert('prospect_contact', $emailData);

                        }

                        if($userValue['phone_number'] != ''){

                            $phoneNumberData = array(
                                "prospect_id" => $prospectId,
                                "contact" => $userValue['phone_number'],
                                "contact_type" => "phone",
                                "is_wrong" >"0",
                                "is_primary" =>"1"
                            );
                            $db->Insert('prospect_contact', $phoneNumberData);

                        }
                    }
                    //core::PrintArray($userValue);
                }
            }

            }
            $db->ConnectionClose();
            $response['success'] = true;
            $response['title'] = "Records Syn";
            $response['msg'] = ' Record (s) Synchronize successfully';
            echo json_encode($response);
            exit;

        }

    }
}

$db->ConnectionClose();
$response['success'] = true;
$response['title'] = "Records Syn";
$response['msg'] = ' Record (s) Synchronize successfully';
echo json_encode($response);