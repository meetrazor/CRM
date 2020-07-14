<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "tickets";
$table_id = 'ticket_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST);
    $data = $db->FilterParameters($_POST, array('comment'));
    $data['comment'] = Utility::cleanedit($data['comment']);
    $validator = array(

        'rules' => array(
            //'email' => array('email' => true),
            //'customer_id' => array('required' => true),
            'status_id' => array('required' => true),
            //'disposition_id' => array('required' => true),
            'comment' => array('required' => true),
            //'loan_type_id' => array('required' => true),
            // 'product_type_id' => array('required' => true),
            // 'reason_id' => array('required' => true),
            //'query_stage_id' => array('required' => true),
            //'query_type_id' => array('required' => true),
            //'mobile_no' => array('required' => true),
        ),
        'messages' => array(
            'email' => array( 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'customer_id' => array('required' => 'Please select customer'),
            'status_id' => array('required' => 'Please select status'),
            //'disposition_id' => array('required' => 'Please select disposition'),
            'reason_id' => array('required' => 'Please select reason'),
            'loan_type_id' => array('required' => 'Please select loan type'),
            'product_type_id' => array('required' => 'Please select product type'),
            'query_stage_id' => array('required' => 'Please select query stage'),
            'query_type_id' => array('required' => 'Please select type'),
            'comment' => array('required' => 'Please enter comment'),

        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $id = (isset($data['ticket_id']) && $data['ticket_id'] != '') ? $data['ticket_id'] : 0;
        $customerData = $db->FetchRowForForm("customer_master","*","customer_id = '{$data['customer_id']}'");

        $response = array();
        $exist_condition = "1 != 1";

        if($id > 0){

            $exist_condition .= " && ticket_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                $ticketData = $db->FetchToArray($table,"*","$table_id = $id");
                $ticketHistoryId = $db->Insert("ticket_history",$ticketData[0],1);


                if($ticketHistoryId != ''){
                    $lastTicketDocumentId = $db->FetchToArray("ticket_documents","ticket_document_id",'ticket_id = '.$id.' and (ticket_history_id = "" or ticket_history_id is null) order by ticket_document_id desc');
                    $imageHistoryArray = array("ticket_history_id"=>$ticketHistoryId);
                    $lastTicketDocumentId = is_array($lastTicketDocumentId) ? implode(",",$lastTicketDocumentId) : $lastTicketDocumentId;
                    $db->UpdateWhere("ticket_documents", $imageHistoryArray, "ticket_document_id in ($lastTicketDocumentId)");
                }

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));

                if(isset($data['escalate_to']) && $data['escalate_to'] != ''){
                    if ($userLevel == 'level1'){
                        $data['escalate_to_2'] = $data['escalate_to'];
                        $data['escalate_to_2_date'] = DATE_TIME_DATABASE;
                    }elseif ($userLevel == 'level2'){
                        $data['escalate_to_3'] = $data['escalate_to'];
                        $data['escalate_to_3_date'] = DATE_TIME_DATABASE;
                    } else {
                        $escalateUserLevel = $db->FetchCellValue("admin_user","user_level","user_id = '{$data['escalate_to']}'");

                        if ($escalateUserLevel == 'level2'){
                            $data['escalate_to_2'] = $data['escalate_to'];
                            $data['escalate_to_2_date'] = DATE_TIME_DATABASE;
                        }elseif ($escalateUserLevel == 'level3'){
                            $data['escalate_to_3'] = $data['escalate_to'];
                            $data['escalate_to_3_date'] = DATE_TIME_DATABASE;
                        }
                    }
                }

                $udpate = $db->Update($table, $data, $table_id, $id);
                $statusData = $db->FetchRowForForm("status_master","*","status_id = '{$data['status_id']}'");
                if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                    $customerMessage = "Dear Client,%0aYour complaint ticket number ".$data['ticket_number']." has been RESOLVED now.%0aTeam,%0aPSB Loans in 59 Minutes";
                    if(isset($customerData['mobile_no']) && $customerData['mobile_no'] != ''){
                        $status = Utility::sendSMS($customerData['mobile_no'],$customerMessage);
                        Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$customerData['customer_id'],"",$customerMessage,$customerData['mobile_no'],'sms','customer',$customerData['customer_id']);
                    }
                }

                if(isset($customerData['email']) && $customerData['email'] != '' && $data['reason_id'] = R_BANK_SUPPORT){
                    if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                        $ticketEmailBody = Utility::closeTicketBody($id,$data['ticket_number']);
                        $status = sMail(array($customerData['customer_name'] => $customerData['email']),"PSB Loans in 59 Minutes", "Complain Resolved ".$data['ticket_number']."", $ticketEmailBody, "PSB Loans in 59 Minutes", "no-reply@psbloansin59minutes.com", $filepath = '');
                        Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$id,NET_TICKET,$ticketEmailBody,$customerData['email']);
                    }
                }

                if(isset($_FILES['filename']) && count($_FILES['filename']['tmp_name'])){
                    $activityUploadPath = Utility::UploadPath() ."/tickets/";
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
                            $ticketFileData = array('ticket_id'=>$id,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                            $ticketFileData = array_merge($ticketFileData,$db->TimeStampAtCreate($user_id));
                            $db->Insert("ticket_documents", $ticketFileData);
                        }
                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "client with  mobile: {$data['mobile']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $tableId = $db->GetNextAutoIncreamentValue($table);
            $data['ticket_number'] = Core::PadString($tableId, 5 ,"PSB");
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){
                $data['is_latest'] = 1;
                if(isset($data['assign_to']) && $data['assign_to'] == ''){
                    $data['assign_to'] = Utility::getUserForTicket($data['reason_id']);
                }
                $data['assign_to_date'] = DATE_TIME_DATABASE;
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);
                $statusData = $db->FetchRowForForm("status_master","*","status_id = '{$data['status_id']}'");
                if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                    $customerMessage = "Dear Client,%0aYour complaint ticket number ".$data['ticket_number']." has been RESOLVED now.%0aTeam,%0aPSB Loans in 59 Minutes";
                } else {
                    $customerMessage ="Dear Client,%0aYou have successfully registered your complaint. Your ticket no is ".$data['ticket_number'].".%0aYou will receive one more SMS after the resolution of your query.%0aTeam,%0aPSB Loans in 59 Minutes";
                }
                if(isset($customerData['mobile_no']) && $customerData['mobile_no'] != ''){
                    $status = Utility::sendSMS($customerData['mobile_no'],$customerMessage);
                    Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$customerData['customer_id'],"",$customerMessage,$customerData['mobile_no'],'sms','customer',$customerData['customer_id']);
                }
                if(isset($customerData['email']) && $customerData['email'] != '' && $data['reason_id'] = R_BANK_SUPPORT){
                    if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                        $ticketEmailBody = Utility::closeTicketBody($insertId,$data['ticket_number']);
                    } else {
                        $ticketEmailBody = Utility::openTicketBody($insertId,$data['ticket_number']);
                    }
                    $status = sMail(array($customerData['customer_name'] => $customerData['email']),"PSB Loans in 59 Minutes", "Complain Registered ".$data['ticket_number']."", $ticketEmailBody, "PSB Loans in 59 Minutes", "no-reply@psbloansin59minutes.com", $filepath = '');
                    Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$insertId,NET_TICKET,$ticketEmailBody,$customerData['email']);
                }
                $data['ticket_id'] = $insertId;
                //$ticketHistoryId = $db->Insert("ticket_history",$data);


                if(isset($_FILES['filename']) && count($_FILES['filename']['tmp_name'])){
                    $activityUploadPath = Utility::UploadPath() ."/tickets/";
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
                            $ticketFileData = array('ticket_id'=>$insertId,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                            $ticketFileData = array_merge($ticketFileData,$db->TimeStampAtCreate($user_id));
                            $db->Insert("ticket_documents", $ticketFileData);
                        }
                    }
                }

                $response['success'] = true;
                $response['act'] = 'added';
                $response['lead_id'] = $insertId;
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully and Lead Number is '.$data['ticket_number'].'!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "lead with mobile: {$data['mobile_no']} already exist";
            }
        }
        echo json_encode($response);
    }
} elseif($action == 'addcomment'){
    $data = $db->FilterParameters($_POST);
    $validator = array(

        'rules' => array(
            'lead_id' => array('required' => true),
            'lead_comment' => array('required' => true),
        ),
        'messages' => array(
            'lead_id' => array('required' => "Please select lead"),
            'lead_comment' => array('required' => "Please enter lead comment"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
        exit;
    }
    if($data['lead_id'] != ''){
        $data = array_merge($data,$db->TimeStampAtCreate($user_id));
        $db->Insert("lead_comment",$data);
        $response['success'] = true;
        $response['title'] = 'Success';
        $response['msg'] = "Comment added successfully";
    } else {
        $response['success'] = false;
        $response['title'] = 'unsuccessfully';
        $response['msg'] = "Comment added unsuccessfully";
    }
    echo json_encode($response);
} elseif($action == 'commentview'){
    $data = $db->FilterParameters($_POST);
    $leadId = isset($data['lead_id']) ? $data['lead_id'] : "";
    if($leadId != ''){
        $leadHistory = $db->FetchToArray("lead_comment","*","lead_id = '{$leadId}' and is_delete = 0",array('created_at'=>"desc"));
        if(is_array($leadHistory) and count($leadHistory) > 0){
            $lead_html_div = "";
            $lead_html_div .= "<table class='table table-condensed table-bordered table-hover'>";
            $lead_html_div .= "<tr>";
            $lead_html_div .= "<td><b>Comment</b></td>";
            $lead_html_div .= "<td><b>Created On</b></td>";
            $lead_html_div .= "<tr>";
            foreach($leadHistory as $id => $history){
                $lead_html_div .= "<tr>";
                $lead_html_div .= "<td>".$history['lead_comment']."</td>";
                $lead_html_div .= "<td>".core::YMDToDMY($history['created_at'],true)."</td>";
                $lead_html_div .= "<tr>";

            }
            $lead_html_div .= "</table>";
            echo $lead_html_div;
        } else {
            echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";

        }
    } else {
        echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";
    }
}elseif($action == 'get_sub_query_stage_dd'){

    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id']) && $data['id'] != ''){
        $condition = "query_stage_id = '{$data['id']}'";
    }
    if(isset($data['sub_query_stage_id']) && $data['sub_query_stage_id'] != 0){
        $selectedSubQuery = $data['sub_query_stage_id'];
    }else{
        $selectedSubQuery = null;
    }
    $subStageDd = $db->CreateOptions('html', 'sub_query_stage_master', array('sub_query_stage_id','sub_query_stage_name'), $selectedSubQuery, array('sub_query_stage_name' => 'asc'), $condition."and is_active = '1'");

    if(isset($_POST['empty_opt'])){
        $subStageDd = "<option value=''></option>" . $subStageDd;
    }else{
        $subStageDd = Core::PrependNullOption($subStageDd);
    }
    echo $subStageDd;

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
}elseif($action == 'get_product_type_dd'){

    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id'])){
        if(is_array($data['id'])){
            $loanTypeId  = implode(",",$data['id']);
            $condition = "loan_type_id in ($loanTypeId)";
        } else {
            if($data['id'] != ''){
                $condition = "loan_type_id = '{$data['id']}'";
            }
        }

    }
    $defaultProductTypeId = $db->FetchCellValue("category_master", "category_id", "is_default = 1");
    $selected = (isset($data['product_type_id']) && $data['product_type_id'] != 0 && $data['product_type_id'] != '' ) ? $data['product_type_id'] : $defaultProductTypeId;
    if(isset($data['product_type_id ']) && $data['product_type_id'] != 0 && $data['product_type_id'] != '' ){
        $condition .= "and (is_active = '1' or category_id = {$data['product_type_id']})";

    }else{
        $condition .= "and is_active = '1'";
    }
    $productTypeDd = $db->CreateOptions('html', 'category_master', array('category_id','category_name'), $selected, array('category_name' => 'asc'), $condition);

    if(isset($_POST['empty_opt'])){
        $productTypeDd = "<option value=''></option>" . $productTypeDd;
    }else{
        $productTypeDd = Core::PrependEmptyOption($productTypeDd);
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
    $reasonDd = $db->CreateOptions('html', 'reason_master', array('reason_id','reason_name'), $selectedReason, array('reason_name' => 'asc'), $condition."and is_active = '1'");

    if(isset($_POST['empty_opt'])){
        $reasonDd = "<option value=''></option>" . $reasonDd;
    }else{
        $reasonDd = Core::PrependNullOption($reasonDd);
    }
    echo $reasonDd;

}
include_once 'footer.php';
