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
$db->CharactersetUTF8();

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
$objPHPExcel->getProperties()->setCreator("SIDBI CRM")
    ->setLastModifiedBy("SIDBI CRM")
    ->setTitle("Activity Log")
    ->setSubject("Activity Log List")
    ->setDescription("Activity Log As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);
$table = "activity_log";
$table_id = 'activity_log_id';
$user_id = $ses->Get("user_id");

$table = "activity_log as al";

$actionName = (isset($_GET['filter']['action_name']) && !empty($_GET['filter']['action_name'])) ? $db->FilterParameters($_GET['filter']['action_name']) : '';
$moduleName = (isset($_GET['filter']['module']) && !empty($_GET['filter']['module'])) ? $db->FilterParameters($_GET['filter']['module']) : '';
$userId = (isset($_GET['filter']['user_id']) && !empty($_GET['filter']['user_id'])) ? intval($db->FilterParameters($_GET['filter']['user_id'])) : '';
$logDate = (isset($_GET['filter']['log_date']) && !empty($_GET['filter']['log_date'])) ? $db->FilterParameters($_GET['filter']['log_date']) : '';

$columns = array('al.activity_log_id','al.module','al.action_name','al.description','al.log_date',
    'concat(au.first_name," ",au.last_name) as user_name',
    'al.user_browser','al.user_platform','al.device_type','al.user_ip',
);

$joins = " left join admin_user as au on al.user_id = au.user_id";

// filtering
$sql_where = "where 1 = 1";

if($actionName != ''){
    $sql_where .= " and al.action_name = '{$actionName}'";
}

if($moduleName != ''){
    $sql_where .= " and al.module = '{$moduleName}'";
}

if($userId != ''){
    $sql_where .= " and al.user_id = '{$userId}'";
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

$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order}";
// 	echo $sql;exit;

$res = $db->Query($sql);

//$table = "<table>";
if($db->CountResultRows($res) > 0){

    $result_cols = array();

    $y = 1;

// 	$table .= "<tr>";

    $ignore_columns = array('activity_log_id');
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

                if($key == 'log_date'){
                    $row['log_date'] = ($row['log_date'] != '0000-00-00' && $row['log_date'] != '') ? core::YMDToDMY($row['log_date'],true) : "";
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row['log_date']);

                }
                else{
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
$file_name = 'activity_log_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');