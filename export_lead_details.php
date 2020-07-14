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
    ->setTitle("lead")
    ->setSubject("lead List")
    ->setDescription("lead As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);

// the table being queried
$userType = $ses->Get("user_type");
//echo $action;
$table = "lead_master";
$table_id = "lead_id";
$response='';

        $childIds = Utility::getUserChild($user_id,"user");
        $table = "$table as l";
        $leadId = isset($_GET['lead_id']) ? $db->FilterParameters($_GET['lead_id']) : "";
        $columns = array(
            'l.lead_id','l.lead_code','l.lead_name','l.email','l.remarks','am.activity_type','sm.status_name',
            'l.mobile_no','cam.category_name','NULL as cw_status_name',
            'concat(pm.first_name," ",pm.last_name) as partner_name',
            'IF(lu.user_type = "bd",group_concat(concat(bu.first_name," ",bu.last_name)), NULL) as bd_name',
            'IF(lu.user_type = "kc", group_concat(concat(bu.first_name," ",bu.last_name)), NULL) as kc_name',
            'cm.customer_name',
            'l.created_at','l.updated_at','l.customer_check','l.check_time','s.state_name','c.city_name'

        );
        $seach_columns = array(
            'l.lead_name','l.email','l.mobile_no',
            'IF(lu.user_type = "bd", concat(bu.first_name," ",bu.last_name), NULL)',
            'IF(lu.user_type = "kc", concat(bu.first_name," ",bu.last_name), NULL)',
            'concat(pm.first_name," ",pm.last_name)','cm.customer_name','s.state_name','c.city_name'
        );

        $joins = " LEFT JOIN customer_master as cm ON cm.customer_id = l.customer_id";
        $joins .= " LEFT JOIN partner_master as pm ON pm.partner_id = l.partner_id";
        $joins .= " left join activity_master am ON (l.lead_id = am.type_id)";
        $joins .= " LEFT JOIN status_master as sm ON sm.status_id = am.status_id";
        $joins .= " left join lead_users lu ON (lu.lead_id = l.lead_id)";
        $joins .= " LEFT JOIN admin_user as bu ON bu.user_id = lu.user_id";
        $joins .= " LEFT JOIN category_master as cam ON cam.category_id = l.category_id";
        $joins .= " LEFT JOIN state as s ON (l.state_id = s.state_id)";
        $joins .= " LEFT JOIN city as c ON (c.city_id = l.city_id)";

        $sql_where = "WHERE 1=1 and lu.is_latest = 1 and am.is_latest = 1 and am.activity_on = 'lead'";

        if($leadId != ''){
            $sql_where .= " and l.lead_id  = '{$leadId}'";
        }

        if($userType == UT_BD || $userType == UT_KC || $userType == UT_IA){
            $res = Utility::getReportingUserId(array("$user_id"),array(),$userType);
            $childIds = implode(",",Utility::getUniqueArray($res));
            $sql_where .= " and lu.user_id in ($childIds) and lu.is_latest = 1 and user_type_id in (".$userType.")";

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

        include_once 'control/lead_filter.php';


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
        $sql_group = "group by lu.lead_id,lu.user_type";

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

    $ignore_columns = array('lead_id');
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
$file_name = 'lead_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');