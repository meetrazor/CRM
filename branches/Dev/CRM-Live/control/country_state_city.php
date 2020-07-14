<?php
include_once 'header.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table = "city";
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){
	
	// the table being queried
	$table = "city ct";
	
	// the columns to be filtered, ordered and returned
	// must be in the same order as displayed in the table
	
	$columns = array('ct.city_id','c.country_name','s.state_name','tm.tier_name','ct.city_name','s.state_id','c.country_id','tm.tier_id');
	$seach_columns = array('ct.city_name','s.state_name','c.country_name','tm.tier_name');
	
	$joins = "left join state s on (s.state_id=ct.state_id)";
	$joins .= "left join country c on (c.country_id=ct.country_id)";
	$joins .= "left join tier_master tm on (tm.tier_id=ct.tier_id)";

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
		$sql_where = " and " . $seach_condition;
	}

    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'c.country_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 's.state_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'tm.tier_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'ct.city_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
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
		$row['city_name'] = stripslashes($row['city_name']);
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

	$city = ucwords($data['city_name']);
	$state = ucwords($data['state_name']);
	$country = ucwords($data['country_name']);
	$tier = ucwords($data['tier_name']);


	$country_id = 0;
	$state_id = 0;
	$city_id = 0;
	
	$country_res = $db->FetchRowWhere('country', array('country_id'),"country_name='$country'");
	$country_count = $db->CountResultRows($country_res);
	if($country_count > 0){
		$country_row = $db->MySqlFetchRow($country_res);
		$country_id = $country_row['country_id'];
	}else{
			
		$country_data['country_name'] = $country;
		$country_id = $db->Insert('country', $country_data, true);
	}
	
	$state_res = $db->FetchRowWhere('state', array('state_id'),"state_name='$state' && country_id=$country_id");
	$state_count = $db->CountResultRows($state_res);
	if($state_count > 0){
			
		$state_row = $db->MySqlFetchRow($state_res);
		$state_id = $state_row['state_id'];
	}else{
			
		$state_data['country_id'] = $country_id;
		$state_data['state_name'] = $state;
	
		$state_id = $db->Insert('state', $state_data, true);
	}

    $tier_res = $db->FetchRowWhere('tier_master', array('tier_id'),"tier_name='$tier'");
	$tier_count = $db->CountResultRows($tier_res);
	if($tier_count > 0){

		$tier_row = $db->MySqlFetchRow($tier_res);
		$tier_id = $tier_row['tier_id'];
	}else{
		$tier_data['tier_name'] = $tier;

		$tier_id = $db->Insert('tier_master', $tier_data, true);
	}
    $condition = "city_name='$city' && state_id=$state_id && country_id=$country_id";
    if(isset($_POST['city_id']) && $_POST['city_id'] != ''){
        $condition = "city_name='$city' && state_id=$state_id && country_id=$country_id and city_id != {$_POST['city_id']}";
    }
	$city_res = $db->FetchRowWhere('city', array('city_id'),$condition);
	$city_count = $db->CountResultRows($city_res);
	if($city_count > 0){
	
		$city_row = $db->MySqlFetchRow($city_res);
		$city_id = $city_row['city_id'];
		
		$response['success'] = 'exist';
		$response['title'] = 'Alert!';
		$response['msg'] = "Record already exist!";
	}else{
	
		$city_data['country_id'] = $country_id;
		$city_data['state_id'] = $state_id;
		$city_data['city_name'] = $city;
		$city_data['tier_id'] = $tier_id;

		if(isset($_POST['city_id']) && $_POST['city_id'] != ''){
			$post_city_id = $data['city_id'];
			$condition = "city_id=$post_city_id";
			$city_id = $db->UpdateWhere('city', $city_data, $condition);
		}else{
			
			$city_id = $db->Insert('city', $city_data, true);
		}
            $response['success'] = 'true';
            $response['title'] = 'Successful';
            $response['msg'] = "Record saved successfully!";
		
	}
	echo json_encode($response);
}elseif($action == 'delete'){
	
	$ids = $db->FilterParameters($_POST['id']);
	$id = (is_array($ids)) ? implode(',', $ids) : $ids;
	
	$condition = "city_id in ($id)";
	
	$result = $db->DeleteWhere($table, $condition);
	$response['title'] = "Records Deleted";
	$response['msg'] = 'Country deleted successfully';
	echo json_encode($response);
		
}elseif($action == 'get_country_states'){
	
	$id = $db->FilterParameters($_POST['id']);
	
	$condition = "";
	if($id != ''){
		
		$condition = "country_id='$id'"; 
	}
	$state_dd = $db->CreateOptions('html', 'state', array('state_id','state_name'), null, array('state_name' => 'asc'), $condition);
	
	if(isset($_POST['empty_opt'])){
		$state_dd = "<option value=''></option>" . $state_dd;
	}else{
		$state_dd = Core::PrependNullOption($state_dd);
	}
	echo $state_dd;
	
}elseif($action == 'get_country_cities'){
	
	$id = $db->FilterParameters($_POST['id']);
	
	$condition = "";
	if($id != ''){
		
		$condition = "country_id='$id'"; 
	}
	$city_dd = $db->CreateOptions('html', 'city', array('city_id','city_name'), null, array('city_name' => 'asc'), $condition);
	
	if(isset($_POST['empty_opt'])){
		$city_dd = "<option value=''></option>" . $city_dd;
	}else{
		$city_dd = Core::PrependNullOption($city_dd);
	}
	echo $city_dd;
}elseif($action == 'get_state_cities'){
	
	$id = $db->FilterParameters($_POST['id']);
	
	$condition = "";
	if($id != ''){
		
		$condition = "state_id='$id'"; 
	}
	$city_dd = $db->CreateOptions('html', 'city', array('city_id','city_name'), null, array('city_name' => 'asc'), $condition);
	
	if(isset($_POST['empty_opt'])){
		$city_dd = "<option value=''></option>" . $city_dd;
	}else{
		$city_dd = Core::PrependNullOption($city_dd);
	}
	echo $city_dd;
	
}elseif($action == 'get_city_locality'){

	$id = $db->FilterParameters($_POST['id']);
	$subLocality = isset($_POST['sub_locality']) ? $db->FilterParameters($_POST['sub_locality']) : "";

	$condition = "";
	if($id != ''){

		$condition = "city_id='$id'";
	}
//    if($subLocality != ''){
//
//		$condition .= " and sub_locality_name='$subLocality'";
//	}
    $subLocalityDd = $db->CreateOptions('html', 'sub_locality', array('sub_locality_id','sub_locality_name'), $subLocality, array('sub_locality_name' => 'asc'), $condition);

	if(isset($_POST['empty_opt'])){
		$subLocalityDd = "<option value=''></option>" . $city_dd;
	}else{
        $subLocalityDd = Core::PrependNullOption($subLocalityDd);
	}
	echo $subLocalityDd;

}elseif('option_add_country' === $action){
	
	$table = 'country';
	$table_id = 'country_id';
	
	if(isset($_POST['term']) && $_POST['term']!=''){

		$data['country_name'] = ucwords($_POST['term']);
		
		$exist_count = $db->FunctionFetch($table, 'count', $table_id,"country_name='{$data['country_name']}'");
		
		if($exist_count == 0){
		
			$id = $db->Insert($table, $data, 1);
		
			$response_res = $db->FetchRow($table, $table_id, $id);
			$row = $db->MySqlFetchRow($response_res);
		
			$response['value'] = $row['country_id'];
			$response['text'] = $row['country_name'];
			echo json_encode($response);
		}
	}
}elseif('option_add_state' === $action){
	
	$table = 'state';
	$table_id = 'state_id';
	
	if(isset($_POST['term']) && $_POST['term']!='' && isset($_POST['country_id']) && $_POST['country_id']!=''){
	
		$data['state_name'] = ucwords($_POST['term']);
		$data['country_id'] = $_POST['country_id'];
		
		$exist_count = $db->FunctionFetch($table, 'count', $table_id,"state_name='{$data['state_name']}' && country_id='{$data['country_id']}'");
		
		if($exist_count == 0){
		
			$id = $db->Insert($table, $data, 1);
		
			$response_res = $db->FetchRow($table, $table_id, $id);
			$row = $db->MySqlFetchRow($response_res);
		
			$response['value'] = $row['state_id'];
			$response['text'] = $row['state_name'];
			echo json_encode($response);
		}
	}
}elseif('option_add_city' === $action){
	
	$table = 'city';
	$table_id = 'city_id';
	
	if(isset($_POST['term']) && $_POST['term']!='' && isset($_POST['country_id']) && $_POST['country_id']!='' && isset($_POST['state_id']) && $_POST['state_id']!=''){
		
		$data['city_name'] = ucwords($_POST['term']);
		$data['state_id'] = $_POST['state_id'];
		$data['country_id'] = $_POST['country_id'];
		
		$exist_count = $db->FunctionFetch($table, 'count', $table_id,"city_name='{$data['city_name']}' && state_id='{$data['state_id']}' && country_id='{$data['country_id']}'");
		
		if($exist_count == 0){
		
			$id = $db->Insert($table, $data, 1);
		
			$response_res = $db->FetchRow($table, $table_id, $id);
			$row = $db->MySqlFetchRow($response_res);
		
			$response['value'] = $row['city_id'];
			$response['text'] = $row['city_name'];
			echo json_encode($response);
		}
	}
}elseif('all_country' == $action){
	
	$country_dd = $db->CreateOptions('html', 'country', array('country_id','country_name'), null, array('country_name' => 'asc'));
	
	echo $country_dd;
}elseif('all_state' == $action){
	
	$state_dd = $db->CreateOptions('html', 'state', array('state_id','state_name'), null, array('state_name' => 'asc'));
	
	echo $state_dd;
}elseif('all_city' == $action){
	
	$city_dd = $db->CreateOptions('html', 'city', array('city_id','city_name'), null, array('city_name' => 'asc'));
	
	echo $city_dd;
}elseif($action == 'getstate'){
    $countryId = $db->FilterParameters($_POST['country_id']);
    $condition = "";
    if($countryId != ''){
        if(!intval($countryId)){
            $countryId = $db->FetchCellValue("country","country_id","country_name = '{$countryId}'");
        }
        $condition = "country_id='$countryId'";
    }
    if(!intval($countryId)){
    $brand_dd = $db->CreateOptions('html', 'state', array('state_name','state_name'), null, array('state_name' => 'asc'), $condition);
    } else {
        $brand_dd = $db->CreateOptions('html', 'state', array('state_id','state_name'), null, array('state_name' => 'asc'), $condition);
    }
    $brand_dd = Core::PrependNullOption($brand_dd);
    echo $brand_dd;
}
include_once 'footer.php';