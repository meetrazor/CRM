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

// https://developers.facebook.com/blog/post/2011/05/13/how-to--handle-expired-access-tokens/;
//$jsonData = json_decode(file_get_contents('https://graph.facebook.com/oauth/access_token?client_id=213185289167641&client_secret=d97651b98a89457d6d8dfda7ac82d711&grant_type=fb_exchange_token&fb_exchange_token=EAADB5BFuoxkBAKrJ6bbLNsZAwYXjhMCrHcZB5aZAhXiYp7aS4PzSZCiWMWNfyuPiPtmeLDa9w2rGm1FMyR9i29OsSjsAGEv8K4NvqnlPVWPoZAYgFptFxXnt0tbNL40BlxmNWmkZC2jRjRSao0qNftjKAuSpKwI6kZD'));
//$jsonData = json_decode(json_encode($jsonData), True);
$jsonData = json_decode(@file_get_contents("https://graph.facebook.com/v2.8/449622328579373/leadgen_forms?access_token=EAADB5BFuoxkBAKrJ6bbLNsZAwYXjhMCrHcZB5aZAhXiYp7aS4PzSZCiWMWNfyuPiPtmeLDa9w2rGm1FMyR9i29OsSjsAGEv8K4NvqnlPVWPoZAYgFptFxXnt0tbNL40BlxmNWmkZC2jRjRSao0qNftjKAuSpKwI6kZD"));

$jsonData = json_decode(json_encode($jsonData), True);
//core::PrintArray($jsonData);

if(count($jsonData['data']) > 0){
    foreach($jsonData['data'] as $data){
        $campaignName = $data['name'];
        list($campaignActualName,$categoryName,$cityName) = array_pad(explode('_', $campaignName, 3), 3, null);
        $cityId = $db->FetchCellValue("city","city_id","lower(city_name) = '".strtolower($cityName)."'");
        $categoryId = $db->FetchCellValue("category_master","category_id","lower(category_code) = '".strtolower($categoryName)."'");
        $insertData = array(
            "campaign_name" => $data['name'],
            "phone_number" => $data['id'],
            'campaign_code' => Core::PadString($db->GetNextAutoIncreamentValue("campaign_master"), 0 ,"SML"),
            'vendor_id' => VENDOR_LAVAL,
            'campaign_type_id' => CAMPAIGN_TYPE_SML
        );
        $insertData = array_merge($insertData,$db->TimeStampAtCreate($login_id));
        $campaignId = Utility::addOrFetchFromTable("campaign_master",$insertData,"campaign_id","phone_number = '{$data['id']}'");
        if($categoryId != ''){
            $db->DeleteWhere("campaign_category","campaign_id = '{$campaignId}'");
            $categoryData['campaign_id'] = $campaignId;
            $categoryData['category_id'] = $categoryId;
            $categoryData = array_merge($categoryData,$db->TimeStampAtCreate($login_id));
            $db->Insert('campaign_category', $categoryData);
        }
        if($cityId != ''){
            $db->DeleteWhere("campaign_city","campaign_id = '{$campaignId}'");
            $cityData['campaign_id'] = $campaignId;
            $cityData['city_id'] = $cityId;
            $db->Insert('campaign_city', $cityData);
        }
        if($campaignId != ''){
            $leadDataJ = json_decode(file_get_contents('https://graph.facebook.com/v2.8/'.$data['id'].'/leads?access_token=EAADB5BFuoxkBAKrJ6bbLNsZAwYXjhMCrHcZB5aZAhXiYp7aS4PzSZCiWMWNfyuPiPtmeLDa9w2rGm1FMyR9i29OsSjsAGEv8K4NvqnlPVWPoZAYgFptFxXnt0tbNL40BlxmNWmkZC2jRjRSao0qNftjKAuSpKwI6kZD&limit=5&pretty=1&date_format=U'));
            $leadData = json_decode(json_encode($leadDataJ), True);
          //  core::PrintArray($leadData);
            $viaId = '';
            foreach($leadData as $data){
            foreach($data as $key => $val){
                if(is_array($val) && array_key_exists("created_time",$val)){
                    $createdTime = date('Y-m-d H:i:s',$val['created_time']);
                }
                if(is_array($val) && array_key_exists("id",$val)){
                    $viaId = $val['id'];
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
                        "amount" => in_array("your_desired_loan_amount",$userValue) ? $userValue['your_desired_loan_amount'] : "",
                        "created_at"=>$createdTime,
                        "created_by"=>$login_id,
                        "updated_at"=>$createdTime,
                        "updated_by"=>$login_id,
                        "prospect_via"=>"fb",
                        "via_id"=>$viaId,
                        "category_id"=>$categoryId,
                    );
                    //$prospectId = Utility::addOrFetchFromTable("prospect_master",$insertData,"prospect_id","campaign_id = '{$campaignId}' and first_name = '{$userValue['full_name']}' and via_id = '{$viaId}'");
                    $res = $db->FetchRowWhere("prospect_master", array("prospect_id"), "campaign_id = '{$campaignId}' and category_id = '{$categoryId}' and via_id = '{$viaId}'");
                    $count = $db->CountResultRows($res);
                    //echo $count."<br/>";
                    if($count > 0) {
                        $row = $db->MySqlFetchRow($res);
                        $prospectId = $row['prospect_id'];
                    } else {
                        $prospectId = $db->Insert("prospect_master", $insertData, true);
                        if($prospectId != ''){
                            $db->Insert("prospect_queue",array(
                                    "prospect_id" => $prospectId,
                                    "campaign_id" => $campaignId,
                                    "created_at"=>$createdTime,
                                    "created_by"=>$login_id,
                                    "updated_at"=>$createdTime,
                                    "updated_by"=>$login_id,
                                    "date_queued"=>$createdTime,
                                    "call_via"=>"fb"
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
                                $userValue['phone_number'] = str_replace(array("+91"," "),array("",""),$userValue['phone_number']);
                                $phoneNumberData = array(
                                    "prospect_id" => $prospectId,
                                    "contact" => $userValue['phone_number'],
                                    "contact_type" => "phone",
                                    "is_wrong" =>"0",
                                    "is_primary" =>"1"
                                );
                                $db->Insert('prospect_contact', $phoneNumberData);
                            }
                        }
                    }

                    //core::PrintArray($userValue);
                }
            }

        }
            $numberUpdate = "UPDATE prospect_contact SET contact = REPLACE(contact, '+91', '') WHERE contact_type = 'phone'";
            $db->Query($numberUpdate);
            $spaceUpdate = "UPDATE prospect_contact SET contact = REPLACE(contact, ' ', '') WHERE contact_type = 'phone'";
            $db->Query($spaceUpdate);
//            $db->ConnectionClose();
//            $response['success'] = true;
//            $response['title'] = "Records Syn";
//            $response['msg'] = ' Record (s) Synchronize successfully';
//            echo json_encode($response);
//            exit;

        }

    }
}

$db->ConnectionClose();
$response['success'] = true;
$response['title'] = "Records Syn";
$response['msg'] = ' Record (s) Synchronize successfully';
echo json_encode($response);