<?php
@session_start();
$user_id = $_SESSION['user_id'];
include_once 'header.php';

/* DB table to use */

$table ="status_master";
$table_id ="status_id";

$action = $db->FilterParameters($_GET['act']);
if($action == 'add'){

    $data = $db->FilterParameters($_POST);
    $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
    $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;
    $data['is_close'] = (isset($data['is_close'])) ? 1 : 0;
    $data['cal_price'] = (isset($data['cal_price'])) ? 1 : 0;
    $data['is_callback'] = (isset($data['is_callback'])) ? 1 : 0;
    $data['is_show_to_kc'] = (isset($data['is_show_to_kc'])) ? 1 : 0;
    $data['is_email_send'] = (isset($data['is_email_send'])) ? 1 : 0;
    $data['is_sms_send'] = (isset($data['is_sms_send'])) ? 1 : 0;
    $data['status_name'] = ucwords(strtolower($data['status_name']));
    $data['activity_type'] = ucwords(strtolower($data['activity_type']));
    $statusName = $data['status_name'];
    $status_res = $db->FetchRowWhere($table, array('status_name'),"status_name='$statusName' and status_type = '{$data['status_type']}'");
    $status_count = $db->CountResultRows($status_res);
    $statusOrders = (isset($data['order_number'])) ? $data['order_number'] : array();
    if($status_count > 0){
        $response['success'] = false;
        $response['title'] = 'Error:';
        $response['msg'] = "status is Already Exist";

    }else{
        $data = array_merge($data,$db->TimeStampAtCreate($user_id));
        $statusId = $db->Insert($table, $data, true);
        $response['success'] = true;
        $response['title'] = 'Successful';
        $response['msg'] = "Record added successfully!";
        $response['value'] = $statusId;
        $response['text'] = $data['status_name'];
    }
    echo json_encode($response);
}
include_once 'footer.php';