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
            //'email' => array('email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
            'state_id' => array('required' => true),
            'city_id' => array('required' => true),
            'sub_locality_id' => array('required' => true),
            'lead_name' => array('required' => true),
            'category_id' => array('required' => true),
        ),
        'messages' => array(
            //'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'lead_name' => array('required' => "Please enter client name"),
            'category_id' => array('required' => "Please select category"),
            'sub_locality_id' => array('required' => 'Please select sub locality')
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['birth_date'] = isset($data['birth_date']) ? core::DMYToYMD($data['birth_date']) : "";
        $id = (isset($data['lead_id']) && $data['lead_id'] != '') ? $data['lead_id'] : 0;


        $data['is_close'] = isset($data['is_close']) ? 1 : 0;

        $response = array();
        $exist_condition = "1!=1";

        if($id > 0){

            $exist_condition .= " && lead_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);
                $leadData = $db->FetchToArray($table,"*","$table_id = $id");
                $db->Insert("lead_master_history",$leadData[0]);
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
            $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$data['category_id']}'");
            $tableId = $db->GetNextAutoIncreamentValue($table);
            $data['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                //$data['lead_manager'] = $user_id;
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);
                $data['lead_id'] = $insertId;
                $db->Insert("lead_master_history",$data);
                $response['success'] = true;
                $response['act'] = 'added';
                $response['lead_id'] = $insertId;
                $response['title'] = 'Successful';
                $response['data'] = $data;
                $response['msg'] = 'Record added successfully and Lead Number is '.$data['lead_code'].'!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "lead with mobile: {$data['mobile_no']} already exist";
            }
        }
        echo json_encode($response);
    }
} elseif ($action == 'calfees'){
    $data = $db->FilterParameters($_POST);
    $catPrice = 0;
    if($data['category_id'] != '' && $data['tier_id'] != '') {
        $catPrice = $db->FetchCellValue("tier_category","commission","category_id = '{$data['category_id']}' and tier_id = '{$data['tier_id']}'");
    }
    echo $catPrice;
}
include_once 'footer.php';