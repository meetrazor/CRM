<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "tier_category";
$table_id = 'tier_category_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('edit' === $action){

    $data = $db->FilterParameters($_POST);

    $validator = array(

        'rules' => array(
            'tier_id' => array('required' => true),
            'category_id' => array('required' => true),
            'effective_date' => array('required' => true),
            'commission' => array('required' => true,"number"=>true),
            'max_withdrawal' => array('required' => true,"number"=>true),
        ),
        'messages' => array(
            'tier_id' => array('required' => "please select tier"),
            'category_id' => array('required' => "please select category"),
            'effective_date' => array('required' => "please select effective date"),
            'commission' => array('required' => "please enter commission","number"=>"please enter number only"),
            'max_withdrawal' => array('required' => "please enter max withdrawal","number"=>"please enter number only"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $response = array();
        $tier_category_id = isset($data['tier_category_id']) ? $data['tier_category_id'] : "";
        if($tier_category_id  != '') {
            $data['effective_date'] = isset($data['effective_date']) ? core::DMYToYMD($data['effective_date']) : "";
            $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
            $db->UpdateWhere($table,$data,"tier_category_id = '{$data['tier_category_id']}'");
            $response['success'] = true;
            $response['act'] = 'added';
            $response['title'] = 'Successful';
            $response['msg'] = 'Record updated successfully';
        } else {
            $response['success'] = false;
            $response['act'] = 'added';
            $response['title'] = 'Unsuccessful';
            $response['msg'] = 'Record added unsuccessfully';
        }



        echo json_encode($response);
    }
}
include_once 'footer.php';