<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "vendor_master";
$table_id = 'vendor_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $validator = array(

        'rules' => array(
            'email' => array('required' => true, 'email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
           // 'state_id' => array('required' => true),
         //   'city_id' => array('required' => true),
            'vendor_name' => array('required' => true),
        ),
        'messages' => array(
            'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'vendor_name' => array('required' => "Please enter vendor name"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $data['last_name'] = isset($data['last_name']) ? ucwords($data['last_name']) : "";

        $id = (isset($data['vendor_id']) && $data['vendor_id'] != '') ? $data['vendor_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        $exist_condition = "email='{$data['email']}'";

        if($id > 0){

            $exist_condition .= " && vendor_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                if(isset($data['id-disable-check']) && $data['id-disable-check'] == 1){
                    unset($data['email']);
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
                $response['msg'] = "Vendor with the email: {$data['email']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){

                $password = $core->GenRandomStr(6);
                $data['pass_str'] = md5($password);
                $data['is_new'] = 1;
                $data['vendor_code'] = Utility::GenerateNo($table,"vendor");
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);

                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Vendor with the email: {$data['email']} already exist";
            }
        }
        echo json_encode($response);
    }
}
include_once 'footer.php';
