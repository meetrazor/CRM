<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "customer_master";
$table_id = 'customer_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table cm";

    $columns = array(
        'cm.customer_id','cm.customer_name',
        'cm.email','cm.mobile_no','c.city_name','cm.created_at',
        'concat(au.first_name," ",au.last_name) as created_by',
        'cm.is_active'

    );
    $seach_columns = array(
        'concat(cm.first_name," ",cm.last_name) as full_name',
        'cm.email','cm.mobile_no','cm.created_at',
        'concat(au.first_name," ",au.last_name)',
    );

    $joins = " left join admin_user au on (au.user_id = cm.created_by)";
    $joins .= " left join city c on (c.city_id = cm.city_id)";

    // filtering
// filtering
    $sql_where = "WHERE 1=1";
    if ( isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "customer_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_1'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_4']) && $_GET['sSearch_4'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.email" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_4'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }

    if ( isset($_GET['sSearch_5']) && $_GET['sSearch_5'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "cm.mobile_no" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_5'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
    }
    if ( isset($_GET['sSearch_6']) && $_GET['sSearch_6'] != '')
    {
        $seach_condition = "";
        $seach_condition .= "c.city_name" . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch_6'] ) . "%' OR ";
        $seach_condition = '(' . substr($seach_condition, 0, -3) . ')';
        $sql_where .= " AND " . $seach_condition;
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
    $db->UpdateWhere("customer_master",array("is_new"=>1),"1=1");
    while ($row = $db->MySqlFetchRow($main_query))
    {
        $row['created_at'] = ($row['created_at'] != '0000-00-00' && $row['created_at'] != '') ? core::YMDToDMY($row['created_at']) : "";
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
    $idArray = (is_array($ids)) ? $ids : array($ids);
    $id = (is_array($ids)) ? implode("','", $ids) : $ids;
    $response = array();
    if(is_array($idArray)) {
        foreach ($idArray as $typeId) {
            $existingCheckR = $db->FetchCellValue("lead_master", "$table_id", "$table_id = '{$typeId}'");

            if ($existingCheckR != '') {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete customer because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";

                $db->DeleteWhere($table, $condition);
                //$db->UpdateWhere($table,array("is_active"=>0),$condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = ' customer (s) deleted successfully';
            }
        }
    }
    echo json_encode($response);

}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $customerId = isset($data['customer_id']) ? $data['customer_id'] : "";
    if(isset($customerId) and $customerId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and customer_id != '{$customerId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email'");
    }
    $emailCount = $db->CountResultRows($emailRes);
    if($emailCount > 0){
        $flag = "false";
    }
    echo $flag;
} elseif ($action == 'getcustomer'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("customer_name","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("customer_id as value","concat(customer_name,' ',mobile_no) as text"),$condition,array("customer_name"=>"asc"));

    echo json_encode($leadInfo);
}elseif($action == 'customerdd'){

    $customer_id = $db->FilterParameters($_POST['customer_id']);
    $customerLimit = isset($_POST['customer_limit']) ? $db->FilterParameters($_POST['customer_limit']) : "";
    $customerLimit = ($customerLimit != '') ? array(0,10) : "";
    $customerDd = $db->CreateOptions('html', 'customer_master', array('customer_id',"concat(customer_name,' ',mobile_no)"), $customer_id, array('customer_name' => 'asc'),null,$customerLimit);

    if(isset($_POST['empty_opt'])){
        $customerDd = "<option value=''></option>" . $customerDd;
    }else{
        $customerDd = Core::PrependNullOption($customerDd);
    }
    echo $customerDd;

}  elseif($action == 'customerhistory'){
    $data = $db->FilterParameters($_POST);
    if($data['event'] == 'hide'){
        $event = 'show';
        $customer_html_div = '<a data-rel="show_customer_history" id="show_customer_history" class="btn btn-minier btn-primary" href="javascript:void(0);" onclick="typeDetails(';
        $customer_html_div .= "'".$event."'";
        $customer_html_div .= ');"><i class="icon icon-eye-open bigger-150" data-placement="bottom" data-rel="tooltip" data-original-title="View Customer History"></i> View Customer History</a>';
        echo $customer_html_div;
    }
    else{
        $customerId = isset($data['customer_id']) ? $data['customer_id'] : "";
        if($customerId != ''){

            $mainTable = array("ticket_history as th",array("th.comment","th.created_at",'th.updated_at','th.ticket_number,th.ticket_history_id'));
            $joinTable = array(
                array("left","disposition_master as dm","dm.disposition_id = th.disposition_id",array("dm.disposition_name")),
                array("left","status_master as sm","sm.status_id = th.status_id",array("sm.status_name")),
                array("left","query_type_master qtm","qtm.query_type_id = th.query_type_id",array("qtm.query_type_name")),
                array("left","admin_user as cu","th.updated_by = cu.user_id",array("concat(first_name,' ',last_name) as updated_by")),
            );
            $condition = "th.customer_id = '{$customerId}'";
            $customerInfoR = $db->JoinFetch($mainTable,$joinTable,$condition,array("th.updated_at"=>"desc"),null);
            $customerHistory = $db->FetchToArrayFromResultset($customerInfoR);
            //$ticketHistory = $db->FetchToArray("tickets","*","ticket_id = '{$ticketId}'",array('created_at'=>"desc"));
            if(is_array($customerHistory) and count($customerHistory) > 0){
                $event = 'hide';
                $customer_html_div = "";
                $customer_html_div .= '<a data-rel="hide_customer_history" id="hide_customer_history" class="btn btn-minier btn-danger" href="javascript:void(0);" onclick="typeDetails(';
                $customer_html_div .= "'".$event."'";
                $customer_html_div .= ');"><i class="icon icon-eye-close bigger-150" data-placement="bottom" data-rel="tooltip" data-original-title="Hide Customer History"></i> Hide Customer History</a>';
                $customer_html_div .= "<table class='table table-condensed table-bordered table-hover'>";
                $customer_html_div .= "<tr>";
                $customer_html_div .= "<td><b>Ticket</b></td>";
                $customer_html_div .= "<td><b>Comment</b></td>";
                $customer_html_div .= "<td><b>Disposition</b></td>";
                $customer_html_div .= "<td><b>Status</b></td>";
                $customer_html_div .= "<td><b>Query Type</b></td>";
                $customer_html_div .= "<td><b>Documents</b></td>";
                $customer_html_div .= "<td><b>Updated By</b></td>";
                $customer_html_div .= "<td><b>Updated On</b></td>";
                $customer_html_div .= "<tr>";
                foreach($customerHistory as $id => $history){
                    $ticketDocument = $db->FetchToArray("ticket_documents","*","ticket_history_id = '{$history['ticket_history_id']}'");
                    $imageHtml = '';
                    if(count($ticketDocument) > 0){
                        $imageHtml = '';
                        foreach($ticketDocument as $documentData){
                            $fileExt = pathinfo($documentData['filename'],PATHINFO_EXTENSION);
                            $fileabsPath = (!in_array($fileExt,Utility::imageExtensions())) ? "uploads/docimage.png" : TICKET_IMAGE_PATH.$documentData['filename'];

                            if($documentData['filename'] != '' && file_exists(TICKET_IMAGE_PATH_ABS.$documentData['filename'])){

                                $imageHtml .= "<div class='row-fluid inline' id='new_image_".$documentData['ticket_document_id']."'>";
                                $imageHtml .= "<ul class='ace-thumbnails'>";
                                $imageHtml .= " <li>";
                                $imageHtml .= " <a href='javascript:void(0);' class='cboxElement'>";
                                $imageHtml .= "<img src='".$fileabsPath."' alt='".$documentData['real_filename']."' title='".$documentData['real_filename']."' style='width: 150px;height: 150px'>";
                                $imageHtml .= "</a>";

                                $imageHtml .= "<div class='tools tools-bottom'>";
                                $imageHtml .= "<a href='javascript:void(0);' onclick='DeleteImage(".$documentData['ticket_document_id'].")'>";
                                $imageHtml .= "<i class='icon-remove red'></i>";
                                $imageHtml .= "</a>";

                                $imageHtml .= "<a href='".TICKET_IMAGE_PATH.$documentData['filename']."' target='_blank'  data-placement='top' data-rel='tooltip' data-original-title='Download'>";
                                $imageHtml .= "<i class='icon-download'></i>";
                                $imageHtml .= "</a>";
                                $imageHtml .= "</div>";
                                $imageHtml .= "</li>";
                                $imageHtml .= "</ul>";
                                $imageHtml .= " </div>";

                            }
                        }
                    }
                    $customer_html_div .= "<tr>";
                    $customer_html_div .= "<td>".$history['ticket_number']."</td>";
                    $customer_html_div .= "<td>".$history['comment']."</td>";
                    $customer_html_div .= "<td>".$history['disposition_name']."</td>";
                    $customer_html_div .= "<td>".$history['status_name']."</td>";
                    $customer_html_div .= "<td>".$history['query_type_name']."</td>";
                    $customer_html_div .= "<td>".$imageHtml."</td>";
                    $customer_html_div .= "<td>".$history['updated_by']."</td>";
                    $customer_html_div .= "<td>".core::YMDToDMY($history['updated_at'],true)."</td>";
                    $customer_html_div .= "<tr>";

                }
                $customer_html_div .= "</table>";
                echo $customer_html_div;
            } else {
                $event = 'hide';
                $customer_html_div = '<a data-rel="hide_customer_history" id="hide_customer_history" class="btn btn-minier btn-danger" href="javascript:void(0);" onclick="typeDetails(';
                $customer_html_div .= "'".$event."'";
                $customer_html_div .= ');"><i class="icon icon-eye-close bigger-150" data-placement="bottom" data-rel="tooltip" data-original-title="Hide Customer History"></i> Hide Customer History</a>';
                $customer_html_div .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";
                echo $customer_html_div;
            }
        } else {
            $event = 'hide';
            $customer_html_div = '<a data-rel="hide_customer_history" id="hide_customer_history" class="btn btn-minier btn-danger" href="javascript:void(0);" onclick="typeDetails(';
            $customer_html_div .= "'".$event."'";
            $customer_html_div .= ');"><i class="icon icon-eye-close bigger-150" data-placement="bottom" data-rel="tooltip" data-original-title="Hide Customer History"></i> Hide Customer History</a>';
            $customer_html_div .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
                <div class='col-xs-12' align='center'>
                <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
                </div>
                </div>";
            echo $customer_html_div;
        }
    }
} elseif($action == 'customerinfo'){
    $data = $db->FilterParameters($_POST);
    $customerId = isset($data['customer_id']) ? $data['customer_id'] : "";
    if($customerId != ''){
        $mainTable = array("customer_master as cm",array("cm.*"));
        $joinTable = array(
            array("left","state as s","s.state_id = cm.state_id",array("s.state_name")),
            array("left","city as c","c.city_id = cm.city_id",array("c.city_name")),
            array("left","admin_user as au","au.user_id = cm.created_by",array("au.first_name as created_by")),

        );
        $customerR = $db->JoinFetch($mainTable,$joinTable,"cm.customer_id in ($customerId)");
        $customerInfo = $db->FetchToArrayFromResultset($customerR);
        $html = '';
        $email = array();
        $number = array();
        if(count($customerInfo) > 0){

            $html .= '<table class="table table-bordered">';
            $html .= '<tr><th>Customer Name</th>';
            $html .= '<td>'.core::StripAllSlashes($customerInfo[0]['customer_name']).'</td><tr>';

            $html .= '<tr><th>Email</th>';
            $html .= '<td>'.$customerInfo[0]['email'].'</td><tr>';
            $html .= '<tr><th>Contact Number</th>';
            $html .= '<td>'.$customerInfo[0]['mobile_no'].'</td><tr>';
            $html .= '<tr><th>Address</th>';
            $html .= '<td>'.$customerInfo[0]['address'].'</td><tr>';
            $html .= '<tr><th>State</th>';
            $html .= '<td>'.$customerInfo[0]['state_name'].'</td><tr>';
            $html .= '<tr><th>City</th>';
            $html .= '<td>'.$customerInfo[0]['city_name'].'</td><tr>';
            $html .= '<tr><th>Pincode</th>';
            $html .= '<td>'.$customerInfo[0]['pincode'].'</td><tr>';
            $html .= '<tr><th>Created On</th>';
            $html .= '<td>'.core::YMDToDMY($customerInfo[0]['created_at'],true).'</td><tr>';
            $html .= '<tr><th>Created By</th>';
            $html .= '<td>'.$customerInfo[0]['created_by'].'</td><tr>';

            $html .= '</table>';
            echo $html;
        } else {
            echo NO_RESULT;
        }

    } else {
        echo NO_RESULT;
    }
}
include_once 'footer.php';
