<?php
include_once 'header.php';

$action = $db->FilterParameters($_GET['act']);
$table = 'role_master';
$table_id = 'role_id';
$default_sort_column = 'role_id';

if($action == 'fetch'){

	
	$table = "$table as e";
	
	$columns = array('e.role_id','e.role_name');
	
	$seach_columns = array('e.role_name');

	
	$joins = "";

    $sql_where =  "WHERE e.for_admin != 1";


    if($userType != UT_ADMIN and $isAdmin == 1){
        $userTypeRole = $db->FetchToArray("role_user_type","role_id","user_type_id = '{$userType}'");
        $userTypeRoleIds = (count($userTypeRole) > 0 ) ? implode(",",$userTypeRole) : "-1";
        $sql_where .= " and e.role_id in ($userTypeRoleIds)";
    } else {
        $sql_where = "WHERE 1=1";
    }

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
}elseif($action == 'add'){		

	$date = date('Y-m-d H:i:s');
	$user_id = $_SESSION['user_id'];
	$userType = isset($_POST['user_type']) ? $_POST['user_type'] : array();
	$_POST['created_on'] = $date;
	$_POST['updated_on'] = $date;
	$_POST['created_by'] = $user_id;
	$_POST['updated_by'] = $user_id;
    //$_POST['role_name'] = Utility::ucWordWithBracket($_POST['role_name']);
	
	$counrole = $db->FunctionFetch($table, 'count', 'role_id',"role_name='{$_POST['role_name']}'");
	if($counrole > 0){
		echo json_encode(array('success' => false, 'msg'=>'Role Already Present.','title' => 'Error','msg_class' => 'error'));		
		exit();
	}

	if(!array_key_exists("tableid",$_POST) || count($_POST['tableid']) <= 0 ) {
		echo json_encode(array('success' => false, 'msg'=>'Please select atleast one permission.','title' => 'Alert','msg_class' => 'error'));
		exit();
	}
    $role_id = $db->Insert($table, $_POST, 1);
    if(count($userType) > 0){
        foreach($userType as $userTypeId){
            $addData = array(
                "user_type_id" => $userTypeId,
                "role_id" => $role_id,
            );
            $db->Insert("role_user_type",$addData);
        }
    }
    //Utility::addChild($role_id,$_POST['parent_id'],"role");

	$data['created_on'] = $date;
	$data['created_by'] = $user_id;
	$data['updated_on'] = $date;
	$data['updated_by'] = $user_id;


	if(count($_POST['tableid']) > 0 ) {
		foreach($_POST['tableid'] as $key=>$value) {
			
			$data['perm_id'] = $key;
			$data['role_id'] = $role_id;
			$data['auth'] = 1;
			$data['date_time'] = $date;
			
			$result_id = $db->Insert('role_panel_permission', $data);
		}
	}
	echo json_encode(array('success'=>true, 'msg'=>"{$_POST['role_name']} role's permissions saved!",'title' => 'Successful','msg_class' => 'success'));
}elseif($action == 'edit'){

    $_POST = $db->FilterParameters($_POST);

    $date = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'];
    $role_id = $_POST['role_id'];
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : array();

    if(count($userType) > 0){
        $db->DeleteWhere("role_user_type","role_id = '{$role_id}'");
        foreach($userType as $userTypeId){
            $addData = array(
                "user_type_id" => $userTypeId,
                "role_id" => $role_id,
            );
            $db->Insert("role_user_type",$addData);
        }
    }

    $_POST['update_on'] = $date;
    $_POST['update_by'] = $user_id;

    $id = $db->FilterParameters($_POST[$table_id]);
    /*
    if(count($_POST['tableid']) <= 0 ) {
        echo json_encode(array('error' => 'true', 'msg'=>'Please select atleast one permission.'));
        exit();
    }
    */
    $ex_auth = array();

    $sql_ex_auth = "select perm_id, auth from role_panel_permission where role_id='".$role_id."' ";
    $result_ex_auth = mysql_query($sql_ex_auth);


    if(mysql_num_rows($result_ex_auth) > 0 ){
        while($row_ex_auth = mysql_fetch_object($result_ex_auth)) {
            $ex_auth[] = $row_ex_auth->perm_id;
        }
    }

    //print_r($ex_auth);
    //exit;
    //delete auth which not in tableid
    //if value not find in current request then delete  it from table
    foreach($ex_auth as $val) {
        if(!array_key_exists($val, $_POST['tableid'])) {
            $result = $db->Query("delete from role_panel_permission where role_id='".$role_id."' and perm_id='".$val."' ");
        }
    }

    if(count($_POST['tableid']) > 0 ) {

        $data = array();

        $data['created_on'] = $date;
        $data['created_by'] = $user_id;
        $data['updated_on'] = $date;
        $data['updated_by'] = $user_id;

        foreach($_POST['tableid'] as $key=>$value) {
            if(!in_array($key, $ex_auth)) {

                $data['perm_id'] = $key;
                $data['role_id'] = $role_id;
                $data['auth'] = 1;
                $data['date_time'] = $date;

                $result_id = $db->Insert('role_panel_permission', $data);
            }
        }
    }

    echo json_encode(array('success'=>true, 'msg'=>"{$_POST['role_name']} role's permissions updated!",'title' => 'Successful','msg_class' => 'success'));

}elseif($action == 'delete'){

	$ids = $db->FilterParameters($_POST['id']);
	if(is_array($ids) && count($ids) > 0) {
		$ids = implode(',',$ids);
	}
    $condition = "role_id in ($ids)";
//    $child = Utility::hasChild($ids,"role");
//    if($child){
//        echo json_encode(array('success'=>false, 'title' => 'Unsuccessful', 'msg' => "You Can't Delete this role because it has child"));
//        exit;
//    }

	$delete_user_role_perm = $db->DeleteWhere('user_panel_permission', $condition);
	$delete_role_perm = $db->DeleteWhere('role_panel_permission', $condition);
	$delete_role = $db->DeleteWhere($table, $condition);
	//$delete_role_tree = Utility::deleteLeafNode($ids,"role");

	echo json_encode(array('success'=>true, 'title' => 'Successful', 'msg' => 'Role along with permissions deleted successfully'));
} elseif ($action == 'transferchild'){
    $data = $db->FilterParameters($_POST);
    $roleId = $data['role_id'];
    $parentRoleId = $data['parent_role_id'];
    $roleChild = Utility::getChild($roleId,"role");
    //$roleChild = array_diff($roleChild, array("$roleId"));
    $roleChildIds = (count($roleChild) > 0 ) ? implode(",",$roleChild) : "-1";
    $db->UpdateWhere("role_master",array("parent_id"=>$parentRoleId),"role_id in ($roleChildIds)");
    $transfer = Utility::moveSubTree($roleId,$parentRoleId,"role");
    if($transfer != ''){
        echo json_encode(array('success'=>true, 'title' => 'Successful', 'msg' => 'Child Transfer Successfully'));
    }

}
include_once './footer.php';