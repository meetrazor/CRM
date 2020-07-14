<?php
include_once 'header.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="activity_master";
$table_id ="activity_id";

$action = $db->FilterParameters($_GET['act']);
if($action == 'addedit'){

    $data = $db->FilterParameters($_POST);
    $data['start_date_time'] = (isset($data['start_date_time']) && $data['start_date_time'] != '') ? core::DMYToYMD($data['start_date_time']) : 0;
    $leadName = isset($data['type_id']) ? explode(" ",$data['type_id']) : array("","");
    $data['type_id'] = (!isset($data['lead_id'])) ? $db->FetchCellValue("lead_master","lead_id","lead_name = '{$leadName}'") : $data['lead_id'];
    //$data['activity_on'] = 'lead';
    $data['source_type'] = 'lead';
    if(($data['type_id'] == '' || $data['type_id'] == null)){
        $response['success'] = false;
        $response['title'] = 'unsuccessful';
        $response['msg'] = "please enter proper lead name";
        echo json_encode($response);
        exit;
    }
    if(isset($_POST['action']) && $_POST['action'] != ''){

        if($_POST['action'] == 'add'){
            $statusData = $db->FetchRowForForm("status_master","*","status_id = '{$data['status_id']}'");
            if($userType == UT_BD && $statusData['is_show_to_kc'] == 1) {
                $leadData = $db->FetchRowForForm("lead_master","*","lead_id = '{$data['type_id']}'");
                $kcId = Utility::getBdForLead($leadData['city_id'],UT_KC);
                if($kcId == '') {
                    $response['success'] = false;
                    $response['title'] = 'Exist';
                    $response['msg'] = "Kc not available for this lead city";
                    echo json_encode($response);
                    exit;
                }
                if($kcId != ''){
                    Utility::addLeadUser($kcId,$data['type_id'],"kc");
                }
            }
            $timestamp = $db->TimeStampAtCreate($user_id);
            $data = array_merge($data,$timestamp);
            $data['activity_code'] = Utility::GenerateNo($table,"activity");
            $insertId = $db->Insert($table, $data,1);

            if(isset($_FILES['filename']) && count($_FILES['filename']['tmp_name'])){
                $activityUploadPath = Utility::UploadPath() ."/activity_doc/";
                if (!file_exists($activityUploadPath)) {
                    mkdir($activityUploadPath, 0777, true);
                }
                $cnt = count($_FILES['filename']['tmp_name']);
                // $db->DeleteWhere("lead_activity_documents",$condition);
                for($i=0 ; $i<$cnt ; $i++){

                    $filename = Core::UniqueFileName();

                    $extensions = Utility::ImageExtensions();

                    $upload_status = $core->UploadMultipleFile('filename', MAX_UPLOAD_SIZE, $activityUploadPath,$i, $extensions, $filename);

                    if($upload_status['status']){
                        $activityUploadDetails = array('lead_activity_id'=>$insertId,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file'],"created_at"=>DATE_TIME_DATABASE,"created_by"=>$user_id);
                        $uploadDetails = array_merge($activityUploadDetails,$db->TimeStampAtCreate($user_id));
                        $db->Insert("lead_activity_documents", $activityUploadDetails);
                    }
                }
            }
            $response['success'] = true;
            $response['title'] = 'Successful';
            $response['msg'] = "activity added successfully!";
        }elseif($_POST['action'] == 'edit'){

            if($data['lead_activity_id'] != ''){

                $statusData = $db->FetchRowForForm("status_master","*","status_id = '{$data['status_id']}'");
                if($userType == UT_BD && $statusData['is_show_to_kc'] == 1) {
                    $leadData = $db->FetchRowForForm("lead_master","*","lead_id = '{$data['type_id']}'");
                    $kcId = Utility::getBdForLead($leadData['city_id'],UT_KC);
                    if($kcId == '') {
                        $response['success'] = false;
                        $response['title'] = 'Exist';
                        $response['msg'] = "Kc not available for this lead city";
                        echo json_encode($response);
                        exit;
                    }
                    if($kcId != ''){
                        Utility::addLeadUser($kcId,$data['type_id'],"kc");
                    }
                }

                $comId = $data['lead_activity_id'];
                $condition = "activity_id='$comId'";
                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $db->UpdateWhere($table,$data, $condition);
                $createdUser = $db->FetchCellValue($table,"created_by",$condition);

                $response['success'] = true;
                $response['title'] = 'Successful';
                $response['msg'] = "activity updated successfully!";
                if(isset($_FILES['filename']) && count($_FILES['filename']['tmp_name'])){
                    $activityUploadPath = Utility::UploadPath() ."/activity_doc/";
                    if (!file_exists($activityUploadPath)) {
                        mkdir($activityUploadPath, 0777, true);
                    }
                    $cnt = count($_FILES['filename']['tmp_name']);
                    // $db->DeleteWhere("lead_activity_documents",$condition);
                    for($i=0 ; $i<$cnt ; $i++){

                        $filename = Core::UniqueFileName();

                        $extensions = Utility::docExtensions();

                        $upload_status = $core->UploadMultipleFile('filename', MAX_UPLOAD_SIZE, $activityUploadPath,$i, $extensions, $filename);
                        if($upload_status['status']){
                            $activityUploadDetails = array('lead_activity_id'=>$comId,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file'],"created_at"=>DATE_TIME_DATABASE,"created_by"=>$user_id);
                            $uploadDetails = array_merge($activityUploadDetails,$db->TimeStampAtCreate($user_id));
                            $db->Insert("lead_activity_documents", $activityUploadDetails);
                        }
                    }
                }
            }else{
                $response['success'] = false;
                $response['title'] = 'Failed';
                $response['msg'] = "Invalid activity tracker!";
            }
        }
    }else{

        $response['success'] = false;
        $response['title'] = 'Failed';
        $response['msg'] = "Invalid Action!";
    }
    echo json_encode($response);
}
include_once 'footer.php';
