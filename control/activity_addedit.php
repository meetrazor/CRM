<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "activity_master";
$table_id = 'activity_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));
    $data['start_date_time'] = (isset($data['start_date_time']) && $data['start_date_time'] != '') ? core::DMYToYMD($data['start_date_time']) : 0;
    //$data['follow_up_date_time'] = (isset($data['follow_up_date_time']) && $data['follow_up_date_time'] != '') ? core::DMYToYMD($data['follow_up_date_time'],true) : 0;


    $data['duration'] =  $data['duration_minute']." minutes ".$data['duration_second']." second";

    $validator = array(

        'rules' => array(
            //'activity_name' => array('required' => true),
            'remarks' => array('required' => true),
            'start_date_time' => array('required' => true),
            'type_id' => array('required' => true),
            'duration_minute' => array('required' => true,'digits'=>true),
            'duration_second' => array('required' => true,'digits'=>true),
            'call_time' => array('required' => true),
            'disposition_id' => array('required' => true),
        ),
        'messages' => array(
            'activity_name' => array('required' => "please enter activity name"),
            'remarks' => array('required' => "please enter notes"),
            'start_date_time' => array('required' => "please enter start date"),
            'type_id' => array('required' => "please select prospect"),
            'duration_minute' => array('required' => 'please enter minutes','digits'=>'please enter digit only'),
            'duration_second' => array('required' => 'please enter minutes','digits'=>'please enter digit only'),
            'call_time' => array('required' => "please select call start time"),
            'disposition_id' => array('required' => "please select disposition"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $id = (isset($data['activity_id']) && $data['activity_id'] != '') ? $data['activity_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        //$exist_condition = "activity_name='{$data['activity_name']}'";
        $exist_condition = "1 != 1";

        if($id > 0){

            $exist_condition .= " && activity_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                $activityData = $db->FetchRowForForm("activity_master","*","activity_id = '{$id}'");
                $prospectData = $db->FetchRowForForm("prospect_master","*","prospect_id = '{$data['type_id']}'");
                if(isset($activityData['disposition_id']) && $activityData['disposition_id'] != ''){
                    if($activityData['disposition_id'] != $data['disposition_id']){
                        $oldDispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$activityData['disposition_id']}'");
                        if($oldDispositionData['disposition_id'] && $oldDispositionData['disposition_id'] != ''){
                            if($oldDispositionData['is_meeting'] == 0 && $oldDispositionData['is_callback'] == 1){
                                $prospectQueueData = $db->FetchRowForForm("prospect_queue","*","prospect_id = '{$data['type_id']}' and is_done = 0");

                                // deleting existing prospect queue
                                if(isset($prospectQueueData['prospect_queue_id']) && $prospectQueueData['prospect_queue_id'] != ''){
                                    $db->DeleteWhere("prospect_queue","prospect_queue_id = '{$prospectQueueData['prospect_queue_id']}'");
                                }
                            }

                        }
                        $dispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$data['disposition_id']}'");
                        if($dispositionData['is_meeting'] == 0 && $dispositionData['is_callback'] == 1){

                            $db->Insert("prospect_queue",array(
                                    "prospect_id" => isset($data['type_id']) ? $data['type_id'] : "",
                                    "campaign_id" => $prospectData['campaign_id'],
                                    "created_at"=>DATE_TIME_DATABASE,
                                    "created_by"=>$user_id,
                                    "updated_at"=>DATE_TIME_DATABASE,
                                    "updated_by"=>$user_id,
                                    "date_queued"=>$data['follow_up_date_time'],
                                )
                            );
                        }
                        if($dispositionData['is_meeting'] == 1 && $dispositionData['is_callback'] == 1){
                            if(isset($data['activity_on']) && $data['activity_on'] != ''){
                                $bdId = Utility::getBdForLead($prospectData['city_id']);

                                if($bdId == '') {
                                    $response['success'] = false;
                                    $response['title'] = 'Exist';
                                    $response['msg'] = "BD not available for this lead city";
                                    echo json_encode($response);
                                    exit;
                                }
                            }

                            if(isset($data['activity_on']) && $data['activity_on'] == 'bd'){
                                // create customer
                                $contacts = $db->FetchToArray("prospect_contact","*","prospect_id = '{$data['type_id']}' and is_primary = 1");
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
                                $customerId = Utility::addOrFetchFromTable("customer_master",
                                    array_merge(array(
                                        "mobile_no"=>$leadMobile,
                                        "email"=>$leadEmail,
                                        "customer_name"=>$prospectData['first_name']." ".$prospectData['last_name'],
                                        "customer_from" => "crm",
                                        "pincode" => $prospectData['pincode'],
                                        "state_id" => $prospectData['state_id'],
                                        "city_id" => $prospectData['city_id'],
                                        "address" => $prospectData['address'],
                                    ),$db->TimeStampAtCreate($user_id)),"customer_id","mobile_no in ('$leadMobile')");

                                if($data['activity_on'] == "ap"){
                                    //Utility::sendPushNotificationForLead($leadId, $prospectData['prospect_name']);
                                }
                                // create Lead
                                $cwLeadData = array(
                                    "lead_name" => $prospectData['first_name']." ".$prospectData['last_name'],
                                    "mobile_no" => $leadMobile,
                                    "email" => $leadEmail,
                                    "address" => $prospectData['address'],
                                    "pincode" => $prospectData['pincode'],
                                    "state_id" => $prospectData['state_id'],
                                    "city_id" => $prospectData['city_id'],
                                    "amount" => $prospectData['amount'],
                                    "actual_amount" => $prospectData['actual_amount'],
                                    "customer_id" => $customerId,
                                    "status_id" => $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1"),
                                    "category_id" => $prospectData['category_id'],
                                    "remarks" => isset($data['remarks']) ? $data['remarks'] : "",
                                    "bd_id" => isset($bdId) ? $bdId : "",
                                    "prospect_id" => isset($data['type_id']) ? $data['type_id'] : "",
                                    "activity_id" => $id,
                                    "lead_form" => "crm",
                                );

                                $cwLeadData = array_merge($cwLeadData,$db->TimeStampAtCreate($user_id));
                                $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$prospectData['category_id']}'");
                                $tableId = $db->GetNextAutoIncreamentValue("lead_master");
                                $cwLeadData['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
                                $leadId = $db->Insert("lead_master", $cwLeadData,1);
                                // add lead user
                                if($bdId != ''){
                                    $db->UpdateWhere("lead_users",array("is_latest"=>0),"lead_id = '{$id}' and user_type = 'bd'");
                                    $db->Insert("lead_users",array(
                                        "lead_id" => $leadId,
                                        "user_id" => $bdId,
                                        "user_type" => "bd",
                                        "user_type_id" => UT_BD,
                                        "is_latest" => "1",
                                        "created_at"=>DATE_TIME_DATABASE,
                                        "created_by"=>$user_id,
                                    ));
                                }
                                if($data['activity_on'] == "sp"){
                                    //Utility::sendPushNotificationForLead($leadId, $prospectData['prospect_name']);
                                }

                            }
                        }
                    } else {
                        $oldDispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$activityData['disposition_id']}'");
                        if($oldDispositionData['disposition_id'] && $oldDispositionData['disposition_id'] != ''){
                            if($oldDispositionData['is_meeting'] == 0 && $oldDispositionData['is_callback'] == 1){
                                $prospectQueueData = $db->FetchRowForForm("prospect_queue","*","prospect_id = '{$data['type_id']}' and is_done = 0");
                                // deleting existing prospect queue
                                if(isset($prospectQueueData['prospect_queue_id']) && $prospectQueueData['prospect_queue_id'] != ''){
                                    $queueData = array(
                                        "updated_at"=>DATE_TIME_DATABASE,
                                        "updated_by"=>$user_id,
                                        "date_queued"=>$data['follow_up_date_time'],
                                    );
                                    $db->UpdateWhere("prospect_queue",$queueData,"prospect_queue_id = '{$prospectQueueData['prospect_queue_id']}'");
                                }
                            }

                        }
                    }
                }


                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);


                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "activity with the activity name: {$data['activity_name']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                $bdId = '';
                $leadData = $db->FetchRowForForm("prospect_master","*","prospect_id = '{$data['type_id']}'");
                if(isset($data['prospect_queue_id']) && $data['prospect_queue_id'] == ''){
                    $response['success'] = false;
                    $response['title'] = 'Exist';
                    $response['msg'] = "prospect queue missing";
                    echo json_encode($response);
                    exit;
                }

                if(isset($data['activity_on']) && $data['activity_on'] != ''){
                    $bdId = Utility::getBdForLead($leadData['city_id']);
                    if($bdId == '') {
                        $response['success'] = false;
                        $response['title'] = 'Exist';
                        $response['msg'] = "BD not available for this lead city";
                        echo json_encode($response);
                        exit;
                    }
                }
                $data['source_type'] = 'prospect';
                $data['is_latest'] = 1;
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $db->UpdateWhere($table,array("is_latest"=>0),"activity_type = '{$data['activity_type']}' and type_id = '{$data['type_id']}' and source_type = 'prospect'");
                $insertId = $db->Insert($table, $data,1);
                $dispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$data['disposition_id']}'");
                //Utility::statusCall($agentCode,$dispositionData['disposition_code'],$data['follow_up_date_time']);
                if($insertId != ''){
                    $db->UpdateWhere("prospect_master",array("is_done"=>1),"prospect_id = '{$data['type_id']}'");
                    $updateQueue = $db->UpdateWhere("prospect_queue",array_merge(array("is_done"=>1,"activity_id"=>$insertId,"disposition_id"=>$data['disposition_id']),$db->TimeStampAtUpdate($user_id)),"prospect_queue_id = '{$data['prospect_queue_id']}'");

                    if($updateQueue){
                        if($dispositionData['is_meeting'] == 0 && $dispositionData['is_callback'] == 1){

                            $db->Insert("prospect_queue",array(
                                    "prospect_id" => isset($data['type_id']) ? $data['type_id'] : "",
                                    "campaign_id" => $db->FetchCellValue("prospect_master","campaign_id","prospect_id = '{$data['type_id']}'"),
                                    "created_at"=>DATE_TIME_DATABASE,
                                    "created_by"=>$user_id,
                                    "updated_at"=>DATE_TIME_DATABASE,
                                    "updated_by"=>$user_id,
                                    "date_queued"=>$data['follow_up_date_time'],
                                )
                            );
                        }
                        if(isset($data['activity_on']) && $data['activity_on'] == 'bd'){
                            // create customer
                            $contacts = $db->FetchToArray("prospect_contact","*","prospect_id = '{$data['type_id']}' and is_primary = 1");
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
                            $customerId = Utility::addOrFetchFromTable("customer_master",
                                array_merge(array(
                                    "mobile_no"=>$leadMobile,
                                    "email"=>$leadEmail,
                                    "customer_name"=>$leadData['first_name']." ".$leadData['last_name'],
                                    "customer_from" => "crm",
                                    "pincode" => $leadData['pincode'],
                                    "state_id" => $leadData['state_id'],
                                    "city_id" => $leadData['city_id'],
                                    "address" => $leadData['address'],
                                ),$db->TimeStampAtCreate($user_id)),"customer_id","mobile_no in ('$leadMobile')");
    //                        if($data['activity_on'] == "bd"){
    //                            $bdId = Utility::getBdForLead($leadData['city_id']);
    //                        }
                            if($data['activity_on'] == "ap"){
                                //Utility::sendPushNotificationForLead($leadId, $leadData['prospect_name']);
                            }
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
                                "remarks" => isset($data['remarks']) ? $data['remarks'] : "",
                                "bd_id" => isset($bdId) ? $bdId : "",
                                "prospect_id" => isset($data['type_id']) ? $data['type_id'] : "",
                                "activity_id" => $insertId,
                                "lead_form" => "crm",
                            );

                            $cwLeadData = array_merge($cwLeadData,$db->TimeStampAtCreate($user_id));
                            $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$leadData['category_id']}'");
                            $tableId = $db->GetNextAutoIncreamentValue("lead_master");
                            $cwLeadData['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
                            $leadId = $db->Insert("lead_master", $cwLeadData,1);
                            // add lead user
                            if($bdId != ''){
                                $db->UpdateWhere("lead_users",array("is_latest"=>0),"lead_id = '{$id}' and user_type = 'bd'");
                                $db->Insert("lead_users",array(
                                    "lead_id" => $leadId,
                                    "user_id" => $bdId,
                                    "user_type" => "bd",
                                    "user_type_id" => UT_BD,
                                    "is_latest" => "1",
                                    "created_at"=>DATE_TIME_DATABASE,
                                    "created_by"=>$user_id,
                                ));
                            }
                            if($data['activity_on'] == "sp"){
                                //Utility::sendPushNotificationForLead($leadId, $leadData['prospect_name']);
                            }

                        }
                    }
                }
//                $nextId = $db->FetchCellValue("prospect_master","prospect_id","prospect_id = (select min(prospect_id) from prospect_master where prospect_id > {$data['type_id']})");
                //$nextId = Utility::getNextProspect($user_id);
                $response['success'] = true;
                $response['act'] = 'added';
                //$response['next_id'] = $nextId;
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "activity with the activity name: {$data['activity_name']} already exist";
            }
        }
        //$afterId = $db->FetchCellValue("activity_master","activity_id","1=1 order by activity_id desc");
        //$db->UpdateWhere("file_master",array("type_id_after"=>$afterId),"file_id = '{$fileId}'");
        echo json_encode($response);
    }
}
include_once 'footer.php';
