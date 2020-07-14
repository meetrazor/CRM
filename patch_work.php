<?php

include_once 'header.php';

// update missing BD
//$sql = "SELECT * FROM activity_master WHERE activity_on = 'bd' AND type_id IN (SELECT lead_id FROM lead_master WHERE bd_id = 0) AND disposition_id = 1";
//
//$res = $db->Query($sql);
//$data = $db->FetchToArrayFromResultset($res);
//
//if(count($data) > 0){
//    foreach($data as $key => $dataVal){
//        $bdId = Utility::getBdForLead(177);
//        $db->UpdateWhere("lead_master",array("bd_id"=>$bdId),"lead_id = '{$dataVal['type_id']}'");
//        echo $key;
//    }
//}


// add missing lead users
//$sql = "SELECT  * FROM lead_master WHERE lead_id NOT IN (SELECT lead_id FROM lead_users)";
//
//$res = $db->Query($sql);
//$data = $db->FetchToArrayFromResultset($res);
//
//if(count($data) > 0){
//    foreach($data as $key => $dataVal){
//        Utility::addOrFetchFromTable("lead_users",array(
//            "lead_id" => $dataVal['lead_id'],
//            "user_id" => $dataVal['bd_id'],
//            "user_type" => "bd",
//            "user_type_id" => UT_BD,
//            "is_latest" => "1",
//            "created_at"=>$dataVal['created_at'],
//            "created_by"=>$dataVal['created_by'],
//        ),"lead_id","user_id = '{$dataVal['bd_id']}' and lead_id = '{$dataVal['lead_id']}'");
//        echo $key;
//    }
//}
//
//exit;

// add missing lead disposition from activity


$sql = "SELECT  * FROM activity_master WHERE type_id NOT IN (SELECT prospect_id FROM lead_master) AND disposition_id = 1";

$res = $db->Query($sql);
$data = $db->FetchToArrayFromResultset($res);

if(count($data) > 0){
    foreach($data as $key => $dataVal){
         $db->UpdateWhere("activity_master",array("activity_on"=>"bd"),"activity_id = '{$dataVal['activity_id']}'");
        $leadData = $db->FetchRowForForm("prospect_master","*","prospect_id = '{$dataVal['type_id']}'");
        $contacts = $db->FetchToArray("prospect_contact","*","prospect_id = '{$dataVal['type_id']}' and is_primary = 1");
        $email = array();
        $number = array();
        foreach($contacts as $contact){
            if($contact['contact_type'] == 'phone'){
                $number[] = $contact['contact'];
            } else {
                $email[] = $contact['contact'];
            }
        }
        $leadMobile = implode(",",$number);
        $leadEmail = implode(",",$email);
        $assignUserId = Utility::getBdForLead($leadData['city_id']);
        if($assignUserId == '') {
            $assignUserId = Utility::getBdForLead(177);
        }
        $customerId = Utility::addOrFetchFromTable("customer_master",
            array_merge(array(
                "mobile_no"=>$leadMobile,
                "email"=>$leadEmail,
                "customer_name"=>$leadData['first_name']." ".$leadData['last_name'],
                "pincode" => $leadData['pincode'],
                "state_id" => $leadData['state_id'],
                "city_id" => $leadData['city_id'],
                "address" => $leadData['address'],
            ),$db->TimeStampAtCreate($dataVal['created_by'])),"customer_id","mobile_no in ('$leadMobile')");

        // create Lead
        $cwLeadData = array(
            "lead_name" => $leadData['first_name']." ".$leadData['last_name'],
            "mobile_no" => $leadMobile,
            "email" => $leadEmail,
            "address" => $leadData['address'],
            "pincode" => $leadData['pincode'],
            "state_id" => $leadData['state_id'],
            "city_id" => $leadData['city_id'],
            "amount" => $leadData['amount'],
            "actual_amount" => $leadData['actual_amount'],
            "customer_id" => $customerId,
            "status_id" => $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1"),
            "category_id" => $leadData['category_id'],
            "remarks" => isset($dataVal['remarks']) ? $dataVal['remarks'] : "",
            "bd_id" => ($assignUserId != 0) ? $assignUserId : "",
            "prospect_id" => $dataVal['type_id']
        );

        $cwLeadData = array_merge($cwLeadData,$db->TimeStampAtCreate($dataVal['created_by']));
        $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$leadData['category_id']}'");
        $tableId = $db->GetNextAutoIncreamentValue("lead_master");
        $cwLeadData['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
        $leadId = Utility::addOrFetchFromTable("lead_master", $cwLeadData,"lead_id","prospect_id = '{$dataVal['type_id']}' and category_id = '{$leadData['category_id']}' and amount = '{$leadData['amount']}'");
        // add lead user
        if($assignUserId != 0){
            $db->UpdateWhere("lead_users",array("is_latest"=>0),"lead_id = '{$leadId}' and user_type = 'bd'");
            Utility::addOrFetchFromTable("lead_users",array(
                "lead_id" => $leadId,
                "user_id" => $assignUserId,
                "user_type" => "bd",
                "user_type_id" => UT_BD,
                "is_latest" => "1",
                "created_at"=>$dataVal['created_at'],
                "created_by"=>$dataVal['created_by'],
            ),"lead_id","user_id = '{$assignUserId}' and lead_id = '{$leadId}'");
        }
        echo $key;
    }
}



include_once 'footer.php';