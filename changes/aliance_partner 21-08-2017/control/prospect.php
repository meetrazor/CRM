<?php
include_once 'header.php';
include_once '../core/Validator.php';

$table = "prospect_master";
$table_id = 'prospect_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('fetch' === $action){

    $table = "$table pm";

    $campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';

    $type = isset($_GET['display_type']) ? $db->FilterParameters($_GET['display_type']) : "";

    $columns = array(
        'pm.prospect_id','concat(pm.first_name," ",pm.last_name) as prospect_name','cm.campaign_name',
        's.state_name','c.city_name',
        'dm.disposition_name',
        'am.follow_up_date_time',
        'concat(au.first_name," ",au.last_name) as telecaller',
        'am.created_at as last_call',
        'ami.total_transaction','concat(cu.first_name," ",cu.last_name) as created_by','pm.created_at',
        'pm.is_active','dm.is_close','dm.is_callback','dm.is_meeting'

    );
    $seach_columns = array(
        'pm.prospect_name','cm.campaign_name'
    );




    $joins = " left join state as s on (pm.state_id = s.state_id)";
    $joins .= " left join city as c on (c.city_id = pm.city_id)";
    $joins .= " left join campaign_master as cm on (cm.campaign_id = pm.campaign_id)";
    $joins .= " left join prospect_users as pu on (pu.prospect_id = pm.prospect_id and is_latest = 1)";
    $joins .= " left join admin_user as au on (pu.type_id = au.user_id)";
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


    include_once 'prospect_filter.php';
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
        $row['follow_up_date_time'] = ($row['follow_up_date_time'] != '0000-00-00 00:00:00' && $row['follow_up_date_time'] != '') ? core::YMDToDMY($row['follow_up_date_time'],true) : "";
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
            $deleteA = $db->FetchRowWhere("lead_master", $table,"$table = '{$typeId}'");
            $deleteAB = $db->FetchRowWhere("activity_master", $table,"type_id = '{$typeId}' and source_type=prospect");


            $existingCheckC = count($deleteA);
            $existingCheckCount = count($deleteAB);
            if ($existingCheckC > 0 || $existingCheckCount > 0) {
                $response['success'] = false;
                $response['title'] = 'Error:';
                $response['msg'] = "You can't delete prospect because it has record";
                break;
            } else {
                $condition = "$table_id in ('$id')";

                $db->DeleteWhere($table, $condition);
                $db->DeleteWhere("prospect_contact", $condition);
                $db->DeleteWhere("prospect_queue", $condition);
                //$db->UpdateWhere($table,array("is_active"=>0),$condition);
                $response['success'] = true;
                $response['title'] = "Records Deleted";
                $response['msg'] = ' prospect (s) deleted successfully';
            }
        }
    }
    echo json_encode($response);
}elseif($action == 'checkemail'){
    $flag = "true";
    $data = $db->FilterParameters($_POST);
    $email = $data['email'];
    $prospectId = isset($data['prospect_id']) ? $data['prospect_id'] : "";
    if(isset($prospectId) and $prospectId != ''){
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email' and prospect_id != '{$prospectId}'");
    } else {
        $emailRes = $db->FetchRowWhere($table, array('email'),"email='$email'");
    }
    $emailCount = $db->CountResultRows($emailRes);
    if($emailCount > 0){
        $flag = "false";
    }
    echo $flag;
} elseif ($action == 'getparnter'){
    $term = isset($_POST['term'])?$db->FilterParameters($_POST['term']):"";
    $condition = $db->LikeSearchCondition($term,array("concat(first_name,' ',last_name)","mobile_no"));
    $condition = "(".$condition.")";
    $leadInfo = $db->FetchToArray($table,array("prospect_id as value","concat(first_name,' ',last_name) as text"),$condition,array("concat(first_name,' ',last_name)"=>"asc"));

    echo json_encode($leadInfo);
}elseif($action == 'changetc'){
    $data = $db->FilterParameters($_POST);
    $prospectIds = $data['assign_prospect_id'];
    $prospectIdArray = explode(",",$prospectIds);
    $telecallerId = $data['tc_user_id'];
    foreach($prospectIdArray as $prospect => $id){
        $lastTellecallerId = $db->FetchCellValue("prospect_users","type_id","prospect_id = '{$id}' and is_latest = 1 and user_type = 'tc'");
        if($lastTellecallerId != $telecallerId){
            $db->UpdateWhere("prospect_users",array("is_latest"=>0),"prospect_id = '{$id}' and type_id = '{$lastTellecallerId}' and user_type = 'tc'");
            $db->Insert("prospect_users",array(
                "prospect_id" => $id,
                "type_id" => $telecallerId,
                "user_type" => "tc",
                "is_latest" => "1",
                "created_at"=>DATE_TIME_DATABASE,
                "created_by"=>$user_id,
            ));
        }

    }
    $response['success'] = true;
    $response['title'] = "Record Update";
    $response['msg'] = 'Record Update successfully';
    echo json_encode($response);

}elseif($action == 'prospectdd'){

    $prospect_id = $db->FilterParameters($_POST['prospect_id']);

    $prospectDd = $db->CreateOptions('html', 'prospect_master', array('prospect_id','concat(first_name," ",last_name)'), $prospect_id, array('prospect_name' => 'asc'));

    if(isset($_POST['empty_opt'])){
        $prospectDd = "<option value=''></option>" . $prospectDd;
    }else{
        $prospectDd = Core::PrependNullOption($prospectDd);
    }
    echo $prospectDd;

}elseif($action == 'prospectdetails'){

    $prospect_id = $db->FilterParameters($_POST['prospect_id']);
    $mainTable = array("prospect_master as pm",array("pm.prospect_id","pm.*","concat(pm.first_name,' ',pm.last_name) as prospect_name","pm.pincode","pm.address","pm.is_active"));

    $joinTable = array(
        array("left","city as c","c.city_id = pm.city_id",array('c.city_name')),
        array("left","state as s","s.state_id = pm.state_id",array('s.state_name')),
        array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id",array('cm.campaign_name')),
    );

    $prospectDetailRes = $db->JoinFetch($mainTable,$joinTable,"pm.prospect_id = '{$prospect_id}'");
    $prospectDetail = $db->FetchRowInAssocArray($prospectDetailRes);
    $contacts = $db->FetchToArray("prospect_contact","*","prospect_id = '{$prospect_id}'");
    $numbers = '';
    $emails = '';
    if(count($contacts) > 0){
        foreach($contacts as $contact){
            $prime = ($contact['is_primary'] == 1) ? "(prime)" : "";
            $wrong = ($contact['is_wrong'] == 1) ? "(wrong)" : "";
            if($contact['contact_type'] == 'phone'){
                $numbers .= $contact['contact'].$prime.$wrong."<br>";
            } else {
                $emails .= $contact['contact'].$prime.$wrong."<br>";
            }
        }
    }
    $html = '';
    if($prospect_id != ''){
        $html .= "<h5 class='bigger lighter'>Prospect Details</h5>";
        $html .= "<table class='table'>";
        $html .= "<tr>";
        $html .= "<th>Prospect Name</th>";
        $html .= "<td>".$prospectDetail['prospect_name']."</td></tr>";
        $html .= "<tr>";

        $html .= "<tr>";
        $html .= "<th>Campaign Name</th>";
        $html .= "<td>".$prospectDetail['campaign_name']."</td></tr>";
        $html .= "<tr>";

        $html .= "<tr>";
        $html .= "<th>Phone Numbers</th>";
        $html .= "<td><span id='prospect_number'>".$numbers."</span></td></tr>";
        $html .= "<tr>";

        $html .= "<tr>";
        $html .= "<th>Emails</th>";
        $html .= "<td>".$emails."</td></tr>";
        $html .= "<tr>";

        $html .= "<th>Address</th>";
        $html .= "<td>".$prospectDetail['address']."</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<th>Pincode</th>";
        $html .= "<td>".$prospectDetail['pincode']."</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<th>State</th>";
        $html .= "<td>".$prospectDetail['state_name']."</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<th>City</th>";
        $html .= "<td>".$prospectDetail['city_name']."</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td colspan='2'><a data-rel='prospect_edit' class='btn btn-success' onclick='typeEdit(this)' href='javascript:void(0);'>Update Info</a></td>";
        $html .= "</tr>";


    } else {
        //$html .= NO_DATA;
    }
    echo $html;

}elseif($action == 'updateprospect'){
    $db->UpdateWhere("prospect_queue",array("is_block"=>'0'),"block_timestamp <= '".date("Y-m-d H:i:s")."' and is_done = 0");
}elseif($action == 'addtime'){
    $data = $db->FilterParameters($_POST);
    if($data['prospect_id'] != ''){
        $db->UpdateWhere("prospect_queue",array("block_timestamp"=>date("Y-m-d H:i:s",strtotime("+2 min"))),"prospect_id = '{$data['prospect_id']}' and is_done = 0");
    }
} elseif($action == 'prospectinfo'){
    $data = $db->FilterParameters($_POST);
    $prospectId = isset($data['prospect_id']) ? $data['prospect_id'] : "";
    if($prospectId != ''){
        $mainTable = array("prospect_master as lm",array("lm.*"));
        $joinTable = array(
            array("left","state as s","s.state_id = lm.state_id",array("s.state_name")),
            array("left","city as c","c.city_id = lm.city_id",array("c.city_name")),
            array("left","salary_mode_master as smm","smm.salary_mode_id = lm.salary_mode_id",array("smm.salary_mode_name")),
            array("left","campaign_master as cm","cm.campaign_id = lm.campaign_id",array("cm.campaign_name")),
        );
        $prospectR = $db->JoinFetch($mainTable,$joinTable,"lm.prospect_id in ($prospectId)");
        $prospectInfo = $db->FetchToArrayFromResultset($prospectR);
        $html = '';
        $email = array();
        $number = array();
        if(count($prospectInfo) > 0){
            $contacts = $db->FetchToArray("prospect_contact","*","prospect_id = '{$prospectId}'");
            if(count($contacts) > 0) {
                foreach($contacts as $contact){
                    if($contact['contact_type'] == 'phone'){
                        $number[] = $contact['contact'];
                    } else {
                        $email[] = $contact['contact'];
                    }
                }
            }
            $html .= '<table class="table table-bordered">';
            $html .= '<tr><th>Prospect Name</th>';
            $html .= '<td>'.$prospectInfo[0]['first_name']." ".$prospectInfo[0]['last_name'].'</td><tr>';

            $html .= "<tr>";
            $html .= "<th>Employment Type</th>";
            $html .= "<td>".$prospectInfo[0]['employment_type']."</td></tr>";
            $html .= "<tr>";


            if($prospectInfo[0]['employment_type'] == 'salaried'){
                $html .= "<tr><th>Salary Amount</th>";
                $html .= "<td>".$prospectInfo[0]['salary_amount']."</td></tr><tr>";
                $html .= '<tr><th>Mode Of Salary</th>';
                $html .= '<td>'.$prospectInfo[0]['salary_mode_name'].'</td><tr>';
                $html .= "<tr><th>Work Experience</th>";
                $html .= "<td>".$prospectInfo[0]['work_experience']."</td></tr><tr>";
            }

            if($prospectInfo[0]['employment_type'] == 'businessman'){
                $html .= '<tr><th>Profit</th>';
                $html .= '<td>'.$prospectInfo[0]['profit'].'</td><tr>';
                $html .= '<tr><th>Business Vintage</th>';
                $html .= '<td>'.$prospectInfo[0]['business_vintage'].'</td><tr>';
                $html .= '<tr><th>Loan Obligation</th>';
                $html .= '<td>'.$prospectInfo[0]['loan_obligation'].'</td><tr>';
            }
            $html .= '<tr><th>Campaign</th>';
            $html .= '<td>'.$prospectInfo[0]['campaign_name'].'</td><tr>';
            $html .= '<tr><th>Email</th>';
            $html .= '<td>'.implode(",",$email).'</td><tr>';
            $html .= '<tr><th>Contact Number</th>';
            $html .= '<td>'.implode(",",$number).'</td><tr>';
            $html .= '<tr><th>Address</th>';
            $html .= '<td>'.$prospectInfo[0]['address'].'</td><tr>';
            $html .= '<tr><th>State</th>';
            $html .= '<td>'.$prospectInfo[0]['state_name'].'</td><tr>';
            $html .= '<tr><th>City</th>';
            $html .= '<td>'.$prospectInfo[0]['city_name'].'</td><tr>';
            $html .= '<tr><th>Pincode</th>';
            $html .= '<td>'.$prospectInfo[0]['pincode'].'</td><tr>';

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
