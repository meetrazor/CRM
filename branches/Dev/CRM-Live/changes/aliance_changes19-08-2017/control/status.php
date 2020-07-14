<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");
/* DB table to use */

$table ="status_master";
$table_id ="status_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "status_master as sm";//"city ct";


    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('sm.status_id','sm.status_name','sm.status_type','sm.activity_type','sm.sort_order','sm.is_default','sm.is_close','sm.cal_price','sm.is_active','sm.is_callback',
        'is_show_to_kc','is_email_send','is_sms_send'
    );
    $seach_columns = array('sm.status_name','sm.status_type');

    $joins = "";
    // filtering
    $sql_where = "where 1=1";

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



    // ordering
    $sql_order = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $_GET['iSortCol_0'] = $_GET['iSortCol_0'] - 1;
        $sql_order = "ORDER BY  ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
        //echo $sql_order;
        $sql_order = substr_replace( $sql_order, "", -2 );
    }

    // group by 
    // If you are not using then put it blank otherwise mention it
    $sql_group = "";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

// 	echo $sql;exit;
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
    //core::PrintArray($response);
    //exit;
    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);

}elseif($action == 'delete'){
    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();

    if(is_array($idArray)){
        foreach($idArray as $typeId){
            $existingCheckR = $db->FetchCellValue("lead_master","$table_id","status_id = '{$typeId}'");
            $existingCheckT = $db->FetchCellValue("activity_master","$table_id","status_id = '{$typeId}'");

            $existingCheckC = count($existingCheckR);
            $existingCheckCount = count($existingCheckT);
            if($existingCheckC > 0 || $existingCheckCount >0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete status because it has record";
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

}elseif($action == 'default'){
    $data = $db->FilterParameters($_POST['data']);
    $statusId = $data['status_id'];
    $type = $data['type'];
    $data1 = array("is_default"=>'1');
    $clear = $db->UpdateWhere($table,array("is_default"=>0),"type = '{$type}' and organization_id = '{$organizationId}'");
    $res = $db->UpdateWhere($table,$data1,'status_id = "'.$statusId.'"');
    if($res == true){
        $response['success'] = true;
        $response['msg'] = 'Record Updated';
        $response['title'] = 'success';
    }
    echo json_encode($response);
}elseif($action == 'close'){
    $data = $db->FilterParameters($_POST['data']);
    $statusId = $data['status_id'];
    $type = $data['type'];
    $data1 = array("is_close"=>'1');
    $clear = $db->UpdateWhere($table,array("is_close"=>0),"type = '{$type}' and organization_id = '{$organizationId}'");
    $res = $db->UpdateWhere($table,$data1,'status_id = "'.$statusId.'"');
    if($res == true){
        $response['success'] = true;
        $response['msg'] = 'Record Updated';
        $response['title'] = 'success';
    }
    echo json_encode($response);
}elseif($action == 'accountcheck'){
    $data = $db->FilterParameters($_POST['status_type_id']);
    $statusTypeId = $data;
    $isAccountRelated = $db->FetchCellValue("status_types","is_account_related","type_id = '{$statusTypeId}'");
    if($isAccountRelated == 1){
        $accountStatuses = $db->CreateOptions("html","statuses",array("status_id","status_name"),null,array("status_name" => "ASC"),"type = 'account' and organization_id = '{$organizationId}'");
        $accountStatuses = core::PrependEmptyOption($accountStatuses);
        $response['success'] = true;
        $response['data'] = $accountStatuses;
    } else {
        $response['success'] = false;
        $response['data'] = '';
    }
    echo json_encode($response);
}elseif($action == 'checkstatus'){

    $data = $db->FilterParameters($_POST);
    $statusId = $db->FilterParameters($data['status_id']);
    $checkDis = array();

    if($statusId != ''){
        $checkDis = $db->FetchRowForForm($table,array('is_callback'),"status_id = '{$statusId}'");
    }
    echo json_encode($checkDis);

}elseif($action == 'getstatus'){
    $data = $db->FilterParameters($_POST);
    $activityType = (isset($data['activity_type']) && $data['activity_type']!="") ? $data['activity_type'] : "";
    $statusId = (isset($data['status_id']) && $data['status_id']!="") ? $data['status_id'] : "";
    $condition = "";
    if($activityType != ''){
        $condition = "activity_type ='{$activityType}'";
    }
    $statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$statusId,array("status_name"=>"ASC"),"status_type = 'activity' and $condition");
    $statusDd = Core::PrependNullOption($statusDd);

    echo $statusDd;
}

include_once 'footer.php';