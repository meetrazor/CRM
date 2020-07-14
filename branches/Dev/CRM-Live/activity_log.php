<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'bootstrap-daterangepicker/daterangepicker',
    'css/chosen',
    'data-tables/css/dataTables.colVis',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/js/fnStandingRedraw',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
);
include_once 'header.php';
$userDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"));
$moduleDd = $db->CreateOptions("html","activity_log",array("module","module"),null,array("module" => "asc"),null,null,"module");
$actionDd = $db->CreateOptions("html","activity_log",array("action_name","action_name"),null,array("action_name" => "asc"),null,null,"action_name");
?>

    <script type="text/javascript">
        $(function() {

            $(".chzn-select-filter").chosen({
                allow_single_deselect: true
            });

            function showMessage(){
                // bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being filter');
            }

            $('[data-rel=tooltip]').tooltip();

            var breakpointDefinition = {
                tablet: 1024,
                phone : 480
            };
            var responsiveHelper2 = undefined;
            dg_activity_log = $('#dg_activity_log').dataTable({
                "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
                searching: false,
                "bPaginate": true,
                oLanguage : {
                    sSearch : "Search _INPUT_",
                    sLengthMenu : " _MENU_ ",
                    sInfo : "_START_ to _END_ of _TOTAL_",
                    sInfoEmpty : "0 - 0 of 0",
                    oPaginate : {
                        sFirst : '<i class="icon-double-angle-left"></i>',
                        sLast : '<i class="icon-double-angle-right"></i>',
                        sPrevious: '<i class="icon-angle-left"></i>',
                        sNext: '<i class="icon-angle-right"></i>',
                    }
                },
                "bProcessing": true,
                "bServerSide": true,
                "lengthMenu": [[10,25,50,100], [10,25,50,100]],
                "sAjaxSource": "control/activity_log.php",
                "fnServerParams": function ( aoData ) {
                    var form_data = $('#frm_filter').serializeArray();
                    $.each(form_data, function(i, val){
                        aoData.push(val);
                    });
                    aoData.push({ "name": "act", "value": "fetch" });
                    server_params = aoData;
                },
                "aaSorting": [[ 1, "asc" ]],
                "aoColumns": [
                    {
                        mData:'activity_log_id',
                        bSortable: false,
                        mRender: function (v, t, o) {
                            return '<label><input type="checkbox" id="ids_'+v+'" name="actitvity_log_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                        },
                        sClass: 'center'
                    },
                    { "mData": "module" },
                    { "mData": "action_name" },
                    {
                        "mData": "description",
                        "width": "60%",
                    },
                    { "mData": "log_date" },
                    { "mData": "user_name" },
                    { "mData": "user_browser" },
                    { "mData": "user_platform" },
                    { "mData": "device_type" },
                    { "mData": "user_ip" },
                ],
                fnPreDrawCallback: function () {
                    if (!responsiveHelper2) {
                        responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
                    }
                },
                fnRowCallback  : function (nRow) {
                    responsiveHelper2.createExpandIcon(nRow);
                },
                fnDrawCallback : function (oSettings) {
                    responsiveHelper2.respond();
                    $(this).removeAttr('style');
                }
            });

            //filter button
            $("#filter_btn").click(function(){
                showMessage();
                dg_activity_log.fnStandingRedraw();
            });

            $("#clear").click(function(){
                form = $(this).closest('form');
                form.clearForm();
                $(".chzn-select-filter").trigger("liszt:updated");
                dg_activity_log.fnStandingRedraw();

            });

            //date Picker
            if (jQuery().daterangepicker) {
                $(".date-picker")
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
                        });
            }
        });

        function ExportToExcel(ele){

            var query_string = decodeURIComponent($.param(server_params));
            $(ele).attr('href','export_activity_log.php?='+query_string);
            return true;
        }


    </script>

    <!--Advance Filter Start -->
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
                    <div class="row-fluid">
                        <form id ="frm_filter" class="form-inline" name ="frm_filter">

                            <div class="control-group inline">
                                <label class="control-label" for="action_name">Action</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" name="filter[action_name][]" id="filter_action_name" data-placeholder="Action Name" multiple>
                                        <option></option>
                                        <?php echo $actionDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="module">Module</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" name="filter[module][]" id="filter_module_name" data-placeholder="Module Name" multiple>
                                        <option></option>
                                        <?php echo $moduleDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="user">User Name</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" name="filter[user_id][]" id="filter_user_name" data-placeholder="User Name" multiple>
                                        <option></option>
                                        <?php echo $userDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="date_range_1">Log Date</label>
                                <div class="controls">
                                    <div class='row-fluid input-append'>
                                        <input class='input-large date-picker' data-placement='top' type='text' placeholder='Log Date'
                                               name='filter[log_date]' id="date_range_1" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[log_date]' class='help-inline'></span>
                                    </div>

                                </div>
                            </div>

                            <a id="filter_btn" class="btn btn-small btn-primary">
                                <i class="icon-filter"></i>
                                Filter
                            </a>
                            <label class="inline">
                                <input type="button" name="clear" id="clear" value="clear" class="btn btn-small btn-info clear">
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Advance Filter End -->

    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Activity Log
                        <span class="widget-toolbar pull-right">
                            <?php
                            if($acl->IsAllowed($login_id,'ACTIVITY LOG', 'Activity Log', 'Export Activity Log')){
                                ?>
                                <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="bottom" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                            <?php } ?>
                        </span>
                    </div>
                    <table id='dg_activity_log' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Module Name</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Log Date</th>
                            <th>User Name</th>
                            <th>Browser</th>
                            <th>Platform</th>
                            <th>Device Type</th>
                            <th>Ip Address</th>
                        </tr>
                        </thead>
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