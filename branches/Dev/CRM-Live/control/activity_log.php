<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

$table ="activity_log";
$table_id ="activity_log_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "activity_log as al";

    $actionName = (isset($_GET['filter']['action_name']) && !empty($_GET['filter']['action_name'])) ? $db->FilterParameters($_GET['filter']['action_name']) : '';
    $moduleName = (isset($_GET['filter']['module']) && !empty($_GET['filter']['module'])) ? $db->FilterParameters($_GET['filter']['module']) : '';
    $userId = (isset($_GET['filter']['user_id']) && !empty($_GET['filter']['user_id'])) ? $db->FilterParameters($_GET['filter']['user_id']) : '';
    $logDate = (isset($_GET['filter']['log_date']) && !empty($_GET['filter']['log_date'])) ? $db->FilterParameters($_GET['filter']['log_date']) : '';

    $columns = array('al.activity_log_id','al.module','al.action_name','al.description','al.log_date',
        'concat(au.first_name," ",au.last_name) as user_name',
        'al.user_browser','al.user_platform','al.device_type','al.user_ip',
        );

    $joins = " left join admin_user as au on al.user_id = au.user_id";

    // filtering
    $sql_where = "where 1 = 1";

    if($actionName != ''){
        $sql_where .= " AND (al.action_name in ('" . implode("','", $actionName)."'))";
    }

    if($moduleName != ''){
        $sql_where .= " AND (al.module in ('" . implode("','", $moduleName)."'))";
    }

    if($userId != ''){
        $sql_where .= " AND (al.user_id in (" . implode(',', $userId)."))";
    }

    if($logDate != ''){

        list($fromDate,$toDate) = explode(' to ',$logDate);
        $fromDate = core::DMYToYMD($fromDate);
        $toDate = core::DMYToYMD($toDate);
        if(strtotime($fromDate) == strtotime($toDate)){
            $sql_where .= " && DATE_FORMAT(al.log_date, '%Y-%m-%d') = '{$fromDate}'";
        } else {
            $sql_where .= " && (DATE_FORMAT(al.log_date, '%Y-%m-%d') >= '{$fromDate}' AND DATE_FORMAT(al.log_date, '%Y-%m-%d') <= '{$toDate}')";
        }

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
        $row['log_date'] = core::YMDToDMY($row['log_date'],true);
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);

}
include_once 'footer.php';