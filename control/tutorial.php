<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="tutorial";
$table_id ="tutorial_id";

function yt_exists($videoID) {
    $theURL = "http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=$videoID&format=json";
    $headers = get_headers($theURL);
    if (substr($headers[0], 9, 3) !== "404") {
        return true;
    } else {
        return false;
    }
}


$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "tutorial as t";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('t.tutorial_id','t.tutorial_title','t.youtube_id','t.message','t.created_at','concat(au.first_name," ",au.last_name) as created_by');
    $seach_columns = array('t.tutorial_title','t.message');

    $joins = " left join admin_user as au on t.created_by = au.user_id";

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
        $row['tutorial_title'] = stripslashes($row['tutorial_title']);
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
            'tutorial_title' => array('required' => true),
            'message' => array('required' => true),
            'youtube_id' => array('required' => true,"youtube_id_check" => true),
        ),
        'messages' => array(
            'tutorial_title' => array('required' => 'Please enter tutorial title'),
            'message' => array('required' => 'Please enter message'),
            'youtube_id' => array('required' => 'Please enter youtube id',"youtube_id_check" => "Invalid youtube id")
        )
    );

    $validator_obj = new Validator($validator);
    $validator_obj->addMethod('youtube_id_check', 'yt_exists');
    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {
        //$data['tutorial_title'] = ucwords(strtolower($data['tutorial_title']));

        $tutorialName = $data['tutorial_title'];


        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $tutorial_res = $db->FetchRowWhere($table, array('tutorial_title'),"tutorial_title='$tutorialName'");

                $tutorial_count = $db->CountResultRows($tutorial_res);
                if($tutorial_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "tutorial is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $db->Insert($table, $data, true);
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "tutorial added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['tutorial_id'] != ''){
                    $tutorial_res = $db->FetchRowWhere($table, array('tutorial_title'),"tutorial_title='$tutorialName' and tutorial_id != '{$data['tutorial_id']}'");
                    $tutorial_count = $db->CountResultRows($tutorial_res);
                    if($tutorial_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "tutorial is Already Exist";
                    } else {
                        $tutorialId = $data['tutorial_id'];
                        $condition = "tutorial_id='$tutorialId'";
                        $db->UpdateWhere($table,$data, $condition);
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "tutorial updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid tutorial";
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
    $condition = "$table_id in ('$id')";
    $result = $db->DeleteWhere($table, $condition);
    $response['success'] = true;
    $response['title'] = "Records Deleted";
    $response['msg'] = 'record(s) deleted successfully';
    echo json_encode($response);

}
include_once 'footer.php';