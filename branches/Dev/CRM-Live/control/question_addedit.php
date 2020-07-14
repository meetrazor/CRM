<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "question_master";
$table_id = 'question_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
$optionArray = array('select','radio','checkbox');
if('addedit' === $action){

    $data = $db->FilterParameters($_POST);
    $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
    $data['is_required'] = (isset($data['is_required'])) ? 1 : 0;
    $data['is_multiple'] = (isset($data['is_multiple'])) ? 1 : 0;
    $data['reason'] = (isset($data['reason_for']))?implode(',', $data['reason_for']):"";
    $data['agentcalltype'] = (isset($data['agentcalltype_for']))?implode(',', $data['agentcalltype_for']):"";
//print_r($data);exit;
    $validator = array(

        'rules' => array(
            'question_for' => array('required' => true),
            'question_name' => array('required' => true),
            'question_short_name' => array('required' => true),
            'option_type' => array('required' => true),
            'sort_order' => array('number' => true),
        ),
        'messages' => array(
            'question_for' => array('required' => 'Please select question for'),
            'question_name' => array('required' => 'Please enter question'),
            'question_short_name' => array('required' => 'Please enter a short name of question'),
            'option_type' => array('required' => 'Please select option type'),
            'sort_order' => array('number' => 'Please enter only number'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){
        echo json_encode(array('success' => false, 'errors' => $errors));
        exit;
    } else {
        $id = (isset($data['question_id']) && $data['question_id'] != '') ? $data['question_id'] : 0;
        $existCondition = "question_name = '".$data['question_name']."' && option_type = '".$data['option_type']."'";
        $existShortNameCondition = "question_short_name = '".$data['question_short_name']."'";

        $response = array();
        if($id > 0){
            $existShortNameRes = $db->FunctionFetch($table, 'count', array($table_id), $existShortNameCondition." && question_id != '$id'", array(0,1));
            if($existShortNameRes == 0){
                $existCondition .= " && question_id != '$id'";
                $exist = $db->FunctionFetch($table, 'count', array($table_id), $existCondition, array(0,1));
                if($exist == 0){
                    $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                    $questionOption = (isset($data['option_value'])) ? $data['option_value'] : array();
                    $udpate = $db->Update($table, $data, $table_id, $id);
                    if($udpate != ''){
                        if(in_array($data['option_type'],$optionArray) && count($questionOption) > 0){
//                        $db->DeleteWhere("question_option_value","question_id = '{$id}'");
                            $optionWeight = 0;
                            $flagInsertOption = 0;
                            foreach($questionOption as $option){

                                if(isset($option['option_value_id']) && $option['option_value_id'] != '')
                                {
                                    $existOptionValueC = "option_value_id = '".$option['option_value_id']."'";
                                    $existOptionValueId = $db->FunctionFetch("question_option_value", 'count', array("option_value_id"), $existOptionValueC, array(0,1));
                                    if($existOptionValueId == 1){
                                        $updateData = array(
                                            'option_value' => $option['option_value'],
                                            'sort_order' => $option['sort_order'],
                                            'weight' => $option['weight'],
                                        );
                                        $flagInsertOption = ($db->Update("question_option_value", $updateData, "option_value_id", $option['option_value_id'])) ? 1 : 0;
                                    }
                                }
                                else{
                                    $insertData = array(
                                        'question_id' => $id,
                                        'option_value' => $option['option_value'],
                                        'sort_order' => $option['sort_order'],
                                        'weight' => $option['weight'],
                                    );
                                    $flagInsertOption = ($db->Insert("question_option_value", $insertData,1)) ? 1 : 0 ;
                                }
                            }
                            if($data['option_type'] == 'radio' || $data['option_type'] == 'select'){
                                foreach( $questionOption as $k => $v )
                                {
                                    $optionWeight = max( array( $optionWeight, $v['weight'] ) );
                                }
                            }
                            elseif ($data['option_type'] == 'checkbox'){
                                foreach( $questionOption as $k => $v ) {
                                    $optionWeight = $optionWeight + $v['weight'];
                                }
                            }
                            if($flagInsertOption == 1 ){
                                $questionWeight['question_weight'] = $optionWeight;
                                $db->Update("question_master", $questionWeight, $table_id, $id);
                            }

                            $response['success'] = true;
                            $response['act'] = 'updated';
                            $response['title'] = 'Successful';
                            $response['msg'] = 'Record updated successfully!!';
                        } else {
                            $response['success'] = true;
                            $response['act'] = 'updated';
                            $response['title'] = 'Successful';
                            $response['msg'] = 'Record updated successfully!!';
                        }
                    }
                    else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "Problem in updating record!";
                    }
                }else{
                    $response['success'] = false;
                    $response['title'] = 'Exist';
                    $response['msg'] = "Question: ' {$data['question_name']} ' with ' {$data['option_type']} ' option already exist";
                }
            }
            else{
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Question Short Name: ' {$data['question_short_name']} ' already exist";
            }
        }else{
            // Adding a new record
            $existShortNameRes = $db->FunctionFetch($table, 'count', array($table_id), $existShortNameCondition, array(0,1));
            if($existShortNameRes == 0){
                $exist = $db->FunctionFetch($table, 'count', array($table_id), $existCondition, array(0,1));
                if($exist > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Question: ' {$data['question_name']} ' with ' {$data['option_type']} ' option already exist";
                }else{
                    $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                    $questionOption = (isset($data['option_value'])) ? $data['option_value'] : array();
                    $insertId = $db->Insert($table, $data, true);
                    if($insertId != ''){
                        if(in_array($data['option_type'],$optionArray) && count($questionOption) > 0){
                            $optionWeight = 0;
                            $flagInsertOption = 0;
                            foreach($questionOption as $option){
                                $insertData = array(
                                    'question_id' => $insertId,
                                    'option_value' => $option['option_value'],
                                    'sort_order' => $option['sort_order'],
                                    'weight' => $option['weight'],
                                );
                                $flagInsertOption = ($db->Insert("question_option_value", $insertData,1)) ? 1 : 0;
                            }
                            if($data['option_type'] == 'radio' || $data['option_type'] == 'select'){
                                foreach( $questionOption as $k => $v )
                                {
                                    $optionWeight = max( array( $optionWeight, $v['weight'] ) );
                                }
                            }
                            elseif ($data['option_type'] == 'checkbox'){
                                foreach( $questionOption as $k => $v ) {
                                    $optionWeight = $optionWeight + $v['weight'];
                                }
                            }
                            if($flagInsertOption == 1 ){
                                $questionWeight['question_weight'] = $optionWeight;
                                $db->Update("question_master", $questionWeight, $table_id, $insertId);
                            }

                        }
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "Record added successfully!";
                    }
                    else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "Problem in inserting new record!";
                    }
                }
            }
            else{
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Question Short Name: ' {$data['question_short_name']} ' already exist";
            }
        }
        echo json_encode($response);
    }
}
elseif ('delete_row' == $action) {

    $response = array();
    if(isset($_POST['id'])){
        $ids = $db->FilterParameters($_POST['id']);
        $condition = "option_value_id = $ids ";
        $existingCheckR = $db->Fetch("call_audit_answer", "option_value_id", "option_value_id = '{$ids}'");
        $existingCheckC = $db->CountResultRows($existingCheckR);
        if ($existingCheckC > 0) {
            $response['success'] = false;
            $response['title'] = 'Error:';
            $response['msg'] = "You can't delete this option value because it has record!";
        }else{
            $result = $db->DeleteWhere("question_option_value", $condition);
            if($result){

                $questionTable = array("question_master as qm",array("qm.option_type"));
                $joinOptionTable = array(
                    array("left","question_option_value as qov","qov.question_id = qm.question_id",array("qov.question_id,  IF( qm.option_type = 'checkbox' , SUM(qov.weight) , MAX(qov.weight) ) as sum_weight")),
                );
                $sumWeightQ = $db->JoinFetch($questionTable,$joinOptionTable,"qm.question_id = ".$_POST['question_id'],null,array(0,1),"qov.question_id");
                $sumWeightR = $db->FetchToArrayFromResultset($sumWeightQ);
                $questionWeight['question_weight'] = $sumWeightR[0]['sum_weight'];
                $db->Update("question_master", $questionWeight, $table_id, $_POST['question_id']);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'option is deleted successfully';
            }else{
                $response['success'] = false;
                $response['title'] = "Not Deleted";
                $response['msg'] = 'Something went wrong!';
            }
        }
    }
    else{
        $response['success'] = true;
        $response['title'] = "Removed";
        $response['msg'] = 'option is removed successfully';
    }
    echo json_encode($response);
}