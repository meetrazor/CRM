<?php
@session_start();
include_once './header.php';

$table = 'user_perm_master';
$table_id = 'perm_id';

$action = $_GET['act'];
$action = $db->FilterParameters($action);

if($action == 'fetch'){
	
	$table = "$table up";
		
	$columns = array(
		'perm_id','section_name','page_name','realpage_name','permission_label','permission_desc','display_order',
		'DATE_FORMAT(up.updated_on,"%d-%m-%Y") as rcd_updated_on','concat(au1.first_name," ",au1.last_name) as rcd_updated_by',
	);
	$seach_columns = array(
		'section_name','page_name','realpage_name','permission_label'
	);
	
	$joins = "left join admin_user au1 on (au1.user_id = up.updated_by)";
	
	// filtering
	$sql_where = "";
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
		$sql_where = "where " . $seach_condition;
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
		
	$data = $db->FilterParameters($_POST);
	
	$date = date('Y-m-d H:i:s');
	$user_id = $_SESSION['user_id'];
	
	$data['created_on'] = $date;
	$data['updated_on'] = $date;
	$data['created_by'] = $user_id;
	$data['updated_by'] = $user_id;
	
	$data['page_name'] = ucwords($data['page_name']);
	$data['permission_label'] = ucwords($data['permission_label']);
	
	$result = $db->Insert($table, $data, 1);
	
	$response['success'] = true;
	$response['title'] = 'Successful';
	$response['msg'] = 'Record added!';
	
	echo json_encode($response);	
}elseif($action == 'delete'){
    $ids = $db->FilterParameters($_POST['id']);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;

    $condition = "$table_id in ('$id')";

    $result = $db->DeleteWhere($table, $condition);
    $response['title'] = "Records Deleted";
    $response['msg'] = ' record(s) deleted successfully';
    echo json_encode($response);

}
include_once './footer.php';