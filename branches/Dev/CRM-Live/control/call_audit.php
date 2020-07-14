<?php

include_once 'header.php';
include_once '../core/Validator.php';

$table = "call_audit";
$table_id = 'call_audit_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

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
        'ca.audit_date','ca.audit_time','ca.mobile','SUM(qov.weight) as weight','null as weight_percentage',
        'ca.created_at',
        'CONCAT(au2.first_name," ",au2.last_name) as creator_name','ca.created_by',
        'group_concat(DISTINCT caa.question_id) as audit_question_id',
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
    $mainQuery = mysql_query($sql) or die(mysql_error());

    // get the number of filtered rows
    $filtered_rows_query = mysql_query("SELECT FOUND_ROWS()") or die(mysql_error());

    $row = mysql_fetch_array($filtered_rows_query);
    $response['iTotalDisplayRecords'] = $row[0];
    $response['iTotalRecords'] = $row[0];

    $response['sEcho'] = intval($_GET['sEcho']);
    $response['aaData'] = array();

    while ($row = $db->MySqlFetchRow($mainQuery))
    {
        $rowQuestionWeight = 0;
        $id = $row['call_audit_id'];

        // calculate total weight for question for audit record
        $rowQuestionId = isset($row['audit_question_id']) ? explode(",",$row['audit_question_id']) : array();
        if(is_array($rowQuestionId) && count($rowQuestionId) > 0){
            foreach ($rowQuestionId as $questionId){
                $rowQuestionWeight = $rowQuestionWeight + (array_key_exists($questionId,$questionWeight) ? $questionWeight[$questionId] : 0);
            }
        }

        // calculate performance in %
        if(isset($row['weight']) && $row['weight'] > 0 ){
            $inPer = ($row['weight'] * 100) / $rowQuestionWeight;
        }
        else{
            $inPer = 0;
        }
        $row['weight'] = ($rowQuestionWeight != 0) ? $row['weight']."/".$rowQuestionWeight : 0;
        $row['inPercent'] = ($rowQuestionWeight != 0) ? round($inPer,2)."%" : 0;
        $row['audit_date'] = core::YMDToDMY($row['audit_date']);
        $row['created_at'] = core::YMDToDMY($row['created_at']);
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

    $response = array();
    if(is_array($ids)){
        $condition = "call_audit_id in ('$id')";
        if($db->DeleteWhere($table,$condition)){
            if($db->DeleteWhere("call_audit_answer",$condition)){
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'call audit(s) deleted successfully';
            }
        }else{
            $response['success'] = false;
            $response['title'] = "Error";
            $response['msg'] = 'Something went wrong!';
        }
    }
    echo json_encode($response);

}elseif($action == 'get_audit_details'){
    $data = $db->FilterParameters($_POST);
    if(isset($data['call_audit_id']) and $data['call_audit_id'] != ''){

        $mainTable = array("call_audit_answer as caa",array("caa.call_audit_id","caa.option_value_id","caa.question_id","group_concat(caa.option_value) as option_value"));
        $joinTable = array(
            array("left","question_master as qm","caa.question_id = qm.question_id",array("qm.question_name as question_name")),
            array("left","question_option_value as qov1","caa.option_value_id = qov1.option_value_id",array("sum(qov1.weight) as weight")),
        );

        $callAuditAnsQ = $db->JoinFetch($mainTable,$joinTable,"caa.call_audit_id = '{$data['call_audit_id']}'",'','','caa.question_id');
        $callAuditAnsR = $db->FetchToArrayFromResultset($callAuditAnsQ);


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

        if(count($callAuditAnsR) > 0) {

            $html  = "<table class='table table-bordered'>";
            $html .= "<thead>";
            $html .= "<tr>";
            $html .= "<th>Question</th>";
            $html .= "<th>Answer</th>";
            $html .= "<th>Weight</th>";
            $html .= "</tr>";
            $html .= "</thead>";
            $html .= "<tbody>";
            $weight = 0;
            $sumWeight = 0;
            foreach($callAuditAnsR as $details){
                if($details['question_id'] != 0 || $details['question_id'] != ''){
                    $sumWeightQId =  array_key_exists($details['question_id'],$questionWeight) ? $questionWeight[$details['question_id']] : 0;
                }else{
                    $sumWeightQId = 0;
                }
                $html .= "<tr>";
                $html .= "<td>".core::StripAllSlashes($details['question_name'])."</td>";
                $html .= "<td>".$details['option_value']."</td>";
                if(isset($details['weight'])){
                    $html .= "<td>".$details['weight'].'/'.$sumWeightQId."</td>";
                }else{
                    $html .= "<td></td>";
                }
                $html .= "</tr>";
                $weight += $details['weight'];
                $sumWeight += $sumWeightQId;
            }
            if($weight > 0 ){
                $inPer = ($weight * 100) / $sumWeight;
            }
            else{
                $inPer = 0;
            }
            $html .= "<tr><th>Total Weight:</th><td><b>".$weight.'/'.$sumWeight."</b></td><td><b>".round($inPer,2).'%'."</b></td></tr>";
            $html .= "</tbody>";
            $html .= "</table>";
            echo $html;
        } else {
            echo NO_RESULT;
        }
    } else{
        echo NO_RESULT;
    }
}
include_once 'footer.php';
