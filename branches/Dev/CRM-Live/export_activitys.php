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
$objPHPExcel->getProperties()->setCreator("alliance_partner")
    ->setLastModifiedBy("alliance_partner")
    ->setTitle("transaction")
    ->setSubject("transaction List")
    ->setDescription("transaction As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);

// the table being queried
$table = "activity_master";
$table_id = 'activity_id';
$user_id = $ses->Get("user_id");

$activityCondition = Utility::activityCondition();


$table = "$table am";

$campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';
$prospectId = (isset($_GET['prospect_id']) && !empty($_GET['prospect_id'])) ? intval($db->FilterParameters($_GET['prospect_id'])) : '';
$dispositionId = (isset($_GET['disposition_id']) && !empty($_GET['disposition_id'])) ? intval($db->FilterParameters($_GET['disposition_id'])) : '';

$columns = array(
    'am.activity_id','am.activity_name','concat(pm.first_name," ",pm.last_name) as prospect_name',
    'GROUP_CONCAT(pc.contact SEPARATOR  ",") as mobile_no','catm.category_name','pm.amount',
    's.state_name','c.city_name', 'dm.disposition_name',
    'am.remarks','cm.campaign_name',
    'concat(au.first_name," ",au.last_name) as telecaller','pm.created_at as prospect_created_at','concat(am.start_date_time," ",call_time) as start_date_time',
    'am.duration','dm.is_close','dm.is_callback','pm.prospect_id','am.is_latest'
);
$seach_columns = array(
    'pm.activity_name','cm.campaign_name'
);

$joins = " left join prospect_master as pm on (am.type_id = pm.prospect_id)";
$joins .= " left join prospect_contact as pc on (pc.prospect_id = pm.prospect_id)";
$joins .= " left join state as s on (pm.state_id = s.state_id)";
$joins .= " left join city as c on (c.city_id = pm.city_id)";
$joins .= " left join category_master as catm on (catm.category_id = pm.category_id)";
$joins .= " left join campaign_master as cm on (cm.campaign_id = pm.campaign_id)";
$joins .= " left join disposition_master as dm on (dm.disposition_id = am.disposition_id)";
$joins .= " left join admin_user as au on (am.created_by = au.user_id)";

// filtering
// filtering
$sql_where = "WHERE 1=1 and am.disposition_id is not null and ".$activityCondition;
$sql_having = "having 1=1";
if($campaignId != ''){
    $sql_where .= " and cm.campaign_id = '{$campaignId}'";
}

if($prospectId != ''){
    $sql_where .= " and pm.prospect_id = '{$prospectId}'";
}

if($dispositionId != ''){
    $sql_where .= " and dm.disposition_id = '{$dispositionId}'";
}

if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'pm.activity_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}


if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'concat(pm.first_name," ",pm.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}

if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'dm.disposition_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}

if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'cm.campaign_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}

if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'concat(au.first_name," ",au.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}


if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'mobile_no' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_having .= " AND " . $seach_condition;
}


include_once 'control/activity_filter.php';
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
$sql_group = " group by pm.prospect_id";

// paging
$sql_limit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
{
    $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
}

$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_having} {$sql_order}";

// 	echo $sql;exit;

$res = $db->Query($sql);

//$table = "<table>";
if($db->CountResultRows($res) > 0){

    $result_cols = array();

    $y = 1;

// 	$table .= "<tr>";

    $ignore_columns = array('activity_id','prospect_id','is_close','is_latest','is_callback');
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
$file_name = 'transaction_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');