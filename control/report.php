<?php
include_once 'header.php';
include_once '../core/Validator.php';
include_once '../phpexcel/PHPExcel.php';
$table = "prospect_master";
$table_id = 'prospect_id';
$user_id = $ses->Get("user_id");
$action = $db->FilterParameters($_GET['act']);
if('telecaller' === $action){
    $data = $db->FilterParameters($_POST);
    $campaignId = (isset($data['campaign_id'])) ? $data['campaign_id']  : "";
    $categoryId = (isset($data['category_id'])) ? $data['category_id']  : "";
    $cityId = (isset($data['city_id'])) ? $data['city_id']  : "";
    $userId = (isset($data['tele_caller_id'])) ? $data['tele_caller_id']  : "";
    $mainTable = array("activity_master as am",array("concat(u.first_name,' ',u.last_name) as user_name",'count(am.activity_id) as call_count',"u.user_id"));
    $joinTable = array(
        array("left","prospect_master as pm","pm.prospect_id = am.type_id"),
        array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id"),
        array("left","category_master as catm","catm.category_id = pm.category_id"),
        array("left","admin_user as u","u.user_id = am.created_by"),
        array("left","city as c","c.city_id = pm.city_id")

    );
    $condition = ($userId != '') ? "am.created_by = '{$userId}'" : "1=1";
    $leadCountCondition = '1=1';
    if(isset($data['date']) && $data['date'] != ''){
        $date_range_str = $data['date'];
        $date_range_arr = explode(" to ", $date_range_str);
        $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
        $rangeTo = Core::DMYToYMD($date_range_arr[1]);
        if($rangeFrom == $rangeTo){
            $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') = '$rangeFrom')";
            $leadCountCondition .= " && (DATE_FORMAT(lm.created_at,'%Y-%m-%d') = '$rangeFrom')";
        } else {
            $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(am.created_at,'%Y-%m-%d') <= '$rangeTo')";
            $leadCountCondition .= " && (DATE_FORMAT(lm.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(lm.created_at,'%Y-%m-%d') <= '$rangeTo')";
        }
        $condition .= $range_condition;

    }

    if($campaignId != ''){
        if(is_array($campaignId)){
            $cIds = implode(",",$campaignId);
        } else {
            $cIds = $campaignId;
        }
        $condition .= " and cm.campaign_id in ($cIds)";
        $leadCountCondition .= " and cm.campaign_id in ($cIds)";
    }

    if($categoryId != ''){
        if(is_array($categoryId)){
            $catIds = implode(",",$categoryId);
        } else {
            $catIds = $categoryId;
        }
        $condition .= " and catm.category_id in ($catIds)";
        $leadCountCondition .= " and catm.category_id in ($catIds)";

    }

    if($cityId != ''){
        if(is_array($cityId)){
            $cityIds = implode(",",$cityId);
        } else {
            $cityIds = $cityId;
        }
        $condition .= " and c.city_id in ($cityIds)";
        $leadCountCondition .= " and c.city_id in ($cityIds)";
    }

    $userWiseQ = $db->JoinFetch($mainTable,$joinTable,$condition,array("count(am.activity_id)"=>"desc"),null,"u.user_id");
    $userWiseRes = $db->FetchToArrayFromResultset($userWiseQ);

    if(empty($userWiseRes)){
        $userWiseRes =  array(array(
            "user_name" => 0,
            "call_count" => 0,
            "user_id" => 0
        ));
    }
        if(count($userWiseRes) > 0){
        foreach ($userWiseRes as $userData){
            $LCondition = $leadCountCondition." and lm.created_by = '{$userData['user_id']}'";
            $callToLead = Utility::userCount("lead_count",$LCondition);
            $dataArrElem = array(
                "User Name"                      => $userData['user_name'],
                "User Count"                         => $userData['call_count'],
                "Lead Count"                         => $callToLead,
            );
            $dataChartElem = array();
            foreach ($dataArrElem as $key => $value){
                $value = ($value == '') ? "other" : $value;
                $dataChartElem[] = ($key != "User Name") ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
    }

    $response = array("success" => true, "report_data" =>$dataChart);
    echo json_encode($response);

} elseif('cityreport' == $action){
    $data = $db->FilterParameters($_POST);
    $cityId = (isset($data['city_id'])) ? $data['city_id']  : "";
    $campaignId = (isset($data['campaign_id'])) ? $data['campaign_id']  : "";
    $categoryId = (isset($data['category_id'])) ? $data['category_id']  : "";
    $mainTable = array("prospect_master as pm",array("c.city_name",'count(*) as city_count',"c.city_id"));
    $joinTable = array(
        array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id"),
        array("left","category_master as catm","catm.category_id = pm.category_id"),
        array("left","city as c","c.city_id = pm.city_id")

    );
    $condition = ($cityId != '') ? "c.city_id = '{$cityId}'" : "1=1";
    $cityCountCondition = '1=1';
    $leadCountCondition = '1=1';
    $prospectCondition = ' and 1=1';
    if(isset($data['date']) && $data['date'] != ''){
        $date_range_str = $data['date'];
        $date_range_arr = explode(" to ", $date_range_str);
        $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
        $rangeTo = Core::DMYToYMD($date_range_arr[1]);
        if($rangeFrom == $rangeTo){
            $range_condition = " && (pm.DATE_FORMAT(pm.created_at,'%Y-%m-%d') = '$rangeFrom')";
            $cityCountCondition .= " && (DATE_FORMAT(created_at,'%Y-%m-%d') = '$rangeFrom')";
            $leadCountCondition .= " && (lm.DATE_FORMAT(created_at,'%Y-%m-%d') = '$rangeFrom')";
        } else {
            $range_condition = " && (pm.DATE_FORMAT(pm.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(pm.created_at,'%Y-%m-%d') <= '$rangeTo')";
            $cityCountCondition .= " && (DATE_FORMAT(created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(created_at,'%Y-%m-%d') <= '$rangeTo')";
            $leadCountCondition .= " && (lm.DATE_FORMAT(created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(lm.created_at,'%Y-%m-%d') <= '$rangeTo')";
        }
        $condition .= $range_condition;

    }

    if($campaignId != ''){
        if(is_array($campaignId)){
            $cIds = implode(",",$campaignId);
        } else {
            $cIds = $campaignId;
        }
        $condition .= " and cm.campaign_id in ($cIds)";
        $leadCountCondition .= " and cm.campaign_id in ($cIds)";
        $prospectCondition .= " and campaign_id in ($cIds)";
    }

    if($categoryId != ''){
        if(is_array($categoryId)){
            $catIds = implode(",",$categoryId);
        } else {
            $catIds = $categoryId;
        }
        $condition .= " and catm.category_id in ($catIds)";
        $leadCountCondition .= " and catm.category_id in ($catIds)";
        $prospectCondition .= " and category_id in ($catIds)";
    }

    $cityWiseQ = $db->JoinFetch($mainTable,$joinTable,$condition,array("count(*)"=>"desc"),array(0,10),"c.city_id");
    $cityWiseRes = $db->FetchToArrayFromResultset($cityWiseQ);
    if(empty($cityWiseRes)){
        $cityWiseRes =  array(array(
            "city_name" => 0,
            "city_count" => 0,
            "city_id" => 0
        ));
    }

    if(count($cityWiseRes) > 0){
        foreach ($cityWiseRes as $cityData){
            $cCondition = $cityCountCondition." && type_id in (select prospect_id from prospect_master where city_id = '{$cityData['city_id']}' $prospectCondition) AND source_type = 'prospect'";
            $cityCall = Utility::userCount("call",$cCondition);
            $LCondition = $leadCountCondition." and lm.city_id = '{$cityData['city_id']}'";
            $callToLead = Utility::userCount("lead_count",$LCondition);
            $dataArrElem = array(
                "City Name"                      => $cityData['city_name'],
                "City Count"                         => $cityData['city_count'],
                "City Lead"                         => $callToLead,
                "City Call"                         => $cityCall,
            );
            $dataChartElem = array();
            foreach ($dataArrElem as $key => $value){
                $value = ($value == '') ? "other" : $value;
                $dataChartElem[] = ($key != "City Name") ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
    }

    $response = array("success" => true, "report_data" =>$dataChart);
    echo json_encode($response);

}elseif('leadreport' == $action){
    $data = $db->FilterParameters($_POST);

    $condition = "1=1";
    $leadCondition = "1=1";

    if(isset($data['date']) && $data['date'] != ''){
        $date_range_str = $data['date'];
        $date_range_arr = explode(" to ", $date_range_str);
        $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
        $rangeTo = Core::DMYToYMD($date_range_arr[1]);
        if($rangeFrom == $rangeTo){
            $leadCondition .= " && (DATE_FORMAT(lm.created_at,'%Y-%m-%d') = '$rangeFrom')";
            $range_condition = " && (DATE_FORMAT(created_at,'%Y-%m-%d') = '$rangeFrom')";
        } else {
            $leadCondition .= " && (DATE_FORMAT(lm.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(lm.created_at,'%Y-%m-%d') <= '$rangeTo')";
            $range_condition = " && (DATE_FORMAT(created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(created_at,'%Y-%m-%d') <= '$rangeTo')";
        }
        $condition .= $range_condition;

    }

    $leadRes =  array(
        "Total Prospect" =>  Utility::userCount("prospect",$condition),
        "Total Call" =>  Utility::userCount("call",$condition." group by type_id"),
        "Total Lead" =>  Utility::userCount("lead_count",$leadCondition),
    );

    if(count($leadRes) > 0){
        foreach ($leadRes as $label => $leadData){
            $dataArrElem = array(
                $label                      => $leadData,
            );
            $dataChartElem = array();
            foreach ($dataArrElem as $key => $value){

                $dataChartElem[] = $key;
                $dataChartElem[] = (int) $value;
            }
            $dataChart[] = $dataChartElem;
        }
    }

    $response = array("success" => true, "report_data" =>$dataChart);
    echo json_encode($response);

}elseif('dispositionreport' == $action){
    $data = $db->FilterParameters($_POST);
    $campaignId = (isset($data['campaign_id'])) ? $data['campaign_id']  : "";
    $dispositionId = (isset($data['disposition_id'])) ? $data['disposition_id']  : "";

    $condition = "am.is_latest = 1 and source_type = 'prospect'";

    if($dispositionId != ''){
        if(is_array($dispositionId)){
            $dIds = implode(",",$dispositionId);
        } else {
            $dIds = $dispositionId;
        }
        $condition .= " and dm.disposition_id in ($dIds)";
    }

    if($campaignId != ''){
        if(is_array($campaignId)){
            $cIds = implode(",",$campaignId);
        } else {
            $cIds = $campaignId;
        }
        $condition .= " and cm.campaign_id in ($cIds)";
    }


    if(isset($data['date']) && $data['date'] != ''){
        $date_range_str = $data['date'];
        $date_range_arr = explode(" to ", $date_range_str);
        $rangeFrom = Core::DMYToYMD($date_range_arr[0]);
        $rangeTo = Core::DMYToYMD($date_range_arr[1]);
        if($rangeFrom == $rangeTo){
            $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') = '$rangeFrom')";
        } else {
            $range_condition = " && (DATE_FORMAT(am.created_at,'%Y-%m-%d') >= '$rangeFrom' AND DATE_FORMAT(am.created_at,'%Y-%m-%d') <= '$rangeTo')";
        }
        $condition .= $range_condition;

    }

    $mainTable = array("activity_master as am",array("dm.disposition_name",'count(*) as disposition_count'));
    $joinTable = array(
        array("left","prospect_master as pm","pm.prospect_id = am.type_id"),
        array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id"),
        array("left","disposition_master as dm","dm.disposition_id = am.disposition_id")
    );

    $dispositionRes = $db->JoinFetch($mainTable,$joinTable,$condition,array("count(*)"=>"desc"),null,"dm.disposition_id");
    $dispositionWiseData = $db->FetchToArrayFromResultset($dispositionRes);
    if(empty($dispositionWiseData)){
        $dispositionWiseData =  array(array(
            "disposition_name" => 0,
            "disposition_count" => 0
        ));
    }

    if(count($dispositionWiseData) > 0){
        foreach ($dispositionWiseData as $dispositionData){
            $dataArrElem = array(
                "Disposition Name"                      => $dispositionData['disposition_name'],
                "Disposition Count"                         => $dispositionData['disposition_count']
            );
            $dataChartElem = array();
            foreach ($dataArrElem as $key => $value){
                $value = ($value == '') ? "other" : $value;
                $dataChartElem[] = ($key != "Disposition Name") ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
    }

    $response = array("success" => true, "report_data" =>$dataChart);
    echo json_encode($response);

}
elseif('campaignreport' == $action){
    $data = $db->FilterParameters($_POST);
    $campaignId = (isset($data['campaign_id'])) ? $data['campaign_id']  : "";

    $condition = "1=1";


    if($campaignId != ''){
        if(is_array($campaignId)){
            $cIds = implode(",",$campaignId);
        } else {
            $cIds = $campaignId;
        }
        $condition .= " and cm.campaign_id in ($cIds)";
    }


    $mainTable = array("prospect_master as pm",array("cm.campaign_name",'count(*) as campaign_count'));
    $joinTable = array(
        array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id")

    );
    $campaignRes = $db->JoinFetch($mainTable,$joinTable,$condition,array("count(*)"=>"desc"),array(0,10),"cm.campaign_id");
    $campaignWiseData = $db->FetchToArrayFromResultset($campaignRes);
    if(empty($campaignWiseData)){
        $campaignWiseData =  array(array(
            "campaign_name" => 0,
            "campaign_count" => 0
        ));
    }

    if(count($campaignWiseData) > 0){
        foreach ($campaignWiseData as $campaignData){
            $dataArrElem = array(
                "campaign Name"                      => $campaignData['campaign_name'],
                "campaign Count"                         => $campaignData['campaign_count']
            );
            $dataChartElem = array();
            foreach ($dataArrElem as $key => $value){
                $value = ($value == '') ? "other" : $value;
                $dataChartElem[] = ($key != "campaign Name") ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
    }

    $response = array("success" => true, "report_data" =>$dataChart);
    echo json_encode($response);

}
elseif('monthreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterYear = (isset($data['year'])) ? $data['year']  : "";
    $filterMonth = (isset($data['month'])) ? $data['month']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = 't.is_merged != 1';

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterYear != ''){
        $selectedYear = implode(",",$filterYear);
        $misCondition .= " AND (DATE_FORMAT(t.created_at, '%Y') in ($selectedYear))";
    }

    if($filterMonth != ''){
        $selectedMonth = implode(",",$filterMonth);
        $misCondition .= " AND (DATE_FORMAT(t.created_at, '%m') in ($selectedMonth))";
    }

    $mainTable = array("tickets as t",array("count(*) as number","DATE_FORMAT(t.created_at, '%b-%y') as month"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"desc","MONTH(t.created_at)"=>"desc"),$limit,"MONTH(t.created_at), YEAR(t.created_at),
     IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");

    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $monthFormatData = array();
    $totalCount = 0;
    $totalPercent = 0;
    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $monthFormatData[$value['month']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));
    if($reportType == 'html'){
        if(count($monthFormatData) > 0){
        ?>
        <table class="table" id="dg_mis">
            <thead>
            <tr>
                <th>Month</th>
                <?php
                $total = array();
                foreach($statusData as $statusName){ ?>
                    <th><?php echo $statusName; ?></th>
                <?php }?>
                <th>Total</th>
                <th>In %</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(count($monthFormatData) > 0){
                foreach($monthFormatData as $monthKey => $monthData){

                    if($monthKey != ''){
                        ?>
                        <tr>
                            <td><?php echo $monthKey; ?></td>

                            <?php
                            $subTotalOfStatus = 0;
                            if(count($monthData) > 0){

                                foreach($statusData as $statusName){

                                    if(array_key_exists($statusName,$monthData)){
                                        $subTotalOfStatus += $monthData[$statusName];
                                        echo "<td class='te-number'>".$monthData[$statusName]."</td>";
                                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $monthData[$statusName] : $monthData[$statusName];
                                    } else {
                                        echo "<td>-</td>";
                                    }
                                }
                            }
                            $totalPercent += (100*$subTotalOfStatus/$totalCount);
                            echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                            echo "<td class='te-number'>".round(100*$subTotalOfStatus/$totalCount , 2)."</td>";
                            ?>
                        </tr>
                    <?php }?>
                <?php }?>
            <?php } ?>
            <?php
            if(count($monthFormatData) > 0){
                echo "<tr>";
                    echo "<th colspan='1'>Total</th>";

                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                    echo "<td class='te-number'><b>".$totalCount."</b></td>";
                    echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Month",
         );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;
        foreach ($monthFormatData as $monthKey => $monthData) {
            $dataRows[$key] = array($monthKey);
            $subTotalOfStatus = 0;
            foreach($statusData as $statusName){
                if(array_key_exists($statusName,$monthData)){
                    $subTotalOfStatus += $monthData[$statusName];
                    array_push($dataRows[$key],$monthData[$statusName]);
                    $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $monthData[$statusName] : $monthData[$statusName];
                } else {
                    array_push($dataRows[$key],0);
                }
            }
            // add Row Total
            array_push($dataRows[$key],$subTotalOfStatus);
            // add Row Percentage
            array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
            $totalPercent += (100*$subTotalOfStatus/$totalCount);
            $key = $key + 1;
        }
        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("month_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    } elseif($reportType == 'chart'){

        if(empty($misData)){
            $misData =  array(array(
                "month_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Month Name";
        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($monthFormatData) > 0){
            foreach($monthFormatData as $monthKey => $monthData){
                $dataArrElem = array(
                    "Month Name"                      => $monthKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$monthData)){
                        $subTotalOfStatus += $monthData[$statusName];
                        $dataArrElem[$statusName] = $monthData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Month Name") ? (int) $value : $value.$percent;
                    if($key != "Month Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;

                }
                $dataChart[] = $dataChartElem;

            }
        }else {
            $dataArrElem = array(
                "Month Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Month Name") ? (int) $value : $value;
                if($key != "Month Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }

}elseif ('segmentreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterSegmentName = (isset($data['call_from'])) ? $data['call_from']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = 't.is_merged != 1';

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){

        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterSegmentName != ''){
        $filterSegmentName = implode("','",$filterSegmentName);
        $misCondition .= " AND (t.call_from in ('$filterSegmentName'))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number","t.call_from as segment"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.call_from IS NULL OR t.call_from = '', 0, t.call_from),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $segmentFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;
    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['segment'] = ($value['segment'] != '') ?  $value['segment'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $segmentFormatData[$value['segment']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));
    if($reportType == 'html'){
        if(count($segmentFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Segment</th>
                    <?php
                    $total = array();
                    foreach($statusData as $statusName){ ?>
                        <th><?php echo $statusName; ?></th>
                    <?php }?>
                    <th>Total</th>
                    <th>In %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($segmentFormatData) > 0){
                    foreach($segmentFormatData as $segmentKey => $segmentData){

                        if($segmentKey != ''){
                            ?>
                            <tr>
                                <td><?php echo $segmentKey; ?></td>

                                <?php
                                $subTotalOfStatus = 0;
                                if(count($segmentData) > 0){

                                    foreach($statusData as $statusName){

                                        if(array_key_exists($statusName,$segmentData)){
                                            $subTotalOfStatus += $segmentData[$statusName];
                                            echo "<td class='te-number'>".$segmentData[$statusName]."</td>";
                                            $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $segmentData[$statusName] : $segmentData[$statusName];
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    }
                                }
                                $totalPercent += (100*$subTotalOfStatus/$totalCount);
                                echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                echo "<td class='te-number'>".round(100*$subTotalOfStatus/$totalCount , 2)."</td>";
                                ?>
                            </tr>
                        <?php }?>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($segmentFormatData) > 0){
                    echo "<tr>";
                        echo "<th colspan='1'>Total</th>";

                        foreach($statusData as $statusName){
                            if(array_key_exists($statusName,$total)){
                                echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                            } else {

                                echo "<td>-</td>";
                            }
                        }
                        echo "<td class='te-number'><b>".$totalCount."</b></td>";
                        echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Segment",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($segmentFormatData as $segmentKey => $segmentData){
            $dataRows[$key] = array($segmentKey);

            $subTotalOfStatus = 0;
            if(count($segmentData) > 0){

                foreach($statusData as $statusName){

                    if(array_key_exists($statusName,$segmentData)){
                        $subTotalOfStatus += $segmentData[$statusName];
                        array_push($dataRows[$key],$segmentData[$statusName]);
                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $segmentData[$statusName] : $segmentData[$statusName];
                    } else {
                        array_push($dataRows[$key],0);
                    }
                }
            }
            // add Row Total
            array_push($dataRows[$key],$subTotalOfStatus);
            // add Row Percentage
            array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
            $totalPercent += (100*$subTotalOfStatus/$totalCount);
            $key = $key + 1;
       }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("segment_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    } elseif($reportType == 'chart'){
        if(empty($misData)){
            $misData =  array(array(
                "segment_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Segment Name";

        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($segmentFormatData) > 0){
            foreach($segmentFormatData as $segmentKey => $segmentData){
                $dataArrElem = array(
                    "Segment Name"                      => $segmentKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$segmentData)){
                        $subTotalOfStatus += $segmentData[$statusName];
                        $dataArrElem[$statusName] = $segmentData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Segment Name") ? (int) $value : $value.$percent;
                    if($key != "Segment Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;
                }
                $dataChart[] = $dataChartElem;

            }
        }else {
            $dataArrElem = array(
                "Segment Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Segment Name") ? (int) $value : $value;
                if($key != "Segment Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }
}elseif ('priorityreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterQueryStageId = (isset($data['query_stage_id'])) ? $data['query_stage_id']  : "";
    $filterReasonId = (isset($data['reason_id'])) ? $data['reason_id']  : "";
    $filterPriorityId = (isset($data['query_type_id'])) ? $data['query_type_id']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = 't.is_merged != 1';

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterPriorityId != ''){
        $selectedPriority = implode(",",$filterPriorityId);
        $misCondition .= " AND (t.query_type_id in ($selectedPriority))";
    }

    if($filterReasonId != ''){
        $selectedReason = implode(",",$filterReasonId);
        $misCondition .= " AND (t.reason_id in ($selectedReason))";
    }

    if($filterQueryStageId != ''){
        $selectedQueryStage = implode(",",$filterQueryStageId);
        $misCondition .= " AND (t.query_stage_id in ($selectedQueryStage))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number,t.query_stage_id,t.reason_id"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","query_type_master as qtm","qtm.query_type_id = t.query_type_id",array("qtm.query_type_name as priority")),
        array("left","query_stage_master as qsm","qsm.query_stage_id = t.query_stage_id",array("qsm.query_stage_name As query_stage")),
        array("left","reason_master as rm","t.reason_id = rm.reason_id",array("rm.reason_name")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.query_type_id IS NULL OR t.query_type_id = '', 0, t.query_type_id),
    IF(t.reason_id IS NULL OR t.reason_id = '', 0, t.reason_id),
    IF(t.query_stage_id IS NULL OR t.query_stage_id = '', 0, t.query_stage_id),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $priorityFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;

    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['priority'] = ($value['priority'] != '') ?  $value['priority'] : "Blank";
        $value['query_stage'] = ($value['query_stage'] != '') ?  $value['query_stage'] : "Blank";
        $value['reason_name'] = ($value['reason_name'] != '') ?  $value['reason_name'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $value['query_stage'] = $value['query_stage_id']."@".$value['query_stage'];
        $value['reason_name'] = $value['reason_id']."@".$value['reason_name'];
        $priorityFormatData[$value['priority']][$value['reason_name']][$value['query_stage']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));

    if($reportType == 'html'){
        $total = array();
        if(count($priorityFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Priority</th>
                    <th>Reason</th>
                    <th>Query Stage</th>
                    <?php
                    foreach($statusData as $statusName){ ?>
                        <th><?php echo $statusName; ?></th>
                    <?php } ?>
                    <th>Total</th>
                    <th>In %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($priorityFormatData) > 0){
                    foreach($priorityFormatData as $priorityName => $reasonArray){
                        $priorityDisplay = 1;
                        $totalOfPriority = 0;
                        $totalOfPriorityPer = 0;
                        $priorityStatusTotal = array();
                        foreach($reasonArray as $reasonName => $queryStageArray) {
                            $reasonDisplay = 1;
                            $reasonName = trim(strstr($reasonName,"@"),"@");
                            $reasonStatusTotal = array();
                            $totalReasonStatus = 0;
                            $totalReasonStatusPer = 0;
                            foreach ($queryStageArray as $queryStageName => $queryStageData) {
                                if ($priorityName != '') {
                                    ?>
                                    <tr>
                                        <td><?php echo ($priorityDisplay == 1) ? $priorityName : ""; ?></td>
                                        <td><?php echo ($reasonDisplay == 1) ? $reasonName : ""; ?></td>
                                        <td><?php echo trim(strstr($queryStageName,"@"),"@"); ?></td>

                                        <?php
                                        $subTotalOfStatus = 0;
                                        if (count($queryStageData) > 0) {

                                            foreach ($statusData as $statusName) {
                                                if (is_array($queryStageData)) {
                                                    if (array_key_exists($statusName, $queryStageData)) {
                                                        $subTotalOfStatus += $queryStageData[$statusName];
                                                        $totalReasonStatus += $queryStageData[$statusName];
                                                        echo "<td class='te-number'>" . $queryStageData[$statusName] . "</td>";
                                                        $total[$statusName] = (array_key_exists($statusName, $total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                                        $reasonStatusTotal[$statusName] = (array_key_exists($statusName, $reasonStatusTotal)) ? $reasonStatusTotal[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                                        $priorityStatusTotal[$statusName] = (array_key_exists($statusName, $priorityStatusTotal)) ? $priorityStatusTotal[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                                    } else {
                                                        echo "<td>-</td>";
                                                    }
                                                }
                                            }
                                        }
                                        $statusPer = (100*$subTotalOfStatus/$totalCount);
                                        $totalReasonStatusPer += $statusPer;
                                        $totalPercent += $statusPer;
                                        echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                        echo "<td class='te-number'>".core::numberFormat($statusPer)."</td>";
                                        ?>
                                    </tr>
                                <?php }
                                $priorityDisplay = 0;
                                $reasonDisplay = 0;
                            } ?>
                            <tr style="text-align: center;font-weight: bold;">
                                <th colspan="3" class="center"><?php echo "Total Of ".$reasonName; ?></th>
                                <?php
                                foreach($statusData as $statusName){
                                    if(array_key_exists($statusName,$reasonStatusTotal)){
                                        echo "<td class='te-number'><b>".$reasonStatusTotal[$statusName]."</b></td>";
                                    } else {
                                        echo "<td>0</td>";
                                    }
                                }
                                $totalOfPriority += $totalReasonStatus;
                                $totalOfPriorityPer += $totalReasonStatusPer;
                                echo "<td class='te-number'>".$totalReasonStatus."</td>";
                                echo "<td class='te-number'>".core::numberFormat($totalReasonStatusPer)."</td>";
                                ?>
                            </tr>
                        <?php } ?>
                        <tr style="text-align: center;font-weight: bold;">
                            <th colspan="3" class="center"><?php echo "Total Of ".$priorityName; ?></th>
                            <?php
                            foreach($statusData as $statusName){
                                if(array_key_exists($statusName,$priorityStatusTotal)){
                                    echo "<td class='te-number'><b>".$priorityStatusTotal[$statusName]."</b></td>";
                                } else {
                                    echo "<td>0</td>";
                                }
                            }
                            ?>
                            <td class='te-number'><?php echo $totalOfPriority; ?></td>
                            <td class='te-number'><?php echo core::numberFormat($totalOfPriorityPer); ?></td>
                        </tr>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($priorityFormatData) > 0){
                    echo "<tr>";
                        echo "<th colspan='3' class='center'>Grand Total</th>";

                        foreach($statusData as $statusName){
                            if(array_key_exists($statusName,$total)){
                                echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                            } else {

                                echo "<td>-</td>";
                            }
                        }
                        echo "<td class='te-number'><b>".$totalCount."</b></td>";
                        echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Priority",
            "Reason",
            "Query Stage",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($priorityFormatData as $priorityName => $reasonArray) {
            foreach ($reasonArray as $reasonName => $reasonData) {

                foreach ($reasonData as $queryStageName => $queryStageData) {

                    if ($reasonName != '') {
                        $dataRows[$key] = array($priorityName);
                        //add reason name
                        array_push($dataRows[$key],trim(strstr($reasonName, "@"), "@"));
                        //add query stage
                        array_push($dataRows[$key], trim(strstr($queryStageName, "@"), "@"));

                        $subTotalOfStatus = 0;
                        if (count($queryStageData) > 0) {

                            foreach ($statusData as $statusName) {

                                if (array_key_exists($statusName, $queryStageData)) {
                                    $subTotalOfStatus += $queryStageData[$statusName];
                                    array_push($dataRows[$key], $queryStageData[$statusName]);
                                    $total[$statusName] = (array_key_exists($statusName, $total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                } else {
                                    array_push($dataRows[$key], 0);
                                }
                            }
                        }
                        // add Row Total
                        array_push($dataRows[$key], $subTotalOfStatus);
                        // add Row Percentage
                        array_push($dataRows[$key], round(100 * $subTotalOfStatus / $totalCount, 2));
                        $totalPercent += (100 * $subTotalOfStatus / $totalCount);
                        $key = $key + 1;
                    }

                }
            }
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total','','');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("priority_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;
    } elseif($reportType == 'chart'){
        $priorityFormatChartData = array();
        foreach($misData as $key => $value){
            if(isset($priorityFormatChartData[$value['priority']]) && in_array($priorityFormatChartData[$value['priority']],$priorityFormatChartData)){
                if(array_key_exists($value['status'],$priorityFormatChartData[$value['priority']])){
                    $priorityFormatChartData[$value['priority']][$value['status']] = $value['number'] + $priorityFormatChartData[$value['priority']][$value['status']];
                } else {
                    $priorityFormatChartData[$value['priority']][$value['status']] = $value['number'];
                }
            } else {
                $priorityFormatChartData[$value['priority']][$value['status']] = $value['number'];
            }
        }

        if(empty($misData)){
            $misData =  array(array(
                "priority_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Priority Name";

        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($priorityFormatChartData) > 0){
            foreach($priorityFormatChartData as $priorityKey => $priorityData){
                $dataArrElem = array(
                    "Priority Name"                      => $priorityKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$priorityData)){
                        $subTotalOfStatus += $priorityData[$statusName];
                        $dataArrElem[$statusName] = $priorityData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Priority Name") ? (int) $value : $value.$percent;
                    if($key != "Priority Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;
                }
                $dataChart[] = $dataChartElem;
            }
        } else {
            $dataArrElem = array(
                "Priority Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Priority Name") ? (int) $value : $value;
                if($key != "Priority Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }
}elseif ('bankreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterQueryStageId = (isset($data['query_stage_id'])) ? $data['query_stage_id']  : "";
    $filterReasonId = (isset($data['reason_id'])) ? $data['reason_id']  : "";
    $filterBankId = (isset($data['bank_id'])) ? $data['bank_id']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = 'sm.status_id is not null and t.is_merged != 1';

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterBankId != ''){
        $selectedBank = implode(",",$filterBankId);
        $misCondition .= " AND (t.bank_id in ($selectedBank))";
    }

    if($filterReasonId != ''){
        $selectedReason = implode(",",$filterReasonId);
        $misCondition .= " AND (t.reason_id in ($selectedReason))";
    }

    if($filterQueryStageId != ''){
        $selectedQueryStage = implode(",",$filterQueryStageId);
        $misCondition .= " AND (t.query_stage_id in ($selectedQueryStage))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number,t.query_stage_id,t.reason_id"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","query_stage_master as qsm","qsm.query_stage_id = t.query_stage_id",array("qsm.query_stage_name As query_stage")),
        array("left","reason_master as rm","rm.reason_id = t.reason_id",array("rm.reason_name As reason_name")),
        array("left","bank_master as bm","bm.bank_id = t.bank_id",array("bm.bank_name As bank_name")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.bank_id IS NULL OR t.bank_id = '', 0, t.bank_id),
    IF(t.reason_id IS NULL OR t.reason_id = '', 0, t.reason_id),
    IF(t.query_stage_id IS NULL OR t.query_stage_id = '', 0, t.query_stage_id),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $bankFormatData = array(); 

    $totalCount = 0;
    $totalPercent = 0;

    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['bank_name'] = ($value['bank_name'] != '') ? $value['bank_name'] : "Blank";
        $value['query_stage'] = ($value['query_stage'] != '') ?  $value['query_stage'] : "Blank";
        $value['reason_name'] = ($value['reason_name'] != '') ?  $value['reason_name'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $value['query_stage'] = $value['query_stage_id']."@".$value['query_stage'];
        $value['reason_name'] = $value['reason_id']."@".$value['reason_name'];
        $bankFormatData[$value['bank_name']][$value['reason_name']][$value['query_stage']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));

    if($reportType == 'html'){
        $total = array();
        if(count($bankFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Bank</th>
                    <th>Reason</th>
                    <th>Query Stage</th>
                    <?php
                    foreach($statusData as $statusName){ ?>
                        <th><?php echo $statusName; ?></th>
                    <?php } ?>
                    <th>Total</th>
                    <th>In %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($bankFormatData) > 0){
                    foreach($bankFormatData as $bankName => $reasonData){
                        $bankDisplay = 1;
                        $totalOfBank = 0;
                        $totalOfBankPer = 0;
                        $bankStatusTotal = array();
                        ?>
                        <?php
                        foreach($reasonData as $reasonName => $queryStageData){
                            $reasonDisplay = 1;
                            $reasonName = trim(strstr($reasonName,"@"),"@");
                            $reasonStatusTotal = array();
                            $totalReasonStatus = 0;
                            $totalReasonStatusPer = 0;
                            foreach($queryStageData as $queryStageName => $stageStatusData){

                                if($reasonName != ''){ ?>
                                    <tr>
                                        <td><?php echo ($bankDisplay == 1) ? $bankName : ""; ?></td>
                                        <td><?php echo ($reasonDisplay == 1) ? $reasonName : ""; ?></td>
                                        <td><?php echo trim(strstr($queryStageName,"@"),"@"); ?></td>

                                        <?php
                                        $subTotalOfStatus = 0;
                                        if(count($stageStatusData) > 0){
                                            foreach($statusData as $statusName){
                                                if(array_key_exists($statusName,$stageStatusData)){
                                                    $subTotalOfStatus += $stageStatusData[$statusName];
                                                    $totalReasonStatus += $stageStatusData[$statusName];
                                                    echo "<td class='te-number'>".$stageStatusData[$statusName]."</td>";
                                                    $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                    $reasonStatusTotal[$statusName] = (array_key_exists($statusName,$reasonStatusTotal)) ? $reasonStatusTotal[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                    $bankStatusTotal[$statusName] = (array_key_exists($statusName, $bankStatusTotal)) ? $bankStatusTotal[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                } else {
                                                    echo "<td>-</td>";
                                                }
                                            }
                                        }
                                        $statusPer = (100*$subTotalOfStatus/$totalCount);
                                        $totalReasonStatusPer += $statusPer;
                                        $totalPercent += $statusPer;
                                        echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                        echo "<td class='te-number'>".core::numberFormat($statusPer)."</td>";
                                        ?>
                                    </tr>
                                <?php }
                                $bankDisplay = 0;
                                $reasonDisplay = 0;
                            } ?>
                            <tr style="text-align: center;font-weight: bold;">
                                <th colspan="3" class="center"><?php echo "Total Of ".$reasonName; ?></th>
                                <?php
                                foreach($statusData as $statusName){
                                    if(array_key_exists($statusName,$reasonStatusTotal)){
                                        echo "<td class='te-number'><b>".$reasonStatusTotal[$statusName]."</b></td>";
                                    } else {
                                        echo "<td>0</td>";
                                    }
                                }
                                $totalOfBank += $totalReasonStatus;
                                $totalOfBankPer += $totalReasonStatusPer;
                                echo "<td class='te-number'>".$totalReasonStatus."</td>";
                                echo "<td class='te-number'>".core::numberFormat($totalReasonStatusPer)."</td>";
                                ?>
                            </tr>
                        <?php } ?>
                        <tr style="text-align: center;font-weight: bold;">
                            <th colspan="3" class="center"><?php echo "Total Of ".$bankName; ?></th>
                            <?php
                            foreach($statusData as $statusName){
                                if(array_key_exists($statusName,$bankStatusTotal)){
                                    echo "<td class='te-number'><b>".$bankStatusTotal[$statusName]."</b></td>";
                                } else {
                                    echo "<td>0</td>";
                                }
                            }
                            ?>
                            <td class='te-number'><?php echo $totalOfBank; ?></td>
                            <td class='te-number'><?php echo core::numberFormat($totalOfBankPer); ?></td>
                        </tr>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($bankFormatData) > 0){
                    echo "<tr class='bolder'>";
                        echo "<th colspan='3' class='center'>Grand Total</th>";
                        foreach($statusData as $statusName){
                            if(array_key_exists($statusName,$total)){
                                echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                            } else {

                                echo "<td>-</td>";
                            }
                        }
                        echo "<td class='te-number'><b>".$totalCount."</b></td>";
                        echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                    echo "<tr class='bolder'>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Bank",
            "Reason",
            "Query Stage",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($bankFormatData as $bankName => $reasonArray) {
            foreach ($reasonArray as $reasonName => $reasonData) {

                foreach ($reasonData as $queryStageName => $queryStageData) {

                    if ($reasonName != '') {
                        $dataRows[$key] = array($bankName);
                        //add reason name
                        array_push($dataRows[$key],trim(strstr($reasonName, "@"), "@"));
                        //add query stage
                        array_push($dataRows[$key], trim(strstr($queryStageName, "@"), "@"));

                        $subTotalOfStatus = 0;
                        if (count($queryStageData) > 0) {

                            foreach ($statusData as $statusName) {

                                if (array_key_exists($statusName, $queryStageData)) {
                                    $subTotalOfStatus += $queryStageData[$statusName];
                                    array_push($dataRows[$key], $queryStageData[$statusName]);
                                    $total[$statusName] = (array_key_exists($statusName, $total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                } else {
                                    array_push($dataRows[$key], 0);
                                }
                            }
                        }
                        // add Row Total
                        array_push($dataRows[$key], $subTotalOfStatus);
                        // add Row Percentage
                        array_push($dataRows[$key], round(100 * $subTotalOfStatus / $totalCount, 2));
                        $totalPercent += (100 * $subTotalOfStatus / $totalCount);
                        $key = $key + 1;
                    }

                }
            }
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total','','');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("bank_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;
    } elseif($reportType == 'chart'){
        $bankFormatChartData = array();
        foreach($misData as $key => $value){
            $value['bank_name'] = ($value['bank_name'] != '') ? $value['bank_name'] : "Blank";
            $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
            if(isset($bankFormatChartData[$value['bank_name']]) && in_array($bankFormatChartData[$value['bank_name']],$bankFormatChartData)){
                if(array_key_exists($value['status'],$bankFormatChartData[$value['bank_name']])){
                    $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'] + $bankFormatChartData[$value['bank_name']][$value['status']];
                } else {
                    $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'];
                }
            } else {
                $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'];
            }
        }

        if(empty($misData)){
            $misData =  array(array(
                "bank_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Bank Name";

        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($bankFormatChartData) > 0){
            foreach($bankFormatChartData as $bankKey => $bankData){

                $dataArrElem = array(
                    "Bank Name"                      => $bankKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$bankData)){
                        $subTotalOfStatus += $bankData[$statusName];
                        $dataArrElem[$statusName] = $bankData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Bank Name") ? (int) $value : $value.$percent;
                    if($key != "Bank Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;
                }
                $dataChart[] = $dataChartElem;
            }
        }else {
            $dataArrElem = array(
                "Bank Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Bank Name") ? (int) $value : $value;
                if($key != "Bank Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }
}elseif ('reasonreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterQueryStageId = (isset($data['query_stage_id'])) ? $data['query_stage_id']  : "";
    $filterReasonId = (isset($data['reason_id'])) ? $data['reason_id']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = 't.is_merged != 1';

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterReasonId != ''){
        $selectedReason = implode(",",$filterReasonId);
        $misCondition .= " AND (t.reason_id in ($selectedReason))";
    }

    if($filterQueryStageId != ''){
        $selectedQueryStage = implode(",",$filterQueryStageId);
        $misCondition .= " AND (t.query_stage_id in ($selectedQueryStage))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number,t.query_stage_id,t.reason_id,t.status_id"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","reason_master as rm","rm.reason_id = t.reason_id",array("rm.reason_name As reason_name")),
        array("left","query_stage_master as qsm","qsm.query_stage_id = t.query_stage_id",array("qsm.query_stage_name As query_stage")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.reason_id IS NULL OR t.reason_id = '', 0, t.reason_id),
    IF(t.query_stage_id IS NULL OR t.query_stage_id = '', 0, t.query_stage_id),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $reasonFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;

    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['query_stage'] = ($value['query_stage'] != '') ?  $value['query_stage'] : "Blank";
        $value['reason_name'] = ($value['reason_name'] != '') ?  $value['reason_name'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $value['query_stage'] = $value['query_stage_id']."@".$value['query_stage'];
        $value['reason_name'] = $value['reason_id']."@".$value['reason_name'];
        $reasonFormatData[$value['reason_name']][$value['query_stage']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));

    if($reportType == 'html'){
        if(count($reasonFormatData) > 0){
            $total = array();
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Reason</th>
                    <th>Query Stage</th>
                    <?php
                    foreach($statusData as $statusName){ ?>
                        <th><?php echo $statusName; ?></th>
                    <?php }?>
                    <th>Total</th>
                    <th>In %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($reasonFormatData) > 0){

                    foreach($reasonFormatData as $reasonName => $reasonData){
                        $reasonDisplay = 1;
                        $reasonName = trim(strstr($reasonName,"@"),"@");
                        $reasonStatusTotal = array();
                        $totalReasonStatus = 0;
                        $totalReasonStatusPer = 0;
                        foreach($reasonData as $queryStageName => $queryStageData){
                            if($reasonName != ''){
                                ?>
                                <tr>
                                    <td><?php echo ($reasonDisplay == 1) ? $reasonName : ""; ?></td>
                                    <td><?php echo trim(strstr($queryStageName,"@"),"@"); ?></td>
                                    <?php
                                    $subTotalOfStatus = 0;
                                    if(count($queryStageData) > 0){
                                        foreach($statusData as $statusName){

                                            if(array_key_exists($statusName,$queryStageData)){
                                                $subTotalOfStatus += $queryStageData[$statusName];
                                                $totalReasonStatus += $queryStageData[$statusName];
                                                echo "<td class='te-number'>".$queryStageData[$statusName]."</td>";
                                                $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                                $reasonStatusTotal[$statusName] = (array_key_exists($statusName,$reasonStatusTotal)) ? $reasonStatusTotal[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                            } else {
                                                echo "<td>-</td>";
                                            }
                                        }
                                    }
                                    $statusPer = (100*$subTotalOfStatus/$totalCount);
                                    $totalReasonStatusPer += $statusPer;
                                    $totalPercent += $statusPer;
                                    echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                    echo "<td class='te-number'>".core::numberFormat($statusPer)."</td>";
                                    ?>
                                </tr>
                            <?php }
                            $reasonDisplay = 0;
                        } ?>
                        <tr style="text-align: center;font-weight: bold;">
                            <td colspan="2" class="center"><?php echo "Total Of ".$reasonName; ?></td>
                            <?php
                            foreach($statusData as $statusName){
                                if(array_key_exists($statusName,$reasonStatusTotal)){
                                    echo "<td class='te-number'><b>".$reasonStatusTotal[$statusName]."</b></td>";
                                } else {
                                    echo "<td>0</td>";
                                }
                            }
                            echo "<td class='te-number'>".$totalReasonStatus."</td>";
                            echo "<td class='te-number'>".core::numberFormat($totalReasonStatusPer)."</td>";
                            ?>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <?php
                if(count($reasonFormatData) > 0){
                    echo "<tr class='bolder'>";
                        echo "<th colspan='2' class='center'>Grand Total</th>";

                        foreach($statusData as $statusName){
                            if(array_key_exists($statusName,$total)){
                                echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                            } else {

                                echo "<td>-</td>";
                            }
                        }
                        echo "<td class='te-number'><b>".$totalCount."</b></td>";
                        echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){

        $total = array();
        $headerArr = array(
            "Reason",
            "Query Stage",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;
        foreach($reasonFormatData as $reasonName => $reasonData){
            $subTotalOfStatus = 0;
            foreach($reasonData as $queryStageName => $queryStageData){
                $dataRows[$key] = array(trim(strstr($reasonName,"@"),"@"));
                if($reasonName != ''){
                    array_push($dataRows[$key],trim(strstr($queryStageName,"@"),"@"));

                    $subTotalOfStatus = 0;
                    if(count($queryStageData) > 0){

                        foreach($statusData as $statusName){

                            if(array_key_exists($statusName,$queryStageData)){
                                $subTotalOfStatus += $queryStageData[$statusName];
                                array_push($dataRows[$key],$queryStageData[$statusName]);
                                $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                            } else {
                                array_push($dataRows[$key],0);
                            }
                        }
                    }
                    // add Row Total
                    array_push($dataRows[$key],$subTotalOfStatus);
                    // add Row Percentage
                    array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
                    $totalPercent += (100*$subTotalOfStatus/$totalCount);
                    $key = $key + 1;
                }

            }
         }
        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total','');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("reason_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    } elseif($reportType == 'chart'){
        $reasonFormatChartData = array();
        foreach($misData as $key => $value){
            $value['reason_name'] = ($value['reason_name'] != '') ?  $value['reason_name'] : "Blank";
            $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
            if(isset($reasonFormatChartData[$value['reason_name']]) && in_array($reasonFormatChartData[$value['reason_name']],$reasonFormatChartData)){
                if(array_key_exists($value['status'],$reasonFormatChartData[$value['reason_name']])){
                    $reasonFormatChartData[$value['reason_name']][$value['status']] = $value['number'] + $reasonFormatChartData[$value['reason_name']][$value['status']];
                } else {
                    $reasonFormatChartData[$value['reason_name']][$value['status']] = $value['number'];
                }
            } else {
                $reasonFormatChartData[$value['reason_name']][$value['status']] = $value['number'];
            }
        }

        if(empty($misData)){
            $misData =  array(array(
                "reason_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Reason Name";

        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($reasonFormatChartData) > 0){
            foreach($reasonFormatChartData as $reasonKey => $reasonData){
                $dataArrElem = array(
                    "Reason Name"                      => $reasonKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$reasonData)){
                        $subTotalOfStatus += $reasonData[$statusName];
                        $dataArrElem[$statusName] = $reasonData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Reason Name") ? (int) $value : $value.$percent;
                    if($key != "Reason Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;
                }
                $dataChart[] = $dataChartElem;
            }
        }else {
            $dataArrElem = array(
                "Reason Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Reason Name") ? (int) $value : $value;
                if($key != "Reason Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }
}elseif('agentperformance' == $action){
    $data = $db->FilterParameters($_POST);
    $filterUserId = (isset($data['user_id'])) ? $data['user_id']  : "";
    $auditDate = (isset($data['audit_date'])) ? $data['audit_date']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $misCondition = 'au.user_type = '.UT_TC.'';

    if(is_array($filterUserId) && count(array_filter($filterUserId)) > 0){
        $selectedUser = implode(",",$filterUserId);
        $misCondition .= " AND (utp.user_id in ($selectedUser))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }

        $dateCondition = ($range_to == $range_from) ? " date_format(utp.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(utp.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(utp.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " and ($dateCondition)";
    }

    if($auditDate != ''){
        $date_range_str = $auditDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }

        $dateCondition = ($range_to == $range_from) ? " date_format(utp.audit_date,'%Y-%m-%d') = '$range_from'" : "date_format(utp.audit_date,'%Y-%m-%d') >= '$range_from' AND date_format(utp.audit_date,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " and ($dateCondition)";
    }

    $mainTable = array("admin_user as au",array("concat(au.first_name,' ',au.last_name) as user_name"));
    $joinTable = array(
        array("left","v_user_call_total_performance as utp","utp.user_id = au.user_id",array("utp.total_answer_weight,utp.total_question_weight,utp.performance")),
    );

    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("performance"=>"desc"),$limit,'utp.user_id,utp.audit_date');
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $agentPerformanceData = array();

    if($reportType == 'html'){
        if(count($misData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Agent</th>
                    <th>Performance(%)</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($misData as $key => $data){
                    echo "<tr>";
                    echo "<td>".$data['user_name']."</td>";
                    echo "<td>".core::numberFormat($data['performance'])."</td>";
                    echo "</tr>";
                }?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $headerArr = array(
            "Agent",
            "Performance(%)",
        );
        $dataRows = array();
        foreach($misData as $key => $data){
            $dataRows[$key] = array();
            array_push($dataRows[$key],$data['user_name']);
            array_push($dataRows[$key],core::numberFormat($data['performance']));
        }
        $downloadURL = Utility::writeExportZipExport("agent_performance_report", $headerArr, $dataRows);
        echo  $downloadURL;

    } elseif($reportType == 'chart'){

        $displayKey = 1;
        $dataChart[$displayKey] = array("Agent Name","Performance %");

        if(count($misData) > 0){
            foreach ($misData as $key => $value){
                $dataArrElem["Agent Name"] = $value['user_name'];
                $dataArrElem["Performance"] = $value['performance'];

                $dataChartElem = array();
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[] = ($key != "Agent Name") ? (float) round($value,2) : $value;
                }
                $dataChart[] = $dataChartElem;
            }

        }
        else{
            $dataArrElem = array(
                "Agent Name"            => 'None',
                "Performance"           => 0,
            );
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[] = ($key != "Agent Name") ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }

}elseif('questionerrorreport' == $action){
    $data = $db->FilterParameters($_POST);
    $filterUserId = (isset($data['user_id'])) ? $data['user_id']  : "";
    $questionId = (isset($data['question_id'])) ? $data['question_id']  : "";
    $auditDate = (isset($data['audit_date'])) ? $data['audit_date']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $misCondition = 'question_weight != 0';

    if(is_array($questionId) && count(array_filter($questionId)) > 0){
        $selectedQuestion = implode(",",$questionId);
        $misCondition .= " AND (question_id in ($selectedQuestion))";
    }

    if(is_array($filterUserId) && count(array_filter($filterUserId)) > 0){
        $selectedUser = implode(",",$filterUserId);
        $misCondition .= " AND (user_id in ($selectedUser))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }

        $dateCondition = ($range_to == $range_from) ? " date_format(created_at,'%Y-%m-%d') = '$range_from'" : "date_format(created_at,'%Y-%m-%d') >= '$range_from' AND date_format(created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " and ($dateCondition)";
    }

    if($auditDate != ''){
        $date_range_str = $auditDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }

        $dateCondition = ($range_to == $range_from) ? " date_format(audit_date,'%Y-%m-%d') = '$range_from'" : "date_format(audit_date,'%Y-%m-%d') >= '$range_from' AND date_format(audit_date,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " and ($dateCondition)";
    }

    $mainTable = 'v_user_call_performance';
    $columns = array('SUM(weight) AS answer_weight',
        'SUM(question_weight) AS question_weight','question_id','question_short_name','question_name');

    $misDisRes = $db->Fetch($mainTable,$columns,$misCondition,array("answer_weight"=>"desc"),$limit,'question_id');
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $agentPerformanceData = array();
    $totalMax = 0;
    $totalPerformance = 0;

    if($reportType == 'html'){
        if(count($misData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Question</th>
                    <th>Max</th>
                    <th>Performance</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($misData as $key => $data){
                    $questionCodeName = $data['question_short_name']." - ".core::StripAllSlashes($data['question_name']);
                    $totalMax += $data['question_weight'];
                    $totalPerformance += $data['answer_weight'];
                    echo "<tr>";
                    echo "<td>".$questionCodeName."</td>";
                    echo "<td>".$data['question_weight']."</td>";
                    echo "<td>".$data['answer_weight']."</td>";
                    echo "</tr>";
                }?>
                <tr class='bolder'>
                    <th class='center'>Total</th>
                    <td class='te-number'><b><?= $totalMax ?></b></td>
                    <td class='te-number'><b><?= $totalPerformance ?></b></td>
                </tr>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $headerArr = array(
            "Question",
            "Max",
            "Performance"
        );
        $dataRows = array();
        foreach($misData as $key => $data){
            $dataRows[$key] = array();
            $totalMax += $data['question_weight'];
            $totalPerformance += $data['answer_weight'];
            $questionCodeName = $data['question_short_name']." - ".core::StripAllSlashes($data['question_name']);
            array_push($dataRows[$key],$questionCodeName);
            array_push($dataRows[$key],core::numberFormat($data['question_weight']));
            array_push($dataRows[$key],core::numberFormat($data['answer_weight']));
        }
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        array_push($dataRows[$key],$totalMax);
        array_push($dataRows[$key],$totalPerformance);
        $downloadURL = Utility::writeExportZipExport("question_error_report", $headerArr, $dataRows);
        echo  $downloadURL;

    } elseif($reportType == 'chart'){

//        $displayKey = 1;
//        $dataChart[$displayKey] = array("Question Name","Max","Performance","Question");

        if(count($misData) > 0){
            foreach ($misData as $key => $value){
                $dataArrElem["Question Name"] = $value['question_short_name'];
                $dataArrElem["Question"] = "<div style='padding:5px 5px 5px 5px;'>Question :<b> ".core::StripAllSlashes($value['question_name'])."</b><br> Max :<b> ".$value['question_weight']."</b><br> Performance :<b> ".$value['answer_weight']."</b></div>";
                $dataArrElem["Max"] = $value['question_weight'];
                $dataArrElem["Performance"] = $value['answer_weight'];
                $dataChartElem = array();
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[] = ($key != "Question Name" && $key != "Question" ) ? (int) $value : $value;
                }

                $dataChart[] = $dataChartElem;
            }

        }
        else{
            $dataArrElem = array(
                "Question Name"            => 'None',
                "Question"                 => 'None',
                "Max"                      => 0,
                "Performance"              => 0,
            );
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[] = ($key != "Question Name" && $key != "Question" ) ? (int) $value : $value;
            }
            $dataChart[] = $dataChartElem;
        }
        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }

}elseif ('supportagentreport' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterUserId = (isset($data['user_id'])) ? $data['user_id']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    //$misCondition = "t.is_merged != 1 and au.user_level = 'level1'";
    $misCondition = "t.is_merged != 1";

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }
    
    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterUserId != ''){
        $selectedAgent = implode(",",$filterUserId);
        $misCondition .= " AND (t.assign_to in ($selectedAgent))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","admin_user as au","au.user_id = t.assign_to",array("concat(au.first_name,' ',au.last_name) as agent"))
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.assign_to IS NULL OR t.assign_to = '', 0, t.assign_to),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $agentFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;
    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['agent'] = ($value['agent'] != '') ?  $value['agent'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $agentFormatData[$value['agent']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));
    if($reportType == 'html'){
        if(count($agentFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <th>Agent</th>
                <?php
                $total = array();
                foreach($statusData as $statusName){ ?>
                    <th><?php echo $statusName; ?></th>
                <?php }?>
                <th>Total</th>
                <th>In %</th>
                </thead>
                <tbody>
                <?php
                if(count($agentFormatData) > 0){
                    foreach($agentFormatData as $agentKey => $agentData){

                        if($agentKey != ''){
                            ?>
                            <tr>
                                <td><?php echo $agentKey; ?></td>

                                <?php
                                $subTotalOfStatus = 0;
                                if(count($agentData) > 0){

                                    foreach($statusData as $statusName){

                                        if(array_key_exists($statusName,$agentData)){
                                            $subTotalOfStatus += $agentData[$statusName];
                                            echo "<td class='te-number'>".$agentData[$statusName]."</td>";
                                            $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    }
                                }
                                $totalPercent += (100*$subTotalOfStatus/$totalCount);
                                echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                echo "<td class='te-number'>".round(100*$subTotalOfStatus/$totalCount , 2)."</td>";
                                ?>
                            </tr>
                        <?php }?>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($agentFormatData) > 0){
                    echo "<th colspan='1'>Total</th>";

                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                    echo "<td class='te-number'><b>".$totalCount."</b></td>";
                    echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Agent",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($agentFormatData as $agentKey => $agentData){
            $dataRows[$key] = array($agentKey);

            $subTotalOfStatus = 0;
            if(count($agentData) > 0){

                foreach($statusData as $statusName){

                    if(array_key_exists($statusName,$agentData)){
                        $subTotalOfStatus += $agentData[$statusName];
                        array_push($dataRows[$key],$agentData[$statusName]);
                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                    } else {
                        array_push($dataRows[$key],0);
                    }
                }
            }
            // add Row Total
            array_push($dataRows[$key],$subTotalOfStatus);
            // add Row Percentage
            array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
            $totalPercent += (100*$subTotalOfStatus/$totalCount);
            $key = $key + 1;
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("support_agent_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    }
}elseif ('escalate_to_2_report' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterEscalateTwoDate = (isset($data['escalate_to_2_date'])) ? $data['escalate_to_2_date']  : "";
    $filterUserId = (isset($data['escalate_to_2'])) ? $data['escalate_to_2']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = "t.is_merged != 1 and t.escalate_to_2 != '0'";

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterUserId != ''){
        $selectedAgent = implode(",",$filterUserId);
        $misCondition .= " AND (t.escalate_to_2 in ($selectedAgent))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    if($filterEscalateTwoDate != ''){
        $date_range_str = $filterEscalateTwoDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.escalate_to_2_date,'%Y-%m-%d') = '$range_from'" : "date_format(t.escalate_to_2_date,'%Y-%m-%d') >= '$range_from' AND date_format(t.escalate_to_2_date,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","admin_user as au","au.user_id = t.escalate_to_2",array("concat(au.first_name,' ',au.last_name) as agent"))
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.escalate_to_2 IS NULL OR t.escalate_to_2 = '', 0, t.escalate_to_2),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $agentFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;
    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['agent'] = ($value['agent'] != '') ?  $value['agent'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $agentFormatData[$value['agent']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));
    if($reportType == 'html'){
        if(count($agentFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <th>Agent</th>
                <?php
                $total = array();
                foreach($statusData as $statusName){ ?>
                    <th><?php echo $statusName; ?></th>
                <?php }?>
                <th>Total</th>
                <th>In %</th>
                </thead>
                <tbody>
                <?php
                if(count($agentFormatData) > 0){
                    foreach($agentFormatData as $agentKey => $agentData){

                        if($agentKey != ''){
                            ?>
                            <tr>
                                <td><?php echo $agentKey; ?></td>

                                <?php
                                $subTotalOfStatus = 0;
                                if(count($agentData) > 0){

                                    foreach($statusData as $statusName){

                                        if(array_key_exists($statusName,$agentData)){
                                            $subTotalOfStatus += $agentData[$statusName];
                                            echo "<td class='te-number'>".$agentData[$statusName]."</td>";
                                            $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    }
                                }
                                $totalPercent += (100*$subTotalOfStatus/$totalCount);
                                echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                echo "<td class='te-number'>".round(100*$subTotalOfStatus/$totalCount , 2)."</td>";
                                ?>
                            </tr>
                        <?php }?>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($agentFormatData) > 0){
                    echo "<th colspan='1'>Total</th>";

                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                    echo "<td class='te-number'><b>".$totalCount."</b></td>";
                    echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Agent",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($agentFormatData as $agentKey => $agentData){
            $dataRows[$key] = array($agentKey);

            $subTotalOfStatus = 0;
            if(count($agentData) > 0){

                foreach($statusData as $statusName){

                    if(array_key_exists($statusName,$agentData)){
                        $subTotalOfStatus += $agentData[$statusName];
                        array_push($dataRows[$key],$agentData[$statusName]);
                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                    } else {
                        array_push($dataRows[$key],0);
                    }
                }
            }
            // add Row Total
            array_push($dataRows[$key],$subTotalOfStatus);
            // add Row Percentage
            array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
            $totalPercent += (100*$subTotalOfStatus/$totalCount);
            $key = $key + 1;
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("escalate_to_2_agent_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    }
}elseif ('escalate_to_3_report' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterEscalateThreeDate = (isset($data['escalate_to_3_date'])) ? $data['escalate_to_3_date']  : "";
    $filterUserId = (isset($data['escalate_to_3'])) ? $data['escalate_to_3']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";

    $statusCondition = " status_type = 'support'";
    $misCondition = "t.is_merged != 1 and t.escalate_to_3 != '0'";

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($statusId != ''){
        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterUserId != ''){
        $selectedAgent = implode(",",$filterUserId);
        $misCondition .= " AND (t.escalate_to_3 in ($selectedAgent))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    if($filterEscalateThreeDate != ''){
        $date_range_str = $filterEscalateThreeDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
//    if($range_to == $range_from) {
//        $range_to = date('Y-m-d', strtotime('+1 day', strtotime($range_to)));
//    }
        $dateCondition = ($range_to == $range_from) ? " date_format(t.escalate_to_3_date,'%Y-%m-%d') = '$range_from'" : "date_format(t.escalate_to_3_date,'%Y-%m-%d') >= '$range_from' AND date_format(t.escalate_to_3_date,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("count(*) as number"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","admin_user as au","au.user_id = t.escalate_to_3",array("concat(au.first_name,' ',au.last_name) as agent"))
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
        "IF(t.escalate_to_3 IS NULL OR t.escalate_to_3 = '', 0, t.escalate_to_3),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $agentFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;
    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['agent'] = ($value['agent'] != '') ?  $value['agent'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $agentFormatData[$value['agent']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));
    if($reportType == 'html'){
        if(count($agentFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <th>Agent</th>
                <?php
                $total = array();
                foreach($statusData as $statusName){ ?>
                    <th><?php echo $statusName; ?></th>
                <?php }?>
                <th>Total</th>
                <th>In %</th>
                </thead>
                <tbody>
                <?php
                if(count($agentFormatData) > 0){
                    foreach($agentFormatData as $agentKey => $agentData){

                        if($agentKey != ''){
                            ?>
                            <tr>
                                <td><?php echo $agentKey; ?></td>

                                <?php
                                $subTotalOfStatus = 0;
                                if(count($agentData) > 0){

                                    foreach($statusData as $statusName){

                                        if(array_key_exists($statusName,$agentData)){
                                            $subTotalOfStatus += $agentData[$statusName];
                                            echo "<td class='te-number'>".$agentData[$statusName]."</td>";
                                            $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    }
                                }
                                $totalPercent += (100*$subTotalOfStatus/$totalCount);
                                echo "<td class='te-number'>".$subTotalOfStatus."</td>";
                                echo "<td class='te-number'>".round(100*$subTotalOfStatus/$totalCount , 2)."</td>";
                                ?>
                            </tr>
                        <?php }?>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($agentFormatData) > 0){
                    echo "<th colspan='1'>Total</th>";

                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                    echo "<td class='te-number'><b>".$totalCount."</b></td>";
                    echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Agent",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($agentFormatData as $agentKey => $agentData){
            $dataRows[$key] = array($agentKey);

            $subTotalOfStatus = 0;
            if(count($agentData) > 0){

                foreach($statusData as $statusName){

                    if(array_key_exists($statusName,$agentData)){
                        $subTotalOfStatus += $agentData[$statusName];
                        array_push($dataRows[$key],$agentData[$statusName]);
                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $agentData[$statusName] : $agentData[$statusName];
                    } else {
                        array_push($dataRows[$key],0);
                    }
                }
            }
            // add Row Total
            array_push($dataRows[$key],$subTotalOfStatus);
            // add Row Percentage
            array_push($dataRows[$key],round(100*$subTotalOfStatus/$totalCount , 2));
            $totalPercent += (100*$subTotalOfStatus/$totalCount);
            $key = $key + 1;
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key],$total[$statusName]);
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key],$totalCount);
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("escalate_to_3_agent_wise_report", $headerArr, $dataRows);
        echo  $downloadURL;

    }
}elseif ('avgresolvetime' == $action){
    $data = $db->FilterParameters($_POST);
    $statusId = (isset($data['status_id'])) ? $data['status_id']  : "";
    $filterCreatedDate = (isset($data['created_date'])) ? $data['created_date']  : "";
    $filterQueryStageId = (isset($data['query_stage_id'])) ? $data['query_stage_id']  : "";
    $filterReasonId = (isset($data['reason_id'])) ? $data['reason_id']  : "";
    $filterBankId = (isset($data['bank_id'])) ? $data['bank_id']  : "";
    $filterLoanTypeId = (isset($data['loan_type_id'])) ? $data['loan_type_id']  : "";
    $filterProductTypeId = (isset($data['product_type_id'])) ? $data['product_type_id']  : "";
    $limit = (isset($data['limit'])) ? array(0,$data['limit']) : null;
    $reportType = (isset($data['report_type'])) ? $data['report_type']  : "";


    $statusCondition = " status_type = 'support' and is_close = 1";
    $misCondition = 'sm.status_id is not null and t.is_merged != 1 and sm.is_close = 1';

    if($statusId != ''){

        $selectedStatus = implode(",",$statusId);
        $misCondition .= " AND (sm.status_id in ($selectedStatus))";
        $statusCondition .= " AND (status_id in ($selectedStatus))";
    }

    if($filterBankId != ''){
        $selectedBank = implode(",",$filterBankId);
        $misCondition .= " AND (t.bank_id in ($selectedBank))";
    }

    if($filterReasonId != ''){
        $selectedReason = implode(",",$filterReasonId);
        $misCondition .= " AND (t.reason_id in ($selectedReason))";
    }

    if($filterQueryStageId != ''){
        $selectedQueryStage = implode(",",$filterQueryStageId);
        $misCondition .= " AND (t.query_stage_id in ($selectedQueryStage))";
    }

    if($filterLoanTypeId != ''){
        $selectedLoanType = implode(",",$filterLoanTypeId);
        $misCondition .= " AND (t.loan_type_id in ($selectedLoanType))";
    }

    if($filterProductTypeId != ''){
        $selectedProductType = implode(",",$filterProductTypeId);
        $misCondition .= " AND (t.product_type_id in ($selectedProductType))";
    }

    if($filterCreatedDate != ''){
        $date_range_str = $filterCreatedDate;
        $date_range_arr = explode(" to ", $date_range_str);
        $range_from = Core::DMYToYMD($date_range_arr[0]);
        $range_to = Core::DMYToYMD($date_range_arr[1]);
        $dateCondition = ($range_to == $range_from) ? " date_format(t.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(t.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(t.created_at,'%Y-%m-%d') <= '$range_to'";

        $misCondition .= " AND ($dateCondition)";
    }

    $mainTable = array("tickets as t",array("(sum(TIMESTAMPDIFF(SECOND, t.created_at, T.updated_at))/count(*)) as number,t.query_stage_id,t.reason_id"));
    $joinTable = array(
        array("left","status_master as sm","sm.status_id = t.status_id",array("sm.status_name as status")),
        array("left","query_stage_master as qsm","qsm.query_stage_id = t.query_stage_id",array("qsm.query_stage_name As query_stage")),
        array("left","reason_master as rm","rm.reason_id = t.reason_id",array("rm.reason_name As reason_name")),
        array("left","loan_type_master as ltm","ltm.loan_type_id = t.loan_type_id",array("ltm.loan_type_id,ltm.loan_type_name")),
        array("left","category_master as cm","cm.category_id = t.product_type_id",array("cm.category_id,cm.category_name")),
        array("left","bank_master as bm","bm.bank_id = t.bank_id",array("bm.bank_name As bank_name")),
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("number" => "desc","YEAR(t.created_at)"=>"asc","MONTH(t.created_at)"=>"asc"),$limit,
    "IF(t.loan_type_id IS NULL OR t.loan_type_id = '', 0, t.loan_type_id),
    IF(t.product_type_id IS NULL OR t.product_type_id = '', 0, t.product_type_id),
    IF(t.reason_id IS NULL OR t.reason_id = '', 0, t.reason_id),
    IF(t.query_stage_id IS NULL OR t.query_stage_id = '', 0, t.query_stage_id),
    IF(t.status_id IS NULL OR t.status_id = '', 0, t.status_id)");
    $misData = $db->FetchToArrayFromResultset($misDisRes);
    $loanTypeFormatData = array();

    $totalCount = 0;
    $totalPercent = 0;

    foreach($misData as $key => $value){
        $totalCount = $totalCount + $value['number'];
        $value['loan_type_name'] = ($value['loan_type_name'] != '') ? $value['loan_type_name'] : "Blank";
        $value['category_name'] = ($value['category_name'] != '') ? $value['category_name'] : "Blank";
        $value['query_stage'] = ($value['query_stage'] != '') ?  $value['query_stage'] : "Blank";
        $value['reason_name'] = ($value['reason_name'] != '') ?  $value['reason_name'] : "Blank";
        $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
        $value['category_name'] = $value['category_id']."@".$value['category_name'];
        $value['query_stage'] = $value['query_stage_id']."@".$value['query_stage'];
        $value['reason_name'] = $value['reason_id']."@".$value['reason_name'];
        $loanTypeFormatData[$value['loan_type_name']][$value['category_name']][$value['reason_name']][$value['query_stage']][$value['status']] = $value['number'];
    }
    $statusData = $db->FetchToArray("status_master",array("status_name"),$statusCondition,array("sort_order"=>"asc"));
    $statusData = array_merge($statusData,array("Blank"));

    if($reportType == 'html'){
        $total = array();
        if(count($loanTypeFormatData) > 0){
            ?>
            <table class="table" id="dg_mis">
                <thead>
                <tr>
                    <th>Loan Type</th>
                    <th>Category</th>
                    <th>Reason</th>
                    <th>Query Stage</th>
                    <?php
                    foreach($statusData as $statusName){ ?>
                        <th><?php echo $statusName; ?></th>
                    <?php } ?>
                    <th>Total</th>
                    <th>In %</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($loanTypeFormatData) > 0){
                    foreach($loanTypeFormatData as $loanTypeName => $categoryData){
                        $loanTypeDisplay = 1;
                        $categoryDisplay = 1;
                        $totalOfLoanType = 0;
                        $totalOfLoanTypePer = 0;
                        $loanTypeStatusTotal = array();
                        ?>
                        <?php
                        foreach($categoryData as $categoryName => $reasonData){
                            $categoryName = trim(strstr($categoryName,"@"),"@");
                            $categoryStatusTotal = array();
                            $totalOfCategory = 0;
                            $totalOfCategoryPer = 0;
                            foreach($reasonData as $reasonName => $queryStageData){
                                $reasonDisplay = 1;
                                $reasonName = trim(strstr($reasonName,"@"),"@");
                                $reasonStatusTotal = array();
                                $totalReasonStatus = 0;
                                $totalReasonStatusPer = 0;
                                foreach($queryStageData as $queryStageName => $stageStatusData){

                                    if($reasonName != ''){ ?>
                                        <tr>
                                            <td><?php echo ($loanTypeDisplay == 1) ? $loanTypeName : ""; ?></td>
                                            <td><?php echo ($categoryDisplay == 1) ? $categoryName : ""; ?></td>
                                            <td><?php echo ($reasonDisplay == 1) ? $reasonName : ""; ?></td>
                                            <td><?php echo trim(strstr($queryStageName,"@"),"@"); ?></td>

                                            <?php
                                            $subTotalOfStatus = 0;
                                            if(count($stageStatusData) > 0){
                                                foreach($statusData as $statusName){
                                                    if(array_key_exists($statusName,$stageStatusData)){
                                                        $subTotalOfStatus += $stageStatusData[$statusName];
                                                        $totalReasonStatus += $stageStatusData[$statusName];
                                                        echo "<td class='te-number'>".Utility::formatTimeFromSecond($stageStatusData[$statusName])."</td>";
                                                        $total[$statusName] = (array_key_exists($statusName,$total)) ? $total[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                        $categoryStatusTotal[$statusName] = (array_key_exists($statusName,$categoryStatusTotal)) ? $categoryStatusTotal[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                        $reasonStatusTotal[$statusName] = (array_key_exists($statusName,$reasonStatusTotal)) ? $reasonStatusTotal[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                        $loanTypeStatusTotal[$statusName] = (array_key_exists($statusName, $loanTypeStatusTotal)) ? $loanTypeStatusTotal[$statusName] + $stageStatusData[$statusName] : $stageStatusData[$statusName];
                                                    } else {
                                                        echo "<td>-</td>";
                                                    }
                                                }
                                            }
                                            $statusPer = (100*$subTotalOfStatus/$totalCount);
                                            $totalReasonStatusPer += $statusPer;
                                            $totalPercent += $statusPer;
                                            echo "<td class='te-number'>".Utility::formatTimeFromSecond($subTotalOfStatus)."</td>";
                                            echo "<td class='te-number'>".core::numberFormat($statusPer)."</td>";
                                            ?>
                                        </tr>
                                    <?php }
                                    $loanTypeDisplay = 0;
                                    $categoryDisplay = 0;
                                    $reasonDisplay = 0;
                                } ?>
                                <!-- Display Total of Reason -->
                                <tr style="text-align: center;font-weight: bold;">
                                    <th colspan="4" class="center"><?php echo "Total Of ".$reasonName; ?></th>
                                    <?php
                                    foreach($statusData as $statusName){
                                        if(array_key_exists($statusName,$reasonStatusTotal)){
                                            echo "<td class='te-number'><b>".Utility::formatTimeFromSecond($reasonStatusTotal[$statusName])."</b></td>";
                                        } else {
                                            echo "<td>0</td>";
                                        }
                                    }
                                    $totalOfCategory += $totalReasonStatus;
                                    $totalOfCategoryPer += $totalReasonStatusPer;
                                    echo "<td class='te-number'>".Utility::formatTimeFromSecond($totalReasonStatus)."</td>";
                                    echo "<td class='te-number'>".core::numberFormat($totalReasonStatusPer)."</td>";
                                    ?>
                                </tr>
                            <?php
                            } ?>
                            <!-- Display Total of Category -->
                            <tr style="text-align: center;font-weight: bold;">
                                <th colspan="4" class="center"><?php echo "Total Of ".$categoryName; ?></th>
                                <?php
                                foreach($statusData as $statusName){
                                    if(array_key_exists($statusName,$categoryStatusTotal)){
                                        echo "<td class='te-number'><b>".Utility::formatTimeFromSecond($categoryStatusTotal[$statusName])."</b></td>";
                                    } else {
                                        echo "<td>0</td>";
                                    }
                                }
                                $totalOfLoanType += $totalOfCategory;
                                $totalOfLoanTypePer += $totalOfCategoryPer;
                                ?>
                                <td class='te-number'><?php echo Utility::formatTimeFromSecond($totalOfLoanType); ?></td>
                                <td class='te-number'><?php echo core::numberFormat($totalOfLoanTypePer); ?></td>
                            </tr>
                        <?php } ?>
                        <!-- Display Total of Loan Type -->
                        <tr style="text-align: center;font-weight: bold;">
                            <th colspan="4" class="center"><?php echo "Total Of ".$loanTypeName; ?></th>
                            <?php
                            foreach($statusData as $statusName){
                                if(array_key_exists($statusName,$loanTypeStatusTotal)){
                                    echo "<td class='te-number'><b>".Utility::formatTimeFromSecond($loanTypeStatusTotal[$statusName])."</b></td>";
                                } else {
                                    echo "<td>0</td>";
                                }
                            }
                            ?>
                            <td class='te-number'><?php echo Utility::formatTimeFromSecond($totalOfLoanType); ?></td>
                            <td class='te-number'><?php echo core::numberFormat($totalOfLoanTypePer); ?></td>
                        </tr>
                    <?php }?>


                <?php } ?>
                <?php
                if(count($loanTypeFormatData) > 0){
                    echo "<tr class='bolder'>";
                    echo "<th colspan='4' class='center'>Grand Total</th>";
                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".Utility::formatTimeFromSecond($total[$statusName])."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                    echo "<td class='te-number'><b>".Utility::formatTimeFromSecond($totalCount)."</b></td>";
                    echo "<td class='te-number'><b>".round($totalPercent,2)."%</b></td>";
                    echo "<tr class='bolder'>";
                }
                ?>
                </tbody>
            </table>
        <?php
        } else {
            echo "<div>
                    <h3 class='col-xs-12' align='center'>
                        <span style='color:#438EB9;'>No Result Found..</span>
                    </h3>
                </div>";
        }
    } elseif ($reportType == 'export'){
        $total = array();
        $headerArr = array(
            "Loan Type",
            "Product",
            "Reason",
            "Query Stage",
        );
        foreach($statusData as $statusName){
            array_push($headerArr,$statusName);
        }
        array_push($headerArr,"Total");
        array_push($headerArr,"In %");
        $dataRows = array();
        $key = 0;

        foreach($loanTypeFormatData as $loanTypeName => $categoryArray) {

            foreach ($categoryArray as $categoryName => $reasonArray) {

                foreach ($reasonArray as $reasonName => $reasonData) {

                    foreach ($reasonData as $queryStageName => $queryStageData) {

                        if ($reasonName != '') {
                            $dataRows[$key] = array($loanTypeName);
                            // add category Name
                            array_push($dataRows[$key],trim(strstr($categoryName, "@"), "@"));

                            //add reason name
                            array_push($dataRows[$key],trim(strstr($reasonName, "@"), "@"));

                            //add query stage
                            array_push($dataRows[$key], trim(strstr($queryStageName, "@"), "@"));

                            $subTotalOfStatus = 0;
                            if (count($queryStageData) > 0) {

                                foreach ($statusData as $statusName) {

                                    if (array_key_exists($statusName, $queryStageData)) {
                                        $subTotalOfStatus += $queryStageData[$statusName];
                                        array_push($dataRows[$key], Utility::formatTimeFromSecond($queryStageData[$statusName]));
                                        $total[$statusName] = (array_key_exists($statusName, $total)) ? $total[$statusName] + $queryStageData[$statusName] : $queryStageData[$statusName];
                                    } else {
                                        array_push($dataRows[$key], 0);
                                    }
                                }
                            }
                            // add Row Total
                            array_push($dataRows[$key], Utility::formatTimeFromSecond($subTotalOfStatus));
                            // add Row Percentage
                            array_push($dataRows[$key], round(100 * $subTotalOfStatus / $totalCount, 2));
                            $totalPercent += (100 * $subTotalOfStatus / $totalCount);
                            $key = $key + 1;
                        }

                    }
                }
            }
        }

        // total percentage adding
        $key = $key + 1;
        $dataRows[$key] = array('Total','','','');
        foreach($statusData as $statusName){
            if(array_key_exists($statusName,$total)){
                array_push($dataRows[$key], Utility::formatTimeFromSecond($total[$statusName]));
            } else {
                array_push($dataRows[$key],0);
            }
        }
        // add Total
        array_push($dataRows[$key], Utility::formatTimeFromSecond($totalCount));
        // add Percentage
        array_push($dataRows[$key],round($totalPercent,2));

        $downloadURL = Utility::writeExportZipExport("loan_type_avg_time_report", $headerArr, $dataRows);
        echo  $downloadURL;
    } elseif($reportType == 'chart'){
        $bankFormatChartData = array();
        foreach($misData as $key => $value){
            $value['bank_name'] = ($value['bank_name'] != '') ? $value['bank_name'] : "Blank";
            $value['status'] = ($value['status'] != '') ?  $value['status'] : "Blank";
            if(isset($bankFormatChartData[$value['bank_name']]) && in_array($bankFormatChartData[$value['bank_name']],$bankFormatChartData)){
                if(array_key_exists($value['status'],$bankFormatChartData[$value['bank_name']])){
                    $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'] + $bankFormatChartData[$value['bank_name']][$value['status']];
                } else {
                    $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'];
                }
            } else {
                $bankFormatChartData[$value['bank_name']][$value['status']] = $value['number'];
            }
        }

        if(empty($misData)){
            $misData =  array(array(
                "bank_name" => 0,
                "status_count" => 0,
                "status_name" => 0
            ));
        }

        //$dataChartElem = array();
        $displayKey = 1;
        $dataChart[$displayKey][] = "Bank Name";

        foreach($statusData as $statusName){
            $dataChart[$displayKey][] = $statusName;
            $dataChart[$displayKey][] = array("role" => "annotation");
        }
        if(count($bankFormatChartData) > 0){
            foreach($bankFormatChartData as $bankKey => $bankData){

                $dataArrElem = array(
                    "Bank Name"                      => $bankKey,
                );
                $subTotalOfStatus = 0;
                foreach($statusData as $statusName){
                    if(array_key_exists($statusName,$bankData)){
                        $subTotalOfStatus += $bankData[$statusName];
                        $dataArrElem[$statusName] = $bankData[$statusName];
                    } else {
                        $dataArrElem[$statusName] = 0;
                    }
                }
                $percent = ' ( '.round(100*$subTotalOfStatus/$totalCount , 2).' % )';
                $dataChartElem = array();
                $displayChartKey = 0;
                foreach ($dataArrElem as $key => $value){
                    $dataChartElem[$displayChartKey] = ($key != "Bank Name") ? (int) $value : $value.$percent;
                    if($key != "Bank Name"){
                        $displayChartKey = $displayChartKey + 1;
                        $dataChartElem[$displayChartKey] = (int) $value;
                    }
                    $displayChartKey = $displayChartKey + 1;
                }
                $dataChart[] = $dataChartElem;
            }
        }else {
            $dataArrElem = array(
                "Bank Name"                      => 'None',
            );
            foreach($statusData as $statusName){
                $dataArrElem[$statusName] = 0;
            }
            $displayChartKey = 0;
            foreach ($dataArrElem as $key => $value){
                $dataChartElem[$displayChartKey] = ($key != "Bank Name") ? (int) $value : $value;
                if($key != "Bank Name"){
                    $displayChartKey = $displayChartKey + 1;
                    $dataChartElem[$displayChartKey] = (int) $value;
                }
                $displayChartKey = $displayChartKey + 1;
            }
            $dataChart[] = $dataChartElem;
        }

        $response = array("success" => true, "report_data" =>$dataChart);
        echo json_encode($response);
    }
}
include_once 'footer.php';
