<?php
if(isset($_GET['filter']['created_on']) && $_GET['filter']['created_on'] != ''){
    $date_range_str = $_GET['filter']['created_on'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(pm.created_at,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(pm.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(pm.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
//echo $sql_where;
if(isset($_GET['filter']['campaign_name']) && $_GET['filter']['campaign_name'] != '')
{

    $sql_where .= " AND (cm.campaign_id in (" . implode(',', $_GET['filter']['campaign_name'])."))";
}

if(isset($_GET['filter']['state']) && $_GET['filter']['state'] != '')
{
    $sql_where .= " AND (s.state_id in (" . implode(',', $_GET['filter']['state'])."))";
}

if(isset($_GET['filter']['city']) && $_GET['filter']['city'] != '')
{
    $sql_where .= " AND (c.city_id in (" . implode(',', $_GET['filter']['city'])."))";
}

if(isset($_GET['filter']['created_by']) && $_GET['filter']['created_by'] != '')
{
    $sql_where .= " AND (cu.user_id in (" . implode(',', $_GET['filter']['created_by'])."))";
}


