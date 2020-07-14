<?php
ini_set('memory_limit', -1);
set_time_limit(0);

include_once 'core/session.php';
$ses = new Session();
$ses ->init();
$user_id = $ses->Get('user_id');
$organizationId = $ses->Get('organization_id');

// Database set it movedb please change it

include_once 'core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once 'core/Db.php';
$db = new Db();
$db->ConnectionOpen();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();



// Including Site Setting
include_once 'core/SiteSettings.php';

include_once 'phpexcel/PHPExcel.php';
$objPHPExcel = new PHPExcel();

$time_stamp = date('d-m-Y H:i:s');
$objPHPExcel->getProperties()->setCreator("alliance_partner")
    ->setLastModifiedBy("alliance_partner")
    ->setTitle("activity")
    ->setSubject("activity List")
    ->setDescription("activity As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);
$table = "activity_master";
$table_id = 'activity_id';
$user_id = $ses->Get("user_id");

    $activityCondition = Utility::activityCondition();


    $table = "$table am";

    $leadId = (isset($_GET['lead_id']) && !empty($_GET['lead_id'])) ? intval($db->FilterParameters($_GET['lead_id'])) : '';
    $statusId = (isset($_GET['status_id']) && !empty($_GET['status_id'])) ? intval($db->FilterParameters($_GET['status_id'])) : '';

    $columns = array(
        'am.activity_id','lm.lead_name','am.remarks','am.activity_type',
        'sm.status_name','am.created_at','concat(cu.first_name," ",cu.last_name) as created_by','lm.lead_id','sm.is_close','am.is_latest','sm.is_callback'


    );
    $seach_columns = array(
        'lm.lead_name','am.remarks'
    );

    $joins = " left join lead_master as lm on (am.type_id = lm.lead_id)";
    $joins .= " left join status_master as sm on (sm.status_id = am.status_id)";
    $joins .= " left join admin_user as cu on (cu.user_id = am.created_by)";

    // filtering

    $activityCondition .= " and am.activity_on = 'lead'";

    $sql_where = "WHERE 1=1 and ".$activityCondition;

    if($leadId != ''){
        $sql_where .= " and lm.lead_id = '{$leadId}'";
    }

    if($statusId != ''){
        $sql_where .= " and sm.status_id = '{$statusId}'";
    }


    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'lm.lead_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'am.activity_type' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
    {
        $seach_condition = "";
        $seach_condition .= 'sm.status_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    include_once 'control/bd_activity_filter.php';

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
    $sql_group = "";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";
// 	echo $sql;exit;

$res = $db->Query($sql);

//$table = "<table>";
if($db->CountResultRows($res) > 0){

    $result_cols = array();

    $y = 1;

// 	$table .= "<tr>";

    $ignore_columns = array('activity_id','lead_id');
    for($i=0, $c='A';$i<mysql_numfields($res);$i++){

        $column_info = mysql_fetch_field($res, $i);
        $result_cols[] = $column_info->name;

        $column_name = $column_info->name;
        if(!in_array($column_name, $ignore_columns)){

            $column_name = str_replace("_", " ", $column_name);
            $column_name = ucwords($column_name);
            $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $column_name);
            $c++;
// 			$table .= "<th>" . $column_info->name . "</th>";
        }

    }
    $objPHPExcel->getActiveSheet()->getStyle("A$y:$c$y")->getFont()->setBold(true);

// 	$table .= '</tr>';
    $y++;
//
    while ($row = $db->MySqlFetchRow($res)) {

// 		Core::PrintArray($row);
// 		$table .= '<tr>';
        $i=0;
        $c='A';
        foreach ($row as $key => $val){

            if(!in_array($key, $ignore_columns)){

                if($key == 'ledger_date'){
                    $row['ledger_date'] = ($row['ledger_date'] != '0000-00-00' && $row['ledger_date'] != '') ? core::YMDToDMY($row['ledger_date'],true) : "";
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row['ledger_date']);

                }else{
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row[$key]);
                }

// 				$table .= "<th>" . $row[$key] . "</th>";
                $i++;
                $c++;
            }
        }
        $y++;
// 		$table .= '</tr>';
    }
//
//// 	echo "<table>" . $table . '</table>';
//// 	exit;
    for($j='A'; $j<$c;$j++){
        $objPHPExcel->getActiveSheet()->getColumnDimension("$j")->setAutoSize(true);
    }
// 	$table .= '</table>';
}

// echo $table;exit;
$file_name = 'activity_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');