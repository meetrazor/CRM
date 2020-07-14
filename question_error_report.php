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

$createdDate = (isset($_GET['created_date']) && $_GET['created_date'] != '') ? $db->FilterParameters($_GET['created_date']) : "";
$auditDate = (isset($_GET['audit_date']) && $_GET['audit_date'] != '') ? $db->FilterParameters($_GET['audit_date']) : "";

$selectedQuestion = (isset($_GET['question_id']) && $_GET['question_id'] != '') ? explode(",",$_GET['question_id']) : "";
$questionDd = $db->CreateOptions("html", "question_master", array("question_id", "question_short_name"), $selectedQuestion, array("question_id" => "asc"),"is_active = '1'");

$selectedUser = (isset($_GET['question_user_id']) && $_GET['question_user_id'] != '') ? explode(",",$_GET['question_user_id']) : "";
$tcUserDd = $db->CreateOptions("html", "admin_user", array("user_id", "CONCAT(first_name,' ',last_name) as user_name"), null, array("user_id" => "asc"),"is_active = '1' and user_type = ".UT_TC."");


$range_to = '';
$range_from = '';
$message = 'Question Error Report';
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

    function getMisData(reportType){
        $.ajax({
            type: 'post',
            url: "control/report.php?act=questionerrorreport",
            data: {
                "user_id": $('#question_user_id').val(),
                "question_id": $('#question_id').val(),
                "created_date": $('#created_date').val(),
                "audit_date": $('#audit_date').val(),
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

    function exportReport(){
        getMisData("export");
    }
</script>

<script>
    var pageTitle = '';
    function PrintElem(elem)
    {
        var html = $("<div>"+$(elem).html()+"</div>");

        pageTitle = "Question Error Report";

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
                <label class="control-label" for="question_user_id">Agent </label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="question_user_id" data-placeholder="Select Agent" name="filter[agent][]" multiple>
                        <?php echo $tcUserDd; ?>
                    </select>
                </div>
            </div>

            <div class="control-group inline">
                <label class="control-label" for="question_id">Question Name </label>
                <div class="controls">
                    <select class="chzn-select-filter filter-report" id="question_id" data-placeholder="Select Question Name" name="filter[question][]" multiple>
                        <option></option>
                        <?php echo $questionDd; ?>
                    </select>
                </div>
            </div>


            <div class='control-group inline'>
                <label class='control-label' for='start_date'>
                    Audit Date
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='date_picker_range filter-report' data-placement='top' type='text' placeholder='Select Audit Date'
                               name='filter[audit_date]' value="<?php echo $auditDate; ?>" id="audit_date" data-date-format='dd-mm-yyyy'
                               readonly='readonly'>
                        <span class='add-on'><i class='icon-calendar'></i></span>
                    </div>
                </div>
            </div>

            <div class='control-group inline'>
                <label class='control-label' for='start_date'>
                    Created Date
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='date_picker_range filter-report' data-placement='top' type='text' placeholder='Select Created Date'
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
