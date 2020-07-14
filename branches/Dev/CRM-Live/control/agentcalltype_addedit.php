<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "agentcalltype_master";
$table_id = 'agentcalltype_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $domains = (isset($data['domain']) && $data['domain'] != '') ? $data['domain'] : array();    


    if(count($domains) != count(array_unique($domains))){
        $response['success'] = false;
        $response['title'] = 'Incorrect';
        $response['msg'] = "Duplicate domain address";
        echo json_encode($response);
        exit;
    }


    $validator = array(

        'rules' => array(
            'name' => array('required' => true),        
        ),
        'messages' => array(
            'name' => array('required' => "Please enter name"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $id = (isset($data['agentcalltype_id']) && $data['agentcalltype_id'] != '') ? $data['agentcalltype_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $response = array();
        $exist_condition = "name='{$data['name']}'";

        if($id > 0){

            $exist_condition .= " && agentcalltype_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);

                if(count($domains) > 0){
                    $db->DeleteWhere("bank_domain","$table_id = '$id'");
                    foreach($domains as $ekey => $domain) {
                        $domainData = array(
                            "bank_id" => $id,
                            "domain" => $domain,

                        );
                        $db->Insert('bank_domain', $domainData);
                    }
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

                    if(count($domains) > 0){
                        $db->DeleteWhere("bank_domain","$table_id = '$id'");
                        foreach($domains as $ekey => $domain) {
                            $domainData = array(
                                "agentcalltype_id" => $insertId,
                                "domain" => $domain,
                            );
                            $db->Insert('bank_domain', $domainData);
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
                $response['msg'] = "Prospect with the bank_name: {$data['bank_name']} already exist";
            }
        }
        echo json_encode($response);
    }
}
include_once 'footer.php';
