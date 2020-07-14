<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="category_master";
$table_id ="category_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "category_master cm";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('cm.category_id','ltm.loan_type_name','cm.category_name','cm.category_type','cm.commission','cm.effective_date','cm.category_code','cm.is_business','cm.is_active','cm.is_default','ltm.loan_type_id');
    $seach_columns = array('category_name','category_type','commission','loan_type_name');

    $joins = " left join loan_type_master as ltm  on (cm.loan_type_id = ltm.loan_type_id)";

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
        $row['effective_date'] = ($row['effective_date'] != '0000-00-00' && $row['effective_date'] != '') ? core::YMDToDMY($row['effective_date']) : "";
        $row['category_name'] = stripslashes($row['category_name']);
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
            'category_name' => array('required' => true),
            'loan_type_id' => array('required' => true),
            'category_type' => array('required' => true),
            //'commission' => array('required' => true,'number'=>true),
            //'effective_date' => array('required' => true),
        ),
        'messages' => array(
            'category_name' => array('required' => 'Please enter Product/loan name'),
            'loan_type_id' => array('required' => 'Please select loan type name'),
            'category_type' => array('required' => 'Please select category type'),
            'commission' => array('required' => 'Please enter commission','number'=>'Please enter number only'),
            'effective_date' => array('required' => 'Please select effective date'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        //$data['category_name'] = ucwords(strtolower($data['category_name']));
        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
        $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;
        $data['is_business'] = (isset($data['is_business'])) ? 1 : 0;
        $data['effective_date'] = (isset($data['effective_date'])) ? core::DMYToYMD($data['effective_date']) : '0000-00-00';

        $categoryName = $data['category_name'];
        $existCondition = "category_name = '".$categoryName."' && category_type = '{$data['category_type']}' && loan_type_id = '". $data['loan_type_id'] ."' ";

        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $category_res = $db->FetchRowWhere($table, array('category_name'),$existCondition);
                //$category_res = $db->FetchRowWhere($table, array('category_name'),"category_name='$categoryName' and category_type= '{$data['category_type']}' and effective_date = '".core::DMYToYMD($data['effective_date'])."'");
                $sourceCategoryRes = $db->FetchRowWhere($table, array('category_name'),"is_default = 1 and category_type= '{$data['category_type']}'");
                $sourceCategoryCount = $db->CountResultRows($sourceCategoryRes);
                $category_count = $db->CountResultRows($category_res);
                if($category_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Category is Already Exist";
                }elseif($sourceCategoryCount == 1 && $sourceCategoryCount == $data['is_default']){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Default category is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Category added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['category_id'] != ''){
                    $existCondition .= " && category_id != '{$data['category_id']}'";

                    $category_res = $db->FetchRowWhere($table, array('category_name'),$existCondition);
                    $category_count = $db->CountResultRows($category_res);
                    $sourceCategoryRes = $db->FetchRowWhere($table, array('category_name'),"is_default = 1 and category_type= '{$data['category_type']}' and category_id != '{$data['category_id']}'");
                    $sourceCategoryCount = $db->CountResultRows($sourceCategoryRes);
                    if($category_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "category is Already Exist";
                    }elseif($sourceCategoryCount == 1 && $sourceCategoryCount == $data['is_default']){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "Default category is Already Exist";
                    } else {
                        $categoryId = $data['category_id'];
                        $condition = "category_id='$categoryId'";
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "category updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid category";
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
            $existingCheckR = $db->Fetch("lead_master",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete category because it has record";
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

} elseif($action == 'checkbus'){
    $data = $db->FilterParameters($_POST);
    $response['is_business'] = $db->FetchCellValue("category_master","is_business","category_id = '{$data['category_id']}'");
    echo json_encode($response);

}
include_once 'footer.php';