<?php
$asset_css = array(
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen.create-option',
    'bootstrap-daterangepicker/daterangepicker',
);

$asset_js = array(
    'js/chosen.jquery.min',
    'js/chosen.create-option.jquery',
    'bootstrap-datepicker/js/bootstrap-datepicker',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
);
include_once 'header.php';
$teleCallerDd = $db->CreateOptions('html', 'admin_user', array('user_id','concat(first_name," ",last_name)'), null, array('concat(first_name," ",last_name)'=>"asc"),"user_type = ".UT_TC."");
$cityDd = $db->CreateOptions('html', 'city', array('city_id','city_name'), null, array('city_name'=>"asc"));
$dispositionDd = $db->CreateOptions("html","disposition_master",array("disposition_id","disposition_name"),null,array("disposition_name"=>"asc"));
$campaignDd = $db->CreateOptions("html", "campaign_master", array("campaign_id", "campaign_name"), null, array("campaign_name" => "asc"));
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"));
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),null,array("sort_order"=>"asc"),"status_type = 'support'");
$segmentDd = $db->CreateOptions("html","tickets",array("call_from","call_from"),null,array("call_from"=>"asc"),null,null,"call_from");
$priorityDd = $db->CreateOptions("html","query_type_master",array("query_type_id","query_type_name"),null,array("query_type_name"=>"asc"));
$reasonDd = $db->CreateOptions("html","reason_master",array("reason_id","reason_name"),null,array("reason_name"=>"asc"));
$queryStageDd = $db->CreateOptions("html","query_stage_master",array("query_stage_id","query_stage_name"),null,array("query_stage_name"=>"asc"));
$bankDd = $db->CreateOptions("html","bank_master",array("bank_id","bank_name"),null,array("bank_name"=>"asc"));
$tcUserDd = $db->CreateOptions("html", "admin_user", array("user_id", "CONCAT(first_name,' ',last_name) as user_name"), null, array("user_id" => "asc"),"is_active = '1' and user_type = ".UT_TC."");
$questionDd = $db->CreateOptions("html", "question_master", array("question_id", "question_short_name"), null, array("question_id" => "asc"),"is_active = '1'");
?>
    <style rel="stylesheet">
        .btn_download {
            padding-right: 1em;
        }
    </style>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">google.load('visualization', '1.0', {'packages':['corechart']});</script>

    <script type="text/javascript">

    var brOptions;
    var nrOptions;

    $(function() {

        $(".chzn-select,.chzn-select-nr,.chzn-select-dis,.chzn-select-cam,.chzn-select-month,.chzn-select-year," +
            ".chzn-select-segment,.chzn-select-status,.chzn-select-filter").chosen({
            allow_single_deselect:true
        });

        $(".chzn-select").change(function(){
            cityChart();
        });

        $(".chzn-select-nr").change(function(){
            telecallerChart();
        });

        $(".chzn-select-dis").change(function(){
            dispositionChart();
        });

        $(".chzn-select-cam").change(function(){
            campaignChart();
        });

        $(".month-report").change(function(){
            monthChart();
        });

        $(".segment-report").change(function(){
            segmentChart();
        });

        $(".priority-report").change(function(){
            priorityChart();
        });

        $(".bank-report").change(function(){
            bankChart();
        });

        $(".reason-report").change(function(){
            reasonChart();
        });

        $(".agent-report").change(function(){
            agentReportChart();
        });

        $(".error-report").change(function(){
            questionReportChart();
        });


        if (jQuery().daterangepicker) {

            var start = Date.today().moveToFirstDayOfMonth();
            var end = Date.today().moveToLastDayOfMonth();

            $(".date-picker").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "left",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    cityChart();
                });

            $(".date-picker-tr").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    telecallerChart()
                });


            $(".date-picker-lead").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    leadChart();
                });

            $(".date-picker-disposition").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    dispositionChart();
                });

            $(".date-picker-segment").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-segment").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    segmentChart();
                });

            $(".date-picker-priority").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-priority").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    priorityChart();
                });

            $(".date-picker-bank").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-bank").daterangepicker(
                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    bankChart();
                });



            $(".date-picker-reason").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-reason").daterangepicker(

                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    reasonChart();
                });


            $(".date-picker-audit").daterangepicker(

                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    agentReportChart();
                });

            $(".date-picker-error-audit").daterangepicker(

                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    questionReportChart();
                });

            $(".date-picker-agent").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-agent").daterangepicker(

                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    agentReportChart();
                });


            $(".date-picker-error-agent").val(start.toString("dd-MM-yyyy") + ' to ' + end.toString("dd-MM-yyyy"));

            $(".date-picker-error-agent").daterangepicker(

                {
                    ranges: {
                        Today: ["today", "today"],
                        Yesterday: ["yesterday", "yesterday"],
                        "Last 7 Days": [Date.today().add({
                            days: -6
                        }), "today"],
                        "Last 30 Days": [Date.today().add({
                            days: -29
                        }), "today"],
                        "This Month": [
                            Date.today().moveToFirstDayOfMonth(),
                            Date.today().moveToLastDayOfMonth()],
                        "Last Month": [
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    months: -1
                                }),
                            Date.today().moveToFirstDayOfMonth()
                                .add({
                                    days: -1
                                })]
                    },
                    opens: "right",
                    format: "dd-MM-yyyy",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom Range",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th",
                            "Fr", "Sa"],
                        monthNames: ["January", "February", "March",
                            "April", "May", "June", "July",
                            "August", "September", "October",
                            "November", "December"],
                        firstDay: 1
                    },
                    buttonClasses: ["btn-danger"]
                },
                function (e, t) {
                    $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
                    questionReportChart();
                });

        }

        $(".clear").click(function(){
            $(this).closest("td").find(".input-large").val("");
            if($(this).closest("td").find(".input-large").attr("id") == "date_range_lead"){
                leadChart();
            }else if($(this).closest("td").find(".input-large").attr("id") == "date_range_city"){
                cityChart();
            } else if($(this).closest("td").find(".input-large").attr("id") == "date_range_disposition"){
                dispositionChart();
            } else if($(this).closest("form").attr("id") == "frm_report_month"){
                $(".month-report").val('').trigger("chosen:updated");
                monthChart();
            }  else if($(this).closest("form").attr("id") == "frm_report_segment"){
                $(".segment-report").val('').trigger("chosen:updated");
                segmentChart();
            } else if($(this).closest("form").attr("id") == "frm_report_priority"){
                $(".priority-report").val('').trigger("chosen:updated");
                priorityChart();
            }  else if($(this).closest("form").attr("id") == "frm_report_bank"){
                $(".bank-report").val('').trigger("chosen:updated");
                bankChart();
            }  else if($(this).closest("form").attr("id") == "frm_report_reason"){
                $(".reason-report").val('').trigger("chosen:updated");
                reasonChart();
            } else if($(this).closest("form").attr("id") == "frm_agent_performance"){
                $(".agent-report").val('').trigger("chosen:updated");
                agentReportChart();
            }else if($(this).closest("form").attr("id") == "frm_question_report"){
                $(".error-report").val('').trigger("chosen:updated");
                questionReportChart();
            } else {
                telecallerChart();
            }

            return false;
        });

        //cityChart();
        //telecallerChart();
        //leadChart();
        //dispositionChart();
        //campaignChart();
        <?php if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Month Report'))) { ?>
            monthChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Segment Report'))) { ?>
            segmentChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Priority Report'))) { ?>
            priorityChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Bank Report'))) { ?>
            bankChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Reason Report'))) { ?>
            reasonChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Agent Performance'))) { ?>
            agentReportChart();
        <?php }
        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Question Error Report'))) { ?>
            questionReportChart();
        <?php } ?>
    });

    function telecallerChart(){
        $.ajax({
            type: 'post',
            url: "control/report.php?act=telecaller",
            data: {
                "tele_caller_id": $('#tele_caller_id').val(),
                "campaign_id": $('#t_campaign_id').val(),
                "category_id": $('#t_category_id').val(),
                "city_id": $('#t_city_id').val(),
                "date": $('.date-picker-tr').val(),
            },
            dataType: 'json',
            success: function (data) {
                drawTelecallerChart(data);
            }
        })
    }

    function drawTelecallerChart(data) {


        if(data.success){


            chartData = data.report_data;

            // console.log(tableData);
//                    console.log(chartData);

            // console.log(tableData);
            console.log(chartData);

            var arr = [['City','Transaction Made','Prospect To Lead']];
            arr = arr.concat(chartData);

            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                chart: {
                    title: 'Tele Caller Wise Performance',
                }
            };


            var chart = new google.visualization.ColumnChart(document.getElementById('telecaller_chart_div'));

            chart.draw(data, options);

        }

    }


    function cityChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=cityreport",
            data: {
                "city_id": $('#city_id').val(),
                "campaign_id": $('#a_campaign_id').val(),
                "category_id": $('#a_category_id').val(),
                "date": $('.date-picker').val(),
            },
            dataType: 'json',
            success: function (data) {
                drawCityChart(data);
            }
        })
    }

    function drawCityChart(data) {


        if(data.success){


            chartData = data.report_data;

            // console.log(tableData);
//                    console.log(chartData);

            // console.log(tableData);
            console.log(chartData);

            var arr = [['City','Prospect','Leads','Transaction Made']];
            arr = arr.concat(chartData);

            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                chart: {
                    title: 'Area Wise Performance',
                }
            };


            var chart = new google.visualization.ColumnChart(document.getElementById('area_div'));

            chart.draw(data, options);

        }

    }

    function leadChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=leadreport",
            data: {
                "date": $('.date-picker-lead').val(),
            },
            dataType: 'json',
            success: function (data) {
                drawLeadChart(data);
            }
        })
    }

    function drawLeadChart(data) {


        if(data.success){


            chartData = data.report_data;

            // console.log(tableData);
//                    console.log(chartData);

            // console.log(tableData);
            console.log(chartData);

            var arr = [['Parameter','Count']];
            arr = arr.concat(chartData);

            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                chart: {
                    title: 'Prospect to Lead',
                }
            };


            var chart = new google.visualization.ColumnChart(document.getElementById('lead_div'));

            chart.draw(data, options);

        }

    }

    function dispositionChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=dispositionreport",
            data: {
                "disposition_id": $('#d_disposition_id').val(),
                "campaign_id": $('#d_campaign_id').val(),
                "date": $('.date-picker-disposition').val(),
            },
            dataType: 'json',
            success: function (data) {
                drawDispositionChart(data);
            }
        })
    }

    function drawDispositionChart(data) {


        if(data.success){


            chartData = data.report_data;

            // console.log(tableData);
//                    console.log(chartData);

            // console.log(tableData);
            console.log(chartData);

            var arr = [['Disposition','Count']];
            arr = arr.concat(chartData);

            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                chart: {
                    title: 'Disposition Counts',
                }
            };


            var chart = new google.visualization.ColumnChart(document.getElementById('disposition_div'));

            chart.draw(data, options);

        }

    }


    function campaignChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=campaignreport",
            data: {
                "campaign_id": $('#c_campaign_id').val(),
            },
            dataType: 'json',
            success: function (data) {
                drawCampaignChart(data);
            }
        })
    }

    function drawCampaignChart(data) {


        if(data.success){


            chartData = data.report_data;

            // console.log(tableData);
//                    console.log(chartData);

            // console.log(tableData);
            console.log(chartData);

            var arr = [['Campaign','Total Prospect']];
            arr = arr.concat(chartData);

            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                chart: {
                    title: 'Campaign Performance',
                },

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('campaign_div'));

            chart.draw(data, options);

        }

    }

    function monthChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=monthreport",
            data: {
                "status_id": $('#month_status_id').val(),
                "month": $('#month').val(),
                "year": $('#year').val(),
                "limit": 12,
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawMonthChart(data);
            }
        })
    }

    function drawMonthChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });

            //arr = arr.concat(chartData);
            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                title: 'Month Wise Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Month'},
                vAxis: {title: 'Status'}
                 //   isStacked: 'percent'

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('month_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('month_div_png').innerHTML = '<a download="month-report.png" title="month-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(data, options);

        }

    }

    function segmentChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=segmentreport",
            data: {
                "status_id": $('#segment_status_id').val(),
                "created_date": $('.date-picker-segment').val(),
                "call_from": $('#call_from').val(),
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawSegmentChart(data);
            }
        })
    }

    function drawSegmentChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });
            //arr = arr.concat(chartData);
            //console.log(arr);
            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                title: 'Segment Wise Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Segment'},
                vAxis: {title: 'Status'}
                 //   isStacked: 'percent'

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('segment_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('segment_div_png').innerHTML = '<a download="segment-report.png" title="segment-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(data, options);

        }

    }

    function priorityChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=priorityreport",
            data: {
                "status_id": $('#priority_status_id').val(),
                "query_type_id": $('#query_type_id').val(),
                "query_stage_id": $('#query_stage_id').val(),
                "reason_id": $('#reason_id').val(),
                "created_date": $('.date-picker-priority').val(),
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawPriorityChart(data);
            }
        })
    }

    function drawPriorityChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });
            //arr = arr.concat(chartData);
            //console.log(arr);
            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                title: 'Priority Wise Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Priority'},
                vAxis: {title: 'Status'}
                 //   isStacked: 'percent'

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('priority_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('priority_div_png').innerHTML = '<a download="priority-report.png" title="priority-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(data, options);

        }

    }

    function bankChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=bankreport",
            data: {
                "status_id": $('#bank_status_id').val(),
                "bank_id": $('#bank_id').val(),
                "query_stage_id": $('#bank_query_stage_id').val(),
                "reason_id": $('#bank_reason_id').val(),
                "created_date": $('.date-picker-bank').val(),
                "limit": 15,
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawBankChart(data);
            }
        });
    }

    function drawBankChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });
            //arr = arr.concat(chartData);
            //console.log(arr);
            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                title: 'Bank Wise Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Bank'},
                vAxis: {title: 'Status'}
                 //   isStacked: 'percent'

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('bank_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('bank_div_png').innerHTML = '<a download="bank-report.png" title="bank-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(data, options);

        }

    }

    function reasonChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=reasonreport",
            data: {
                "status_id": $('#reason_status_id').val(),
                "query_stage_id": $('#reason_query_stage_id').val(),
                "reason_id": $('#reason_reason_id').val(),
                "created_date": $('.date-picker-reason').val(),
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawReasonChart(data);
            }
        });
    }

    function drawReasonChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });
            //arr = arr.concat(chartData);
            //console.log(arr);
            var data = google.visualization.arrayToDataTable(arr);

            var options = {
                title: 'Reason Wise Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Reason'},
                vAxis: {title: 'Status'}
                 //   isStacked: 'percent'
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('reason_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('reason_div_png').innerHTML = '<a download="reason-report.png" title="reason-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(data, options);

        }

    }

    function agentReportChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=agentperformance",
            data: {
                "user_id": $('#user_id').val(),
                "audit_date": $('.date-picker-audit').val(),
                "created_date": $('.date-picker-agent').val(),
                "limit": 10,
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawAgentReportChart(data);
            }
        });
    }

    function drawAgentReportChart(data) {


        if(data.success){

            chartData = data.report_data;

            var arr = [];
            $.each(chartData, function (data, value) {
                arr.push(value);
            });
            //arr = arr.concat(chartData);
            //console.log(arr);
            var data = google.visualization.arrayToDataTable(arr);

            var view = new google.visualization.DataView(data);

            view.setColumns([0, 1,
                { calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" }]);

            var options = {
                title: 'Agent Performance Report',
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                vAxis: {title: 'Performance'}
                 //   isStacked: 'percent'

            };

            var chart = new google.visualization.ColumnChart(document.getElementById('agent_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('agent_div_png').innerHTML = '<a download="agent-report.png" title="agent-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(view, options);

        }

    }

    function questionReportChart() {

        $.ajax({
            type: 'post',
            url: "control/report.php?act=questionerrorreport",
            data: {
                "user_id": $('#question_user_id').val(),
                "question_id": $('#question_id').val(),
                "audit_date": $('.date-picker-error-audit').val(),
                "created_date": $('.date-picker-error-agent').val(),
                "report_type": "chart"
            },
            dataType: 'json',
            success: function (data) {
                drawQuestionReportChart(data);
            }
        });
    }

    function drawQuestionReportChart(data) {

        if(data.success){

            chartData = data.report_data;

            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Question Name');
            dataTable.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
            dataTable.addColumn('number', 'Max');
            dataTable.addColumn('number', 'Performance');

            dataTable.addRows(chartData);

            var view = new google.visualization.DataView(dataTable);

                view.setColumns([0, 1,
                    2,
                    { calc: "stringify",
                        sourceColumn: 2,
                        type: "string",
                        role: "annotation" },
                    3,
                    { calc: "stringify",
                        sourceColumn: 3,
                        type: "string",
                        role: "annotation" }
                ]);

            var options = {
                title: 'Question Error Report',
                focusTarget: 'category',
                tooltip: { isHtml: true },
                bars: 'vertical',
                bar: {groupWidth: "50%"},
                legend: {position: 'top'},
                hAxis: {title: 'Question'},
                vAxis: {title: 'Max / Performance'}
            };

            // Create and draw the visualization.
            var chart = new google.visualization.ColumnChart(document.getElementById('question_report_div'));

            // create downloadable link for chart.
            google.visualization.events.addListener(chart, 'ready', function() {
                document.getElementById('question_report_div_png').innerHTML = '<a download="question-error-report.png" title="question-error-report" href="' + chart.getImageURI() + '">Download</a>';
            });

            chart.draw(view, options);

        }

    }

    </script>
<?php
if($acl->IsAllowed($login_id,'REPORT', 'REPORT', 'View BD Report')){
    ?>
<div class="row-fluid">
    <div class='span12'>
        <div class="span4" onclick="location.href='prospect.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='new_prospect'>
                        <?php echo Utility::userCount("prospect"); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Today New Prospect</span>
                </span>
            </div>
        </div>
        <div class="span4" onclick="location.href='activity.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='new_prospect'>
                        <?php echo Utility::userCount('follow_up_balance'); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Today Follow up</span>
                </span>
            </div>
        </div>
        <div class="span4" onclick="location.href='activity.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='call'>
                        <?php echo Utility::userCount("lead_count","lm.created_at = '".ONLY_DATE_YMD."'"); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Today Lead</span>
                </span>
            </div>
        </div>
    </div>
</div>
    <hr>
<div class="row-fluid">
    <div class='span12'>
        <div class="span4"  onclick="location.href='activity.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='new_prospect'>
                        <?php echo Utility::userCount("prospect",'1=1'); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Till Date Prospects</span>
                </span>
            </div>
        </div>
        <div class="span4" onclick="location.href='activity.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='new_prospect'>
                        <?php echo Utility::userCount("balance_prospect"); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Prospects to be called </span>
                </span>
            </div>
        </div>

        <div class="span4" onclick="location.href='lead.php';">
            <div class="center">
                <span class="btn btn-large btn-primary no-hover" style="width: 200px">

                    <span class="bigger-75" id='total_lead'>
                        <?php echo Utility::userCount("lead_count"); ?>
                    </span>

                    <br>
                    <span class="smaller-70" id=''>Total Lead</span>
                </span>
            </div>
        </div>

    </div>
</div>
    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Month Report'))) { ?>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class="span12 widget-container-span ui-sortable">
                    <div class="widget-box">
                        <div class="widget-header">
                            <h5>Month Wise Report
                                <a class="btn btn-minier btn-info" href="month_report.php?token=<?php echo $token; ?>">
                                    Detail
                                    <i class="icon-print icon-eye-open"></i>
                                </a>
                            </h5>
                            <span id="month_div_png" class="btn_download"></span>
                        </div>

                        <div class="widget-body">
                            <div class="widget-main">
                                <form id="frm_report_month" class="form-inline" name="frm_report_month">
                                    <table id="">
                                        <tr>
                                            <td colspan="">

                                                <div class="control-group inline">
                                                    <label class="control-label" for="city_id">Month</label>
                                                    <div class="controls">
                                                        <select class="chzn-select-month month-report" id="month" data-placeholder="Select Month" multiple name="filter[month][]">
                                                            <?php echo core::MonthsDropDown($selectedMonth); ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="control-group inline">
                                                    <label class="control-label" for="campaign_id">Year</label>
                                                    <div class="controls">
                                                        <select class="chzn-select-year month-report" id="year" data-placeholder="Select Year" multiple name="filter[year][]">
                                                            <?php echo core::YearDropDown(1990,date("Y"),date("Y")); ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="control-group inline">
                                                    <label class="control-label" for="city_id">Status</label>
                                                    <div class="controls">
                                                        <select class="chzn-select-status month-report" id="month_status_id" data-placeholder="Select Month Status" multiple name="filter[status][]">
                                                            <?php echo $statusDd; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <label>
                                                    <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                </label>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <div id="month_div"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Segment Report'))) { ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="row-fluid">
                    <div class="span12 widget-container-span ui-sortable">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h5>Segment Wise Report
                                <a class="btn btn-minier btn-info" href="segment_report.php?token=<?php echo $token; ?>">
                                    Detail
                                    <i class="icon-print icon-eye-open"></i>
                                </a>
                                </h5>
                                <span id="segment_div_png" class="btn_download"></span>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main">
                                    <form id="frm_report_segment" class="form-inline" name="frm_report_segment">
                                        <table id="">
                                            <tr>
                                                <td colspan="">

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="call_from">Segment </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-segment segment-report" id="call_from" data-placeholder="Select Segment" name="filter[call_from][]" multiple>
                                                                <?php echo $segmentDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='created_date'>
                                                            Created Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-segment' data-placement='top' type='text'
                                                                       placeholder='Select Created Date'
                                                                       name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="status_id">Status</label>
                                                        <div class="controls">
                                                            <select class="chzn-select-status segment-report" id="segment_status_id" data-placeholder="Select Segment Status" multiple name="filter[status][]">
                                                                <?php echo $statusDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <label>
                                                        <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div id="segment_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php } ?>

    <?php if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Priority Report'))) { ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="row-fluid">
                    <div class="span12 widget-container-span ui-sortable">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h5>Priority Wise Report
                                    <a class="btn btn-minier btn-info" href="priority_report.php?token=<?php echo $token; ?>">
                                        Detail
                                        <i class="icon-print icon-eye-open"></i>
                                    </a>
                                </h5>
                                <span id="priority_div_png" class="btn_download"></span>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main">
                                    <form id="frm_report_priority" class="form-inline" name="frm_report_priority">
                                        <table id="">
                                            <tr>
                                                <td colspan="">

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="query_type_id">Priority </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter priority-report" id="query_type_id" data-placeholder="Select Priority" name="filter[query_type_id][]" multiple>
                                                                <?php echo $priorityDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="reason_id">Reason </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter priority-report" id="reason_id" data-placeholder="Select Reason" name="filter[reason][]" multiple>
                                                                <?php echo $reasonDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="query_stage_id">Query Stage </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter priority-report" id="query_stage_id" data-placeholder="Select Query Stage" name="filter[query_stage][]" multiple>
                                                                <?php echo $queryStageDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='created_date'>
                                                            Created Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-priority' data-placement='top' type='text'
                                                                       placeholder='Select Created Date'
                                                                       name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="status_id">Status</label>
                                                        <div class="controls">
                                                            <select class="chzn-select-status priority-report" id="priority_status_id" data-placeholder="Select Priority Status" multiple name="filter[status][]">
                                                                <?php echo $statusDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <label>
                                                        <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div id="priority_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Bank Report'))) {
        ?>
            <div class="row-fluid">
                <div class="span12">
                    <div class="row-fluid">
                        <div class="span12 widget-container-span ui-sortable">
                            <div class="widget-box">
                                <div class="widget-header">
                                    <h5>Bank Wise Report
                                        <a class="btn btn-minier btn-info" href="bank_report.php?token=<?php echo $token; ?>">
                                            Detail
                                            <i class="icon-print icon-eye-open"></i>
                                        </a>
                                    </h5>
                                    <span id="bank_div_png" class="btn_download"></span>
                                </div>

                                <div class="widget-body">
                                    <div class="widget-main">
                                        <form id="frm_report_bank" class="form-inline" name="frm_report_bank">
                                            <table id="">
                                                <tr>
                                                    <td colspan="">

                                                        <div class="control-group inline">
                                                            <label class="control-label" for="bank_id">Bank </label>
                                                            <div class="controls">
                                                                <select class="chzn-select-filter bank-report" id="bank_id" data-placeholder="Select Bank" name="filter[bank][]" multiple>
                                                                    <?php echo $bankDd; ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="control-group inline">
                                                            <label class="control-label" for="reason_id">Reason </label>
                                                            <div class="controls">
                                                                <select class="chzn-select-filter bank-report" id="bank_reason_id" data-placeholder="Select Reason" name="filter[reason][]" multiple>
                                                                    <?php echo $reasonDd; ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="control-group inline">
                                                            <label class="control-label" for="query_stage_id">Query Stage </label>
                                                            <div class="controls">
                                                                <select class="chzn-select-filter bank-report" id="bank_query_stage_id" data-placeholder="Select Query Stage" name="filter[query_stage][]" multiple>
                                                                    <?php echo $queryStageDd; ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class='control-group inline'>
                                                            <label class='control-label' for='created_date'>
                                                                Created Date
                                                            </label>
                                                            <div class='controls'>
                                                                <div class='row-fluid input-append'>
                                                                    <input class='input-large date-picker-bank' data-placement='top' type='text'
                                                                           placeholder='Select Created Date'
                                                                           name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                           readonly='readonly'>
                                                                    <span class='add-on'><i class='icon-calendar'></i></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="control-group inline">
                                                            <label class="control-label" for="status_id">Status</label>
                                                            <div class="controls">
                                                                <select class="chzn-select-status bank-report" id="bank_status_id" data-placeholder="Select Priority Status" multiple name="filter[status][]">
                                                                    <?php echo $statusDd; ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <label>
                                                            <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                        </label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                        <div id="bank_div"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Reason Report'))) {
        ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="row-fluid">
                    <div class="span12 widget-container-span ui-sortable">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h5>Reason Wise Report
                                    <a class="btn btn-minier btn-info" href="reason_report.php?token=<?php echo $token; ?>">
                                        Detail
                                        <i class="icon-print icon-eye-open"></i>
                                    </a>
                                </h5>
                                <span id="reason_div_png" class="btn_download"></span>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main">
                                    <form id="frm_report_reason" class="form-inline" name="frm_report_reason">
                                        <table id="">
                                            <tr>
                                                <td colspan="">

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="reason_id">Reason </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter reason-report" id="reason_reason_id" data-placeholder="Select Reason" name="filter[reason][]" multiple>
                                                                <?php echo $reasonDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="query_stage_id">Query Stage </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter reason-report" id="reason_query_stage_id" data-placeholder="Select Query Stage" name="filter[query_stage][]" multiple>
                                                                <?php echo $queryStageDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='created_date'>
                                                            Created Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-reason' data-placement='top' type='text'
                                                                       placeholder='Select Created Date'
                                                                       name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="status_id">Status</label>
                                                        <div class="controls">
                                                            <select class="chzn-select-status reason-report" id="reason_status_id" data-placeholder="Select Priority Status" multiple name="filter[status][]">
                                                                <?php echo $statusDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <label>
                                                        <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div id="reason_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Agent Performance'))) {
        ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="row-fluid">
                    <div class="span12 widget-container-span ui-sortable">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h5>Agent Performance Report
                                    <a class="btn btn-minier btn-info" href="agent_performance.php?token=<?php echo $token; ?>">
                                        Detail
                                        <i class="icon-print icon-eye-open"></i>
                                    </a>
                                </h5>
                                <span id="agent_div_png" class="btn_download"></span>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main">
                                    <form id="frm_agent_performance" class="form-inline" name="frm_agent_performance">
                                        <table id="">
                                            <tr>
                                                <td colspan="">

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="user_id">Agent </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter agent-report" id="user_id" data-placeholder="Select Agent" name="filter[agent][]" multiple>
                                                                <?php echo $tcUserDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='start_date'>
                                                            Audit Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-audit' data-placement='top' type='text' placeholder='Select Audit Date'
                                                                       name='filter[audit_date]' id="audit_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='created_date'>
                                                            Created Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-agent' data-placement='top' type='text'
                                                                       placeholder='Select Created Date'
                                                                       name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <label>
                                                        <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div id="agent_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Question Error Report'))) {
        ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="row-fluid">
                    <div class="span12 widget-container-span ui-sortable">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h5>Question Error Report
                                    <a class="btn btn-minier btn-info" href="question_error_report.php?token=<?php echo $token; ?>">
                                        Detail
                                        <i class="icon-print icon-eye-open"></i>
                                    </a>
                                </h5>
                                <span id="question_report_div_png" class="btn_download"></span>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main">
                                    <form id="frm_question_report" class="form-inline" name="frm_question_report">
                                        <table id="">
                                            <tr>
                                                <td colspan="">

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="question_user_id">Agent </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter error-report" id="question_user_id" data-placeholder="Select Agent" name="filter[agent][]" multiple>
                                                                <?php echo $tcUserDd; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="control-group inline">
                                                        <label class="control-label" for="question_id">Question Name </label>
                                                        <div class="controls">
                                                            <select class="chzn-select-filter error-report" id="question_id" data-placeholder="Select Question Name" name="filter[question][]" multiple>
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
                                                                <input class='input-large date-picker-error-audit' data-placement='top' type='text' placeholder='Select Audit Date'
                                                                       name='filter[audit_date]' id="audit_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class='control-group inline'>
                                                        <label class='control-label' for='created_date'>
                                                            Created Date
                                                        </label>
                                                        <div class='controls'>
                                                            <div class='row-fluid input-append'>
                                                                <input class='input-large date-picker-error-agent' data-placement='top' type='text'
                                                                       placeholder='Select Created Date'
                                                                       name='filter[created_date]' id="created_date" data-date-format='dd-mm-yyyy'
                                                                       readonly='readonly'>
                                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <label>
                                                        <input type="button" value="clear" class="btn btn-mini btn-info clear">
                                                    </label>

                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div id="question_report_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php } ?>
<?php
include_once 'footer.php';
?>
