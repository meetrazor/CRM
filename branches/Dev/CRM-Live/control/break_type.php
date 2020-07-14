<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "break_type_master";
$table_id = "break_type_id";
$response='';
if('fetch' == $action){
    $table = "break_type_master";

    $columns = array(
        'break_type_id','break_type_name','duration','is_active'
    );
    $seach_columns = array(
        'break_type_name'
    );

    $joins = "";
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
    $breakTypeName = $data['break_type_name'];
    if(isset($_POST['action']) && $_POST['action'] != ''){

        if($_POST['action'] == 'add'){
            $break_type_res = $db->FetchRowWhere($table, array('break_type_name'),"break_type_name='$breakTypeName'");
            $break_type_count = $db->CountResultRows($break_type_res);
            if($break_type_count > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "Break type is Already Exist";
            }else{
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $db->Insert($table, $data, true);
                $response['success'] = true;
                $response['title'] = 'Successful';
                $response['msg'] = "Record added successfully!";
            }
        }elseif($_POST['action'] == 'edit'){

            if($data['break_type_id'] != ''){
                $break_type_res = $db->FetchRowWhere($table, array('break_type_name'),"break_type_name='$breakTypeName' and break_type_id != '{$data['break_type_id']}'");
                $break_type_count = $db->CountResultRows($break_type_res);
                if($break_type_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Break type is Already Exist";
                } else {
                    $condition = "break_type_id=".$data['break_type_id']."";
                    $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                    $db->UpdateWhere($table,$data, $condition);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Record updated successfully!";
                }

            }else{
                $response['success'] = false;
                $response['title'] = 'Failed';
                $response['msg'] = "Invalid break type Code!";
            }
        }
    }else{

        $response['success'] = false;
        $response['title'] = 'Failed';
        $response['msg'] = "Invalid Action!";
    }
    echo json_encode($response);
}elseif($action == 'delete'){

    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();

    if(is_array($idArray)){
        foreach($idArray as $typeId){
            $existingCheckR = $db->Fetch("partner_payout",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete break type because it has record";
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

