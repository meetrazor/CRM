<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "call_audit";
$table_id = 'call_audit_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if ('addedit' === $action) {
    if(core::LoginCheck(true)){
        $response['success'] = false;
        $response['title'] = 'Exist';
        $response['msg'] = 'Something went wrong please contact admin';
        echo json_encode($response);
        exit;
    }
    $data = $db->FilterParameters($_POST);
    $data['agentcalltype_id']=isset($data['agentcalltype_for'])?$data['agentcalltype_for']:"";
    $data['reasonid']=isset($data['reason_for'])?$data['reason_for']:"";
//    core::PrintArray($data,1);
    $validator = array(

        'rules' => array(
            'user_id' => array('required' => true,),
            'audit_date' => array('required' => true),
            'audit_time' => array('required' => true),
            'mobile' => array('required' => true)
        ),
        'messages' => array(
            'user_id' => array('required' => 'Please select user'),
            'audit_date' => array('required' => 'Please enter audit date'),
            'audit_time' => array('required' => 'Please enter audit time'),
            'mobile' => array('required' => 'Please enter mobile number')
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);
    $data['audit_date'] = $data['audit_date'] = core::DMYToYMD($data['audit_date']);
    $optionValueArray = (isset($data['option_values'])) ? $data['option_values'] : array();

    if (count($errors) > 0) {

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $id = (isset($data['call_audit_id']) && $data['call_audit_id'] != '') ? $data['call_audit_id'] : 0;
        $response = array();
        $data['audit_date'] = core::DMYToYMD($data['audit_date']);
        $exist_condition = "user_id ='{$data['user_id']}' && audit_date = '{$data['audit_date']}' && mobile = '{$data['mobile']}' ";
        if ($id > 0) {
            // update record..
            $exist_condition .= " && $table_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if ($exist == 0) {
                $flag = 0;

                if (isset($_FILES['audio_file'])) {
                    $activityUploadPath = Utility::UploadPath() . "/audio_files/";
                    if (!file_exists($activityUploadPath)) {
                        mkdir($activityUploadPath, 0777, true);
                    }
                    $filename = Core::UniqueFileName();
                    $extensions = Utility::audioExtensions();
                    $upload_status = $core->UploadFile('audio_file', MAX_UPLOAD_SIZE, $activityUploadPath, $extensions);
                    if ($upload_status['status']) {
                        $data['audio_file'] = $upload_status['filename'];
                    }else{
                        $data['audio_file'] = '';
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = $upload_status['msg']." : ".implode($extensions);
                        echo json_encode($response);
                        exit();
                    }
                }else{
                    unset($data['audio_file']);
                }

                $data = array_merge($data, $db->TimeStampAtUpdate($user_id));

                $udpate = $db->Update($table, $data, $table_id, $id);
                if($udpate){
                    $flag = 1;
                    if (count($optionValueArray) > 0) {
                        $db->DeleteWhere("call_audit_answer","call_audit_id = '{$id}'");
                        foreach ($optionValueArray as $dataArray) {
                            $insertDataArray = '';

                            // for checkbox
                            if(is_array($dataArray['option_value'])){
                                for($i = 0 ; $i < count($dataArray['option_value']); $i++){
                                    if (strpos($dataArray['option_value'][$i], '||') !== false) {
                                        $insertDataArray = explode('||', $dataArray['option_value'][$i]);
                                    }
                                    if(is_array($insertDataArray)){
                                        $insertData = array(
                                            'call_audit_id' => $id,
                                            'option_value_id' => (isset($insertDataArray[0])) ? $insertDataArray[0] : '',
                                            'question_id' => (isset($insertDataArray[1])) ? $insertDataArray[1] : '',
                                            'option_value' => (isset($insertDataArray[2])) ? $insertDataArray[2] : ''
                                        );
                                    }
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }
                            }
                            else{
                                if (strpos($dataArray['option_value'], '||') !== false) {
                                    $insertDataArray = explode('||', $dataArray['option_value']);
                                }
                                // for radio, checkbox, select
                                if(is_array($insertDataArray)){
                                    $insertData = array(
                                        'call_audit_id' => $id,
                                        'option_value_id' => (isset($insertDataArray[0])) ? $insertDataArray[0] : '',
                                        'question_id' => (isset($insertDataArray[1])) ? $insertDataArray[1] : '',
                                        'option_value' => (isset($insertDataArray[2])) ? $insertDataArray[2] : ''
                                    );
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }else{
                                    $insertData = array(
                                        'call_audit_id' => $id,
                                        'question_id' => $dataArray['question_id'],
                                        'option_value' => $dataArray['option_value']
                                    );
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }
                            }
                        }
                    }
                    else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "You have not given answer of any question!";
                    }

                }
                if ($flag == 1) {
                    $response['success'] = true;
                    $response['act'] = 'added';
                    $response['title'] = 'Successful';
                    $response['msg'] = 'Record updated successfully!!';
                } else {
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Something went wrong!";
                }
            } else {
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Call audit already exist for entered mobile number of selected date";
            }
        } else {
            // Adding a new record..
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if ($exist == 0) {

                if (isset($_FILES['audio_file'])) {
                    $activityUploadPath = Utility::UploadPath() . "/audio_files/";
                    if (!file_exists($activityUploadPath)) {
                        mkdir($activityUploadPath, 0777, true);
                    }
                    $filename = Core::UniqueFileName();
                    $extensions = Utility::audioExtensions();
                    $upload_status = $core->UploadFile('audio_file', MAX_UPLOAD_SIZE, $activityUploadPath,$extensions);
                    if ($upload_status['status']) {
                        $data['audio_file'] = $upload_status['filename'];
                    }else{
                        $data['audio_file'] = '';
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = $upload_status['msg']." : ".implode($extensions);
                        echo json_encode($response);
                        exit();
                    }
                }

                $flag = 0;
                $data = array_merge($data, $db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data, 1);
                if ($insertId != '') {
                    $flag = 1;
                    if (count($optionValueArray) > 0) {
                        foreach ($optionValueArray as $dataArray) {
                            $insertDataArray = '';
                            // for checkbox
                            if(is_array($dataArray['option_value'])){
                                for($i = 0 ; $i < count($dataArray['option_value']); $i++){
                                    if (strpos($dataArray['option_value'][$i], '||') !== false) {
                                        $insertDataArray = explode('||', $dataArray['option_value'][$i]);
                                    }
                                    if(is_array($insertDataArray)){
                                        $insertData = array(
                                            'call_audit_id' => $insertId,
                                            'option_value_id' => (isset($insertDataArray[0])) ? $insertDataArray[0] : '',
                                            'question_id' => (isset($insertDataArray[1])) ? $insertDataArray[1] : '',
                                            'option_value' => (isset($insertDataArray[2])) ? $insertDataArray[2] : ''
                                        );
                                    }
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }
                            }
                            else{
                                if (strpos($dataArray['option_value'], '||') !== false) {
                                    $insertDataArray = explode('||', $dataArray['option_value']);
                                }
                                // for radio, select
                                if(is_array($insertDataArray)){
                                    $insertData = array(
                                        'call_audit_id' => $insertId,
                                        'option_value_id' => (isset($insertDataArray[0])) ? $insertDataArray[0] : '',
                                        'question_id' => (isset($insertDataArray[1])) ? $insertDataArray[1] : '',
                                        'option_value' => (isset($insertDataArray[2])) ? $insertDataArray[2] : ''
                                    );
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }else{
                                    $insertData = array(
                                        'call_audit_id' => $insertId,
                                        'question_id' => $dataArray['question_id'],
                                        'option_value' => $dataArray['option_value']
                                    );
                                    $flag = ($db->Insert("call_audit_answer", $insertData, 1)) ? 1 : 0;
                                }
                            }
                        }
                    }else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "You have not given answer of any question!";
                    }
                }
                if ($flag == 1) {
                    $response['success'] = true;
                    $response['act'] = 'added';
                    $response['title'] = 'Successful';
                    $response['msg'] = 'Record added successfully!!';
                } else {
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Something went wrong!";
                }
            } else {
                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Call audit already exist for entered mobile number of selected date";
            }
        }
        echo json_encode($response);
    }
}
//elseif($action == 'check_audit'){
//    $data = $db->FilterParameters($_POST);
//    $response = array();
//    if(isset($data['user_id']) && $data['user_id'] != '' && isset($data['audit_date'])
//        && $data['audit_date'] != '' && isset($data['mobile']) && $data['mobile'] != ''){
//        $data['audit_date'] = core::DMYToYMD($data['audit_date']);
//        $exist_condition = "user_id ='{$data['user_id']}' && audit_date = '{$data['audit_date']}' && mobile = '{$data['mobile']}' ";
//        $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
//        if($exist > 0 ){
//            $data['audit_date'] = core::YMDToDMY($data['audit_date']);
//            $response['success'] = true;
//            $response['msg'] = "Call audit is already done for mobile number: {$data['mobile']} on date: {$data['audit_date']} ";
//        }
//    }
//    echo json_encode($response);
//}
elseif ($action == 'check_audit') {
    $flag = "true";
    $data = $db->FilterParameters($_POST);
//    Core::PrintArray($data,1);
    if (isset($data['user_id']) && $data['user_id'] != '' && isset($data['audit_date'])
        && $data['audit_date'] != '' && isset($data['mobile']) && $data['mobile'] != '') {
        $data['audit_date'] = core::DMYToYMD($data['audit_date']);
        $exist_condition = "user_id ='{$data['user_id']}' && audit_date = '{$data['audit_date']}' && mobile = '{$data['mobile']}' ";
        $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0, 1));
        if ($exist > 0) {
            $flag = "false";
        }
    }
    echo $flag;
}
include_once 'footer.php';
