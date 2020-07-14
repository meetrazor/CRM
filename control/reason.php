<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="reason_master";
$table_id ="reason_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "reason_master rm";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('rm.reason_id','cm.loan_type_id','rm.category_id','rm.reason_name','rm.is_active',
        'rm.is_default','ltm.loan_type_name','cm.category_name');
    $seach_columns = array('reason_name','category_name','loan_type_name');

    $joins = " left join category_master as cm on (rm.category_id = cm.category_id)";
    $joins .= " left join loan_type_master as ltm on (cm.loan_type_id = ltm.loan_type_id)";

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
            'reason_name' => array('required' => true),
            'category_id' => array('required' => true),
        ),
        'messages' => array(
            'reason_name' => array('required' => 'Please enter reason name'),
            'category_id' => array('required' => 'Please select product type'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
        $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;

        $reasonName = $data['reason_name'];
        $productType = $data['category_id'];


        if(isset($_POST['action']) && $_POST['action'] != ''){
            if($data['is_default'] == 1){
                $db->UpdateWhere($table,array("is_default" => 0),"category_id = '$productType'");
            }
            if($_POST['action'] == 'add'){

                $reason_res = $db->FetchRowWhere($table, array('reason_name'),"reason_name = '$reasonName' and category_id = '$productType'");
                $sourceReasonRes = $db->FetchRowWhere($table, array('reason_name'),"is_default = 1");
                $sourceReasonCount = $db->CountResultRows($sourceReasonRes);
                $reason_count = $db->CountResultRows($reason_res);
                if($reason_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Reason is Already Exist";
                //}elseif($sourceReasonCount == 1 && $sourceReasonCount == $data['is_default']){
                }elseif(1 != 1){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Default reason is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Reason added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['reason_id'] != ''){
                    $reason_res = $db->FetchRowWhere($table, array('reason_name'),"reason_name = '$reasonName' and category_id = '$productType' and reason_id != '{$data['reason_id']}'");
                    $reason_count = $db->CountResultRows($reason_res);
                    $sourceReasonRes = $db->FetchRowWhere($table, array('reason_name'),"is_default = 1 and reason_id != '{$data['reason_id']}'");
                    $sourceReasonCount = $db->CountResultRows($sourceReasonRes);
                    if($reason_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "reason is Already Exist";
                    }elseif(1 != 1){
                    //}elseif($sourceReasonCount == 1 && $sourceReasonCount == $data['is_default']){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "Default reason is Already Exist";
                    } else {
                        $reasonId = $data['reason_id'];
                        $condition = "reason_id = '$reasonId'";
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "reason updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid reason";
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
//            $existingCheckR = $db->Fetch("lead_master",$table_id,"$table_id = '{$typeId}'");
            $existingCheckR = $db->Fetch("tickets",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete reason because it has record";
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

}elseif ($action == 'get_product_type_dd'){
    $data = $db->FilterParameters($_POST);
    $condition = "";
    if(isset($data['id']) && $data['id'] != ''){
        $condition = "loan_type_id = '{$data['id']}' AND is_active = '1'";
    }
    $productTypeDd = $db->CreateOptions('html', 'category_master', array('category_id','category_name'), null, array('category_name' => 'asc'), $condition);
    if(isset($_POST['empty_opt'])){
        $productTypeDd = "<option value=''></option>" . $productTypeDd;
    }else{
        $productTypeDd = Core::PrependNullOption($productTypeDd);
    }
    echo $productTypeDd;
}
include_once 'footer.php';