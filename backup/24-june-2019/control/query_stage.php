<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "query_stage_master";
$table_id = "query_stage_id";
$response='';
if('fetch' == $action){
    $table = "query_stage_master qsm";

    $columns = array(
        'qsm.query_stage_id','ltm.loan_type_name','cm.category_name','r.reason_name','qsm.query_stage_name','qsm.is_active','qsm.is_default','r.reason_id',
        'cm.loan_type_id','r.category_id'
    );
    $seach_columns = array(
        'reason_name','query_stage_name','loan_type_name','category_name'
    );

    $joins = " left join reason_master as r on (qsm.reason_id = r.reason_id)";
    $joins .= " left join category_master as cm on (r.category_id = cm.category_id)";
    $joins .= " left join loan_type_master as ltm on (cm.loan_type_id = ltm.loan_type_id)";
    $sql_where = "WHERE 1=1";

    if ($_GET['sSearch'] != "")
    {

        $seach_condition = "";
        foreach ($seach_columns as $column)
        {
            $column = strtolower($column);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, $index + 4);
            }

            $seach_condition .= $column . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch'] ) . "%' OR ";
        }
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    $sql_order = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sql_order = "ORDER BY  ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
        $sql_order = substr_replace( $sql_order, "", -2 );
    }
    $sql_group = "";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

    //echo $sql;exit;
    $main_query = mysql_query($sql) or die(mysql_error());

    // get the number of filtered rows
    $filtered_rows_query = mysql_query("SELECT FOUND_ROWS()") or die(mysql_error());

    $row = mysql_fetch_array($filtered_rows_query);
    $response['iTotalDisplayRecords'] = $row[0];
    $response['iTotalRecords'] = $row[0];

    $response['sEcho'] = intval($_GET['sEcho']);
    $response['aaData'] = array();
    while ($row = $db->MySqlFetchRow($main_query))
    {
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}elseif($action == 'addedit'){

    $data = $db->FilterParameters($_POST);
    $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
    $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;
    $queryStageName = $data['query_stage_name'];
    $validator = array(
        'rules' => array(
            'reason_id' => array('required' => true),
            'query_stage_name' => array(
                'required' => true,
                'maxlength' => 100
            ),
        ),
        'messages' => array(
            'reason_id' => array('required' => 'Please select reason'),
            'query_stage_name' => array(
                'required' => 'Please enter query Stage name',
                'maxlength' => 'Max length is 100 character for query Stage'
                ),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){
        echo json_encode(array('success' => false, 'errors' => $errors));
    }
    else{
        if(isset($_POST['action']) && $_POST['action'] != ''){
            $id = (isset($data['query_stage_id']) && $data['query_stage_id'] != '') ? $data['query_stage_id'] : 0;

            $existCondition = "query_stage_name = '".$queryStageName."' && reason_id = '". $data['reason_id'] ."' ";
            $dataUpdate['is_default'] = 0;
            $newConditionCheck = "is_default = '1'";

            if($_POST['action'] == 'add'){
                $flag = 0;
                $query_stage_res = $db->FetchRowWhere($table, array($table_id),$existCondition);
                $query_stage_count = $db->CountResultRows($query_stage_res);
                if($query_stage_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Query type is Already Exist";
                }else{
                    if($data['is_default'] == 1){
                        $newExist = $db->FunctionFetch($table, 'count', array($table_id), $newConditionCheck, array(0,1));
                        if($newExist > 0)
                        {
                            $flag = ($db->UpdateWhere($table,$dataUpdate,$newConditionCheck)) ? 1 : 0;
                        }
                    }
                    $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                    $insertId = $db->Insert($table, $data, true);
                    $flag = ($insertId != '') ? 1 : 0;
                    if($flag == 1){
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "Record added successfully!";
                    }else{
                        $response['success'] = false;
                        $response['title'] = 'Error';
                        $response['msg'] = "Something went wrong in insert!";
                    }
                }

            }elseif($_POST['action'] == 'edit'){
                $flag = 0;
                $existCondition .= " && query_stage_id != '$id'";
                if($data['query_stage_id'] != ''){
                    $query_stage_res = $db->FetchRowWhere($table, array($table_id),$existCondition);
                    $query_stage_count = $db->CountResultRows($query_stage_res);
                    if($query_stage_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "Query Type is Already Exist";
                    } else {
                        if($data['is_default'] == 1){
                            $newExist = $db->FunctionFetch($table, 'count', array($table_id), $newConditionCheck, array(0,1));
                            if($newExist > 0)
                            {
                                $flag = ($db->UpdateWhere($table,$dataUpdate,$newConditionCheck)) ? 1 : 0;
                            }
                        }
                        $condition = "query_stage_id = ".$data['query_stage_id']."";
                        $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                        $flag = ($db->UpdateWhere($table,$data, $condition)) ? 1 : 0;
                        if($flag == 1){
                            $response['success'] = true;
                            $response['title'] = 'Successful';
                            $response['msg'] = "Record updated successfully!";
                        }else{
                            $response['success'] = false;
                            $response['title'] = 'Error';
                            $response['msg'] = "Something went wrong in update!";
                        }
                    }
                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid query type Code!";
                }
            }
        }else{
            $response['success'] = false;
            $response['title'] = 'Failed';
            $response['msg'] = "Invalid Action!";
        }
        echo json_encode($response);
    }

}elseif($action == 'delete'){

    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();

    if(is_array($idArray)){
        foreach($idArray as $typeId){
            $existingCheckR = $db->Fetch("tickets",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);

            $existingCheckSubR = $db->Fetch("sub_query_stage_master",$table_id,"$table_id = '{$typeId}'");
            $existingCheckSubC = $db->CountResultRows($existingCheckSubR);
            if($existingCheckC > 0 || $existingCheckSubC > 0 ){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete query stage because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'record(s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
}
include_once 'footer.php';

