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
    ->setTitle("prospector")
    ->setSubject("prospector List")
    ->setDescription("prospector As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);

// the table being queried
$table = "prospect_master";
$table_id = 'prospect_id';
$user_id = $ses->Get("user_id");



$table = "$table pm";

$campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';

$type = isset($_GET['display_type']) ? $db->FilterParameters($_GET['display_type']) : "";

$columns = array(
    'pm.prospect_id','concat(pm.first_name," ",pm.last_name) as prospect_name',
    'null as mobile_no','null as email','cmm.category_name as product',
    'c.city_name',
    'pm.actual_amount',
    'cm.campaign_name',
    'null as telecaller',
    'dm.disposition_name',
    'null as bd_name',
    'null as first_call',
    //'concat(au.first_name," ",au.last_name) as telecaller',
    'am.created_at as last_call',
    'am.duration as last_call_duration',
    'am.duration as total_call_duration',
    'ami.total_transaction','concat(cu.first_name," ",cu.last_name) as created_by','pm.created_at',
    'vm.vendor_name',
    //'pm.is_active','dm.is_close','dm.is_callback','dm.is_meeting'

);
$seach_columns = array(
    'pm.prospect_name','cm.campaign_name'
);




$joins = " left join state as s on (pm.state_id = s.state_id)";
$joins .= " left join city as c on (c.city_id = pm.city_id)";
$joins .= " left join campaign_master as cm on (cm.campaign_id = pm.campaign_id)";
$joins .= " left join category_master as cmm on (cmm.category_id = pm.category_id)";
$joins .= " left join vendor_master as vm on (vm.vendor_id = cm.vendor_id)";
//    $joins .= " left join prospect_users as pu on (pu.prospect_id = pm.prospect_id and is_latest = 1)";
//    $joins .= " left join admin_user as au on (pu.type_id = au.user_id)";
$joins .= " left join (
              SELECT    MAX(activity_id) as max_id,type_id,source_type,count(*) as total_transaction
              FROM      activity_master
              GROUP BY  source_type,type_id
          ) as ami on (ami.type_id = pm.prospect_id and ami.source_type = 'prospect')";
$joins .= " left join activity_master am ON (ami.max_id = am.activity_id)";
$joins .= " left join disposition_master dm ON (dm.disposition_id = am.disposition_id)";
$joins .= " left join admin_user cu ON (cu.user_id = pm.created_by)";

// filtering
// filtering
$sql_where = "WHERE 1=1";

if($type == "today"){
    $sql_where = "WHERE date_format(am.follow_up_date_time,'%Y-%m-%d') >= '".ONLY_DATE_YMD."'";
} elseif ($type == "tomorrow"){
    $sql_where = "WHERE date_format(am.follow_up_date_time,'%Y-%m-%d') < '".ONLY_DATE_YMD."' and date_format(am.follow_up_date_time,'%Y-%m-%d') != '1970-01-01' ";
}

if($ses->Get("user_type") != UT_ADMIN){
    $userType = ($ses->Get("user_type") == UT_BD) ? "bd" : "tc";
    //  $sql_where .= " and pu.user_type = '{$userType}'";
}



if($campaignId != ''){
    $sql_where .= " and cm.campaign_id = '{$campaignId}'";
}
if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'concat(pm.first_name," ",pm.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}


if ( isset($_GET['sSearch_2']) && $_GET['sSearch_2'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'cm.campaign_name' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_2'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}

if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'concat(au.first_name," ",au.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sql_where .= " AND " . $seach_condition;
}


include_once 'control/prospect_filter.php';
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
$historyCallArray = array("call",'remarks','disposition');
if($db->CountResultRows($res) > 0){

    $result_cols = array();

    $y = 1;

// 	$table .= "<tr>";

    $ignore_columns = array('prospect_id');
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
    $lastHeaderColumn = $c;
    $objPHPExcel->getActiveSheet()->getStyle("A$y:$c$y")->getFont()->setBold(true);

// 	$table .= '</tr>';
    $y++;
//
    $maxCount = 0;
    while ($row = $db->MySqlFetchRow($res)) {


// 		$table .= '<tr>';
        $i=0;
        $c='A';
        $prospectId = $row['prospect_id'];
        $totalTransaction = $row['total_transaction'];
        if($totalTransaction > $maxCount){
            $maxCount = $totalTransaction;
        }
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
        $mainTable = array("activity_master as am",array("am.remarks","am.created_at"));
        $joinTable = array(
            array("left","disposition_master as dm","dm.disposition_id = am.disposition_id",array("dm.disposition_name"))
        );
        $activityHistoryQ = $db->JoinFetch($mainTable,$joinTable,"am.type_id = '{$prospectId}' and am.source_type = 'prospect'",array("am.created_at"=>"asc"));
        //$activityHistory = $db->FetchToArrayFromResultset($activityHistoryQ);

        if($db->CountResultRows($activityHistoryQ) > 0){

            while ($rowHistory = $db->MySqlFetchRow($activityHistoryQ)) {

                foreach($rowHistory as $hkey => $hvalue){
                    if($key == 'created_at'){
                        $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at'],true) : "";
                        $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $rowHistory['created_at']);

                    }else{
                        $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $rowHistory[$hkey]);
                    }


                    $c++;
                }
            }
        }
        $y++;
// 		$table .= '</tr>';
    }

    for($i=0, $c=$lastHeaderColumn;$i<$maxCount;$i++){
        $count = $i + 1;
        foreach($historyCallArray as $callArrayData){
            if($callArrayData == 'call'){
                $callArrayData = $count."call";
            }
            $objPHPExcel->getActiveSheet()->SetCellValue($c . 1 , $callArrayData);
            $objPHPExcel->getActiveSheet()->getStyle("A1:".$c."1")->getFont()->setBold(true);
            $c++;
        }

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
$file_name = 'prospector_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');