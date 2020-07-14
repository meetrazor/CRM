<?php
include_once 'header.php';
include_once '../core/Validator.php';
$user_id = $ses->Get('user_id');

/* DB table to use */

$table ="company_master";
$table_id ="company_id";

$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    // the table being queried
    $table = "company_master";//"city ct";

    // the columns to be filtered, ordered and returned
    // must be in the same order as displayed in the table

    $columns = array('company_id','company_name','is_active');
    $seach_columns = array('company_name');

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
        $row['company_name'] = stripslashes($row['company_name']);
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
            'company_name' => array('required' => true),
        ),
        'messages' => array(
            'company_name' => array('required' => 'Please enter company name'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
    } else {

        $category = (isset($data['category_id']) && $data['category_id'] != '') ? $data['category_id'] : array();
        $rssFeed = (isset($data['rss_feed_link']) && $data['rss_feed_link'] != '') ? $data['rss_feed_link'] : array();

        $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;

        $companyName = $data['company_name'];


        if(isset($_POST['action']) && $_POST['action'] != ''){

            if($_POST['action'] == 'add'){

                $company_res = $db->FetchRowWhere($table, array('company_name'),"company_name='$companyName'");
                $company_count = $db->CountResultRows($company_res);
                if($company_count > 0){
                    $response['success'] = false;
                    $response['title'] = 'Error:';
                    $response['msg'] = "Company is Already Exist";
                }else{
                    $timestamp = $db->TimeStampAtCreate($user_id);
                    $data = array_merge($data,$timestamp);
                    $insertId = $db->Insert($table, $data, 1);
                    if(count($category) > 0){
                        $db->DeleteWhere("company_category","$table_id = '{$insertId}'");
                        foreach($category as $key => $categoryId) {
                            $emailData = array(
                                "company_id" => $insertId,
                                "category_id" => $categoryId,
                                "rss_feed_link" => array_key_exists($key,$rssFeed) ? $rssFeed[$key] : "",
                            );
                            $db->Insert('company_category', $emailData);
                        }
                    }
                    $response['success'] = true;
                    $response['title'] = 'Successful';
                    $response['msg'] = "Company added successfully!";
                }
            }elseif($_POST['action'] == 'edit'){

                if($data['company_id'] != ''){
                    $company_res = $db->FetchRowWhere($table, array('company_name'),"company_name='$companyName' and company_id != '{$data['company_id']}'");
                    $company_count = $db->CountResultRows($company_res);
                    if($company_count > 0){
                        $response['success'] = false;
                        $response['title'] = 'Error:';
                        $response['msg'] = "company is Already Exist";
                    } else {
                        $companyId = $data['company_id'];
                        $condition = "company_id='$companyId'";
                        $db->UpdateWhere($table,$data, $condition);
                        if(count($category) > 0){
                            $db->DeleteWhere("company_category","$table_id = '{$companyId}'");
                            foreach($category as $key => $categoryId) {
                                $emailData = array(
                                    "company_id" => $companyId,
                                    "category_id" => $categoryId,
                                    "rss_feed_link" => array_key_exists($key,$rssFeed) ? $rssFeed[$key] : "",
                                );
                                $db->Insert('company_category', $emailData);
                            }
                        }
                        $response['success'] = true;
                        $response['title'] = 'Successful';
                        $response['msg'] = "company updated successfully!";
                    }

                }else{
                    $response['success'] = false;
                    $response['title'] = 'Failed';
                    $response['msg'] = "Invalid company";
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
            $existingCheckR = $db->Fetch("rss_feed",$table_id,"$table_id = '{$typeId}'");
            $existingCheckC = $db->CountResultRows($existingCheckR);
            if($existingCheckC > 0){
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete company because it has feed";
                break;
            } else {
                $condition = "$table_id in ('$id')";
                $result = $db->DeleteWhere($table, $condition);
                $result = $db->DeleteWhere("company_category", $condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = 'record(s) deleted successfully';
            }
        }
    }
    echo json_encode($response);

}elseif('companyCat' === $action){

    $data = $db->FilterParameters($_POST);
    $html = '';
    if($data['company_id'] != '') {
        $companyCategory = $db->FetchToArray("company_category","*","company_id = '{$data['company_id']}'");
        if(count($companyCategory) > 0){
            foreach($companyCategory as $key => $companyCategoryData){
                $categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$companyCategoryData['category_id'],array("category_name"=>"asc"),"category_type = 'rss'");
                $html .= '<tr id="category_'.$key.'">
                            <td>
                                <select id="category_id'.$key.'" name="category_id[]" data-placeholder="Select category" class="">
                                    <option></option>
                                    '.$categoryDd.'
                                </select>
                                <span for="category_id'.$key.'" class="help-inline"></span>
                            </td>
                            <td>
                                <input type="url" name="rss_feed_link[]" id="rss_feed_link'.$key.'" value='.$companyCategoryData['rss_feed_link'].'>
                            </td>
                            <td>';

                            if($key != 0) {
                            $html .= '<a href="javascript:void(0);" onclick="RemoveCategoryRow('.$key.')"><i class="icon-remove red"></i></a>';
                            }
                $html .= '</td>
                    </tr>';
            }
        } else {
            $categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"),"category_type = 'rss'");
            $html .= '<tr id="category_0">
                        <td>
                            <select id="category_id0" name="category_id[]" data-placeholder="Select category" class="">
                                <option></option>
                                '.$categoryDd.'
                            </select>
                            <span for="category_id0" class="help-inline"></span>
                        </td>
                        <td>
                            <input type="url" name="rss_feed_link[]" id="rss_feed_link0"/>
                        </td>
                        <td></td>
                    </tr>';
        }
    }
    echo $html;
}
include_once 'footer.php';