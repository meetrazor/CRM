<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "campaign_master";
$table_id = 'campaign_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $validator = array(

        'rules' => array(
            'campaign_name' => array('required' => true),
            'phone_number' => array('required' => true),
            'start_date' => array('required' => true),
            'end_date' => array('required' => true),
            'description' => array('required' => true)
        ),
        'messages' => array(
            'campaign_name' => array('required' => "please enter campaign name"),
            'phone_number' => array('required' => "please enter campaign ID","number"=> "please enter only number"),
            'start_date' => array('required' => "please enter start date"),
            'end_date' => array('required' => 'please select end Date'),
            'description' => array('required' => "please enter description")
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $id = (isset($data['campaign_id']) && $data['campaign_id'] != '') ? $data['campaign_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['start_date'] = isset($data['start_date']) ? core::DMYToYMD($data['start_date']) : '';
        $data['end_date'] = isset($data['end_date']) ? core::DMYToYMD($data['end_date']) : '';
        $category = isset($data['category_id']) ? $data['category_id'] : array();
        $telecaller = isset($data['user_id']) ? $data['user_id'] : array();

        $response = array();
        $exist_condition = "campaign_name='{$data['campaign_name']}'";

        if($id > 0){

            $exist_condition .= " && campaign_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);
                if(count($category) > 0){
                    $db->DeleteWhere("campaign_category","$table_id = $id");
                    foreach($category as $categoryId) {
                        $categoryData['campaign_id'] = $id;
                        $categoryData['category_id'] = $categoryId;
                        $db->Insert('campaign_category', $categoryData);
                    }
                }
                if(count($telecaller) > 0){
                    $db->DeleteWhere("campaign_telecaller","campaign_id = $id");
                    foreach($telecaller as $telecallerId) {
                        $telecallerData['campaign_id'] = $id;
                        $telecallerData['user_id'] = $telecallerId;
                        $db->Insert('campaign_telecaller', $telecallerData);
                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Campaign with the campaign name: {$data['campaign_name']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){
                unset($data['category_id']);
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $campaignSort = $db->FetchCellValue("campaign_type_master","sort_code","campaign_type_id = '{$data['campaign_type_id']}'");
                $data['campaign_code'] = Core::PadString($db->GetNextAutoIncreamentValue($table), 0 ,$campaignSort);
                $insertId = $db->Insert($table, $data,1);

                if(count($category) > 0){
                    foreach($category as $categoryId) {
                        $categoryData['campaign_id'] = $insertId;
                        $categoryData['category_id'] = $categoryId;
                        $db->Insert('campaign_category', $categoryData);
                    }
                }
                if(count($telecaller) > 0){
                    foreach($telecaller as $telecallerId) {
                        $telecallerData['campaign_id'] = $insertId;
                        $telecallerData['user_id'] = $telecallerId;
                        $db->Insert('campaign_telecaller', $telecallerData);
                    }
                }
                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Campaign with the campaign name: {$data['campaign_name']} already exist";
            }
        }
        echo json_encode($response);
    }
}
include_once 'footer.php';
