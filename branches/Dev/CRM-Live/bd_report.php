<?php
$asset_css = array(
    'css/jquery.gritter',
    'bootstrap-daterangepicker/daterangepicker',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen',
);
$asset_js = array(
    'js/jquery.gritter.min',
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/dataTables.colVis',
    'js/bootbox.min',
    'js/jquery-form/jquery.form',
    'bootstrap-datepicker/js/bootstrap-datepicker',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
    'js/chosen.jquery.min',
);
include "header.php";
$selectedCampaign=array();
$today = date('Y-m-d');
$tomorrow = date("d-m-Y", time()+86400);
$condition = '1=1';
$statusCondition = ' is_active = 1';
$selectedRm = array();
$selectedCategory=array();
$misCondition = 'am.status_id is not null';
$campaignId = (isset($_GET['campaign_id']) && $_GET['campaign_id'] != '') ? $db->FilterParameters($_GET['campaign_id']) : "";
$cityId = (isset($_GET['city_id']) && $_GET['city_id'] != '') ? $db->FilterParameters($_GET['city_id']) : "";
$categoryId = (isset($_GET['category_id']) && $_GET['category_id'] != '') ? $db->FilterParameters($_GET['category_id']) : "";
$selectedCampaign = (isset($_GET['campaign_id']) && $_GET['campaign_id'] != '') ? explode(",",$_GET['campaign_id']) : "";
$selectedCity = (isset($_GET['city_id']) && $_GET['city_id'] != '') ? explode(",",$_GET['city_id']) : "";
$selectedCategory = (isset($_GET['category_id']) && $_GET['category_id'] != '') ? explode(",",$_GET['category_id']) : "";
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$selectedCity,array("city_name"=>"asc"));
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$selectedCategory,array("category_name"=>"asc"));
$campaignDd = $db->CreateOptions("html","campaign_master",array("campaign_id","campaign_name"),$selectedCampaign,array("campaign_name"=>"asc"));
$createdDate = (isset($_GET['created_date']) && $_GET['created_date'] != '') ? $db->FilterParameters($_GET['created_date']) : "";
$range_to = '';
$range_from = '';
$message = 'Report of this month';

if($createdDate != ''){
    $date_range_str = $createdDate;
    $date_range_arr = explode(" to ", $date_range_str);
    $range_from = Core::DMYToYMD($date_range_arr[0]);
    $range_to = Core::DMYToYMD($date_range_arr[1]);

    if($message == ''){
        $message = ($range_to != $range_from) ? 'Report From Created Date '.core::YMDToDMY($range_from)." To ".core::YMDToDMY($range_to) : "Report OF ".core::YMDToDMY($range_to);
    }
    $dateCondition = ($range_to == $range_from) ? " date_format(am.created_at,'%Y-%m-%d') = '$range_from'" : "date_format(am.created_at,'%Y-%m-%d') >= '$range_from' AND date_format(am.created_at,'%Y-%m-%d') <= '$range_to'";

    $misCondition .= " and ($dateCondition)";
} else {
    $misCondition .= " AND ((date_format(am.created_at,'%m-%Y') = '".date('m-Y')."'))";
}
if($campaignId != ''){

    $misCondition .= " AND (cm.campaign_id in ($campaignId))";

}
if($cityId != ''){

    $misCondition .= " AND (c.city_id in ($cityId))";
}
if($categoryId != ''){

    $misCondition .= " AND (ct.category_id in ($categoryId))";
}
$mainTable = array("activity_master as am",array("count(*) as number"));
$joinTable = array(
    array("left","status_master as sm","sm.status_id = am.status_id",array("sm.status_name")),
    array("left","admin_user as u","u.user_id = am.created_by",array('concat(u.first_name," ",u.last_name) as user_name','u.user_id','u.user_type')),
    array("left","prospect_master as pm","am.type_id = pm.prospect_id"),
    array("left","campaign_master as cm","cm.campaign_id = pm.campaign_id",array('cm.campaign_name')),
    array("left","city as c","c.city_id = pm.city_id",array('c.city_name')),
    array("left","category_master as ct","ct.category_id = pm.category_id",array('ct.category_name'))

);
$misDisRes = $db->JoinFetch($mainTable,$joinTable,$misCondition,array("sm.status_name"=>"asc"),null,"sm.status_id,u.user_id");
$misData = $db->FetchToArrayFromResultset($misDisRes);
$statusFormatData = array();

