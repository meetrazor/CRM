<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "admin_user";
$table_id = 'user_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table au";

    $columns = array(
        'au.user_id','concat(au.first_name," ",au.last_name) as full_name','ut.user_type_name',
        'r.role_name','au.user_level','concat(ru.first_name," ",ru.last_name) as reporting_to',
        'au.email','au.mobile_no',
        'au.updated_at',
        'concat(au2.first_name," ",au2.last_name) as rcd_updated_by',
        'au.is_active','null as activity_count','ut.user_type_id'

    );
    $seach_columns = array(
        'au.full_name','au.email','au.mobile_no','DATE_FORMAT(au.created_on,"%d-%m-%Y")','DATE_FORMAT(au.updated_on,"%d-%m-%Y")',
    );

    $joins = " left join role_master r on (r.role_id = au.role_id)";
    $joins .= " left join admin_user au2 on (au2.user_id = au.updated_by)";
    $joins .= " left join admin_user ru on (ru.user_id = au.reporting_to)";
    $joins .= " left join user_type_master ut on (ut.user_type_id = au.user_type)";

    // filtering
// filtering

    if($userType == UT_TC and $isAdmin == 1){
        $sql_where = "WHERE au.user_type = ".UT_TC."";
    } elseif($userType == UT_BD and $isAdmin == 1){
        $sql_where = "WHERE au.user_type = ".UT_BD."";
    } elseif($userType == UT_KC and $isAdmin == 1){
        $sql_where = "WHERE au.user_type = ".UT_KC."";
    } else {
        $sql_where = "WHERE 1=1";
    }

    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "concat(au.first_name,' ',au.last_name)" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "concat(au1.first_name,' ',au1.last_name)" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "au.email" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "au.mobile_no" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "r.role_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

   if ( isset($_GET['sSearch_7']) && $_GET['sSearch_7'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'ut.user_type_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_7'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }




    // ordering
    $sql_order = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        //$_GET['iSortCol_0'] = $_GET['iSortCol_0'] - 1;
        $sql_order = "ORDER BY  ";
        for ( $i = 0; $i < mysql_real_escape_string( $_GET['iSortingCols'] ); $i++ )
        {
            $column = strtolower($columns[$_GET['iSortCol_' . $i]]);
            if(false !== ($index = strpos($column, ' as '))){
                $column = substr($column, 0, $index);
            }
            $sql_order .= $column . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
        }
//        echo $sql_order;
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
        $row['updated_at'] = ($row['updated_at'] != '0000-00-00' && $row['updated_at'] != '') ? core::YMDToDMY($row['updated_at']) : "";
        $userId=$row['user_id'];
        $condition = "created_by=$userId";
        if($row['user_type_id'] == UT_TC){
            $condition .= " and source_type = 'prospect'";
        } else {
            $condition .= " and activity_on = 'lead'";
        }
        $row['activity_count'] = $db->FunctionFetch("activity_master", "count", "activity_id", $condition);

        //$ticketCondition = "created_by=$userId";
        $ticketCondition = "1=1";
        if($row['user_type_id'] == UT_ST) {
            if ($row['user_level'] == 'level1') {
                $ticketCondition .= " and assign_to = $userId";
            } elseif ($row['user_level'] == 'level2') {
                $ticketCondition .= " and escalate_to_2 = $userId";
            } elseif ($row['user_level'] == 'level3') {
                $ticketCondition .= " and escalate_to_3 = $userId";
            }
        }
        $row['ticket_count'] = $db->FunctionFetch("tickets", "count", "ticket_id", $ticketCondition);
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}elseif('addedit' === $action){

    $data = $db->FilterParameters($_POST,array("address"));

    $validator = array(

        'rules' => array(
            'email' => array('required' => true, 'email' => true),
            'mobile_no' => array('required' => true,"mobile_no"=> true),
            'state_id' => array('required' => true),
            'city_id' => array('required' => true),
            'first_name' => array('required' => true),
            'last_name' => array('required' => true),
           // 'agent_code' => array('required' => true),
        ),
        'messages' => array(
            'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id'),
            'mobile_no' => array('required' => "Please enter mobile number","mobile_no"=>"Please enter valid mobile number"),
            'state_id' => array('required' => "Please select state"),
            'city_id' => array('required' => "Please select city"),
            'first_name' => array('required' => "Please enter first name"),
            'last_name' => array('required' => "Please enter last name"),
            'agent_code' => array('required' => "Please enter agent code"),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $prospectCity = isset($data['user_city']) ? $data['user_city'] : array();
        $queryStage = isset($data['query_stage']) ? $data['query_stage'] : array();
        $reason = isset($data['reason_id']) ? $data['reason_id'] : array();
        $userCategory = isset($data['user_category']) ? $data['user_category'] : array();
        $data['first_name'] = isset($data['first_name']) ? ucwords($data['first_name']) : "";
        $data['last_name'] = isset($data['last_name']) ? ucwords($data['last_name']) : "";
        $data['birth_date'] = isset($data['birth_date']) ? core::DMYToYMD($data['birth_date']) : "";
        $id = (isset($data['user_id']) && $data['user_id'] != '') ? $data['user_id'] : 0;


        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['is_admin'] = isset($data['is_admin']) ? 1 : 0;

        $response = array();
        $exist_condition = "email='{$data['email']}'";

        if($id > 0){

            $exist_condition .= " && user_id != '$id'";
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

            if($exist == 0){
                if(isset($data['id-disable-check']) && $data['id-disable-check'] == 1){
                    unset($data['email']);
                }

                $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                $udpate = $db->Update($table, $data, $table_id, $id);

                if(count($prospectCity) > 0){
                    $db->DeleteWhere("user_city","user_id = '{$id}'");
                    foreach($prospectCity as $cityId){
                        $userCityData = array(
                            "user_id" => $id,
                            "city_id" => $cityId,
                        );
                        $db->Insert("user_city",$userCityData);
                    }
                }

                if(count($userCategory) > 0){
                    $db->DeleteWhere("user_category","user_id = '{$id}'");
                    foreach($userCategory as $categoryId){
                        $userCategoryData = array(
                            "user_id" => $id,
                            "category_id" => $categoryId,
                        );
                        $db->Insert("user_category",$userCategoryData);
                    }
                }

                if(count($queryStage) > 0){
                    $db->DeleteWhere("user_query_stage","user_id = '{$id}'");
                    foreach($queryStage as $queryStageId){
                        $userStageData = array(
                            "user_id" => $id,
                            "query_stage_id" => $queryStageId,
                        );
                        $db->Insert("user_query_stage",$userStageData);
                    }
                }

                if(count($reason) > 0){
                    $db->DeleteWhere("user_reason","user_id = '{$id}'");
                    foreach($reason as $reasonId){
                        $userStageData = array(
                            "user_id" => $id,
                            "reason_id" => $reasonId,
                        );
                        $db->Insert("user_reason",$userStageData);
                    }
                }

                $response['success'] = true;
                $response['act'] = 'updated';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record updated successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "User with the email: {$data['email']} already exist";
            }
        }else{

            // Adding a new record if user id is not found
            $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
            if($exist == 0){

                $password = (isset($data['password'])) ? $data['password'] : $core->GenRandomStr(6);
                $data['pass_str'] = md5($password);

                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId = $db->Insert($table, $data,1);
                if($data['reporting_to'] != ''){
                    Utility::addChild($insertId,$data['reporting_to']);
                } else {
                    Utility::addChild($insertId,$insertId);
                }

                if(count($prospectCity) > 0){
                    foreach($prospectCity as $cityId){
                        $userCityData = array(
                            "user_id" => $insertId,
                            "city_id" => $cityId,
                        );
                        $db->Insert("user_city",$userCityData);
                    }
                }

                if(count($userCategory) > 0){
                    foreach($userCategory as $categoryId){
                        $userCategoryData = array(
                            "user_id" => $insertId,
                            "category_id" => $categoryId,
                        );
                        $db->Insert("user_category",$userCategoryData);
                    }
                }

                if(count($queryStage) > 0){

                    foreach($queryStage as $queryStageId){
                        $userStageData = array(
                            "user_id" => $insertId,
                            "query_stage_id" => $queryStageId,
                        );
                        $db->Insert("user_query_stage",$userStageData);
                    }
                }

                if(count($reason) > 0){
                    foreach($reason as $reasonId){
                        $userStageData = array(
                            "user_id" => $insertId,
                            "reason_id" => $reasonId,
                        );
                        $db->Insert("user_reason",$userStageData);
                    }
                }


                $rolePermissions = $db->FetchToArray("role_panel_permission",array("perm_id"),"role_id = '{$data['role_id']}'");
                if(count($rolePermissions) > 0){
                    foreach($rolePermissions as $key=>$value) {

                        $userPermissionData['perm_id'] = $value;
                        $userPermissionData['role_id'] = $data['role_id'];
                        $userPermissionData['usermaster_id'] = $insertId;
                        $userPermissionData['auth'] = 1;
                        $userPermissionData['date_time'] = DATE_TIME_DATABASE;

                        $result_id = $db->Insert('user_panel_permission', $userPermissionData);

                    }
                }
                //$user_info = Utility::registraionEmailToUser($insertId,$password);
                //$status = sMail(array($data['first_name']." ".$data['last_name'] => $data['email']),"CRM Partner", "Welcome to CRM", $user_info, "CRM Partner", USER_NAME, $filepath = '');
                //Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$insertId,'',$user_info,$data['email']);
                $response['success'] = true;
                $response['act'] = 'added';
                $response['title'] = 'Successful';
                $response['msg'] = 'Record added successfully!!';
            }else{

                $response['success'] = false;
                $response['title'] = 'Exist';
                $response['msg'] = "User with the email: {$data['email']} already exist";
            }
        }
        echo json_encode($response);
    }
}elseif($action == 'getcities'){
    $stateId = $db->FilterParameters($_POST['state_id']);
    $condition = "";
    if($stateId != ''){
        $condition = "state_id ='{$stateId}'";
        $res = $db->FetchToArray("city","city_id",$condition);
        $citIds = implode(",",$res);
    }
    $cityDd = $db->CreateOptions('html', 'city', array('city_id','city_name'), null, array('city_name' => 'asc'), "city_id in ($citIds)");
    $cityDd = Core::PrependNullOption($cityDd);

    echo $cityDd;
}elseif($action == 'getusers'){
    $userType = $db->FilterParameters($_POST['user_type']);
    $condition = "1 = 1";
    if($userType != ''){
        //$condition .= " and (user_type = '{$userType}' or user_type = ".UT_ADMIN." )";
        $condition .= " and user_type = '{$userType}'";
    }
    $userDd = $db->CreateOptions('html', 'admin_user', array("user_id","concat(first_name,' ',last_name) as user_name"), null, array("first_name"=>"ASC"), $condition);
    $userDd = Core::PrependNullOption($userDd);

    echo $userDd;
}elseif($action == 'delete'){
    $ids = $db->FilterParameters($_POST['id']);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $condition = "user_id in ('$id')";
    $db->DeleteWhere($table,$condition);
    $db->DeleteWhere("user_panel_permission","usermaster_id in ('$id')");
    //$db->UpdateWhere($table,array("is_active"=>0),$condition);
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' user (s) deleted successfully';
    echo json_encode($response);

}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $userId = isset($data['user_id']) ? $data['user_id'] : "";
    if(isset($userId) and $userId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and user_id != '{$userId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email'");
    }
    $emailCount = $db->CountResultRows($emailRes);
    if($emailCount > 0){
        $flag = "false";
    }
    echo $flag;
}elseif($action == 'changetablestate'){
    $data = $db->FilterParameters($_POST);
    $data['column_hidden'] = json_encode($data['column_shown']);
    $data['user_id'] = $user_id;
    $data['last_updated'] = date('Y-m-d H:i:s');
    $res = $db->FetchRowWhere("user_table_state", array('user_table_state_id'),"page='".$_POST['page']."' and user_id = '$user_id'");
    $resCount = $db->CountResultRows($res);
    if($resCount > 0){
        $res = $db->UpdateWhere("user_table_state",$data,"page='".$_POST['page']."' and user_id = '$user_id'");
    } else {
        $res = $db->Insert("user_table_state",$data);
    }
    return $res;
} elseif($action == 'resetpassword'){
    $data = $db->FilterParameters($_POST);
    $userId = $data['user_id'];
    if($userId != ''){
        $password = $data['password'];
        $data['pass_str'] = md5($password);
        $db->UpdateWhere($table,array("pass_str"=>$data['pass_str']),"user_id = '{$userId}'");
        $response['success'] = true;
        $response['title'] = "Password Reset";
        $response['msg'] = 'password reset successfully';
    } else {
        $response['success'] = false;
        $response['title'] = "Password Reset";
        $response['msg'] = 'password reset Unsuccessfully';
    }
    echo json_encode($response);
}

include_once 'footer.php';
