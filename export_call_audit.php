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
    ->setTitle("Call Audit")
    ->setSubject("Call Audit List")
    ->setDescription("Call Audit As of ".$time_stamp)
    ->setKeywords("office 2007 openxml")
    ->setCategory("Information");

$objPHPExcel->setActiveSheetIndex(0);
$table = "call_audit";
$table_id = 'call_audit_id';
$user_id = $ses->Get("user_id");

//setting up export data

$questionTable = array("question_master as qm",array("qm.option_type"));
$joinOptionTable = array(
    array("left","question_option_value as qov","qov.question_id = qm.question_id",array("qov.question_id,  IF( qm.option_type = 'checkbox' , SUM(qov.weight) , MAX(qov.weight) ) as sum_weight")),
);

$sumWeightQ = $db->JoinFetch($questionTable,$joinOptionTable,null,null,null,"qov.question_id");
$sumWeightR = $db->FetchToArrayFromResultset($sumWeightQ);

$questionWeight = array();

foreach ($sumWeightR as $row){
    $questionWeight[$row['question_id']] = $row['sum_weight'];
}

$table = "$table ca";
$userId = (isset($_GET['filter']['user_id']) && !empty($_GET['filter']['user_id'])) ? intval($db->FilterParameters($_GET['filter']['user_id'])) : '';
$auditDate = (isset($_GET['filter']['audit_date']) && !empty($_GET['filter']['audit_date'])) ? $db->FilterParameters($_GET['filter']['audit_date']) : '';
$createDate = (isset($_GET['filter']['create_date']) && !empty($_GET['filter']['create_date'])) ? $db->FilterParameters($_GET['filter']['create_date']) : '';

$columns = array(
    'ca.call_audit_id','CONCAT(au1.first_name," ",au1.last_name) as user_name',
    'ca.audit_date','ca.audit_time','ca.mobile','ca.created_at',
    'CONCAT(au2.first_name," ",au2.last_name) as creator_name','ca.created_by',
    'SUM(qov.weight) as weight','group_concat(DISTINCT caa.question_id) as audit_question_id',
);

$seach_columns = array(
    'user_name','ca.audit_date'
);

$joins = " left join admin_user au1 on (ca.user_id = au1.user_id)";
$joins .= " left join admin_user au2 on (ca.created_by = au2.user_id)";
$joins .= " left join call_audit_answer caa on (ca.call_audit_id = caa.call_audit_id)";
$joins .= " left join question_option_value qov on (caa.option_value_id = qov.option_value_id)";

// filtering
// filtering
$sqlWhere = "WHERE 1=1";

if($userId != ''){
    $sqlWhere .= " and ca.user_id = '{$userId}'";
}

if($auditDate != ''){
    list($fromDate,$toDate) = explode(' to ',$auditDate);
    $fromDate = core::DMYToYMD($fromDate);
    $toDate = core::DMYToYMD($toDate);
    if(strtotime($fromDate) == strtotime($toDate)){
        $sqlWhere .= " && ca.audit_date = '{$fromDate}'";
    } else {
        $sqlWhere .= " && ca.audit_date >= '{$fromDate}' AND ca.audit_date <= '{$toDate}'";
    }
}

if($createDate != ''){

    list($fromDate,$toDate) = explode(' to ',$createDate);
    $fromDate = core::DMYToYMD($fromDate);
    $toDate = core::DMYToYMD($toDate);
    if(strtotime($fromDate) == strtotime($toDate)){
        $sqlWhere .= " && DATE_FORMAT(ca.created_at, '%Y-%m-%d') = '{$fromDate}'";
    } else {
        $sqlWhere .= " && (DATE_FORMAT(ca.created_at, '%Y-%m-%d') >= '{$fromDate}' AND DATE_FORMAT(ca.created_at, '%Y-%m-%d') <= '{$toDate}')";
    }

}

if ( isset($_GET['sSearch_3']) && $_GET['sSearch_3'] != '')
{
    $seach_condition = "";
    $seach_condition .= 'concat(au1.first_name," ",au1.last_name)' . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_3'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sqlWhere .= " AND " . $seach_condition;
}

if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
{
    $seach_condition = "";
    $seach_condition .= "ca.mobile" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
    $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
    $sqlWhere .= " AND " . $seach_condition;
}


// ordering
$sql_order = "";
if ( isset( $_GET['iSortCol_0'] ) )
{
    $_GET['iSortCol_0'] = $_GET['iSortCol_0'] - 1;
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
$sql_group = "GROUP BY ca.call_audit_id";

// paging
$sql_limit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
{
    $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
}

$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sqlWhere} {$sql_group} {$sql_order} {$sql_limit}";
// 	echo $sql;exit;

$res = $db->Query($sql);

//$table = "<table>";
if($db->CountResultRows($res) > 0){

    $result_cols = array();

    $y = 1;

// 	$table .= "<tr>";

    $ignore_columns = array('call_audit_id','created_by');
    for($i=0, $c='A';$i<mysql_numfields($res);$i++){

        $column_info = mysql_fetch_field($res, $i);
        $result_cols[] = $column_info->name;

        $column_name = $column_info->name;
        if(!in_array($column_name, $ignore_columns)){

            if($column_name == 'audit_question_id'){
                $column_name = 'performance(%)';
            }
            if($column_name == 'weight'){
                $column_name = 'performance';
            }
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

                $rowQuestionWeight = 0;

                // calculate total weight for question for audit record
                $rowQuestionId = isset($row['audit_question_id']) ? explode(",",$row['audit_question_id']) : array();
                if(is_array($rowQuestionId) && count($rowQuestionId) > 0){
                    foreach ($rowQuestionId as $questionId){
                        $rowQuestionWeight = $rowQuestionWeight + (array_key_exists($questionId,$questionWeight) ? $questionWeight[$questionId] : 0);
                    }
                }

                if($key == 'weight'){
                    $row['weight'] = ($rowQuestionWeight != 0) ? $row['weight']."/".$rowQuestionWeight : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row['weight']);
                }

                // calculate performance in %
                if(isset($row['weight']) && $row['weight'] > 0 ){
                    $inPer = ($row['weight'] * 100) / $rowQuestionWeight;
                }
                else{
                    $inPer = 0;
                }

                if($key == 'audit_question_id'){
                    $row['audit_question_id'] = ($rowQuestionWeight != 0) ? round($inPer,2)."%" : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row['audit_question_id']);
                }

                $row['audit_date'] = core::YMDToDMY($row['audit_date']);
                $row['created_at'] = core::YMDToDMY($row['created_at']);


                if($key == 'created_at'){
                    $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at']) : "";
                    $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row['created_at']);

                }
                $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $row[$key]);

// 				$table .= "<th>" . $row[$key] . "</th>";
                $i++;
                $c++;
            }
        }
        $y++;
// 		$table .= '</tr>';
    }
//
// 	echo "<table>" . $table . '</table>';
// 	exit;
    for($j='A'; $j<$c;$j++){
        $objPHPExcel->getActiveSheet()->getColumnDimension("$j")->setAutoSize(true);
    }
// 	$table .= '</table>';
}

// echo $table;exit;
$file_name = 'call_audit_'.DATE_TIME_INDIAN.'.xls';
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$file_name.'"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
