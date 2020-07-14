<?php
include_once 'header.php';

$action = $db->FilterParameters($_GET['act']);

$table = 'user_panel_permission';
$table_id = 'up_id';

if($action == 'fetch'){
	
	$table = "admin_user as au";
	
	$columns = array('au.user_id','rm.role_name','concat(au.first_name," ",au.last_name) as name','au.email');
	$seach_columns = array('rm.role_name','concat(au.first_name," ",au.last_name)','au.email');
	
	$joins = " left join role_master as rm on rm.role_id = au.role_id";
	
	$sql_where = "where user_id in (select usermaster_id from user_panel_permission group by usermaster_id) ";

    if($userType == UT_TC and $isAdmin == 1){
        $sql_where .= " and au.user_type = ".UT_TC."";
    } elseif($userType == UT_BD and $isAdmin == 1){
        $sql_where .= " and au.user_type = ".UT_BD."";
    } elseif($userType == UT_KC and $isAdmin == 1){
        $sql_where = "WHERE au.user_type = ".UT_KC."";
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
	
//	Core::PrintArray($_POST);exit;
	$_POST = $db->FilterParameters($_POST);
	
	$date= date('Y-m-d H:i:s');
	$login_id = $_SESSION['user_id'];
	$role_id = $_POST['role_id'];
	
	$_POST['date_time'] = $date; 
	$_POST['created_on'] = $date;
	$_POST['updated_on'] = $date;
	$_POST['created_by'] = $login_id;
	$_POST['updated_by'] = $login_id;
	
	if(count($_POST['user_names']) <= 0 ) {
		echo json_encode(array('success' => false, 'msg'=>'Please select Users name.','title' => 'Error','msg_class' => 'error'));
		exit();
	}
	if(count($_POST['tableid']) <= 0 ) {
		echo json_encode(array('success' => false, 'msg'=>'Please select atleast one permission.','title' => 'Error','msg_class' => 'error'));
		exit();
	}
	
	$data['created_on'] = $date;
	$data['created_by'] = $login_id;
	$data['updated_on'] = $date;
	$data['updated_by'] = $login_id;
	
	if(count($_POST['user_names']) > 0 and count($_POST['tableid']) > 0 ) {
		foreach($_POST['user_names'] as $userid){
			foreach($_POST['tableid'] as $key=>$value) {
				
				$data['perm_id'] = $key;
				$data['role_id'] = $role_id;
				$data['usermaster_id'] = $userid;
				$data['auth'] = 1;
				$data['date_time'] = $date;
					
				$result_id = $db->Insert('user_panel_permission', $data);
				
// 				$result_id = $db->Query("insert into user_panel_permission (perm_id, role_id, usermaster_id, auth, date_time) values('".$key."', '".$_POST['role_id']."', '".$userid."', '1', '".date('Y-m-d H:i:s')."' )");
			}
		}	
	}	
	
	echo json_encode(array('success'=>true, 'msg'=>"User permissions saved!",'title' => 'Successful','msg_class' => 'success'));
}elseif($action == 'edit'){
	
	$_POST = $db->FilterParameters($_POST);
	
	$user_id = $_POST['usermaster_id'];
	
	$login_id = $_SESSION['user_id'];
	$role_id = $_POST['role_id'];
	
	$date= date('Y-m-d H:i:s');
	
	$data['date_time'] = $date; 
	$data['created_on'] = $date;
	$data['created_by'] = $login_id;
	$data['updated_on'] = $date;
	$data['updated_by'] = $login_id;
	
//	Core::PrintArray($_POST);exit;
	//$id = $db->FilterParameters($_POST[$table_id]);
	/*
	if(count($_POST['tableid']) <= 0 ) {
		echo json_encode(array('error' => 'true', 'msg'=>'Please select atleast one permission.'));		
		exit();
	}
	*/
	$ex_auth = array();
	
	$sql_ex_auth = "select perm_id, auth from user_panel_permission where usermaster_id='".$_POST['usermaster_id']."' ";
	$result_ex_auth = mysql_query($sql_ex_auth);
	
	
	if(mysql_num_rows($result_ex_auth) > 0 ){
		while($row_ex_auth = mysql_fetch_object($result_ex_auth)) {
			$ex_auth[] = $row_ex_auth->perm_id;
		}
	}
	
	//delete auth which not in tableid 
	//if value not find in current request then delete  it from table
	foreach($ex_auth as $val) {
		if(!array_key_exists($val, $_POST['tableid'])) {
			$result = $db->Query("delete from user_panel_permission where usermaster_id='".$_POST['usermaster_id']."' and perm_id='".$val."' ");
		}
	}
	
// 	Core::PrintArray($ex_auth);
// 	Core::PrintArray($_POST['tableid']);
// 	exit;
	
	if(count($_POST['tableid']) > 0 ) {			
		foreach($_POST['tableid'] as $key=>$value) {
			if(!in_array($key, $ex_auth)) {
				
				$data['perm_id'] = $key;
				$data['role_id'] = $role_id;
				$data['usermaster_id'] = $user_id;
				$data['auth'] = 1;
				$data['date_time'] = $date;
					
				$result_id = $db->Insert('user_panel_permission', $data);
			} 				
		}			
	}
	echo json_encode(array('success'=>true, 'msg'=>"User permissions updated!",'title' => 'Successful','msg_class' => 'success'));
}elseif($action == 'delete'){
	
	$ids = $db->FilterParameters($_POST['id']);
	if(is_array($ids) && count($ids) > 0) {
		$ids = implode(',',$ids);
	}
	
	$condition = "usermaster_id IN ($ids)";
	$result = $db->DeleteWhere('user_panel_permission', $condition);
	
	echo json_encode(array('success'=>true, 'title' => 'Successful', 'msg' => 'User permissions deleted successfully'));
}
elseif($action == 'getrole_data'){
	
	$role_data = '';
	
	$_POST = $db->FilterParameters($_POST);
	
	$section_type = $db->GetEnumvalues("user_perm_master","section_name");

		$i = 0;
	  foreach($section_type as $section_name) {				  
        $role_data .='<div class="span5 widget-container-span ui-sortable">
						<div class="widget-box">
							<div class="widget-header">
								<h5>'.ucfirst(strtolower($section_name)).' Options</h5>
								<div class="widget-toolbar">
									<a href="#" data-action="collapse">
										<i class="icon-chevron-up"></i>
									</a>
								</div>
							</div>
							<div class="widget-body">
								<div style="display: block;" class="widget-body-inner">
									<div class="widget-main">
										<label>
											<input type="checkbox" value="'.$i.'" class="chk_select">
											<span class="lbl"> Select All </span>
										</label>
										<div id="div_'.$i.'">
											<table class="table">';
        
		$res_page = $db->Query("select * from user_perm_master where section_name='".$section_name."' group by page_name order by display_order");
		if(mysql_num_rows($res_page) > 0) {
			while($row_page = mysql_fetch_object($res_page)) {
                   		
           		$role_data .='<tr class="alternate">
									<td width="30%">'.$row_page->page_name.'</td>
									<td>';

					$res_perm = $db->Query("select * from user_perm_master where page_name='".$row_page->page_name."' order by perm_id");
					if(mysql_num_rows($res_perm) > 0) {
						while($row_perm = mysql_fetch_object($res_perm)) {		
							$sql = "select * from role_panel_permission where role_id='".$_POST['role_id']."' and perm_id='".$row_perm->perm_id."' and auth='1' ";
							$res_check = $db->Query($sql);
							if(mysql_num_rows($res_check) > 0 ) {
								$chk = 'checked="checked"';
							} else {
								$chk = '';
							}
                            $role_data .= '<label>
                            				<input type="checkbox" '.$chk.'  name="tableid['.$row_perm->perm_id.']" id="checkbox_'.$row_perm->perm_id.'" value="yes" title="'.$row_perm->permission_desc.'" />
											<span class="lbl">'.$row_perm->permission_label.'</span>
										</label>';
						} 
					} 
                   $role_data .='</td>
             				</tr>';
        } }
		
						$role_data .='</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>';
						
			$i++;
         
        }
		echo $role_data;
}
include_once 'footer.php';