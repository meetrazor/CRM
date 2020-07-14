<?php
if(isset($_GET['filter']['created_on']) && $_GET['filter']['created_on'] != ''){
    $date_range_str = $_GET['filter']['created_on'];
    $date_range_arr = explode(" to ", $date_range_str);
    $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
    $rangeTo = Core::DMYToYMD($date_range_arr[1]);
    if($rangeFrom == $rangeTo){
        $range_condition = " && (DATE_FORMAT(l.created_at,'%Y-%m-%d') = '$rangeFrom')";
    } else {
        $range_condition = " && (DATE_FORMAT(l.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(l.created_at,'%Y-%m-%d') <= '$rangeTo')";
    }
    $sql_where .= $range_condition;

}
//echo $sql_where;
if(isset($_GET['filter']['lead_name']) && $_GET['filter']['lead_name'] != '')
{

    $sql_where .= " AND (l.lead_id in (" . implode(',', $_GET['filter']['lead_name'])."))";
}

if(isset($_GET['filter']['partner']) && $_GET['filter']['partner'] != '')
{
    $sql_where .= " AND (pm.partner_id in (" . implode(',', $_GET['filter']['partner'])."))";
}

if(isset($_GET['filter']['customer']) && $_GET['filter']['customer'] != '')
{
    $sql_where .= " AND (cm.customer_id in (" . implode(',', $_GET['filter']['customer'])."))";
}

if(isset($_GET['filter']['status']) && $_GET['filter']['status'] != '')
{
    $sql_where .= " AND (sm.status_id in (" . implode(',', $_GET['filter']['status'])."))";
}
if(isset($_GET['filter']['category']) && $_GET['filter']['category'] != '')
{
    $sql_where .= " AND (l.category_id in (" . implode(',', $_GET['filter']['category'])."))";
}
if(isset($_GET['filter']['locality']) && $_GET['filter']['locality'] != '')
{
    $sql_where .= " AND (l.sub_locality_id in (" . implode(',', $_GET['filter']['locality'])."))";
}
if(isset($_GET['filter']['state']) && $_GET['filter']['state'] != '')
{
    $sql_where .= " AND (s.state_id in (" . implode(',', $_GET['filter']['state'])."))";
}

if(isset($_GET['filter']['city']) && $_GET['filter']['city'] != '')
{
    $sql_where .= " AND (c.city_id in (" . implode(',', $_GET['filter']['city'])."))";
}
if(isset($_GET['filter']['telecaller']) && $_GET['filter']['telecaller'] != '')
{
    $sql_where .= " AND (bu.user_id in (" . implode(',', $_GET['filter']['telecaller'])."))";
}
