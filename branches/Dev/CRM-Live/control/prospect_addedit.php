<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "prospect_master";
$table_id = 'prospect_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $emails = (isset($data['email']) && $data['email'] != '') ? $data['email'] : array();
    $emailWrong = (isset($data['email_wrong']) && $data['email_wrong'] != '') ? $data['email_wrong'] : array();
    $emailPrimary = (isset($data['email_primary']) && $data['email_primary'] != '') ? $data['email_primary'] : array();

    $phoneNumber = (isset($data['phone_number']) && $data['phone_number'] != '') ? $data['phone_number'] : array();
    $phoneNumberWrong = (isset($data['phone_number_wrong']) && $data['phone_number_wrong'] != '') ? $data['phone_number_wrong'] : array();
    $phoneNumberPrimary = (isset($data['phone_number_primary']) && $data['phone_number_primary'] != '') ? $data['phone_number_primary'] : array();

    if(count($emails) != count(array_unique($emails))){
        $response['success'] = false;
        $response['title'] = 'Incorrect';
        $response['msg'] = "Duplicate email address";
        echo json_encode($response);
        exit;
    }

    if(count($phoneNumber) != count(array_unique($phoneNumber))){
        $response['success'] = false;
        $response['title'] = 'Incorrect';
        $response['msg'] = "Duplicate phone number";
        echo json_encode($response);
        exit;
    }

    foreach($phoneNumber as $number){
        $res = Utility::serverSideValidation($number,"number");
        if($res != '' && $res != 1) {
            $response['success'] = false;
            $response['title'] = 'Incorrect';
            $response['msg'] = "Phone Number : {$number} incorrect";
            echo json_encode($response);
            exit;
        }
    }

    foreach($emails as $email){
        $res = Utility::serverSideValidation($email,"email");
        if($res != '' && $res != 1) {
            $response['success'] = false;
            $response['title'] = 'Incorrect';
            $response['msg'] = "Email Address : {$number} incorrect";
            echo json_encode($response);
            exit;
        }
    }


    $validator = array(

        'rules' => array(
            'first_name' => array('required' => true),
            'campaign_id' => array('required' => true),
            'state_id' => array('required' => true),
            'city_id' => array('required' => true),
         //   'user_id' => array('required' => true),
        ),
        'messages' => array(
            'first_name' => array('required' => "Please enter prospect name"),
            'campaign_id' => array('required' => "Please select campaign"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'user_id' => array('required' => "Please select telecaller"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $id = (isset($data['prospect_id']) && $data['prospect_id'] != '') ? $data['prospect_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        $exist_condition = "first_name='{$data['first_name']}' && campaign_id != '{$data['campaign_id']}'";

        if($id > 0){

            $exist_condition .= " && prospect_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                if(isset($data['id-disable-check']) && $data['id-disable-check'] == 1){
                    unset($data['email']);
                }

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);

                if(count($emails) > 0){
                    $db->DeleteWhere("prospect_contact","$table_id = '$id' and contact_type = 'email'");
                    foreach($emails as $ekey => $email) {
                        $emailData = array(
                            "prospect_id" => $id,
                            "contact" => $email,
                            "contact_type" => "email",
                            "is_wrong" => array_key_exists($ekey,$emailWrong) ? 1 : 0,
                            "is_primary" => in_array($ekey,$emailPrimary) ? 1 : 0,

                        );
                        $db->Insert('prospect_contact', $emailData);
                    }
                }

                if(count($phoneNumber) > 0){
                    $db->DeleteWhere("prospect_contact","$table_id = '$id' and contact_type = 'phone'");
                    foreach($phoneNumber as $pnkey => $number) {
                        $phoneNumberData = array(
                            "prospect_id" => $id,
                            "contact" => $number,
                            "contact_type" => "phone",
                            "is_wrong" => array_key_exists($pnkey,$phoneNumberWrong) ? 1 : 0,
                            "is_primary" => in_array($pnkey,$phoneNumberPrimary) ?  1 : 0,

                        );
                        $db->Insert('prospect_contact', $phoneNumberData);
                    }
                }

                if($id != ''){
//                    if($data['last_user_id'] != $data['user_id']){
//                        $db->UpdateWhere("prospect_users",array("is_latest"=>0),"prospect_id = '{$id}' and type_id = '{$data['last_user_id']}' and user_type = 'tc'");
//                        $db->Insert("prospect_users",array(
//                            "prospect_id" => $id,
//                            "type_id" => $data['user_id'],
//                            "user_type" => "tc",
//                            "is_latest" => "1",
//                            "created_at"=>DATE_TIME_DATABASE,
//                            "created_by"=>$user_id,
//                        ));
//                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Prospect with the first_name: {$data['first_name']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){

                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);

                if($insertId != ''){
//                    $db->Insert("prospect_users",array(
//                        "prospect_id" => $insertId,
//                        "type_id" => $data['user_id'],
//                        "user_type" => "tc",
//                        "is_latest" => "1",
//                        "created_at"=>DATE_TIME_DATABASE,
//                        "created_by"=>$user_id
//                    ));


                    if(count($emails) > 0){
                        foreach($emails as $ekey => $email) {
                            $emailData = array(
                                "prospect_id" => $insertId,
                                "contact" => $email,
                                "contact_type" => "email",
                                "is_wrong" => array_key_exists($ekey,$emailWrong) ? '1' : '0',
                                "is_primary" => in_array($ekey,$emailPrimary) ? '1' : '0',

                            );
                            $db->Insert('prospect_contact', $emailData);
                        }
                    }

                    if(count($phoneNumber) > 0){
                        foreach($phoneNumber as $pnkey => $number) {
                            $phoneNumberData = array(
                                "prospect_id" => $insertId,
                                "contact" => $number,
                                "contact_type" => "phone",
                                "is_wrong" => array_key_exists($pnkey,$phoneNumberWrong) ? '1' : '0',
                                "is_primary" => in_array($pnkey,$phoneNumberPrimary) ? '1' : '0',

                            );
                            $db->Insert('prospect_contact', $phoneNumberData);
                        }
                    }
                }

                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Prospect with the prospect_name: {$data['prospect_name']} already exist";
            }
        }
        echo json_encode($response);
    }
}
include_once 'footer.php';
