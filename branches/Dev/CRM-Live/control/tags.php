<?php
include_once 'header.php';

$table = "tag_master";
$table_id = "tag_id";
$action = $db->FilterParameters($_GET['act']);
if('add' === $action){

    $data['tag_name'] = ucwords($_POST['term']);

    $exist_count = $db->FunctionFetch($table, 'count', $table_id,"tag_name='{$data['tag_name']}'");

    if($exist_count == 0){

        $id = $db->Insert($table, $data, 1);

        $response_res = $db->FetchRow($table, $table_id, $id);
        $row = $db->MySqlFetchRow($response_res);

        $response['value'] = $row['tag_id'];
        $response['text'] = $row['tag_name'];
        echo json_encode($response);
    }
}
