<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "rss_feed";
$table_id = 'rss_feed_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table rf";

    $columns = array(
        'rf.rss_feed_id','cm.company_name','catm.category_name','rf.title','rf.description','rf.short_description',
        'GROUP_CONCAT(tm.tag_name SEPARATOR ", ") as tags','rf.pub_date','rf.created_at',
        'concat(au.first_name," ",au.last_name) as created_by','is_direct','is_changed','rf.link',

    );
    $seach_columns = array(
        'rf.rss_feed_id','cm.company_name','catm.category_name','rf.title','rf.link','rf.pub_date','pm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by'
    );

    $joins = " left join admin_user au on (au.user_id = rf.created_by)";
    $joins .= " left join company_master cm on (cm.company_id = rf.company_id)";
    $joins .= " left join category_master catm on (catm.category_id = rf.category_id)";
    $joins .= " left join rss_feed_tags rt on (rt.rss_feed_id = rf.rss_feed_id)";
    $joins .= " left join tag_master tm on (tm.tag_id = rt.tag_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1";
    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.company_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'catm.category_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'rf.title' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'concat(au.first_name," ",au.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
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
//        echo $sql_order;
        $sql_order = substr_replace( $sql_order, "", -2 );
    }

    // group by
    // If you are not using then put it blank otherwise mention it
    $sql_group = " group by rf.rss_feed_id";

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
        $row['title'] = core::StripAllSlashes($row['title']);
        $row['description'] = "<span>".Utility::getCutString($row['description'])."</span>";
        $row['short_description'] = ($row['short_description'] != '') ? "<span>".Utility::getCutString($row['short_description'])."</span>" : "";
        $row['pub_date'] = ($row['pub_date'] != '0000-00-00' && $row['pub_date'] != '') ? core::YMDToDMY($row['pub_date'],true) : "";
        $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
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
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $condition = "$table_id in ('$id')";
    $db->DeleteWhere($table,$condition);
    $db->DeleteWhere("rss_feed_tags",$condition);
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' records (s) deleted successfully';
    echo json_encode($response);
}elseif($action == 'update'){
    $data = $db->FilterParameters($_POST,array("short_description"));
    $udpate = $db->Update($table, array("short_description"=>$data['short_description']), $table_id, $data['rss_feed_id']);
    $response['success'] = true;
    $response['title'] = "Records Updated";
    $response['msg'] = ' Record(s) Updated successfully';
    echo json_encode($response);
}elseif($action == 'deleteimage'){
    $docId = $_POST['id'];
    $old_image = $db->FetchCellValue("rss_feed_image","filename","rss_feed_image_id = {$docId}");
    $uploadpath = NEWS_IMAGE_PATH_ABS;
    $ref_name_jpg = $uploadpath .$old_image;
    if(file_exists($ref_name_jpg)){
        @unlink($ref_name_jpg);
        $db->DeleteWhere("rss_feed_image","rss_feed_image_id = {$docId}");
    }
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = ' record (s) deleted successfully';
    echo json_encode($response);
}
include_once 'footer.php';
