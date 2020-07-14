<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "tier_category";
$table_id = 'tier_category_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST);

    $validator = array(

        'rules' => array(
            'tier_id' => array('required' => true),
            'effective_date' => array('required' => true),
        ),
        'messages' => array(
            'tier_id' => array('required' => "please select tier"),
            'effective_date' => array('required' => "please select effective date"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $response = array();
        $data['effective_date'] = isset($data['effective_date']) ? core::DMYToYMD($data['effective_date']) : "";
        $category = (isset($data['category_id'])) ? $data['category_id']  : array();
        $commission = (isset($data['commission'])) ? $data['commission']  : array();
        $maxWithdrawal = (isset($data['max_withdrawal'])) ? $data['max_withdrawal']  : array();
        if(count($category) > 0) {
            $tierId = (isset($data['tier_id'])) ? $data['tier_id']  : '';
            $effectiveDate = (isset($data['effective_date'])) ? $data['effective_date']  : '';
            foreach ($category as $key => $categoryId){
                $exist_condition = "tier_id = '{$tierId}' and category_id = '{$categoryId}' and effective_date = '{$data['effective_date']}'";
                $insertData['category_id'] = $categoryId;
                $insertData['tier_id'] = $data['tier_id'];
                $insertData['commission'] = (array_key_exists($key,$commission)) ? $commission[$key] : 0;
                $insertData['max_withdrawal'] = (array_key_exists($key,$maxWithdrawal)) ? $maxWithdrawal[$key] : 0;
                $insertData['effective_date'] = isset($data['effective_date']) ? core::DMYToYMD($data['effective_date']) : "";
                $insertData = array_merge($insertData,$db->TimeStampAtCreate($user_id));
                Utility::addOrFetchFromTable($table,$insertData,$table_id,$exist_condition);
            }
        }
        $response['success'] = true;
        $response['act'] = 'added';
        $response['title'] = 'Successful';
        $response['msg'] = 'Record added successfully';


        echo json_encode($response);
    }
}
include_once 'footer.php';