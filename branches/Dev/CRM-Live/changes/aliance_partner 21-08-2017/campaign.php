<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'bootstrap-daterangepicker/daterangepicker',
    'data-tables/css/dataTables.colVis',
    'css/chosen',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/js/fnStandingRedraw',
    'js/jquery.gritter.min',
    'js/bootbox.min',
    'js/jquery-form/jquery.form',
    'bootstrap-datepicker/js/bootstrap-datepicker',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'data-tables/dataTables.colVis'
);

include_once 'header.php';
$hideColumnRes = $db->FetchCellValue("user_table_state","column_hidden","user_id = '{$login_id}' and page = '$current_page'");
if($hideColumnRes) {
    $hideColumn  =  str_replace("\"","",$hideColumnRes);
} else {
    $hideColumn  = '[10,11,12]';
}
$campaignDd = $db->CreateOptions("html","campaign_master",array("campaign_id","campaign_name"),null,array("campaign_name"=>"asc"));
$campaignTypeDd = $db->CreateOptions("html","campaign_type_master",array("campaign_type_id","campaign_type_name"),null,array("campaign_type_name"=>"asc"));
$catergoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"));
$createdByDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"));
?>
    <style type="text/css">
        table#dg_campaign tfoot {
            display: table-header-group;
        }
    </style>
    <script type="text/javascript">
        function showMessage(){
            // bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being filter');
        }
        $.fn.getColumnsShown = function(dTable){
            vCols = new Array();

            $.each(dTable.fnSettings().aoColumns, function(c){
                if(dTable.fnSettings().aoColumns[c].bVisible != true){
                    vCols = vCols.concat(dTable.fnSettings().aoColumns[c].idx)
                }
            });

            return vCols;
        }
        function getCurentFileName(){
            var pagePathName= window.location.pathname;
            return pagePathName.substring(pagePathName.lastIndexOf("/") + 1);
        }
    $(document).ready(function(){
        $(".chzn-select-filter").chosen({
            allow_single_deselect: true
        });

        //filter button
        $("#filter_btn").click(function(){
            showMessage();
            dg_campaign.fnStandingRedraw();
        });

        $("#clear").click(function(){
            form = $(this).closest('form');
            form.clearForm();
            $(".chzn-select-filter").trigger("liszt:updated");
            dg_campaign.fnStandingRedraw();

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


        $('[data-rel=tooltip]').tooltip();
        $(".campaign-count").html(0);

        var breakpointDefinition = {
            pc: 1280,
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_campaign = $('#dg_campaign').dataTable({
            "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
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
            columnDefs: [
                { visible: false, targets: <?php echo $hideColumn; ?> }
            ],
            "bProcessing": true,
            "bServerSide": true,
            "bScrollCollapse" : true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/campaign.php",
            "fnServerParams": function ( aoData ) {

                var form_data = $('#frm_filter').serializeArray();
                $.each(form_data, function(i, val){
                    aoData.push(val);
                });
                aoData.push({ "name": "act", "value": "fetch" });
                server_params = aoData;
            },
            "aaSorting": [[ 6, "desc" ]],
            "aoColumns": [
                {
                    mData: "campaign_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                {"mData": "campaign_code" },
                {"mData": "campaign_name" },
                {"mData": "phone_number" },
                {"mData": "category_name" },
                {"mData": "start_date" },
                {"mData": "end_date"},
                {"mData": "vendor_name"},
                {"mData": "campaign_type_name"},
                {"mData": "amount"},
                {"mData": "description"},
                {
                    "mData": "created_at",
                    "sClass":""
                },
                {
                    "mData": "created_by",
                    "sClass":""
                },
                {
                    "mData": "is_active",
                    mRender: function (v,t,o){
                        return (v == 1) ? 'Yes' : 'No';
                    }
                },
                {
                    "mData":"prospect_count",
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        act_html = act_html + "<a href='prospect.php?token=<?php echo $token; ?>&campaign_id="+o['campaign_id']+"'  data-rel='tooltip' title='Total Prospect'>"+o['prospect_count']+" </a>&nbsp";
                        return act_html;
                    }
                },
                {
                    bSortable :false,
                    mData: null,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Edit Campaign')){
                        ?>
                        act_html = act_html + "<a href='campaign_addedit.php?id="+ o['campaign_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['campaign_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Edit Campaign')){
                        ?>
                        act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['campaign_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        act_html = act_html + "<div class='inline position-relative'>" +
                            "<button title='Campaign&nbsp;Info' data-placement='left' data-rel='tooltip' data-toggle='dropdown' class='btn btn-minier bigger btn-primary dropdown-toggle'>" +
                            "<i class='icon-info-sign icon-only bigger-120'></i>" +
                            "</button>" +
                            "<ul class='dropdown-menu dropdown-icon-only dropdown-yellow pull-right dropdown-caret dropdown-close'>" +

                            <?php
                            if($acl->IsAllowed($login_id,'Prospect', 'Prospect', 'View Prospect')){
                            ?>
                            "<li>" +
                            "<a title='Customer&nbsp;View' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='prospect.php?campaign_id="+o['campaign_id']+"&token=<?php echo $token; ?>' data-original-title='Customer&nbsp;View'>" +
                            "<span class='blue'>" +
                            "<i class='icon-book bigger-110'></i>" +
                            "</span>" +
                            "</a>" +
                            "</li>" +
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'View Campaign Withdrawal')){
                            ?>
                            "<li>" +
                            "<a title='Add&nbsp;Customer' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='prospect_addedit.php?campaign_id="+o['campaign_id']+"&token=<?php echo $token; ?>' data-original-title='Add&nbsp;Customer'>" +
                            "<span class='blue'>" +
                            "<i class='icon-hand-up bigger-110'></i>" +
                            "</span>" +
                            "</a>" +
                            "</li>" +
                            <?php } ?>

                            "</ul>" +
                            "</div>";
                        return act_html;
                    }
                }
            ],
            fnPreDrawCallback: function () {
                if (!responsiveHelper2) {
                    responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
                }
            },
            "fnRowCallback" : function(nRow, aData, iDisplayIndex){
                responsiveHelper2.createExpandIcon(nRow);
                return nRow;
            },
            fnDrawCallback : function (oSettings) {
                responsiveHelper2.respond();
                $(this).removeAttr('style');
                $('[data-rel=tooltip]').tooltip();
            }
        });

        $("tfoot input").keyup( function () {
            dg_campaign.fnFilter( this.value, $(this).attr("colPos") );
        });


        $('#edit_record').click( function (e) {
            var selected_list = $('#dg_campaign tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select a campaign to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            href = $('#edit_record').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_record').attr('href',href);
            return true;
        });

        $('#delete_record').click(function(){

            var delete_ele = $('#dg_campaign tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select campaign to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected campaign(s)? It will delete all campaign related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/campaign.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_campaign.fnDraw();
                                if(resp.success){
                                    showGritter('success',resp.title,resp.msg);
                                } else {
                                    showGritter('error',resp.title,resp.msg);
                                }
                            }
                        });
                    }
                });
            }
        });

        $("#syn_campaign").click(function(){

            $.ajax({
                url: 'fb_form_get.php?token=<?php echo $token; ?>',
                type:'post',
                dataType:'json',
                beforeSend: function (formData, jqForm, options) {
                    bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being syncronize');
                },
                success: function(resp){
                    console.log(resp);
                    bootbox.hideAll();
                    if(resp.success) {
                        dg_campaign.fnStandingRedraw();
                        showGritter('success',resp.title,resp.msg);
                    } else {
                        showGritter('error',resp.title,resp.msg);
                    }

                }
            });

        });
        $(".icon-chevron-up").click();
        var colvis = new $.fn.dataTable.ColVis( dg_campaign,{
            restore: "Restore",
            showAll: "Show all",
            showNone: "Show none",
            exclude: [ 0,1],
            "fnStateChange": function ( iColumn, bVisible ) {
                columnsShown = $('#dg_campaign').getColumnsShown(dg_campaign);
                $.ajax({
                    url: 'control/users.php?act=changetablestate',
                    type:'post',
                    dataType:'json',
                    async:false,
                    data:{ column_shown : columnsShown,module:"campaign",page:getCurentFileName()}
                });
            }

        });
        $( colvis.button() ).insertAfter('span.pull-right');
    });


    function ExportToExcel(ele){

        var query_string = decodeURIComponent($.param(server_params));
        $(ele).attr('href','export_campaigns.php?='+query_string);
        return true;
    }


    function DeleteRecord(rid){

        $('#chk_'+rid).prop('checked', true);
        $('#delete_record').click();
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
                                <label class="control-label" for="campaign_id">Campaign</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" id="filter_campaign" data-placeholder="Select Campaign" name="filter[campaign_name][]" multiple>
                                        <option></option>
                                        <?php echo $campaignDd; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="campaign_type_id">Campaign Type</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" id="filter_campaign_type" data-placeholder="Select Campaign Type" name="filter[campaign_type][]" multiple>
                                        <option></option>
                                        <?php echo $campaignTypeDd; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="catrgory_id">Loan/Product </label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select Loan/Product " name="filter[catrgory][]" multiple>
                                        <option></option>
                                        <?php echo $catergoryDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="date_range_1">Start Date</label>
                                <div class="controls">
                                    <div class='row-fluid input-append'>
                                        <input class='input-large date-picker' data-placement='top' type='text' placeholder='Start Date'
                                               name='filter[start_date]' id="date_range_1" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[start_date]' class='help-inline'></span>
                                    </div>

                                </div>
                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="date_range_1">End Date</label>
                                <div class="controls">
                                    <div class='row-fluid input-append'>
                                        <input class='input-large date-picker' data-placement='top' type='text' placeholder='End Date'
                                               name='filter[end_date]' id="date_range_1" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[end_date]' class='help-inline'></span>
                                    </div>

                                </div>
                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="user">Created By</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" name="filter[created_by][]" id="filter_created_by" data-placeholder="User" multiple>
                                        <option></option>
                                        <?php echo $createdByDd; ?>
                                    </select>
                                </div>

                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="date_range_1">Created Date</label>
                                <div class="controls">
                                    <div class='row-fluid input-append'>
                                        <input class='input-large date-picker' data-placement='top' type='text' placeholder='Created Date'
                                               name='filter[created_on]' id="date_range_1" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[created_on]' class='help-inline'></span>
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
                        Campaign List
					<span class="widget-toolbar pull-right">
                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Add Campaign')){
                            ?>
                            <a id='add_record' href="campaign_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Edit Campaign')){
                            ?>
                            <a id='edit_record' href="campaign_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Delete Campaign')){
                            ?>
                            <a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>

<!--                        --><?php
//                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'dExport Campaign Details')){
//                            ?>
                        <?php
                        if($acl->IsAllowed($login_id,'CAMPAIGN', 'Campaign', 'Delete Campaign')){
                            ?>

                            <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="top" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                        <?php } ?>


                        <a id='syn_campaign' href="javascript:void(0)" data-placement="top" data-rel="tooltip" data-original-title="Synchronize Campaign" class="white"><i class="icon-refresh icon-large white"></i>sync</a>

					</span>
                    </div>
                    <table id='dg_campaign' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox"/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Number</th>
                            <th>Loan/Product Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Created On</th>
                            <th>Created By</th>
                            <th>Is Active</th>
                            <th>Prospect Count</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th class="center">
                            </th>
                            <th>
                                <input type="text"  placeholder="code" name="filter_code" class="span7" colPos="5">
                            </th>

                            <th>
                                <input type="text"  placeholder="name" name="filter_name" class="span7" colPos="1">
                            </th>
                            <th>
                                <input type="text"  placeholder="number" name="filter_number" class="span7" colPos="2">
                            </th>
                            <th>
                                <input type="text"  placeholder="load" name="filter_loan" class="span7" colPos="6">
                            </th>
                            <th>
                                <input type="text"  placeholder="start date" name="filter_start" class="span7" colPos="3">
                            </th>
                            <th>
                                <input type="text"  placeholder="end date" name="filter_end" class="span7" colPos="4">
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
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