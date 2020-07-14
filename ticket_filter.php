<?php
if(isset($_GET['filter']['created_on']) && $_GET['filter']['created_on'] != ''){
    $date_range_str = $_GET['filter']['created_on'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(t.created_at,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(t.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(t.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
//echo $sql_where;
if(isset($_GET['filter']['customer']) && $_GET['filter']['customer'] != '')
{

    $sql_where .= " AND (cm.customer_id in (" . implode(',', $_GET['filter']['customer'])."))";
}

if(isset($_GET['filter']['disposition']) && $_GET['filter']['disposition'] != '')
{
    $sql_where .= " AND (dm.disposition_id in (" . implode(',', $_GET['filter']['disposition'])."))";
}
if(isset($_GET['filter']['status']) && $_GET['filter']['status'] != '')
{
    $sql_where .= " AND (sm.status_id in (" . implode(',', $_GET['filter']['status'])."))";
}
if(isset($_GET['filter']['query_stage']) && $_GET['filter']['query_stage'] != '')
{
    $sql_where .= " AND (t.query_stage_id in (" . implode(',', $_GET['filter']['query_stage'])."))";
}
if(isset($_GET['filter']['query_type']) && $_GET['filter']['query_type'] != '')
{
    $sql_where .= " AND (t.query_type_id in (" . implode(',', $_GET['filter']['query_type'])."))";
}
if(isset($_GET['filter']['created_by']) && $_GET['filter']['created_by'] != '')
{
    $sql_where .= " AND (cu.user_id in (" . implode(',', $_GET['filter']['created_by'])."))";
}



