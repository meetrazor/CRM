<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="loan_type_master";
$table_id ="loan_type_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "loan_type_master";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('loan_type_id','loan_type_name','is_active','is_default');
    $seach_columns = array('loan_type_name');

    $joins = "";

    // filtering
    $sql_where = "where 1 = 1";
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

    $validator = array(

        'rules' => array(
            'loan_type_name' => array('required' => true),
        ),
        'messages' => array(
            'loan_type_name' => array('required' => 'Please enter loan type name'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
        $data['is_default'] = (isset($data['is_default'])) ? 1 : 0;

        $loanTypeName = $data['loan_type_name'];


        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $loanRes = $db->FetchRowWhere($table, array('loan_type_name'),"loan_type_name='$loanTypeName'");
                $sourceLoanRes = $db->FetchRowWhere($table, array('loan_type_name'),"is_default = 1");
                $sourceLoanCount = $db->CountResultRows($sourceLoanRes);
                $loanCount = $db->CountResultRows($loanRes);
                if($loanCount > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Loan Type is Already Exist";
                }elseif($sourceLoanCount == 1 && $sourceLoanCount == $data['is_default']){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Default loan type is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Loan Type added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['loan_type_id'] != ''){
                    $loanRes = $db->FetchRowWhere($table, array('loan_type_name'),"loan_type_name='$loanTypeName' and loan_type_id != '{$data['loan_type_id']}'");
                    $loanCount = $db->CountResultRows($loanRes);
                    $sourceLoanRes = $db->FetchRowWhere($table, array('loan_type_name'),"is_default = 1 and loan_type_id != '{$data['loan_type_id']}'");
                    $sourceLoanCount = $db->CountResultRows($sourceLoanRes);
                    if($loanCount > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "loan Type is Already Exist";
                    }elseif($sourceLoanCount == 1 && $sourceLoanCount == $data['is_default']){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "Default loan type is Already Exist";
                    } else {
                        $reasonId = $data['loan_type_id'];
                        $condition = "loan_type_id = '$reasonId'";
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "loan type updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid loan type";
                }
            }
        }else{

            $response['success'] = false;
            $response['title'] = 'Failed';
            $response['msg'] = "Invalid Action!";
        }
        echo json_encode($response);
    }

}elseif($action == 'delete'){

    $ids = $db->FilterParameters($_POST['id']);
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();

    if(is_array($idArray)){
        foreach($idArray as $typeId){
//            $existingCheckR = $db->Fetch("lead_master",$table_id,"$table_id = '{$typeId}'");
            $existingCheckR = $db->Fetch("tickets",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete loan type because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'loan type(s) deleted successfully';
            }
        }
    }
    echo json_encode($response);

}
include_once 'footer.php';