<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "customer_master";
$table_id = 'customer_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $validator = array(

        'rules' => array(
            //'email' => array('required' => true, 'email' => true),
           // 'email' => array( 'email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
            //'state_id' => array('required' => true),
            //'city_id' => array('required' => true),
            'customer_name' => array('required' => true),
        ),
        'messages' => array(
            'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'customer_name' => array('required' => "Please enter customer name"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $data['last_name'] = isset($data['last_name']) ? ucwords($data['last_name']) : "";

        $id = (isset($data['customer_id']) && $data['customer_id'] != '') ? $data['customer_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        //$exist_condition = "email='{$data['email']}'";
        $exist_condition = "1 != 1";

        if($id > 0){

            $exist_condition .= " && customer_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                if(isset($data['id-disable-check']) && $data['id-disable-check'] == 1){
                    unset($data['email']);
                }

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);
                $education = isset($data['education_ids']) ? $data['education_ids'] : array();
                $category = isset($data['category_ids']) ? $data['category_ids'] : array();
                $subLocality = isset($data['sub_locality_ids']) ? $data['sub_locality_ids'] : array();
                if(count($category) > 0){
                    $db->DeleteWhere("customer_category","customer_id = '$id'");
                    foreach($category as $categoryId) {
                        $categoryData['customer_id'] = $id;
                        $categoryData['category_id'] = $categoryId;
                        $db->Insert('customer_category', $categoryData);
                    }
                }
                if(count($education) > 0){
                    $db->DeleteWhere("customer_education","customer_id = '$id'");
                    foreach($education as $educationId) {
                        $educationData['customer_id'] = $id;
                        $educationData['education_id'] = $educationId;
                        $db->Insert('customer_education', $educationData);
                    }
                }
                if(count($subLocality) > 0){
                    $db->DeleteWhere("customer_sub_locality","customer_id = '$id'");
                    foreach($subLocality as $subLocalityId) {
                        $subLocalityData['customer_id'] = $id;
                        $subLocalityData['sub_locality_id'] = $subLocalityId;
                        $db->Insert('customer_sub_locality', $subLocalityData);
                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Customer with the email: {$data['email']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){

                $password = $core->GenRandomStr(6);
                $data['pass_str'] = md5($password);
                $data['is_new'] = 1;

                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);
                $subLocality = isset($data['sub_locality_ids']) ? $data['sub_locality_ids'] : array();

                if(count($subLocality) > 0){
                    foreach($subLocality as $subLocalityId) {
                        $subLocalityData['customer_id'] = $insertId;
                        $subLocalityData['sub_locality_id'] = $subLocalityId;
                        $db->Insert('customer_sub_locality', $subLocalityData);
                    }
                }
                //$user_info = Utility::registraionEmailToUser($insertId,$password);
                //$status = sMail(array($data['first_name']." ".$data['last_name'] => $data['email']),"Alliance Customer", "Welcome to Alliance Customer", $user_info, "Alliance Customer", "updates@sachinenterprises.co.in", $filepath = '');
                //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$insertId,USER_REGISTRATION_MAIL,$user_info,$data['email']);
                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Customer with the email: {$data['email']} already exist";
            }
        }
        echo json_encode($response);
    }
}
include_once 'footer.php';
