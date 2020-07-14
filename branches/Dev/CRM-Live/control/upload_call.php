<?php

ini_set('memory_limit', -1);
set_time_limit(0);
@session_start();
include_once 'header.php';
$user_id = $ses->Get("user_id");
include '../phpexcel/PHPExcel/IOFactory.php';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
$targetFolder = DOC_ROOT.'uploads/bilk_call/'; // Relative to the root
if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}
$upload_array_size = count($_FILES['csc_execl']['tmp_name']);
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

    $beforeId = $db->FetchCellValue("activity_master","activity_id","1=1 order by activity_id desc");
    $fileId = $db->Insert("file_master",
        array_merge($db->TimeStampAtCreate($user_id),array(
            "file_name"=>$_FILES['csc_execl']['name'],
            "type_id_before"=>$beforeId,
            "file_for"=>"activity"),$misc->detectUserAgent())
        ,1);

    for ($row = 2; $row <= $highestRow; $row++){
        //  Read a row of data into an array
        $row_data = $sheet->rangeToArray('A' . $row . ':' . 'Q' . $row,NULL,true,true);
        $data_actual = $row_data[0];
        $data = $db->FilterParameters($data_actual);
        //$data = $db->FilterParameters($data);
        for($i=0;$i<count($data);$i++){
            $data[$i] = ucwords($data[$i]);
        }
        list($srNo,$date,$name,$category,$amount,$city,$userType,$email,$mobile,$disposition,$comment,$actAmount,
            ,$meetingDate,$campaign,$telecaller,$assignTo) = $data;
        $prospect_data = array();

        $stateId = '';
        $categoryId = '';
        $assignUserId = 0;
        $date  = date('Y-m-d',strtotime($date));


        if($name == ''){
            $upload_status = array('status' => false, 'msg' => 'name of row '.$row.' is empty');
            echo json_encode($upload_status);
            exit;
        }

        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($category));
            $categoryId = Utility::checkOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_code)='{$category}'");
            if($categoryId == 0){
                $upload_status = array('status' => false, 'msg' => 'Product name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }



        if(!empty($city)){
            $city_data['city_name'] = trim(strtolower($city));
            $cityId = Utility::checkOrFetchFromTable('city', $city_data, 'city_id', "lower(city_name)='$city'");
            if($cityId == 0){
                $upload_status = array('status' => false, 'msg' => 'City name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }else {
                $bdCityId = Utility::checkOrFetchFromTable('user_city', $city_data, 'city_id', "city_id = '{$cityId}'");
                if($bdCityId == 0){
                    $upload_status = array('status' => false, 'msg' => 'BD for City '.$city.' of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }

        if(!empty($campaign)){
            $campaign_data['campaign_name'] = trim(strtolower($campaign));
            $campaignId = Utility::checkOrFetchFromTable('campaign_master', $campaign_data, 'campaign_id', "lower(campaign_name)='$campaign' ");
            if($campaignId == 0){
                $upload_status = array('status' => false, 'msg' => 'campaign name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

        if(!empty($disposition)){
            $disposition_data['disposition_name'] = trim(strtolower($disposition));
            $dispositionId = Utility::checkOrFetchFromTable('disposition_master', $disposition_data, 'disposition_id', "lower(disposition_name)='$disposition'");

            if($dispositionId == 0){
                $upload_status = array('status' => false, 'msg' => 'disposition name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }
        $userTypeArray = array("businessman","salaried");
        if(in_array($userType,$userTypeArray)) {
            $upload_status = array('status' => false, 'msg' => 'User Type  of row '.$row.' is not available');
            echo json_encode($upload_status);
            exit;
        }




        if($mobile == '' || $mobile == 0){
            $upload_status = array('status' => false, 'msg' => 'mobile of row '.$row.' is empty');
            echo json_encode($upload_status);
            exit;
        }

        if(!empty($telecaller)){
            $user_data['email'] = trim(strtolower($telecaller));
            if($user_data['email'] != ''){
                $telecallerId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$telecaller}'");
                if($telecallerId == 0){
                    $upload_status = array('status' => false, 'msg' => 'Telecaller of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }

        if(!empty($assignTo)){
            $user_data['email'] = trim(strtolower($assignTo));
            if($user_data['email'] != ''){
                $assignUserId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$user_data['email']}' and user_type = ".UT_BD."");
                if($assignUserId == 0){
                    $upload_status = array('status' => false, 'msg' => 'Assign User of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }
        if($dispositionId != 0) {
            $dispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$dispositionId}'");
            if($dispositionData['is_meeting'] == 1 && $assignUserId ==0){
                $upload_status = array('status' => false, 'msg' => 'Assign User for disposition '.$disposition.' of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

    }


    for ($row = 2; $row <= $highestRow; $row++){
        //  Read a row of data into an array
        $row_data = $sheet->rangeToArray('A' . $row . ':' . 'Q' . $row,NULL,true,true);
        $data_actual = $row_data[0];
        $data = $db->FilterParameters($data_actual);
        //$data = $db->FilterParameters($data);
        for($i=0;$i<count($data);$i++){
            $data[$i] = ucwords($data[$i]);
        }
        list($srNo,$date,$name,$category,$amount,$city,$userType,$email,$mobile,$disposition,$comment,$actAmount,
            ,$meetingDate,$campaign,$telecaller,$assignTo) = $data;
        $prospect_data = array();


        $stateId = '';
        $cityId = '';
        $categoryId = '';
        $date  = date('Y-m-d',strtotime($date));

        if($name == ''){
            $upload_status = array('status' => false, 'msg' => 'name of row '.$row.' is empty');
            echo json_encode($upload_status);
            exit;
        }


        if(!empty($category)){
            $category_data['category_name'] = trim(strtolower($category));
            $categoryId = Utility::checkOrFetchFromTable('category_master', $category_data, 'category_id', "lower(category_code)='{$category}'");
            if($categoryId == 0){
                $upload_status = array('status' => false, 'msg' => 'Product name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }



        if(!empty($city)){
            $city_data['city_name'] = trim(strtolower($city));
            $cityId = Utility::checkOrFetchFromTable('city', $city_data, 'city_id', "lower(city_name)='$city'");
            if($cityId == 0){
                $upload_status = array('status' => false, 'msg' => 'City name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            } else {
                $bdCityId = Utility::checkOrFetchFromTable('user_city', $city_data, 'city_id', "city_id = '{$cityId}'");
                if($bdCityId == 0){
                    $upload_status = array('status' => false, 'msg' => 'BD for City '.$city.' of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }



        if(!empty($campaign)){
            $campaign_data['campaign_name'] = trim(strtolower($campaign));
            $campaignId = Utility::checkOrFetchFromTable('campaign_master', $campaign_data, 'campaign_id', "lower(campaign_name)='$campaign' ");
            if($campaignId == 0){
                $upload_status = array('status' => false, 'msg' => 'campaign name of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }

        if(!empty($disposition)){
            $disposition_data['disposition_name'] = trim(strtolower($disposition));
            $dispositionId = Utility::checkOrFetchFromTable('disposition_master', $disposition_data, 'disposition_id', "lower(disposition_name)='$disposition' ");
            if($dispositionId == 0){
                $upload_status = array('status' => false, 'msg' => 'disposition of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            }
        }
        $userTypeArray = array("businessman","salaried");
        if(in_array($userType,$userTypeArray)) {
            $upload_status = array('status' => false, 'msg' => 'User Type  of row '.$row.' is not available');
            echo json_encode($upload_status);
            exit;
        }


        if($mobile == '' || $mobile == 0){
            $upload_status = array('status' => false, 'msg' => 'mobile of row '.$row.' is empty');
            echo json_encode($upload_status);
            exit;
        }

        if(!empty($telecaller)){
            $user_data['email'] = trim(strtolower($telecaller));
            if($user_data['email'] != ''){
                $telecallerId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$telecaller}'");
                if($telecallerId == 0){
                    $upload_status = array('status' => false, 'msg' => 'Telecaller of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }
        $assignUserId = 0;
        if(!empty($assignTo)){
            $user_data['email'] = trim(strtolower($assignTo));
            if($user_data['email'] != ''){
                $assignUserId = Utility::checkOrFetchFromTable('admin_user', $user_data, 'user_id', "lower(email)='{$user_data['email']}' and user_type = ".UT_BD."");
                if($assignUserId == 0){
                    $upload_status = array('status' => false, 'msg' => 'Assign User of row '.$row.' is not available');
                    echo json_encode($upload_status);
                    exit;
                }
            }
        }

        if($dispositionId != 0) {
            $dispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$dispositionId}'");
            if($dispositionData['is_meeting'] == 1 && $assignUserId ==0){
                $upload_status = array('status' => false, 'msg' => 'Assign User for disposition '.$disposition.' of row '.$row.' is not available');
                echo json_encode($upload_status);
                exit;
            } elseif($assignUserId !=0 && $dispositionData['is_meeting'] != 1){
                $upload_status = array('status' => false, 'msg' => 'Assign User for disposition '.$disposition.' of row '.$row.' is valid');
                echo json_encode($upload_status);
                exit;
            }
        }

        if(!empty($name)){

            $prospect_data['first_name'] = ucwords($name);
            $prospect_data['last_name'] = '';
            $prospect_data['address'] = '';
            $prospect_data['state_id'] = '';
            $prospect_data['city_id'] = $cityId;
            $prospect_data['pincode'] = '';
            $prospect_data['is_done'] = 1;
            $prospect_data['amount'] = $amount;
            $prospect_data['actual_amount'] = $amount;
            $prospect_data['campaign_id'] = $campaignId;
            $prospect_data['category_id'] = $categoryId;
            $prospect_data['employment_type'] = $userType;
            $prospect_data['prospect_via'] = "bulk call";
            $prospect_data['file_id'] = $fileId;

            //$client_person_cond="person_name='$person_name'";
            $prospectCondition =  "pm.category_id = '{$categoryId}' && " . "pm.campaign_id = '{$campaignId}'";

            $emailCheck = is_array($email) ? implode("','",$email)  : ($email != "") ? $email : -1;
            //$prospectCondition .= " and pc.contact in ('$emailCheck')";

            $mobileCheck = is_array($mobile) ? implode("','",$mobile)  : ($mobile != "") ? $mobile : -1;
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
                $prospect_data = array_merge($prospect_data,$db->TimeStampAtCreate($user_id));
                $prospectId = $db->Insert('prospect_master', $prospect_data, 1);
                if($prospectId != ''){
                    // add prospect in queue
                    if((isset($dispositionData['is_close']) && $dispositionData['is_close'] == 1)){
                        $db->UpdateWhere("prospect_queue",array("is_done"=>1),"prospect_id = '{$prospectId}'");
                    }
                    $prospectQueueId = Utility::addOrFetchFromTable("prospect_queue",array(
                            "prospect_id" => $prospectId,
                            "campaign_id" => $campaignId,
                            "created_at"=>$date,
                            "created_by"=>$telecallerId,
                            "updated_at"=>$date,
                            "updated_by"=>$telecallerId,
                            "is_done"=> (isset($dispositionData['is_close']) && $dispositionData['is_close'] == 1) ? 1 : 0,
                            "date_queued"=>$date,
                            "call_via"=>"bulk call",
                            "file_id"=>$fileId,
                        ),'prospect_queue_id',"prospect_id = '{$prospectId}' and date_format('created_at','%Y-%m-%d') = '{$date}'"

                    );
                    $userData = array(
                        "prospect_id" => $prospectId,
                        "type_id" => $assignUserId,
                        "user_type" => "tc",
                        "is_latest" => "1",
                        "created_at"=>DATE_TIME_DATABASE,
                        "created_by"=>$telecallerId
                    );
                    if($assignUserId != 0){
                        Utility::addOrFetchFromTable("prospect_users",$userData,"type_id","prospect_id = '{$prospectId}' and user_type = 'tc' and type_id = '{$assignUserId}'");
                    }


                    if(!empty($email)){
                        $emailData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $email,
                            "contact_type" => "email",
                            "is_wrong" =>0,
                            "is_primary" =>1

                        );
                        Utility::addOrFetchFromTable('prospect_contact', $emailData,"prospect_id","prospect_id = '{$prospectId}'");
                    }

                    if(!empty($mobile)){
                        $phoneNumberData = array(
                            "prospect_id" => $prospectId,
                            "contact" => $mobile,
                            "contact_type" => "phone",
                            "is_wrong" =>0,
                            "is_primary" =>1

                        );
                        Utility::addOrFetchFromTable('prospect_contact', $phoneNumberData,"prospect_id","prospect_id = '{$prospectId}'");
                    }
                     // add activity
                    $activityData = array(
                        "activity_type" => "call",
                        "disposition_id" => $dispositionId,
                        "start_date_time" => $date,
                        "follow_up_date_time" => (isset($dispositionData['is_callback']) && $dispositionData['is_callback'] == 1) ? $date : "",
                        "end_date_time" => $date,
                        "type_id" => $prospectId,
                        "source_type" => "prospect",
                        "is_latest" => 1,
                        "created_at"=>$date,
                        "created_by"=>$telecallerId,
                        "updated_at"=>$date,
                        "updated_by"=>$telecallerId,
                        "call_type"=>"current",
                        "remarks"=>$comment,
                        "activity_on"=>"bd",
                        "activity_via"=>"bulk call",
                        "file_id"=>$fileId,
                        "prospect_queue_id"=>$prospectQueueId,
                    );
                    $db->UpdateWhere("activity_master",array("is_latest"=>0),"activity_type = 'call' and type_id = '{$prospectId}' and source_type = 'prospect'");
                    $activityId = Utility::addOrFetchFromTable('activity_master',$activityData,"activity_id","type_id = '{$prospectId}' and  disposition_id = '{$dispositionId}' AND source_type = 'prospect'");

                    // add customer

                    $customerId = Utility::addOrFetchFromTable("customer_master",
                        array_merge(array(
                            "mobile_no"=>$mobile,
                            "email"=>$email,
                            "customer_name"=>$name,
                            "customer_from" => "bulk call",
                            "pincode" => '',
                            "state_id" => $stateId,
                            "city_id" => $cityId,
                            "address" => '',
                            "file_id" => $fileId,
                        ),$db->TimeStampAtCreate($telecallerId)),"customer_id","mobile_no in ('$mobile')");

                    // create Lead
                    if($assignUserId != 0) {
                        $cwLeadData = array(
                            "lead_name" => $name,
                            "mobile_no" => $mobile,
                            "email" => $email,
                            "address" => '',
                            "pincode" => '',
                            "state_id" => $stateId,
                            "city_id" => '',
                            "amount" => $amount,
                            "actual_amount" => $amount,
                            "customer_id" => $customerId,
                            "status_id" => $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1"),
                            "category_id" => $categoryId,
                            "remarks" => isset($comment) ? $comment : "",
                            "bd_id" => ($assignUserId != 0) ? $assignUserId : "",
                            "prospect_id" => $prospectId,
                            "lead_from"=>"bulk call",
                            "file_id"=>$fileId,
                            "activity_id"=>$activityId,
                        );

                        $cwLeadData = array_merge($cwLeadData,$db->TimeStampAtCreate($telecallerId));
                        $categoryCode = $db->FetchCellValue("category_master","category_code","category_id = '{$categoryId}'");
                        $tableId = $db->GetNextAutoIncreamentValue("lead_master");
                        $cwLeadData['lead_code'] = Core::PadString($tableId, 2 ,"CW".$categoryCode."1212");
                        $leadId = Utility::addOrFetchFromTable("lead_master", $cwLeadData,"lead_id","prospect_id = '{$prospectId}' and category_id = '{$categoryId}' and amount = '{$amount}'");
                        // add lead user
                        if($assignUserId != 0){
                            $db->UpdateWhere("lead_users",array("is_latest"=>0),"lead_id = '{$leadId}' and user_type = 'bd'");
                            Utility::addOrFetchFromTable("lead_users",array(
                                "lead_id" => $leadId,
                                "user_id" => $assignUserId,
                                "user_type" => "bd",
                                "user_type_id" => UT_BD,
                                "is_latest" => "1",
                                "created_at"=>$date,
                                "created_by"=>$telecallerId,
                            ),"lead_id","user_id = '{$assignUserId}' and  is_latest = 0 and user_type = 'bd' and  lead_id = '{$leadId}'");
                        }
                    }
                }
            }
        }

    }
    echo json_encode($upload_status);
}
?>