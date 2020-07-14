<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="disposition_master";
$table_id ="disposition_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "disposition_master as dm";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('dm.disposition_id','dm.disposition_type','dm.disposition_name','dm.disposition_code','pdm.disposition_name as parent_disposition_name','dm.is_close',
        'dm.is_callback','dm.is_meeting','dm.is_active','dm.is_default','dm.parent_disposition_id','null as prospect_count');
    $seach_columns = array('dm.disposition_type','dm.disposition_name','dm.disposition_code');

    $joins = " left join disposition_master as pdm on pdm.disposition_id = dm.parent_disposition_id";

    // filtering
    $sql_where = "where 1 = 1";
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
        $dispositionId=$row['disposition_id'];
        $mainTable = array("activity_master as am",array("pm.prospect_id"));
        $joinTable = array(
            array("left","prospect_master as pm","am.type_id = pm.prospect_id"),
        );
        $countDetails = $db->JoinFetch($mainTable,$joinTable,"am.disposition_id=$dispositionId and am.disposition_id is not null and source_type = 'prospect'");
        $countResult = $db->FetchToArrayFromResultset($countDetails);
        $row['prospect_count'] = count($countResult);
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

    $validator = array(

        'rules' => array(
            'disposition_name' => array('required' => true),
            'disposition_code' => array('required' => true),
        ),
        'messages' => array(
            'disposition_name' => array('required' => 'Please enter disposition name'),
            'disposition_code' => array('required' => 'Please enter disposition code'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['is_callback'] = (isset($data['is_callback'])) ? 1 : 0;
        $data['is_meeting'] = (isset($data['is_meeting'])) ? 1 : 0;
        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
        $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;
        $data['is_close'] = (isset($data['is_close'])) ? 1 : 0;

        $dispositionName = $data['disposition_name'];
        $dispositionType = $data['disposition_type'];


        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $disposition_res = $db->FetchRowWhere($table, array('disposition_name'),"disposition_name='$dispositionName' and disposition_type = '{$dispositionType}'");
                $sourceDispositionRes = $db->FetchRowWhere($table, array('disposition_name'),"is_default = 1");
                $sourceDispositionCount = $db->CountResultRows($sourceDispositionRes);
                $disposition_count = $db->CountResultRows($disposition_res);
                if($disposition_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Disposition is Already Exist";
                }elseif($sourceDispositionCount == 1 && $sourceDispositionCount == $data['is_default']){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Default disposition is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Disposition added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['disposition_id'] != ''){
                    $disposition_res = $db->FetchRowWhere($table, array('disposition_name'),"disposition_name='$dispositionName' and disposition_type = '{$dispositionType}' and disposition_id != '{$data['disposition_id']}'");
                    $disposition_count = $db->CountResultRows($disposition_res);
                    $sourceDispositionRes = $db->FetchRowWhere($table, array('disposition_name'),"is_default = 1 and disposition_id != '{$data['disposition_id']}'");
                    $sourceDispositionCount = $db->CountResultRows($sourceDispositionRes);
                    if($disposition_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "disposition is Already Exist";
                    }elseif($sourceDispositionCount == 1 && $sourceDispositionCount == $data['is_default']){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "Default disposition is Already Exist";
                    } else {
                        $dispositionId = $data['disposition_id'];
                        $condition = "disposition_id='$dispositionId'";
                        $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "disposition updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid disposition";
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
            $existingCheckR = $db->Fetch("activity_master",$table_id,"disposition_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete disposition because it has record";
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

}elseif($action == 'checkdis'){

    $data = $db->FilterParameters($_POST);
    $dispositionId = $db->FilterParameters($_POST['disposition_id']);
    $prospectId = isset($data['prospect_id']) ? $data['prospect_id'] : "";
    $prospectData = $db->FetchRowForForm("prospect_master",array("city_id"),"prospect_id = '{$prospectId}'");
    $checkDis = array();

    if($dispositionId != ''){
        $checkDis = $db->FetchRowForForm($table,array('is_callback','is_meeting'),"disposition_id = '{$dispositionId}'");
    }
    $apDd = $db->CreateOptions("html","partner_master",array("partner_id","concat(first_name,' ',last_name) as partner_name"),null,array("concat(first_name,' ',last_name)"=>"asc"),"is_active = 1 and city_id = '{$prospectData['city_id']}' and parent_partner_id is NULL");
    $checkDis['ap_list'] = core::PrependNullOption($apDd);
    echo json_encode($checkDis);

}
include_once 'footer.php';
