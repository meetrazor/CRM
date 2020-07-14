<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'bootstrap-daterangepicker/daterangepicker',
    'css/chosen',
    'css/datepicker',
    'data-tables/css/dataTables.colVis',
);

$asset_js = array(
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/jquery.autosize-min',
    'js/jquery.maskedinput.min',

    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'js/jquery.gritter.min',
    'js/bootbox.min',

    'data-tables/js/fnStandingRedraw',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
    'js/date-time/bootstrap-datepicker.min',
    'data-tables/dataTables.colVis',

);
include_once 'header.php';
$userDd = $db->CreateOptions("html", "admin_user", array("user_id", "CONCAT(first_name,' ',last_name) as user_name"), null, array("user_id" => "asc"));
?>

    <style type="text/css">
        table#dg_call_audit tfoot {
            display: table-header-group;
        }

        #dg_call_audit td.details-control {
            background: url('assets/data-tables/images/details_open.png') no-repeat center center;
            cursor: pointer;
        }

        #dg_call_audit tr.shown td.details-control {
            background: url('assets/data-tables/images/details_close.png') no-repeat center center;
        }

        #dg_call_audit td.pic {

        }

        #dg_call_audit tr.shown-pic td.pic {

        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {

            var wait = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>" +
                "<div class='col-xs-12' align='center'>" +
                "<h3><span style='color:#438EB9;'>Please wait...</span></h3>" +
                "</div>" +
                "</div>";
            var noResult = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>" +
                "<div class='col-xs-12' align='center'>" +
                "<h3><span style='color:#438EB9;'>No Result Found...</span></h3>" +
                "</div>" +
                "</div>";


            $(".chzn-select-filter").chosen({
                allow_single_deselect: true,
            });

            $('.modal').on('shown.bs.modal', function () {
                $('.chzn-select', this).chosen({
                    allow_single_deselect: true
                });
            });

            function showMessage() {
                // bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being filter');
            }

            $('[data-rel=tooltip]').tooltip();

            var breakpointDefinition = {
                pc: 1280,
                tablet: 1024,
                phone: 480
            };
            var responsiveHelper2 = undefined;
            dg_call_audit = $('#dg_call_audit').dataTable({
                "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
                oLanguage: {
                    sSearch: "Search _INPUT_",
                    sLengthMenu: " _MENU_ ",
                    sInfo: "_START_ to _END_ of _TOTAL_",
                    sInfoEmpty: "0 - 0 of 0",
                    oPaginate: {
                        sFirst: '<i class="icon-double-angle-left"></i>',
                        sLast: '<i class="icon-double-angle-right"></i>',
                        sPrevious: '<i class="icon-angle-left"></i>',
                        sNext: '<i class="icon-angle-right"></i>',
                    }
                },
                "bProcessing": true,
                "bServerSide": true,
                "bScrollCollapse": true,
                "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "sAjaxSource": "control/call_audit.php",
                "fnServerParams": function (aoData) {
                    var form_data = $('#frm_filter').serializeArray();
                    $.each(form_data, function (i, val) {
                        aoData.push(val);
                    });
                    aoData.push({"name": "act", "value": "fetch"});
                    server_params = aoData;
                },
                "aaSorting": [[1, "asc"]],
                "aoColumns": [
                    {
                        mData: "call_audit_id",
                        bSortable: false,
                        mRender: function (v, t, o) {
                            return '<label><input type="checkbox" id="chk_' + v + '" value="' + v + '"/><span class="lbl"></span></label>';
                        },
                        sClass: 'center'
                    },
                    {
                        "mData": null,
                        "class": 'details-control',
                        bSortable: false,
                        "defaultContent": '',
                        "width": "2%"
                    },
                    {
                        "mData": "user_name",
                        "width": "15%"
                    },
                    {
                        "mData": "audit_date",
                        "width": "15%"
                    },
                    {"mData": "audit_time"},
                    {
                        "mData": "mobile",
                        "width": "15%"
                    },
                    {
                        "mData": "weight",
                        bSortable: false,
                    }, {
                        "mData": "inPercent",
                        bSortable: false,
                    },
                    {"mData": "created_at"},
                    {"mData": "creator_name"},
                    {
                        bSortable: false,
                        mData: null,
                        mRender: function (v, t, o) {
                            var act_html = '';
                            <?php
                            if($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Edit Call Audit')){
                            ?>
                            act_html = act_html + "<a href='call_audit_addedit.php?id=" + o['call_audit_id'] + "&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit Call Audit'><i class='icon-edit bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Delete Call Audit')){
                            ?>
                            act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('" + o['call_audit_id'] + "')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            return act_html;
                        }
                    }
                ],
                fnPreDrawCallback: function () {
                    if (!responsiveHelper2) {
                        responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
                    }
                },
                "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                    responsiveHelper2.createExpandIcon(nRow);
                    return nRow;
                },
                fnDrawCallback: function (oSettings) {
                    responsiveHelper2.respond();
                    $(this).removeAttr('style');
                    $('[data-rel=tooltip]').tooltip();
                }
            });

            $("tfoot input").keyup(function () {
                dg_call_audit.fnFilter(this.value, $(this).attr("colPos"));
            });


            $('#edit_record').click(function (e) {
                var selected_list = $('#dg_call_audit tbody input[type=checkbox]:checked');
                var selected_length = selected_list.size();

                if (0 == selected_length) {

                    showGritter('info', 'Alert!', 'Please select call audit to edit.');
                    return false;
                } else if (selected_length > 1) {
                    showGritter('info', 'Alert!', 'Only single record can be edited at a time.');
                    return false;
                }

                href = $('#edit_record').attr('href');
                href += '&id=' + selected_list.val();
                $('#edit_record').attr('href', href);
                return true;
            });


            $('#delete_record').click(function () {

                var delete_ele = $('#dg_call_audit tbody input[type=checkbox]:checked');
                var selected_length = delete_ele.size();

                if (0 == selected_length) {
                    showGritter('info', 'Alert!', 'Please select call audit to delete.');
                    return false;
                } else {
                    bootbox.confirm("Are you sure to delete selected audit(s)? It will delete all audit related data and can not be reverted!", function (result) {
                        if (result) {

                            var delete_id = [];
                            $.each(delete_ele, function (i, ele) {
                                delete_id.push($(ele).val());
                            });

                            $.ajax({
                                url: 'control/call_audit.php?act=delete',
                                type: 'post',
                                dataType: 'json',
                                data: {id: delete_id,},
                                success: function (resp) {
                                    dg_call_audit.fnDraw();
                                    if (resp.success) {
                                        showGritter('success', resp.title, resp.msg);
                                    } else {
                                        showGritter('error', resp.title, resp.msg);
                                    }
                                }
                            });
                        }
                    });
                }
            });


            var Otable = $("#dg_call_audit").DataTable();
            // show more details or rows
            $('#dg_call_audit tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Otable.row(tr);
                if (tr.hasClass('shown-pic')) {
                    tr.removeClass('shown-pic');
                    row.child.hide();
                }
                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    var call_audit_id = row.data().call_audit_id;
                    $.ajax({
                        url: 'control/call_audit.php?act=get_audit_details',
                        type: 'post',
                        dataType: 'html',
                        beforeSend: function () {
                            row.child(wait).show();
                            tr.addClass('shown');
                        },
                        data: {call_audit_id: call_audit_id},
                        success: function (resp) {
                            row.child(resp).show();
                            tr.addClass('shown');
                        }
                    });
                }
            });


            //filter button
            $("#filter_btn").click(function () {
                showMessage();
                dg_call_audit.fnStandingRedraw();
            });

            $("#clear").click(function () {
                form = $(this).closest('form');
                form.clearForm();
                $(".chzn-select-filter").trigger("liszt:updated");
                dg_call_audit.fnStandingRedraw();

            });

            // date picker
            // $('.create-date-picker').datepicker({
            //     todayHighlight : false,
            //     endDate: '-1d',
            //     orientation: 'top',
            //     autoclose: true,
            // }).next().on(ace.click_event, function () {
            //     $(this).prev().focus();
            // });


            //date range Picker
            if (jQuery().daterangepicker) {
                $(".audit-date").daterangepicker(
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
                }
            );

            $(".create-date").daterangepicker(
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
                    }
                );
            }
        });

        function ExportToExcel(ele) {

            var query_string = decodeURIComponent($.param(server_params));
            $(ele).attr('href', 'export_call_audit.php?=' + query_string);
            return true;
        }

        function ExportToExcelAll(ele){

            var query_string = decodeURIComponent($.param(server_params));
            $(ele).attr('href','export_call_audit_all.php?='+query_string);
            return true;
        }

        function DeleteRecord(rid) {

            $('#chk_' + rid).prop('checked', true);
            $('#delete_record').click();
        }

    </script>
    <!--    filter-->
    <div class="row-fluid">
        <div class="span12">
            <div class="widget-box">
                <div class="widget-header">
                    <h4><i class="icon icon-filter"></i>Advance Filters</h4>
                    <span class="widget-toolbar">
                        <a data-action="collapse" href="#" title="Show/Hide Filters">
                            <i class="icon-chevron-up"></i>
                        </a>
                    </span>
                </div>
                <div class="widget-body">
                    <div class="widget-body-inner" style="display: block;">
                        <div class="row-fluid">
                            <form id="frm_filter" class="form-inline" name="frm_filter">

                                <div class="control-group inline">
                                    <label class="control-label" for="date_range_1">Audit Date</label>
                                    <div class="controls">
                                        <div class="row-fluid input-append">
                                            <input class="input-large audit-date"  type="text"
                                                   placeholder="Audit date" name="filter[audit_date]" id="filter_audit_date"
                                                   data-date-format="dd-mm-yyyy" readonly="readonly">
                                            <span class="add-on"><i class="icon-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="control-group inline">
                                    <label class="control-label" for="date_range_2">Create Date</label>
                                    <div class='controls'>
                                        <div class='row-fluid input-append'>
                                            <input class='input-large create-date'  type='text'
                                                   placeholder='Create date'
                                                   name='filter[create_date]' id="filter_create_date" data-date-format='dd-mm-yyyy'
                                                   readonly='readonly'>
                                            <span class='add-on'><i class='icon-calendar'></i></span>
                                        </div>
                                    </div>
                                </div>


                                <div class="control-group inline">
                                    <label class="control-label" for="">User</label>
                                    <div class="controls">
                                        <select class="chzn-select-filter" data-placeholder="Select user"
                                                name="filter[user_id]" multiple>
                                            <option></option>
                                            <?php echo $userDd; ?>
                                        </select>
                                    </div>
                                </div>

                                <a id="filter_btn" class="btn btn-small btn-primary">
                                    <i class="icon-filter"></i>
                                    Filter
                                </a>
                                <label class="inline">
                                    <input type="button" name="clear" id="clear" value="clear"
                                           class="btn btn-small btn-info clear">
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Call Audit List
                        <span class="widget-toolbar pull-right">
                            <?php
                            if ($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Export Call Audit')) {
                            ?>
                            <a target="_blank" id="export_excel" href="javascript:void(0);"
                               class="btn btn-mini btn-primary show-tooltip" data-placement="top" data-rel="tooltip"
                               data-original-title="Excel Export" onclick="return ExportToExcel(this);">
                                <i class="icon-save icon-large white">Export</i></a>&nbsp|
                            <?php } ?>

                            <?php
                            if ($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Export All Call Audit')) {
                            ?>
                            <a target="_blank" id="" href="javascript:void(0);"
                               class="btn btn-mini btn-primary show-tooltip" data-placement="bottom" data-rel="tooltip"
                               data-original-title="Export All Ticket Data" onclick="return ExportToExcelAll(this);">
                                <i class="icon-save icon-large white">Export All</i></a>&nbsp|
                            <?php } ?>

                            <?php
                            if ($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Add Call Audit')) {
                                ?>
                                <a id='add_record' href="call_audit_addedit.php?token=<?php echo $token; ?>"
                                   data-placement="top"
                                   data-rel="tooltip" data-original-title="Add" class="white"><i
                                            class="icon-plus icon-large white"></i>Add</a>&nbsp|
                            <?php } ?>


                            <?php
                            if ($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Edit Call Audit')) {
                                ?>
                                <a id='edit_record' href="call_audit_addedit.php?token=<?php echo $token; ?>"
                                   data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i
                                            class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                            <?php } ?>

                            <?php
                            if ($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Delete Call Audit')) {
                                ?>
                                <a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip"
                                   data-original-title="Delete" class="white"><i
                                            class="icon-trash icon-large white"></i>Delete</a>
                            <?php } ?>

					</span>
                    </div>
                    <table id='dg_call_audit' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox"/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th></th>
                            <th>User Name</th>
                            <th>Audit Date</th>
                            <th>Audit Time</th>
                            <th>Mobile</th>
                            <th>Performance</th>
                            <th>Performance(%)</th>
                            <th>Created At</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th class="center">
                            </th>
                            <th></th>
                            <th>
                                <input type="text" placeholder="User name" name="user_name" class="span12"
                                       colPos="3">
                            </th>
                            <th></th>
                            <th></th>
                            <th>
                                <input type="text" placeholder="Mobile" name="mobile" class="span12" colPos="6">
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php
include_once 'footer.php';
?>