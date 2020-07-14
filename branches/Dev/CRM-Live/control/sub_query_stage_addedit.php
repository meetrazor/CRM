<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/11/2019
 * Time: 1:27 PM
 */

include_once 'header.php';
include_once '../core/Validator.php';

$table = "sub_query_stage_master";
$table_id = 'sub_query_stage_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array('sub_query_stage_description'));
    $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
    $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;
    $data['sub_query_stage_name'] = (isset($data['sub_query_stage_name'])) ? trim($data['sub_query_stage_name']) : '';

    $subQueryStageName = $data['sub_query_stage_name'];
    $data['sub_query_stage_description'] = Utility::cleanedit($data['sub_query_stage_description']);
    $validator = array(

        'rules' => array(
            'query_stage_id' => array('required' => true),
            'sub_query_stage_name' => array(
                'required' => true,
                'maxlength' => 100,
            ),
        ),
        'messages' => array(
            'query_stage_id' => array('required' => 'Please select query stage'),
            'sub_query_stage_name' => array(
                'required' => 'Please enter sub query stage name',
                'maxlength' => 'Max length is 100 character for sub query stage name',
                ),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $id = (isset($data['sub_query_stage_id']) && $data['sub_query_stage_id'] != '') ? $data['sub_query_stage_id'] : 0;
        $queryStageId = $data['query_stage_id'];
        $dataUpdate['is_default'] = 0;
        $response = array();
        $existCondition = "sub_query_stage_name = '".$subQueryStageName."' && query_stage_id = '". $queryStageId ."' ";

        if($id > 0){

            $existCondition .= " && sub_query_stage_id != '$id'";

            $exist = $db->FunctionFetch($table, 'count', array($table_id), $existCondition, array(0,1));
            if($exist == 0){
                // Update record
                $flag = 0;
                if($data['is_default'] == 1){
                    $newConditionCheck = "is_default = '1' && sub_query_stage_id != '$id' && query_stage_id = '$queryStageId'";
                    $newExist = $db->FunctionFetch($table, 'count', array("query_stage_id"), $newConditionCheck, array(0,1));
                    if($newExist > 0)
                    {
                        $newConditionUpdate = "sub_query_stage_id != '$id' && query_stage_id = '$queryStageId'";
                        $flag = ($db->UpdateWhere($table,$dataUpdate,$newConditionUpdate)) ? 1 : 0;
                    }

                }
                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $flag = ($db->Update($table, $data, $table_id, $id)) ? 1: 0;
                if($flag == 1){
                    $response['success'] = true;
                    $response['act'] = 'updated';
                    $response['title'] = 'Successful';
                    $response['msg'] = 'Record updated successfully!!';
                }else{
                    $response['success'] = false;
                    $response['act'] = 'updated';
                    $response['title'] = 'Error';
                    $response['msg'] = 'Something went wrong!!';
                }
            }else{
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Sub query stage with name: {$data['sub_query_stage_name']} already exist";
            }
        }else{

            // Adding a new record
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $existCondition, array(0,1));
                if($exist > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Sub query stage with name: {$data['sub_query_stage_name']} already exist";
                }else{
                    $flag = 0;
                    if($data['is_default'] == 1){
                        $newConditionCheck = "is_default = '1' && query_stage_id = '$queryStageId'";
                        $newExist = $db->FunctionFetch($table, 'count', array("query_stage_id"), $newConditionCheck, array(0,1));
                        if($newExist > 0)
                        {
                            $newConditionUpdate = "query_stage_id = '$queryStageId'";
                            $flag = ($db->UpdateWhere($table,$dataUpdate,$newConditionUpdate)) ? 1 : 0 ;
                        }
                    }
                    $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                    $insertId = $db->Insert($table, $data, true);
                    $flag = ($insertId != '') ? 1 : 0;
                    if($flag == 1){
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "Record added successfully!";
                    }
                    else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "Something went wrong!";
                    }
                }
            }
        echo json_encode($response);
    }
}elseif ($action == 'get_product_type_dd'){
    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id']) && $data['id'] != ''){
        $condition = "loan_type_id = '{$data['id']}' AND is_active = '1'";
    }
    if(isset($data['category_id']) && $data['category_id'] != 0){
        $selectedCategory = $data['category_id'];
    }else{
        $selectedCategory = null;
    }
    $productTypeDd = $db->CreateOptions('html', 'category_master', array('category_id','category_name'), $selectedCategory, array('category_name' => 'asc'), $condition);
    if(isset($_POST['empty_opt'])){
        $productTypeDd = "<option value=''></option>" . $productTypeDd;
    }else{
        $productTypeDd = Core::PrependNullOption($productTypeDd);
    }
    echo $productTypeDd;
}elseif($action == 'get_reason_dd'){
    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id']) && $data['id'] != ''){
        $condition = "category_id = '{$data['id']}'";
    }
    if(isset($data['reason_id']) && $data['reason_id'] != 0){
        $selectedReason = $data['reason_id'];
    }else{
        $selectedReason = null;
    }
    echo $selectedReason;
    $reasonDd = $db->CreateOptions('html', 'reason_master', array('reason_id','reason_name'), $selectedReason, array('reason_name' => 'asc'), $condition."and is_active = '1'");

    if(isset($_POST['empty_opt'])){
        $reasonDd = "<option value=''></option>" . $reasonDd;
    }else{
        $reasonDd = Core::PrependNullOption($reasonDd);
    }
    echo $reasonDd;
}elseif($action == 'get_query_stage_dd'){

    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id']) && $data['id'] != ''){
        $condition = "reason_id = '{$data['id']}'";
    }
    $defaultQueryStageId = $db->FetchCellValue("query_stage_master", "query_stage_id", "is_default='1'");
    $selected = (isset($data['query_stage_id']) && $data['query_stage_id'] != 0 && $data['query_stage_id'] != '' ) ? $data['query_stage_id'] : $defaultQueryStageId;
    if($data['query_stage_id'] != 0 && $data['query_stage_id'] != '' ){
        $condition .= "and (is_active = '1' or query_stage_id = {$data['query_stage_id']})";

    }else{
        $condition .= "and is_active = '1'";
    }
    $queryStageDd = $db->CreateOptions('html', 'query_stage_master', array('query_stage_id','query_stage_name'), $selected, array('query_stage_name' => 'asc'), $condition);

    if(isset($_POST['empty_opt'])){
        $queryStageDd = "<option value=''></option>" . $queryStageDd;
    }else{
        $queryStageDd = Core::PrependNullOption($queryStageDd);
    }
    echo $queryStageDd;
}
include_once 'footer.php';