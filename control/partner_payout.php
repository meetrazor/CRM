<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");

/* DB table to use */

$action = $db->FilterParameters($_GET['act']);
//echo $action;
$table = "partner_payout";
$table_id = "partner_payout_id";
$response='';
if('fetch' == $action){
    $table = "partner_payout as pp";

    $partnerId = (isset($_GET['partner_id']) && !empty($_GET['partner_id'])) ? intval($db->FilterParameters($_GET['partner_id'])) : '';

    $columns = array(
        'pp.partner_payout_id','concat(pm.first_name," ",pm.last_name) as partner_name','ptm.payment_type_name','pp.amount','pp.remarks','ptm.payment_type_id','pm.partner_id'
    );
    $seach_columns = array(
        'concat(pm.first_name," ",pm.last_name)','ptm.payment_type_name','pp.amount','pp.remarks'
    );

    $joins = " LEFT JOIN partner_master as pm ON pm.partner_id = pp.partner_id";
    $joins .= " LEFT JOIN payment_type_master as ptm ON ptm.payment_type_id = pp.payment_type_id";

    $sql_where = "WHERE 1=1";

    if($partnerId != ''){
        $sql_where .= " and pm.partner_id = '{$partnerId}'";
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
    $sql_group = "";

    // paging
    $sql_limit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sql_limit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM {$table} {$joins} {$sql_where} {$sql_group} {$sql_order} {$sql_limit}";

    //echo $sql;exit;
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
        $response['aaData'][] = $row;
    }

    // prevent caching and echo the associative array as json
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
}elseif($action == 'addedit'){

    $data = $db->FilterParameters($_POST);

    if(isset($_POST['action']) && $_POST['action'] != ''){

        if($_POST['action'] == 'add'){
//            $payment_type_res = $db->FetchRowWhere($table, array('payment_type_name'),"payment_type_name='$payment_typeName'");
//            $payment_type_count = $db->CountResultRows($payment_type_res);
            if(1 < 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "Payout is Already Exist";
            }else{
                $data = array_merge($data,$db->TimeStampAtCreate($user_id));
                $insertId =$db->Insert($table, $data, true);
                $oldBalance = $db->FetchCellValue("partner_ledger", "ledger_balance","partner_id = '{$data['partner_id']}' order by updated_at desc");
                $newBalance = intval($oldBalance) - intval($data['amount']);
                $ledgerData = array(
                    "partner_id" => $data['partner_id'],
                    "amount" => $data['amount'],
                    "ledger_type" => 'D',
                    "type_id" => $insertId,
                    "ledger_from" => "payout",
                    "ledger_balance" => $newBalance,
                );
                $ledgerData = array_merge($ledgerData,$db->TimeStampAtCreate($user_id));
                $db->Insert("partner_ledger",$ledgerData);
                if(isset($data['partner_withdrawal_id']) and $data['partner_withdrawal_id']) {
                    $db->UpdateWhere("partner_withdrawal",
                        array_merge(array("is_process"=>1),$db->TimeStampAtUpdate($user_id))
                        ,"partner_withdrawal_id = '{$data['partner_withdrawal_id']}'");
                }
                $response['success'] = true;
                $response['title'] = 'Successful';
                $response['msg'] = "Record added successfully!";
            }
        }elseif($_POST['action'] == 'edit'){

            if($data['payment_type_id'] != ''){
//                $payment_type_res = $db->FetchRowWhere($table, array('payment_type_name'),"payment_type_name='$payment_typeName' and payment_type_id != '{$data['payment_type_id']}'");
//                $payment_type_count = $db->CountResultRows($payment_type_res);
                if(1 < 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Payout is Already Exist";
                } else {
                    $condition = "$table_id=".$data['partner_payout_id']."";
                    $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
                    $db->UpdateWhere($table,$data, $condition);
                    $ledgerData = array(
                        "partner_id" => $data['partner_id'],
                        "amount" => $data['amount'],
                        "ledger_type" => 'D',
                        "type_id" => $data['partner_payout_id'],
                        "ledger_from" => "payout",
                    );
                    $ledgerData = array_merge($ledgerData,$db->TimeStampAtCreate($user_id));
                    $ledgerCondition = "type_id=".$data['partner_payout_id']." and ledger_from = 'payout'";
                    $db->UpdateWhere("partner_ledger",$ledgerData,$ledgerCondition);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Record updated successfully!";
                }

            }else{
                $response['success'] = false;
                $response['title'] = 'Failed';
                $response['msg'] = "Invalid Payout Code!";
            }
        }
    }else{

        $response['success'] = false;
        $response['title'] = 'Failed';
        $response['msg'] = "Invalid Action!";
    }
    echo json_encode($response);
}elseif($action == 'delete'){

    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();
    $condition = "$table_id in ('$id')";
    $result = $db->DeleteWhere($table, $condition);
    $response['success'] = true;
    $response['title'] = 'Successful';
    $response['msg'] = "Record deleted successfully!";
    echo json_encode($response);

} elseif ($action == 'partnerpay') {
    $data = $db->FilterParameters($_POST);
    $amount = 0;
    $partnerId = isset($data['partner_id']) ? $data['partner_id'] : "";
    if($partnerId != ''){
        $amount = $db->FetchCellValue("partner_ledger", "ledger_balance","partner_id = '{$data['partner_id']}' order by updated_at desc");
    }
    echo $amount;
}
include_once 'footer.php';

