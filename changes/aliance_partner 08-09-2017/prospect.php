<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'bootstrap-daterangepicker/daterangepicker',
    'css/chosen',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'data-tables/js/fnStandingRedraw',
    'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'bootstrap-datepicker/js/bootstrap-datepicker',
    'bootstrap-daterangepicker/date',
    'bootstrap-daterangepicker/daterangepicker',
);

include_once 'header.php';
//echo "HL-3Lac /Job-10Year / 23k Salary /form-16 & All doc com..\nAdd-satellite ahmedabad  \nTime-12 to 5";
$campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';
$tellecallerDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = ".UT_TC."");
$campaignDd = $db->CreateOptions("html","campaign_master",array("campaign_id","campaign_name"),null,array("campaign_name"=>"asc"));
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),null,array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),null,array("city_name"=>"asc"));
$vendorDd = $db->CreateOptions("html","vendor_master",array("vendor_id","vendor_name"),null,array("vendor_name"=>"asc"));
$createdByDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"));

?>
    <style type="text/css">
        table#dg_prospect tfoot {
            display: table-header-group;
        }
    </style>
    <script type="text/javascript">
        function showMessage(){
            // bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being filter');
        }
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

    $(document).on('click','.prospect-info', function(){
        var tr = $(this).closest('tr').get(0);
        data = dg_prospect.fnGetData( tr );
        var prospect_id = data.prospect_id;

        if(prospect_id){
            $.ajax({
                url: 'control/prospect.php?act=prospectinfo',
                type:'post',
                dataType:'html',
                beforeSend: function(){
                    $("#modal_prospect_detail").modal('show');
                    $("#prospect_detail_div").html(wait);
                },
                data:{prospect_id:prospect_id },
                success: function(resp){
                    $("#prospect_detail_div").html(resp);
                    $("#modal_prospect_detail").modal('show');
                }
            });
        }
    });

    $(document).ready(function(){

        $(".chzn-select-filter").chosen({
            allow_single_deselect: true
        });

        $("#myTab li").click(function(){
            $(this).addClass("active").siblings().removeClass("active");
            dg_prospect.fnStandingRedraw();
        })



        $('.modal').on('shown.bs.modal', function () {
            $('.chzn-select-model', this).chosen({
                allow_single_deselect:true
            });
        });

        $('.chzn-select', this).chosen({
            allow_single_deselect:true
        });

        //filter button
        $("#filter_btn").click(function(){
            showMessage();
            dg_prospect.fnStandingRedraw();
        });

        $("#clear").click(function(){
            form = $(this).closest('form');
            form.clearForm();
            $(".chzn-select-filter").trigger("liszt:updated");
            dg_prospect.fnStandingRedraw();

        });



        // bulk prospect upload
        $('#csc_execl').ace_file_input({
            no_file:'No File ...',
            btn_choose:'Choose',
            btn_change:'Change',
            droppable:false,
            onchange:null,
            thumbnail:false,
            whitelist:'xlsx|xls'
        }).on('change', function(){
            data = console.log($(this).data('ace_input_files'));
        });
        $('#form_upload_file').ajaxForm({
            url: 'control/upload_prospect.php',
            type:'post',
            dataType: 'json',
            beforeSubmit: function (formData, jqForm, options) {
                $('#form_upload_file button').hide();
                $('#loader').show();
                bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being saved');
            },
            success: function(response) {
                bootbox.hideAll();
                g_class = '';
                if(response.status === true){

                    $('.chzn-select').trigger('chosen:updated');

                    $('.chzn-select').trigger('liszt:updated');
                    g_class = 'success';
                    g_title = 'Successful';
                }else{
                    g_class = 'error';
                    g_title = 'Failed';
                }
                showGritter(g_class,g_title, response.msg);
                $('#csc_execl').ace_file_input('reset_input');
                dg_prospect.fnStandingRedraw();
            },
            complete: function(){
                $('#form_upload_file button').show();
                $('#loader').hide();
            }
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
        // bulk prospect upload end


        $('[data-rel=tooltip]').tooltip();
        $(".prospect-count").html(0);

        var breakpointDefinition = {
            pc: 1280,
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_prospect = $('#dg_prospect').dataTable({
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
            "bProcessing": true,
            "bServerSide": true,
            "bScrollCollapse" : true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/prospect.php",
            "fnServerParams": function ( aoData ) {
                var form_data = $('#frm_filter').serializeArray();
                $.each(form_data, function(i, val){
                    aoData.push(val);
                });
                aoData.push({ "name": "act", "value": "fetch" },{ "name": "campaign_id", "value": "<?php echo $campaignId; ?>" },
                    { "name": "display_type", "value": ""+$("#myTab li.active").attr('id')+"" });
                server_params = aoData;
            },
            "aaSorting": [[ 11, "desc" ]],
            "aoColumns": [
                {
                    mData: "prospect_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                {"mData": "prospect_id" },
                {
                    "mData": "prospect_name",
                    mRender: function (v,t,o){
                        return (v != null) ? "<a href='javascript:void(0)' class='prospect-info' title='View Details'>"+v+"</a>" : "";
                    }
                },
                {"mData": "campaign_name" },
                {"mData": "state_name" },
                {"mData": "city_name" },
                {"mData": "disposition_name" },
                {"mData": "follow_up_date_time" },
                {
                    "mData": "telecaller",
                    "sClass":"hide"
                },
                {"mData": "last_call" },
                {
                    "mData": "total_transaction",
                    mRender: function (v,t,o){
                        return (v != null) ? "<a target='_blank' href='activity.php?prospect_id="+o['prospect_id']+"' title='View Activity'>"+v+"</a>" : "";
                    }
                },
                {"mData": "created_by"},
                {"mData": "created_at"},
                {
                    "mData": "is_active",
                    mRender: function (v,t,o){
                        return (v == 1) ? 'Yes' : 'No';
                    }
                },
                {
                    bSortable :false,
                    mData: null,
                    mRender: function(v,t,o){
                        var act_html = '';

                        if(o['is_close'] != 1) {
                            <?php
                            if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Edit Prospect')){
                            ?>
                           // act_html = act_html + "<a href='activity_addedit.php?prospect_id="+ o['prospect_id'] +"&type=call&token=<?php echo $token; ?>' class='btn btn-minier btn-success' data-placement='bottom' data-rel='tooltip' data-original-title='Call "+o['prospect_name']+"'><i class='icon-phone bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Edit Prospect')){
                            ?>
                            act_html = act_html + "<a href='prospect_addedit.php?id="+ o['prospect_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['prospect_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Delete Prospect')){
                            ?>
                            act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['prospect_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                            <?php } ?>
                        }



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
            dg_prospect.fnFilter( this.value, $(this).attr("colPos") );
        });


        $('#edit_record').click( function (e) {
            var selected_list = $('#dg_prospect tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select a prospect to edit.');
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

            var delete_ele = $('#dg_prospect tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select prospect to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected prospect(s)? It will delete all prospect related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/prospect.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_prospect.fnDraw();
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


        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };
            $("#frm_change_tc").validate({

                rules:{
                    'tc_user_id':{required:true },
                    'assign_prospect_id':{required:true }
                },
                messages:{
                    'partner_id':{required:'Please select tellecaller'},
                    assign_prospect_id:{required:'Please select prospect'},

                },
                errorElement : "span",
                errorClass : "help-inline",
                focusInvalid : false,
                ignore : "",
                invalidHandler : function(e, t) {},
                highlight : function(e) {
                    $(e).closest(".control-group").removeClass("success").addClass("error");
                },
                unhighlight : function(t) {
                    $(t).closest(".control-group").removeClass("error");
                    setTimeout(function() {
                        e(t);
                    }, 3e3);
                },
                success : function(e) {
                    e.closest(".control-group").removeClass("error").addClass("success");
                },
                submitHandler : function(e) {

                    $(e).ajaxSubmit({
                        url: 'control/prospect.php?act=changetc',/*i have changed here*/
                        type:'post',
                        beforeSubmit: function (formData, jqForm, options) {
                            $(e).find('button').hide();
                        },
                        complete: function () {
                            $(e).find('button').show();
                        },
                        dataType: 'json',
                        clearForm: false,
                        success: function (resObj, statusText) {
                            $(e).find('button').show();
                            if(resObj.success){
                                $('#modal_change').modal('hide');
                                dg_prospect.fnStandingRedraw();
                                showGritter('success',resObj.title,resObj.msg);
                            }else{
                                showGritter('error', resObj.title, resObj.msg);
                            }
                        }
                    });
                }
            });
        }



        $('#change_tc').click(function(){
            var selected_list = $('#dg_prospect tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();
            var close = 0;
            if(0 == selected_length){
                showGritter('info','Alert!','Please select record to change');
                return false;
            } else{
                prospect_id = [];
                $.each(selected_list, function(i, ele){
                    var rowIndex = dg_prospect.fnGetPosition($(this).closest('tr')[0]);
                    var rowData = dg_prospect.fnGetData(rowIndex);
//                    if(rowData.is_close == 1) {
//                        showGritter('info','Alert!','You can\'t assign close lead');
//                        close = 1;
//                        return false;
//                    }
                    prospect_id.push($(ele).val());
                });
                $('form#frm_change_tc').find('div.control-group').removeClass("success error");
                $('#assign_prospect_id').val(prospect_id);
                $('#modal_change').modal('show');

            }
        });

        $(".icon-chevron-up").click();
    });

    function ExportToExcel(ele){

        var query_string = decodeURIComponent($.param(server_params));
        $(ele).attr('href','export_prospects.php?='+query_string);
        return true;
    }


    function DeleteRecord(rid){

        $('#chk_'+rid).prop('checked', true);
        $('#delete_record').click();
    }

    </script>
    <div class="alert alert-danger hide" style="font-size: 16px">

        Column filter working when either <b>ENTER</b> has been pressed or there are <b>AT LEAST 3</b> characters in the search
        <br>
    </div>

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
                                <label class="control-label" for="state_id">State </label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select State" name="filter[state][]" multiple>
                                        <option></option>
                                        <?php echo $stateDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="city_id">City</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select City" multiple name="filter[city][]">
                                        <option></option>
                                        <?php echo $cityDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="vendor">Vendor Name</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" name="filter[vendor][]" id="filter_vendor" data-placeholder="Vendor Name" multiple>
                                        <option></option>
                                        <?php echo $vendorDd; ?>
                                    </select>
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

<?php
if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Bulk Upload Prospect')){
    ?>
    <!-- Bulk Upload Start -->
    <div class="row-fluid">
        <div class="span12">
            <div class="span12 widget-container-span ui-sortable">
                <div class="widget-box">
                    <div class="widget-header">
                        <h4>Bulk Prospect Upload</h4>

                        <div class="widget-toolbar">
                            <a data-action="collapse" href="#" data-placement='bottom' data-rel='tooltip' data-original-title='Show/Hide Bulk Upload'>
                                <i class="icon-chevron-up"></i>
                            </a>

                            <a data-action="close" href="#">
                                <i class="icon-remove"></i>
                            </a>
                        </div>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main">
                            <div class="well well-large">
                                The first line in downloaded excel file should remain as it is. Please do not change the order of columns.
                                The correct column order is <span class="text-info">(Product, First Name, Last Name, Amount, Email, Mobile Numbers, Address, State, City, Pincode,
                                 Handler) </span>& you must follow this.
                                <a class="btn btn-mini btn-primary" href="upload_format/bulk_prospect.xlsx">
                                    <i class="icon-download"></i>Download Sample File
                                </a>
                            </div>
                            <form class="form-horizontal" id="form_upload_file" enctype="multipart/form-data">

                                <div class="control-group">
                                    <label class="control-label" for="country_state_city">Select Campaign</label>
                                    <div class="controls">
                                        <div class="span4">
                                            <select id="campaign_id" name="campaign_id" data-placeholder="select campaign" class="chzn-select">
                                                <option></option>
                                                <?php echo $campaignDd; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="country_state_city">Upload File:</label>
                                    <div class="controls">
                                        <div class="span4">
                                            <input multiple="" type="file" name="csc_execl" id="csc_execl" required='true'/>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="span2">
                                            <button class="btn btn-mini btn-primary" type="submit">
                                                <i class="icon-arrow-up"></i>Upload File
                                            </button>
                                        </div>
                                        <div id='loader'  class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please wait...</div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
    <!-- Bulk Upload End -->

    <div class="tabbable">
        <ul class="nav nav-tabs hide" id="myTab">
            <li class="" id="today">
                <a href="javascript:void(0)">
                    Today
                    <span class="badge badge-important"></span>
                </a>
            </li>
            <li id="all" class="active">
                <a href="javascript:void(0)">
                    All
                    <span class="badge badge-important"></span>
                </a>
            </li>

        </ul>
        <div class="tab-content">
            <div id="offices" class="tab-pane in active">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="row-fluid">
                            <div class='span12'>
                                <div class="table-header">
                                 Prospect List
                                <span class="widget-toolbar pull-right">
                                    <?php
                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Dedlete Prospect')){
                                        ?>
                                    <a id='change_tc'  data-placement="top" data-rel="tooltip" data-original-title="Change selected prospect to other telecaller" class="pointer"><i class="icon-user icon-large white">Cahange Telecaller</i></a>
                                    <?php  } ?>
                                    <?php
                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Add Prospect')){
                                        ?>
                                        <a id='add_record' href="prospect_addedit.php?token=<?php echo $token; ?>" data-placement="bottom" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Edit Prospect')){
                                        ?>
                                        <a id='edit_record' href="prospect_addedit.php?token=<?php echo $token; ?>" data-placement="bottom" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Delete Prospect')){
                                        ?>
                                        <a id='delete_record' href="javascript:void(0);" data-placement="bottom" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                                    <?php } ?>

<!--                                    --><?php
//                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'dExport Prospect Details')){
//                                        ?>
                                    <?php
                                    if($acl->IsAllowed($login_id,'PROSPECT', 'Prospect', 'Delete Prospect')){
                                        ?>
                                        <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="bottom" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                                    <?php } ?>

                                </span>
                                </div>
                                <table id='dg_prospect' class="table table-condensed table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="center" width="5%">
                                            <label>
                                                <input type="checkbox"/>
                                                <span class="lbl"></span>
                                            </label>
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Campaign</th>
                                        <th>State</th>
                                        <th>City</th>
                                        <th>Disposition</th>
                                        <th>Follow Up</th>
                                        <th>Assign User</th>
                                        <th>Last Call</th>
                                        <th>Activity</th>
                                        <th>Created by</th>
                                        <th>Created On</th>
                                        <th>Is Active</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th class="center">
                                        </th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="name" name="filter_name" class="span7" colPos="1">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="Campaign" name="filter_campaign" class="span7" colPos="2">
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="user" name="filter_telecaler" class="span7" colPos="3">
                                        </th>

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
            </div>
        </div>
    </div>

    <!-- Change telecaler Model Box Start-->
    <div id="modal_change" class="modal hide" tabindex="-1">
        <form id='frm_change_tc' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Change Prospect Telecaller</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="tc_user_id">Telecaller</label>
                            <div class="controls">
                                <select id="tc_user_id" name="tc_user_id" class="chzn-select-model" data-placeholder="select telecaller">
                                    <option></option>
                                    <?php
                                    echo $tellecallerDd;
                                    ?>
                                </select>
                                <span for="tc_user_id" class="help-inline"></span>
                                <input type="hidden" id='assign_prospect_id' name='assign_prospect_id' value=""/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-small btn-primary">
                    <i class="icon-ok"></i>
                    Save
                </button>
                <button class="btn btn-small" data-dismiss="modal">
                    <i class="icon-remove"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
    <!-- Change telecaler Model Box End-->




    <!-- Prospect Details Model Box Start -->
    <div id="modal_prospect_detail" class="modal hide" tabindex="-1">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Prospect Details</h4>
        </div>
        <div class="modal-body overflow-scrollable">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group" id="prospect_detail_div">

                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-small" data-dismiss="modal">
                <i class="icon-remove"></i>
                Cancel
            </button>
        </div>
    </div>
    <!-- Prospect Details Model Box End -->
<?php
include_once 'footer.php';
?>