foreach($misData as $key => $value){
    $statusFormatData[$value['user_name']][$value['status_name']] = $value['number'];
}
$statusData = $db->FetchToArray("status_master",array("status_name"),"is_active = 1");
?>
<style type="text/css">
</style>
<script type="text/javascript">
    $(document).ready(function(){


        $(".chzn-select-filter").chosen({
            allow_single_deselect: true
        });

        $("#rm_team").click(function(){
            if($(this).prop("checked") == true){
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });


        if (jQuery().daterangepicker) {
            $(".date_picker_range")
                .daterangepicker(
                {
                    ranges : {
                        Today : [ "today", "today" ],
                        Yesterday : [ "yesterday", "yesterday" ],
                        "Last 7 Days" : [ Date.today().add({
                            days : -6
                        }), "today" ],
                        "Last 30 Days" : [ Date.today().add({
                            days : -29
                        }), "today" ],
                        "This Month" : [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth() ],
                        "Last Month" : [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months : -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days : -1
                                }) ]
                    },
                    opens : "right",
                    format : "dd-MM-yyyy",
                    separator : " to ",
                    locale : {
                        applyLabel : "Submit",
                        fromLabel : "From",
                        toLabel : "To",
                        customRangeLabel : "Custom Range",
                        daysOfWeek : [ "Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa" ],
                        monthNames : [ "January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December" ],
                        firstDay : 1
                    },
                    buttonClasses : [ "btn-danger" ]
                },
                function(e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    // addOrUpdateUrlParam("date",$("#login_date").val());
                });
        }

        $("#filter_btn").click(function(){
            //showMessage();
            var newUrl = '';
            $(".filter-report").each(function( index ) {
                var id = $(this).attr("id");
                var value = $(this).val();
                if(value != null && value != '') {
                    newUrl = addOrUpdateUrlParam(id,value);
                }

            });
            document.location.search = newUrl;
        })

        $("#clear").click(function(){
            form = $(this).closest('form');
            form.clearForm();
            $(".chzn-select-filter").trigger("liszt:updated");
            $("#filter_btn").click();
        });


    });

    var url='';


    function addOrUpdateUrlParam(key, value) {
        key =  encodeURI(key);

        value =  encodeURI(value);
        var kvp = url.split('&');

        if (kvp == '' && value != null) {
            //document.location.search = '?' + key + '=' + value;
            url = '?' + key + '=' + value;
        }
        else {

            var i = kvp.length;
            var x;
            while (i--) {
                x = kvp[i].split('=');
                if (x[0] == key && (x[1]) != null) {
                    x[1] = value;
                    kvp[i] = x.join('=');
                    break;
                }
            }

            if (i < 0) { kvp[kvp.length] = [key, value].join('='); }

            //this will reload the page, it's likely better to store this until finished
            //document.location.search = kvp.join('&');
            url = kvp.join('&');
        }
        return url;

    }
</script>

<script>
    var pageTitle = '';
    function PrintElem(elem)
    {
        var html = $("<div>"+$(elem).html()+"</div>");

        pageTitle = "Agent Disposition Report";

        $(html).find('div.table-header').html(pageTitle);
        $(html).find('table#dg_mis').attr("border","1").css({"border-collapse": "collapse","border": "1px solid", "width": "100%"});
        Popup($(html).html());
    }

    function Popup(data)
    {


        var printwindow = window.open('', 'Print Div', 'height=600,width=600');
        printwindow.document.write('<html><head><title>'+pageTitle+'</title>');
        printwindow.document.write('<style>.hide{   display: none;}</style>');

        printwindow.document.write('</head><body >');
        printwindow.document.write(data);
        printwindow.document.write('</body></html>');

        printwindow.document.close(); // necessary for IE >= 10
        printwindow.focus(); // necessary for IE >= 10

        printwindow.print();
        printwindow.close();

        return true;
    }
