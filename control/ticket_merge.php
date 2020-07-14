<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "tickets";
$table_id = 'ticket_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('merge_ticket' === $action){

    $data = $db->FilterParameters($_POST);
    $validator = array(

        'rules' => array(
            'primary_ticket_id' => array('required' => true),
            'merge_action' => array('required' => true),
        ),
        'messages' => array(
            'primary_ticket_id' => array('required' => 'Please select primary ticket'),
            'merge_action' => array('required' => 'Please select an action'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
        exit;
    } else {
        $id = (isset($data['ticket_id']) && $data['ticket_id'] != '') ? $data['ticket_id'] : 0;
        $flag = 0;
        $response = array();

        $isMerged = $db->FetchCellValue($table, "is_merged","$table_id = $id");
        if($isMerged == 1){
            $response['success'] = false;
            $response['title'] = 'Error';
            $response['msg'] = "This ticket number '{$data['ticket_number']}' is already merged!!";
            echo json_encode($response);
            exit();
        }

        $primaryTicketData = $db->FetchToArray($table, array("status_id","ticket_number") ,"$table_id = {$data['primary_ticket_id']}",null,array(0,1));
        if($primaryTicketData[0]['status_id'] == $data['closed_ticket_status_id']){
            $response['success'] = false;
            $response['title'] = 'Error';
            $response['msg'] = "The primary ticket number '{$primaryTicketData[0]['ticket_number']}' is being closed now!!";
            echo json_encode($response);
            exit();
        }

        $updateData = array('is_merged' => 1, 'merged_id' => $data['primary_ticket_id'], 'merge_type' => $data['merge_action']);

        if($data['merge_action'] == 'merge_all_replies'){

            $mergeDataArray = $db->FetchToArray("ticket_history","*","$table_id = $id");
            $count = 0;
            if(count($mergeDataArray) > 0 ){

                foreach ($mergeDataArray as $key => $value){
                    $mergeDataArray[$key]['ticket_id'] = $data['primary_ticket_id'];
                    $mergeDataArray[$key]['ticket_number'] = $primaryTicketData[0]['ticket_number'];
                    unset($mergeDataArray[$key]['ticket_history_id']);
                    $insertId = $db->Insert("ticket_history",$mergeDataArray[$key],1);
                    if($insertId != ''){
                        $count ++;
                    }
                }
                if($count == count($mergeDataArray)){
                    $flag = ($db->Update($table,$updateData,$table_id,$id)) ? 1 : 0;
                }
                else{
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Something went wrong!!";
                }

                if($flag == 1){
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Merged All Replies with primary ticket";
                }
                else{
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Fail Merging All Replies with primary ticket";
                }
            }
            else{
                $response['success'] = false;
                $response['title'] = 'Error';
                $response['msg'] = "There is no any replies in ticket '{$data['ticket_number']}' to be merged with primary ticket!!";
            }
            echo json_encode($response);
        }

        elseif ($data['merge_action'] == 'replace_ticket_data'){

            //get replies and files of this ticket
            $mergeDataArray = $db->FetchToArray("ticket_history","*","$table_id = $id");
            $mergeDataFiles = $db->FetchToArray("ticket_documents","*","$table_id = $id");

                //delete existing replies and files of primary ticket
                $flag = ($db->DeleteWhere("ticket_history","$table_id = {$data['primary_ticket_id']}")) ? 1 : 0;
                $flag = ($db->DeleteWhere("ticket_documents","$table_id = {$data['primary_ticket_id']}")) ? 1 : 0;

                //replace ticket replies
                if(count($mergeDataArray) > 0){
                    $count = 0;
                    foreach ($mergeDataArray as $key => $value){
                        $mergeDataArray[$key]['ticket_id'] = $data['primary_ticket_id'];
                        $mergeDataArray[$key]['ticket_number'] = $primaryTicketData[0]['ticket_number'];
                        unset($mergeDataArray[$key]['ticket_history_id']);
                        $insertId = $db->Insert("ticket_history",$mergeDataArray[$key],1);
                        if($insertId != ''){
                            $count ++;
                        }
                    }
                    if($count == count($mergeDataArray)){
                        $flag =  1;
                    }
                }

                //replace ticket files
                if(count($mergeDataFiles) > 0 ){
                    $count = 0;
                    foreach ($mergeDataFiles as $key => $value){
                        $mergeDataFiles[$key]['ticket_id'] = $data['primary_ticket_id'];
                        unset($mergeDataFiles[$key]['ticket_document_id']);
                        $insertId = $db->Insert("ticket_documents",$mergeDataFiles[$key],1);
                        if($insertId != ''){
                            $count ++;
                        }
                    }
                    if($count == count($mergeDataFiles)){
                        $flag =  1;
                    }
                }

                //replace ticket details
                $thisTicketData = $db->FetchRowForForm($table,"*","$table_id = $id");
                unset($thisTicketData['ticket_id']);
                unset($thisTicketData['ticket_number']);
                if($db->Update($table,$thisTicketData,$table_id,$data['primary_ticket_id'])){
                    $flag = ($db->Update($table,$updateData,$table_id,$id)) ? 1 : 0;
                }

                if($flag == 1){
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Replaced Ticket Data with primary ticket";
                }
                else{
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Fail to replace ticket data with primary ticket";
                }

            echo json_encode($response);
        }

        elseif ($data['merge_action'] == 'merge_ticket_files'){
            $mergeDataArray = $db->FetchToArray("ticket_documents","*","$table_id = $id");
            $count = 0;
            if(count($mergeDataArray) > 0 ){

                foreach ($mergeDataArray as $key => $value){
                    $mergeDataArray[$key]['ticket_id'] = $data['primary_ticket_id'];
                    unset($mergeDataArray[$key]['ticket_document_id']);
                    $insertId = $db->Insert("ticket_documents",$mergeDataArray[$key],1);
                    if($insertId != ''){
                        $count ++;
                    }
                }
                if($count == count($mergeDataArray)){
                    $flag = ($db->Update($table,$updateData,$table_id,$id)) ? 1 : 0;
                }
                else{
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Something went wrong!!";
                }

                if($flag == 1){
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Merged Ticket Files with primary ticket";
                }
                else{
                    $response['success'] = false;
                    $response['title'] = 'Error';
                    $response['msg'] = "Fail Merging Ticket Files with primary ticket";
                }
            }
            else{
                $response['success'] = false;
                $response['title'] = 'Error';
                $response['msg'] = "There is no any files in ticket '{$data['ticket_number']}' to be merged with primary ticket!!";
            }
            echo json_encode($response);
        }
    }
}


