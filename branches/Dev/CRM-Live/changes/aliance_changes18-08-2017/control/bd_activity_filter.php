<?php
if(isset($_GET['filter']['created_on']) && $_GET['filter']['created_on'] != ''){
    $date_range_str = $_GET['filter']['created_on'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(am.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
//echo $sql_where;
if(isset($_GET['filter']['lead_name']) && $_GET['filter']['lead_name'] != '')
{

    $sql_where .= " AND (lm.lead_id in (" . implode(',', $_GET['filter']['lead_name'])."))";
}

if(isset($_GET['filter']['activity']) && $_GET['filter']['activity'] != '')
{
    $activity=$_GET['filter']['activity'];
    $activityNames = implode("','",$activity);
    $sql_where .= " AND (am.activity_type in ('$activityNames'))";
}

if(isset($_GET['filter']['status']) && $_GET['filter']['status'] != '')
{
    $sql_where .= " AND (sm.status_id in (" . implode(',', $_GET['filter']['status'])."))";
}

if(isset($_GET['filter']['created_by']) && $_GET['filter']['created_by'] != '')
{
    $sql_where .= " AND (cu.user_id in (" . implode(',', $_GET['filter']['created_by'])."))";
}


