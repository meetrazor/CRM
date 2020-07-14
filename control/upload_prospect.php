<?php

ini_set('memory_limit', -1);
set_time_limit(0);
@session_start();
include_once 'header.php';
$user_id = $ses->Get("user_id");
include '../phpexcel/PHPExcel/IOFactory.php';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
$targetFolder = DOC_ROOT.'uploads/bulk_prospects/'; // Relative to the root
if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}
$upload_array_size = count($_FILES['csc_execl']['tmp_name']);
$campaignId = isset($_POST['campaign_id']) ? $_POST['campaign_id'] : "";

if($campaignId == ''){
    $upload_status = array('status' => false, 'msg' => 'Please select campaign');
    echo json_encode($upload_status);
    exit;
}

if (!empty($_FILES)) {
    $extensions = array('xls','xlsx'); // File extensions
    $response = array();
    $maxsize = 5242880;
    $upload_status = $core->UploadFile('csc_execl', $maxsize, $targetFolder, $extensions);
    $filename = $upload_status['filename'];
    unset($upload_status['filename']);
    $inputFileName = $targetFolder . $filename;
    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
    } catch(Exception $e) {
        $upload_status = array('status' => false, 'msg' => 'Could not read from file');
        echo json_encode($upload_status);
        exit;
    }

    //  Get worksheet dimensions
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
// 	$highestColumn = $sheet->getHighestColumn();

    //  Loop through each row of the worksheet in turn
    $rowData = array();
    /*
    for ($row = 2; $row <= $highestRow; $row++){
        //  Read a row of data into an array
        $row_data = $sheet->rangeToArray('A' . $row . ':' . 'J' . $row,NULL,true,true);
        $data_actual = $row_data[0];
        $data = $db->FilterParameters($data_actual);
        //$data = $db->FilterParameters($data);
        for($i=0;$i<count($data);$i++){
            $data[$i] = ucwords($data[$i]);
        }
        list($category,$first_name,$last_name,$amount,
            $email,$mobile,$address,$state,$city,$pincode) = $data;
        $prospect_data = array();

        $stateId = '';
        $categoryId = '';
        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($state));
            $categoryId = Utility::checkOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_name)='{$category}'");
            if($categoryId == 0){
                $upload_status = array('status' => false, 'msg' => 'Product name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

         if(!empty($state)){
            $state_data['state_name'] = trim(strtolower($state));
            $state_data['country_id'] = 1;
            $stateId = Utility::checkOrFetchFromTable('state', $state_data, 'state_id', "lower(state_name)='$state'");
            if($stateId == 0){
                $upload_status = array('status' => false, 'msg' => 'State name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

        if(!empty($city)){
            $city_data['city_name'] = trim(strtolower($city));
            $city_data['state_id'] = $stateId;
            $city_data['country_id'] = 1;
            $cityId = Utility::checkOrFetchFromTable('city', $city_data, 'city_id', "lower(city_name)='$city' and state_id = '{$stateId}'");
            if($cityId == 0){
                $upload_status = array('status' => false, 'msg' => 'City name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

        if(!empty($email)){
            $email_data['email'] = trim(strtolower($email));
          //  $prospect_data['email_check'] = Utility::checkOrFetchFromTable('leads', $email_data, 'lead_id', "lower(email)='$email' and lead_manager = '{$user_id}'");
            if(0 != 0){
                $upload_status = array('status' => false, 'msg' => 'Email of row '.$row.' is duplicate');
                echo json_encode($upload_status);
                exit;
            }
        }
        if(!empty($mobile)){
            $mobile_data['mobile'] = trim(strtolower($mobile));
           // $prospect_data['mobile_check'] = Utility::checkOrFetchFromTable('leads', $mobile_data, 'lead_id', "lower(mobile)='$mobile'  and lead_manager = '{$user_id}'");
            if(0 != 0){
                $upload_status = array('status' => false, 'msg' => 'Mobile Number of row '.$row.' is duplicate');
                echo json_encode($upload_status);
                exit;
            }
        }
        if(!empty($assignTo)){
            $user_data['email'] = trim(strtolower($assignTo));
            if($user_data['email'] != ''){
                $assignUserId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$user_data['email']}'");
                if($assignUserId == 0){
                    $upload_status = array('status' => false, 'msg' => 'Assign User of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }
    }
    */
    $beforeId = $db->FetchCellValue("prospect_master","prospect_id","1=1 order by prospect_id desc");
    $fileId = $db->Insert("file_master",
        array_merge($db->TimeStampAtCreate($user_id),array(
            "file_name"=>$_FILES['csc_execl']['name'],
            "type_id_before"=>$beforeId,
            "file_for"=>"prospect"),$misc->detectUserAgent())
        ,1);
    for ($row = 2; $row <= $highestRow; $row++){
        //  Read a row of data into an array
        $row_data = $sheet->rangeToArray('A' . $row . ':' . 'J' . $row,NULL,true,true);
        $data_actual = $row_data[0];
        $data = $db->FilterParameters($data_actual);
        //$data = $db->FilterParameters($data);
        for($i=0;$i<count($data);$i++){
            $data[$i] = ucwords($data[$i]);
        }
        list($category,$first_name,$last_name,$amount,$email,$mobile,$address,$state,$city,$pincode) = $data;
        $prospect_data = array();

        $stateId = '';
        $categoryId = '';
        $emails = array();
        $phoneNumber = array();
        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($state));
            $categoryId = Utility::checkOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_name)='{$category}'");
            if($categoryId == 0){
                $upload_status = array('status' => false, 'msg' => 'Product name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }
        if(!empty($state)){
            $state_data['state_name'] = trim(strtolower($state));
            $state_data['country_id'] = 1;
            $stateId = Utility::checkOrFetchFromTable('state', $state_data, 'state_id', "lower(state_name)='$state'");
            if($stateId == 0){
                $upload_status = array('status' => false, 'msg' => 'State name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

        $cityId = '';
        if(!empty($city)){
            $city_data['city_name'] = trim(strtolower($city));
            $city_data['state_id'] = $stateId;
            $city_data['country_id'] = 1;
            $cityId = Utility::checkOrFetchFromTable('city', $city_data, 'city_id', "lower(city_name)='$city' and state_id = '{$stateId}'");
            if($cityId == 0){
                $upload_status = array('status' => false, 'msg' => 'City name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }


        if(!empty($email)){
            $email_data['email'] = trim(strtolower($email));
            //$prospect_data['email_check'] = Utility::checkOrFetchFromTable('leads', $email_data, 'lead_id', "lower(email)='$email' and lead_manager = '{$user_id}'");
            $emails = explode(",",$email);
            if(0 != 0){
                $upload_status = array('status' => false, 'msg' => 'Email of row '.$row.' is duplicate');
                echo json_encode($upload_status);
                exit;
            }
        }
        if(!empty($mobile)){
            $mobile_data['mobile'] = trim(strtolower($mobile));
            $phoneNumber = explode(",",$mobile);
            //$prospect_data['mobile_check'] = Utility::checkOrFetchFromTable('leads', $mobile_data, 'lead_id', "lower(mobile)='$mobile'  and lead_manager = '{$user_id}'");
            if(0 != 0){
                $upload_status = array('status' => false, 'msg' => 'Mobile Number of row '.$row.' is duplicate');
                echo json_encode($upload_status);
                exit;
            }
        }
        $assignUserId = 0;
//        if(!empty($assignTo)){
//            $user_data['email'] = trim(strtolower($assignTo));
//            if($user_data['email'] != ''){
//                $assignUserId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$user_data['email']}'");
//                if($assignUserId == 0){
//                    $upload_status = array('status' => false, 'msg' => 'Handler of row '.$row.' is not available');
//                    echo json_encode($upload_status);
//                    exit;
//                }
//            } else {
//                $assignUserId = 0;
//            }
//        }
        if(!empty($first_name)){

            $prospect_data['first_name'] = ucwords($first_name);
            $prospect_data['last_name'] = ucwords($last_name);
            $prospect_data['address'] = $address;
            $prospect_data['state_id'] = $stateId;
            $prospect_data['city_id'] = $cityId;
            $prospect_data['pincode'] = $pincode;
            $prospect_data['amount'] = $amount;
            $prospect_data['campaign_id'] = $campaignId;
            $prospect_data['file_id'] = isset($fileId) ?  $fileId : "";
            $prospect_data['prospect_via'] = "bulk";
            //$prospect_data['category_id'] = $db->FetchCellValue("campaign_master","category_id","campaign_id = '{$campaignId}'");
            $prospect_data['category_id'] = $categoryId;

            //$client_person_cond="person_name='$person_name'";
            //  $prospectCondition = "first_name = '{$prospect_data['first_name']}' && " . "campaign_id = '{$campaignId}'";
            $prospect_data = array_merge($prospect_data,$db->TimeStampAtCreate($user_id));
            $prospectCondition =  "pm.category_id = '{$categoryId}' && " . "pm.campaign_id = '{$campaignId}'";

            $emailCheck = is_array($emails) ? implode("','",$emails)  : $emails;
          //  $prospectCondition .= " and pc.contact in ('$emailCheck')";

            $mobileCheck = is_array($phoneNumber) ? implode("','",$phoneNumber)  : $phoneNumber;
            $prospectCondition .= " and pc1.contact in ('$mobileCheck')";

            $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
            $joinTable = array(
                array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id"),
                array("left","prospect_contact as pc1","pc1.prospect_id = pm.prospect_id")
            );
            $prospectRes = $db->JoinFetch($mainTable,$joinTable,$prospectCondition,null,array(0,1),"pm.prospect_id");
            $prospectCount = $db->CountResultRows($prospectRes);
            if($prospectCount > 0) {
                $prospectRow = $db->FetchRowInAssocArray($prospectRes);
                $prospectId = $prospectRow['prospect_id'];
            } else {
                $prospectId = $db->Insert("prospect_master", $prospect_data, true);

                //$prospectId = Utility::addOrFetchFromTable('prospect_master', $prospect_data, 'prospect_id', $prospectCondition);
                if($prospectId != ''){
                    // add prospect in queue
                    $db->Insert("prospect_queue",array(
                            "prospect_id" => $prospectId,
                            "campaign_id" => $campaignId,
                            "created_at"=>DATE_TIME_DATABASE,
                            "created_by"=>$user_id,
                            "updated_at"=>DATE_TIME_DATABASE,
                            "updated_by"=>$user_id,
                            "call_via"=>"fb",
                            "file_id"=>$fileId
                        )

                    );
                    $userData = array(
                        "prospect_id" => $prospectId,
                        "type_id" => $assignUserId,
                        "user_type" => "tc",
                        "is_latest" => "1",
                        "created_at"=>DATE_TIME_DATABASE,
                        "created_by"=>$user_id
                    );
                    if($assignUserId != 0){
                        Utility::addOrFetchFromTable("prospect_users",$userData,"type_id","prospect_id = '{$prospectId}' and user_type = 'tc' and type_id = '{$assignUserId}'");
                    }


                    if(count($emails) > 0){
                        foreach($emails as $ekey => $email) {
                            $primary = ($ekey == 0) ? 1 : 0;
                            $emailData = array(
                                "prospect_id" => $prospectId,
                                "contact" => $email,
                                "contact_type" => "email",
                                "is_wrong" =>0,
                                "is_primary" =>$primary

                            );
                            $db->Insert('prospect_contact', $emailData);
                        }
                    }

                    if(count($phoneNumber) > 0){
                        foreach($phoneNumber as $pnkey => $number) {
                            $primary = ($pnkey == 0) ? 1 : 0;
                            $phoneNumberData = array(
                                "prospect_id" => $prospectId,
                                "contact" => $number,
                                "contact_type" => "phone",
                                "is_wrong" =>0,
                                "is_primary" =>$primary

                            );
                            $db->Insert('prospect_contact', $phoneNumberData);
                        }
                    }
                }
            }
        }

    }
    $afterId = $db->FetchCellValue("prospect_master","prospect_id","1=1 order by prospect_id desc");
    $db->UpdateWhere("file_master",array("type_id_after"=>$afterId),"file_id = '{$fileId}'");
    echo json_encode($upload_status);
}
?>