</script>
<div class="page-header position-relative">
    <h4><?php echo $message; ?></h4>
</div>
<div class='row-fluid'>
    <div class="span12">
        <form class='form-inline' id='frm_kyc_tracker'>


            <div class="control-group inline">
                <label class="control-label" for="campaign_id">Campaign</label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="campaign_id" sdata-placeholder="Select Campaign" multiple name="filter[campaign][]">
                        <option></option>
                        <?php echo $campaignDd; ?>
                    </select>
                </div>
            </div>
            <div class="control-group inline">
                <label class="control-label" for="city_id">City</label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="city_id" data-placeholder="Select City" multiple name="filter[city][]">
                        <option></option>
                        <?php echo $cityDd; ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="city_id">Loan/Product</label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="category_id" data-placeholder="Select Loan/Product" multiple name="filter[category][]">
                        <option></option>
                        <?php echo $categoryDd; ?>
                    </select>
                </div>
            </div>
            <div class='control-group inline'>
                <label class='control-label' for='start_date'>
                    Created Date
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='date_picker_range filter-report' data-placement='top' type='text' placeholder='Date'
                               name='filter[created_date]' value="<?php echo $createdDate; ?>" id="created_date" data-date-format='dd-mm-yyyy'
                               readonly='readonly'>
                        <span class='add-on'><i class='icon-calendar'></i></span>
                    </div>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for=""></label>
                <a id="filter_btn" class="btn btn-small btn-primary">
                    <i class="icon-filter"></i>
                    Filter
                </a>
                <label class="inline">
                    <input type="button" name="clear" id="clear" value="clear" class="btn btn-small clear">
                </label>
                <label class="inline">
                    <button class="btn btn-small btn-info clear" onClick="PrintElem('#print')"><i class="icon icon-print"></i>Print</button>
                </label>
            </div>

        </form>
    </div>
</div>
<?php
if(count($statusFormatData) > 0){
    ?>
    <div class='row-fluid' id="print">
        <div class="span12" id="display_mis">
            <table class="table" id="dg_mis">
                <thead>
                <th>BD</th>
                <?php
                $total = array();
                foreach($statusData as $status){ ?>
                    <th><?php echo $status; ?></th>
                <?php }?>
                </thead>
                <tbody>
                <?php
                if(count($statusFormatData) > 0){
                    foreach($statusFormatData as $user => $misData){
                        if($user != ''){
                            ?>
                            <tr>
                                <td><?php echo $user; ?></td>

                                <?php
                                if(count($misData) > 0){
                                    foreach($statusData as $statusName){
                                        if(array_key_exists($statusName,$misData)){
                                            echo "<td class='te-number'>".$misData[$statusName]."</td>";
                                            $total[$statusName] = (array_key_exists($statusName,$total)) ?$total[$statusName] + $misData[$statusName] : $misData[$statusName];
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    }
                                }
                                ?>
                            </tr>
                        <?php }?>
                    <?php }?>
                <?php } ?>
                <?php
                if(count($statusFormatData) > 0){
                    echo "<th colspan='1'>Total</th>";

                    foreach($statusData as $statusName){
                        if(array_key_exists($statusName,$total)){
                            echo "<td class='te-number'><b>".$total[$statusName]."</b></td>";
                        } else {

                            echo "<td>-</td>";
                        }
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } else { ?>
    <tr>
        <td colspan="21">
            <div>
                <h3 class='col-xs-12' align='center'>
                    <span style='color:#438EB9;'>No Result Found..</span>
                </h3>
            </div>
        </td>
    </tr>
<?php }?>



<?php
include_once 'footer.php';
?>
