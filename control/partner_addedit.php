<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "partner_master";
$table_id = 'partner_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $validator = array(

        'rules' => array(
            'email' => array('required' => true, 'email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
            'state_id' => array('required' => true),
            'city_id' => array('required' => true),
            'first_name' => array('required' => true),
            'last_name' => array('required' => true),
        ),
        'messages' => array(
            'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'first_name' => array('required' => "Please enter first name"),
            'last_name' => array('required' => "Please enter last name"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $data['last_name'] = isset($data['last_name']) ? ucwords($data['last_name']) : "";
        $data['partner_type'] = isset($data['partner_type']) ? implode(",",$data['partner_type']) : "";

        $id = (isset($data['partner_id']) && $data['partner_id'] != '') ? $data['partner_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        $exist_condition = "email='{$data['email']}'";

        if($id > 0){

            $exist_condition .= " && partner_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                if(isset($data['id-disable-check']) && $data['id-disable-check'] == 1){
                    unset($data['email']);
                }

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);
                $education = (isset($data['education']) && $data['education'] != '') ? $data['education'] : array();
                $category = isset($data['category_ids']) ? $data['category_ids'] : array();
                $subLocality = isset($data['sub_locality_ids']) ? $data['sub_locality_ids'] : array();
                if(count($category) > 0){
                    $db->DeleteWhere("partner_category","partner_id = '$id'");
                    foreach($category as $categoryId) {
                        $categoryData['partner_id'] = $id;
                        $categoryData['category_id'] = $categoryId;
                        $db->Insert('partner_category', $categoryData);
                    }
                }
                if(count($education) > 0){
                    foreach($education as $key => $eduData) {

                        $educationData['partner_id'] = $id;
                        $educationData['education_id'] = $eduData['education_id'];
                        $partner_education_id = Utility::addOrFetchFromTable("partner_education",$educationData,"partner_education_id","education_id = '{$eduData['education_id']}' and partner_id = '{$id}'");
                        //$partner_education_id = $db->Insert('partner_education', $educationData,1);

                        if(isset($_FILES['education'.$key.'']) && count($_FILES['education'.$key.'']['tmp_name'])){
                            $uploadPath = Utility::UploadPath() ."/partner_education/";
                            if (!file_exists($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }
                            $cnt = count($_FILES['education'.$key.'']['tmp_name']);
                            for($i=0 ; $i<$cnt ; $i++){

                                $filename = Core::UniqueFileName();

                                $extensions = Utility::docExtensions();

                                $upload_status = $core->UploadMultipleFile('education'.$key.'', MAX_UPLOAD_SIZE, $uploadPath,$i, $extensions, $filename);
                                if($upload_status['status']){
                                    $uploadDetails = array('partner_education_id'=>$partner_education_id,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                                    $db->Insert("partner_education_document", $uploadDetails);
                                }
                            }
                        }
                    }
                }
                if(count($subLocality) > 0){
                    $db->DeleteWhere("partner_sub_locality","partner_id = '$id'");
                    foreach($subLocality as $subLocalityId) {
                        $subLocalityData['partner_id'] = $id;
                        $subLocalityData['sub_locality_id'] = $subLocalityId;
                        $db->Insert('partner_sub_locality', $subLocalityData);
                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Partner with the email: {$data['email']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){

                $password = $core->GenRandomStr(6);
                $data['pass_str'] = md5($password);
                $data['is_new'] = 1;
                $data['partner_code'] = Utility::GenerateNo($table,"partner");
                $data['pass'] = core::GenRandomStr(6);
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);

                $education = (isset($data['education']) && $data['education'] != '') ? $data['education'] : array();
                $category = isset($data['category_ids']) ? $data['category_ids'] : array();
                $subLocality = isset($data['sub_locality_ids']) ? $data['sub_locality_ids'] : array();
                if(count($category) > 0){
                    foreach($category as $categoryId) {
                        $categoryData['partner_id'] = $insertId;
                        $categoryData['category_id'] = $categoryId;
                        $db->Insert('partner_category', $categoryData);
                    }
                }
                if(count($education) > 0){
                    foreach($education as $key => $eduData) {

                        $educationData['partner_id'] = $insertId;
                        $educationData['education_id'] = $eduData['education_id'];
                        $partner_education_id = Utility::addOrFetchFromTable("partner_education",$educationData,"partner_education_id","education_id = '{$eduData['education_id']}' and partner_id = '{$insertId}'");

                        if(isset($_FILES['education'.$key.'']) && count($_FILES['education'.$key.'']['tmp_name'])){
                            $uploadPath = Utility::UploadPath() ."/partner_education/";
                            if (!file_exists($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }
                            $cnt = count($_FILES['education'.$key.'']['tmp_name']);
                            for($i=0 ; $i<$cnt ; $i++){

                                $filename = Core::UniqueFileName();

                                $extensions = Utility::docExtensions();

                                $upload_status = $core->UploadMultipleFile('education'.$key.'', MAX_UPLOAD_SIZE, $uploadPath,$i, $extensions, $filename);
                                if($upload_status['status']){
                                    $uploadDetails = array('partner_education_id'=>$partner_education_id,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                                    $db->Insert("partner_education_document", $uploadDetails);
                                }
                            }
                        }
                    }
                }
                if(count($subLocality) > 0){
                    foreach($subLocality as $subLocalityId) {
                        $subLocalityData['partner_id'] = $insertId;
                        $subLocalityData['sub_locality_id'] = $subLocalityId;
                        $db->Insert('partner_sub_locality', $subLocalityData);
                    }
                }
                //$partnerInfo = Utility::registraionEmailToPartner($insertId,$password);
                //$status = sMail(array($data['first_name']." ".$data['last_name'] => $data['email']),"CW CRM", "Welcome to CW CRM", $partnerInfo, "CRM Partner", USER_NAME);
                //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$insertId,PARTNER_REGISTRATION_MAIL,$partnerInfo,$data['email']);
                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "Partner with the email: {$data['email']} already exist";
            }
        }
        echo json_encode($response);
    }
}elseif($action == 'deleteimage'){
    $docId = $_POST['id'];
    $old_image = $db->FetchCellValue("partner_education_document","filename","partner_education_document_id = {$docId}");
    $uploadpath = PARTNER_EDUCATION_IMAGE_PATH_ABS;
    $ref_name_jpg = $uploadpath .$old_image;
    if(file_exists($ref_name_jpg)){
        @unlink($ref_name_jpg);
        $db->DeleteWhere("partner_education_document","partner_education_document_id = {$docId}");
    }
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' record (s) deleted successfully';
    echo json_encode($response);
}
include_once 'footer.php';
