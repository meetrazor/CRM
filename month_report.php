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
$selectedStatus = array();
$statusCondition = " status_type = 'support'";
$selectedRm = array();
//Requested Filter
$filterStatusId = (isset($_GET['status_id']) && $_GET['status_id'] != '') ? $db->FilterParameters($_GET['status_id']) : "";
$filterYear = (isset($_GET['year']) && $_GET['year'] != '') ? $db->FilterParameters($_GET['year']) : "";
$filterMonth = (isset($_GET['month']) && $_GET['month'] != '') ? $db->FilterParameters($_GET['month']) : "";
$selectedStatus = (isset($_GET['status_id']) && $_GET['status_id'] != '') ? explode(",",$_GET['status_id']) : "";

//Filters dropdown
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$selectedStatus,array("sort_order"=>"asc"),$statusCondition);
$loanDd = $db->CreateOptions("html","loan_type_master",array("loan_type_id","loan_type_name"),null,array("loan_type_name"=>"asc"),"is_active = '1'");
$productTypeDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"),"is_active = '1'");

$range_to = '';
$range_from = '';
$message = 'Month Wise Report';

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

        $("#filter_btn").click(function(){
            getMisData("html");
        });

        $("#clear").click(function(){
            form = $(this).closest('form');
            form.clearForm();
            $(".chzn-select-filter").trigger("liszt:updated");
            $("#filter_btn").click();
        });

        $("#filter_btn").click();
    }); 

    var url='';


    function getMisData(reportType){
        $.ajax({
            type: 'post',
            url: "control/report.php?act=monthreport",
            data: {
                "loan_type_id": $('#loan_type_id').val(),
                "product_type_id": $('#product_type_id').val(),
                "month": $('#month').val(),
                "year": $('#year').val(),
                "status_id": $('#status_id').val(),
                "report_type": reportType
            },
            dataType: 'html',
            beforeSend: function(){
                bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being loaded');
            },
            complete : function (){
                bootbox.hideAll();
            },
            success: function (data) {
                if(reportType != 'export'){
                    $("#display_mis").html(data);
                } else {
                    document.location = data;
                }
            }
        });
    }


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

    var pageTitle = '';
    function PrintElem(elem)
    {
        var html = $("<div>"+$(elem).html()+"</div>");

        pageTitle = "Month Wise Report";

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
        //printwindow.close();

        return false;
    }

    function exportReport() {
        getMisData("export");
    }
</script>
<div class="page-header position-relative">
    <h4><?php echo $message; ?></h4>
</div>
<div class='row-fluid'>
    <div class="span12">
        <form class='form-inline' id='frm_kyc_tracker'> 
            <div class="control-group inline">
                <label class="control-label" for="">Loan Type</label>
                <div class="controls">
                    <select id="loan_type_id" class="chzn-select-filter select-loan-type" data-placeholder="Select Loan Type" name="filter[loan_type_id][]" multiple>
                        <option></option>
                        <?php echo $loanDd; ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="">Product Type</label>
                <div class="controls">
                    <select id="product_type_id" class="chzn-select-filter select-product-type" data-placeholder="Select Product Type" name="filter[product_type_id][]" multiple>
                        <option></option>
                        <?php echo $productTypeDd; ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="campaign_id">Year</label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="year" data-placeholder="Select Year" multiple name="filter[year][]">
                        <?php echo core::YearDropDown(1990,2019,$selectedYear); ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="city_id">Month</label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="month" data-placeholder="Select Month" multiple name="filter[month][]">
                       <?php echo core::MonthsDropDown($selectedMonth); ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="status_id">Status </label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="status_id" data-placeholder="Select Status" name="filter[status][]" multiple>
                        <option></option>
                        <?php echo $statusDd; ?>
                    </select>
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
                <label class="inline">
                    <a href="javascript:void(0)" class="btn btn-small btn-info clear" onClick="exportReport();"><i class="icon icon-save"></i>Export</a>
                </label>
            </div>

        </form>
    </div>
</div>

    <div class='row-fluid' id="print">
        <div class="span12" id="display_mis">

        </div>
    </div>

<?php
include_once 'footer.php';
?>
