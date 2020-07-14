<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "campaign_master";
$table_id = 'campaign_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table cm";

    $columns = array(
        'cm.campaign_id','cm.campaign_code','cm.campaign_name','cm.phone_number',
        'group_concat(catm.category_name SEPARATOR ", ") as category_name','cm.start_date','cm.end_date','vm.vendor_name',
        'ctm.campaign_type_name','cm.amount','cm.description','cm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by',
        'cm.is_active','null as prospect_count'

    );
    $seach_columns = array(
        'cm.phone_number','cm.start_date','cm.end_date',
        'cm.description','cm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by',
    );

    $joins = " left join admin_user au on (au.user_id = cm.created_by)";
    $joins .= " left join vendor_master vm on (vm.vendor_id = cm.vendor_id)";
    $joins .= " left join campaign_category cc on (cc.campaign_id = cm.campaign_id)";
    $joins .= " left join category_master catm on (catm.category_id = cc.category_id)";
    $joins .= " left join campaign_type_master ctm on (ctm.campaign_type_id = cm.campaign_type_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1";
    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.campaign_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.phone_number" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.campaign_code" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "catm.category_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_7']) && $_GET['sSearch_7'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "pm.campaign_code" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_7'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    include_once 'campaign_filter.php';

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
//        echo $sql_order;
        $sql_order = substr_replace( $sql_order, "", -2 );
    }

    // group by
    // If you are not using then put it blank otherwise mention it
    $sql_group = "group by cm.campaign_id";

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
        $row['start_date'] = ($row['start_date'] != '0000-00-00' && $row['start_date'] != '') ? core::YMDToDMY($row['start_date']) : "";
        $row['end_date'] = ($row['end_date'] != '0000-00-00' && $row['end_date'] != '') ? core::YMDToDMY($row['end_date']) : "";
        $row['created_at'] = ($row['created_at'] != '0000-00-00 00:00:00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
        $campaignId=$row['campaign_id'];
        $row['prospect_count'] = $db->FunctionFetch("prospect_master","count","campaign_id","campaign_id=$campaignId");
        $response['aaData'][] = $row;
    }

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
            $existingCheckR = $db->Fetch("prospect_master",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete campaign because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = ' campaign (s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $campaignId = isset($data['campaign_id']) ? $data['campaign_id'] : "";
    if(isset($campaignId) and $campaignId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and campaign_id != '{$campaignId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email'");
    }
    $emailCount = $db->CountResultRows($emailRes);
    if($emailCount > 0){
        $flag = "false";
    }
    echo $flag;
} elseif ($action == 'getparnter'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("concat(first_name,' ',last_name)","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("campaign_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}
include_once 'footer.php';
