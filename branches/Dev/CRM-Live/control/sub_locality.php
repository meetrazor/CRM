<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="sub_locality";
$table_id ="sub_locality_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "sub_locality sl";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('sl.sub_locality_id','sl.sub_locality_name','cm.city_name','is_active','cm.city_id');
    $seach_columns = array('sl.sub_locality_name','cm.city_name');

    $joins = " left join city as cm on cm.city_id = sl.city_id";

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
        $row['sub_locality_name'] = stripslashes($row['sub_locality_name']);
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
            'sub_locality_name' => array('required' => true),
            'city_id' => array('required' => true),

        ),
        'messages' => array(
            'sub_locality_name' => array('required' => 'Please enter sub locality name'),
            'city_id' => array('required' => 'Please select city'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['sub_locality_name'] = ucwords(strtolower($data['sub_locality_name']));
        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;

        $subLocalityName = $data['sub_locality_name'];


        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $sub_locality_res = $db->FetchRowWhere($table, array('sub_locality_name'),"sub_locality_name='$subLocalityName' and city_id = '{$data['city_id']}'");
                $sub_locality_count = $db->CountResultRows($sub_locality_res);
                if($sub_locality_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Sub locality is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Sub locality added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['sub_locality_id'] != ''){
                    $sub_locality_res = $db->FetchRowWhere($table, array('sub_locality_name'),"sub_locality_name='$subLocalityName' and city_id = '{$data['city_id']}' and sub_locality_id != '{$data['sub_locality_id']}'");
                    $sub_locality_count = $db->CountResultRows($sub_locality_res);
                    if($sub_locality_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "sub locality is Already Exist";
                    } else {
                        $sub_localityId = $data['sub_locality_id'];
                        $condition = "sub_locality_id='$sub_localityId'";
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "sub locality updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid sub locality";
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
            $existingCheckR = $db->Fetch("partner_sub_locality",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete sub locality because it has record";
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

}elseif($action == 'get_cus_par'){

    $cityId = isset($_POST['city_id']) ? $db->FilterParameters($_POST['city_id']) : "";
    $categoryId =  isset($_POST['category_id']) ? $db->FilterParameters($_POST['category_id']) : "";
    $cityTier = '';
    $condition = "";
    if($cityId != ''){
        $condition = "city_id='$cityId'";
        $cityTier = $db->FetchCellValue("city","tier_id",$condition);
    }
    $catPrice = $db->FetchCellValue("tier_category","commission","category_id = '{$categoryId}' and tier_id = '{$cityTier}'");
//    $partnerDd = $db->CreateOptions('html', 'partner_master', array("partner_id","concat(first_name,' ',last_name) as partner_name"), null, array("concat(first_name,' ',last_name)"=>"asc"), "partner_id in (select partner_id from partner_sub_locality where $condition)");
//    $customerDd = $db->CreateOptions('html', 'customer_master', array('customer_id','customer_name'), null, array('customer_name' => 'asc'), "customer_id in (select customer_id from customer_sub_locality where $condition)");
    $partnerDd = $db->CreateOptions('html', 'partner_master', array("partner_id","concat(first_name,' ',last_name) as partner_name"), null, array("concat(first_name,' ',last_name)"=>"asc"), $condition);
    $customerDd = $db->CreateOptions('html', 'customer_master', array('customer_id','customer_name'), null, array('customer_name' => 'asc'), $condition);


    $customerDd = Core::PrependEmptyOption($customerDd);
    $partnerDd = Core::PrependEmptyOption($partnerDd);

    $response['assistance_fees'] = $catPrice;
    $response['partner_dd'] = $partnerDd;
    $response['customer_dd'] = $customerDd;
    echo json_encode($response);

}
include_once 'footer.php';