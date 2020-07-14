<?php
if(isset($_GET['filter']['start_date']) && $_GET['filter']['start_date'] != ''){
    $date_range_str = $_GET['filter']['start_date'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(cm.start_date,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(cm.start_date,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(cm.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
if(isset($_GET['filter']['end_date']) && $_GET['filter']['end_date'] != ''){
    $date_range_str = $_GET['filter']['end_date'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(cm.end_date,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(cm.end_date,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(cm.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
if(isset($_GET['filter']['created_on']) && $_GET['filter']['created_on'] != ''){
    $date_range_str = $_GET['filter']['created_on'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(cm.created_at,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(cm.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(cm.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
//echo $sql_where;

if(isset($_GET['filter']['campaign_name']) && $_GET['filter']['campaign_name'] != '')
{

    $sql_where .= " AND (cm.campaign_id in (" . implode(',', $_GET['filter']['campaign_name'])."))";
}
if(isset($_GET['filter']['campaign_type']) && $_GET['filter']['campaign_type'] != '')
{

    $sql_where .= " AND (ctm.campaign_type_id in (" . implode(',', $_GET['filter']['campaign_type'])."))";
}
if(isset($_GET['filter']['catrgory']) && $_GET['filter']['catrgory'] != '')
{
    $sql_where .= " AND (catm.category_id in (" . implode(',', $_GET['filter']['catrgory'])."))";
}
if(isset($_GET['filter']['created_by']) && $_GET['filter']['created_by'] != '')
{
    $sql_where .= " AND (au.user_id in (" . implode(',', $_GET['filter']['created_by'])."))";
}





