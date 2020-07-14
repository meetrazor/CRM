<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'data-tables/css/dataTables.colVis',
    'css/chosen',
    'bootstrap-daterangepicker/daterangepicker',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'data-tables/js/fnStandingRedraw',
    'data-tables/dataTables.colVis',
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
$hideColumnRes = $db->FetchCellValue("user_table_state","column_hidden","user_id = '{$login_id}' and page = '$current_page'");
if($hideColumnRes) {
    $hideColumn  =  str_replace("\"","",$hideColumnRes);
} else {
    $hideColumn  = '[10,11,12,13,14]';
}
$partnerId = (isset($_GET['partner_id']) && !empty($_GET['partner_id'])) ? intval($db->FilterParameters($_GET['partner_id'])) : '';
$customerId = (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) ? intval($db->FilterParameters($_GET['customer_id'])) : '';
$customerDd = $db->CreateOptions("html","customer_master",array("customer_id","customer_name"),$customerId,array("customer_name"=>"asc"),"is_active = 1");
$partnerDd = $db->CreateOptions("html","partner_master",array("partner_id","concat(first_name,' ',last_name) as partner_name"),$partnerId,array("concat(first_name,' ',last_name)"=>"asc"),"is_active = 1");
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),null,array("status_name"=>"asc"),"is_active = 1 and status_type = 'Lead'");
$sourceDd = $db->CreateOptions("html","lead_sources",array("source_id","source_name"),null,array("source_name"=>"asc"),"is_active = 1");
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"));
$subLocalityDd = $db->CreateOptions("html","sub_locality",array("sub_locality_id","sub_locality_name"),null,array("sub_locality_name"=>"asc"));
$leadId = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';
$leadDd = $db->CreateOptions("html","lead_master",array("lead_id","concat(lead_name,' ',lead_code)"),$leadId,array("lead_name"=>"asc"),null,array(0,10));
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),null,array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),null,array("city_name"=>"asc"));
$partnerName = "";
if($partnerId != ''){
    $partnerName = $db->FetchCellValue("partner_master","concat(first_name,' ',last_name)","partner_id = '{$partnerId}'");
}
if($userType != UT_ADMIN){
    $res = Utility::getReportingUserId(array("$login_id"),array(),$userType);
    $childIds = implode(",",Utility::getUniqueArray($res));
    $useCondition = "user_id in ($childIds) and  user_type in (".$userType.")";
} else {
    $useCondition = "user_type in (".UT_BD.")";
}
$bdDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"),$useCondition);
?>
<style type="text/css">
    table#dg_lead tfoot {
        display: table-header-group;
    }
    .edit_area select {
        width: auto;
    }
    #frm_filter label.control-label{
        font-size: 10px;
        font-weight: bold;

    }
    td.details-control {
        background: url('assets/data-tables/images/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('assets/data-tables/images/details_close.png') no-repeat center center;
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


$(document).on('click','#dg_lead tbody tr td:not(.not-click)', function(){
    var tr = $(this).parent('tr').get(0);
    data = dg_lead.fnGetData( tr );

    lead_id = data.lead_id;

    if(lead_id){
        $.ajax({
            url: 'control/lead.php?act=leadinfo',
            type:'post',
            dataType:'html',
            beforeSend: function(){
                $("#modal_lead_detail").modal('show');
                $("#lead_detail_div").html(wait);
            },
            data:{lead_id:lead_id },
            success: function(resp){
                $("#lead_detail_div").html(resp);
                $("#modal_lead_detail").modal('show');
            }
        });
    }
});

$.fn.getColumnsShown = function(dTable){
    vCols = new Array();

    $.each(dTable.fnSettings().aoColumns, function(c){
        if(dTable.fnSettings().aoColumns[c].bVisible != true){
            vCols = vCols.concat(dTable.fnSettings().aoColumns[c].idx)
        }
    });

    return vCols;
}

$(function() {

    function getCurentFileName(){
        var pagePathName= window.location.pathname;
        return pagePathName.substring(pagePathName.lastIndexOf("/") + 1);
    }



    $('#modal_assign,#modal_change').on('shown.bs.modal', function () {
        $('.chzn-select', this).chosen();
    });

    $(".lead-count").html(0);

    $(".chzn-select-filter").chosen({
        allow_single_deselect: true
    });

    // bulk lead upload
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
        url: 'control/upload_leads.php',
        type:'post',
        dataType: 'json',
        beforeSubmit: function (formData, jqForm, options) {
            $('#form_upload_file button').hide();
            $('#loader').show();
            bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, leads is being saved');
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
            dg_lead.fnStandingRedraw();
        },
        complete: function(){
            $('#form_upload_file button').show();
            $('#loader').hide();
        }
    });

    // bulk lead upload end

    $("#filter_partner").ajaxChosen({
        minTermLength:3,
        type: 'post',
        url: 'control/partner.php?act=getparnter',
        dataType: 'json'
    }, function (data) {
        var results = [];

        $.each(data, function (i, val) {
            results.push({ value: val.value, text: val.text });
        });
        return results;
    });

    $("#filter_customer").ajaxChosen({
        minTermLength:3,
        type: 'post',
        url: 'control/customer.php?act=getcustomer',
        dataType: 'json'
    }, function (data) {
        var results = [];

        $.each(data, function (i, val) {
            results.push({ value: val.value, text: val.text });
        });
        return results;
    });

    $("#filter_lead_name").ajaxChosen({
        minTermLength:3,
        type: 'post',
        url: 'control/lead.php?act=getleads',
        dataType: 'json'
    }, function (data) {
        var results = [];

        $.each(data, function (i, val) {
            results.push({ value: val.value, text: val.text });
        });
        return results;
    });

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


    $("#clear").click(function(){
        form = $(this).closest('form');
        form.clearForm();
        $(".chzn-select-filter").trigger("liszt:updated");
        dg_lead.fnStandingRedraw();

    });

    $(".icon-chevron-up").click();

    $("#filter_btn").click(function(){
        showMessage();
        dg_lead.fnStandingRedraw();
    });



    $('[data-rel=tooltip]').tooltip();
    var breakpointDefinition = {
//        tablet: 800,
//        phone : 480
    };
    var responsiveHelper2 = undefined;
    dg_lead = $('#dg_lead').dataTable({
        "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
        "bPaginate":true,
        columnDefs: [
            { visible: false, targets: <?php echo $hideColumn; ?> }
        ],
        oLanguage : {
            sSearch : "Search _INPUT_",
            sLengthMenu : " _MENU_ ",
            sInfo : "_START_ to _END_ of _TOTAL_",
            sInfoEmpty : "0 - 0 of 0",
            oPaginate : {
                sFirst : '<i class="icon-double-angle-left"></i>',
                sLast : '<i class="icon-double-angle-right"></i>',
                sPrevious: '<i class="icon-angle-left"></i>',
                sNext: '<i class="icon-angle-right"></i>'
            }
        },
        "bProcessing": true,
        "bServerSide": true,
        "bScrollCollapse" : true,
        "lengthMenu": [[10,25,50,100], [10,25,50,100]],
        "sAjaxSource": "control/lead.php",//change here
        "fnServerParams": function ( aoData ) { //send other data to server side
            var form_data = $('#frm_filter').serializeArray();

            $.each(form_data, function(i, val){
                aoData.push(val);
            });
            aoData.push({ "name": "act", "value": "fetch" },{ "name": "lead_id", "value": "<?php echo $leadId; ?>" });
            server_params = aoData;
        },
        "aaSorting": [[ 14, "desc" ]],
        "aoColumns": [
            {
                mData: "lead_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="lead_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center not-click'
            },
            {
                "class":          'details-control not-click',
                "orderable":      false,
                "data":           null,
                "defaultContent": ''
            },
            {
                "mData": "lead_code",
                "bSortable":true,

            },
            {
                "mData": "lead_name",
                "bSortable":true,
                "sClass":""

            },
            {
                "mData": "remarks",
                "bSortable":true,
                "sClass":""
            },
            {
                "mData": "activity_type",
                "bSortable":true,
                "sClass":""
            },
            {
                "mData": "status_name",
                "bSortable":true,
                "sClass":""
            },
            {
                "mData": "email",
                "bSortable":true,
                "sWidth":"40%",
                "sClass":""

            },
            {
                "mData": "mobile_no",
                "bSortable":true,
                sClass: ''
            },
            {
                "mData": "category_name",
                "bSortable":true,
            },
            {
                "mData": "cw_status_name",
                "bSortable":true,
                "sClass":"edit_area"
            },
            {
                "mData": "partner_name",
                <?php
                if($userType == UT_BD){ ?>
                "sClass": "hide"
                <?php } ?>
            },
            {
                "mData": "bd_name",
                "bSortable":false,
                <?php
                if($userType == UT_IA){ ?>
                "sClass": "hide"
                <?php } ?>
            },
            {
                "mData": "kc_name",
                "bSortable":false,
                <?php
                if($userType == UT_IA){ ?>
                "sClass": "hide"
                <?php } ?>
            },
            {
                "mData": "customer_name",
                "bSortable":true
            },
            {
                "mData": "state_name",
                "bSortable":true,
                "sClass":""
            },
            {
                "mData": "city_name",
                "bSortable":true,
                "sClass":""
            },
            {
                "mData": "created_at",
                "bSortable":true
            },
            {
                bSortable :false,
                mData: null,
                "sClass":"not-click",
                mRender: function(v,t,o){
                    var act_html = '';
                    var department = "";
                    act_html = act_html + "<a href='javascript:void(0);' class='btn  btn-minier btn-primary comment' data-placement='bottom' data-rel='tooltip' data-original-title='Add comment'><i class='icon-comment bigger-120'></i></a>&nbsp";

                    <?php if (count($activityType) > 0) { ?>
                    department = department + '<div class="btn-group" style=""><button class="btn btn-inverse btn-mini dropdown-toggle" data-toggle="dropdown" data-placement="bottom" data-rel="tooltip" data-original-title="Add Activity">A<span class="caret"></span></button>';
                    department = department + '<ul class="dropdown-menu dropdown-info pull-right" style="min-height: 100px">';
                    department = department + '<li><a href="bd_activity.php?status=all&lead_id='+o['lead_id']+'&token=<?php echo $token; ?>">View Activity</a></li>';

                    <?php
                     foreach($activityType as $activityD) {
                    ?>
                    department = department + '<li><a href="bd_activity_addedit.php?type_id=<?php echo $activityD;?>&lead_id='+o['lead_id']+'&token=<?php echo $token; ?>"><?php echo $activityD; ?></a></li>';
                    <?php } ?>
                    department = department + '</ul></div>';
                    <?php } ?>

                    <?php
                    /*
                    if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Edit Lead')))   {
                    ?>
                    if(o['is_close'] != 1) {
                        act_html = act_html + "<a href='lead_addedit.php?id="+ o['lead_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['lead_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                    } else {
                        if(o['customer_check'] != 1) {
                            act_html = act_html + "<a href='javascript:void(0);' class='btn  btn-minier btn-primary lead_check' id='lead_check_"+o['lead_id']+"' data-placement='bottom' data-rel='tooltip' data-original-title='Complete Lead'><i class='icon-check bigger-120'></i></a>&nbsp";
                        }
                    }
                    <?php } */ ?>

                    <?php
                    /*
                    if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Delete Lead')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['lead_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                    <?php } */ ?>

                    act_html = act_html + department;
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


    var colvis = new $.fn.dataTable.ColVis( dg_lead,{
        restore: "Restore",
        showAll: "Show all",
        showNone: "Show none",
        exclude: [ 0,1],
        "fnStateChange": function ( iColumn, bVisible ) {
            columnsShown = $('#dg_lead').getColumnsShown(dg_lead);
            $.ajax({
                url: 'control/users.php?act=changetablestate',
                type:'post',
                dataType:'json',
                async:false,
                data:{ column_shown : columnsShown,module:"lead",page:getCurentFileName()}
            });
        }

    });
    $( colvis.button() ).insertAfter('span.pull-right');

    //
    var Otable = $("#dg_lead").DataTable();
    // show more details or rows
    $('#dg_lead tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = Otable.row( tr );
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            var leadId = row.data().lead_id;
            console.log(leadId);
            $.ajax({
                url: 'control/lead_addedit.php?act=commentview',
                type:'post',
                dataType:'html',
                beforeSend: function(){
                    row.child( wait ).show();
                    tr.addClass('shown');
                },
                data:{ lead_id : leadId },
                success: function(resp){
                    row.child( resp ).show();
                    tr.addClass('shown');
                }
            });
        }
    } );

    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        $("#frm_assign").validate({

            rules:{
                'partner_id':{required:true },
                'assign_lead_id':{required:true }
            },
            messages:{
                'partner_id':{required:'Please select partner'}

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
                    url: 'control/lead.php?act=assign',/*i have changed here*/
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $('#modal_assign').modal('hide');
                            dg_lead.fnStandingRedraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });

        // Bd change form
        $("#frm_change").validate({

            rules:{
                'bd_user_id':{required:true },
                'bd_lead_id':{required:true }
            },
            messages:{
                'bd_user_id':{required:'Please select Bd'}

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
                    url: 'control/lead.php?act=changebd',/*i have changed here*/
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $('#modal_change').modal('hide');
                            dg_lead.fnStandingRedraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });

         // KC change form
        $("#frm_change_kc").validate({

            rules:{
                'kc_user_id':{required:true },
                'kc_lead_id':{required:true }
            },
            messages:{
                'kc_user_id':{required:'Please select KC'}

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
                    url: 'control/lead.php?act=changekc',/*i have changed here*/
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $('#modal_change_kc').modal('hide');
                            dg_lead.fnStandingRedraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });

        $("#frm_comment").validate({

            rules:{
                lead_comment:{required:true }
            },
            messages:{
                lead_comment:{required:'Please enter comment'}
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
                    url: 'control/lead_addedit.php?act=addcomment',
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $(e).clearForm();
                            $('#modal_comment').modal('hide');
                            dg_lead.fnStandingRedraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }


    $("tfoot input").on("keyup", function(e) {
        // If the length is 3 or more characters, or the user pressed ENTER, search

        if ($(this).val().length > 2 || e.keyCode == 13) {
            // Call the API search function
            dg_lead.fnFilter( this.value, $(this).attr("colPos") );
        }
        if($(this).val() == "") {
            dg_lead.fnFilter( this.value, $(this).attr("colPos") );
        }

    });

    $('#edit_lead').click( function (e) {
        var selected_list = $('#dg_lead tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();
        if(0 == selected_length){

            showGritter('info','Alert!','Please select a record to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        href = $('#edit_lead').attr('href');
        href += '&id=' + selected_list.val();
        $('#edit_lead').attr('href',href);
        return true;
    });

    $('#assign_to').click(function(){
        var account_ele = $('#dg_lead tbody input[type=checkbox]:checked');
        var selected_length = account_ele.size();
        var close = 0;
        if(0 == selected_length){
            showGritter('info','Alert!','Please select lead to assign');
            return false;
        } else{
            var lead_id = [];
            $.each(account_ele, function(i, ele){
                var rowIndex = dg_lead.fnGetPosition($(this).closest('tr')[0]);
                var rowData = dg_lead.fnGetData(rowIndex);
                if(rowData.is_close == 1) {
                    showGritter('info','Alert!','You can\'t assign close lead');
                    close = 1;
                    return false;
                }
                lead_id.push($(ele).val());
            });
            if(close == 0) {
                $('form#frm_assign').find('div.control-group').removeClass("success error");
                $('#assign_lead_id').val(lead_id);
                $('#modal_assign').modal('show');
            }

        }
    });

    $('#change_bd').click(function(){
        var account_ele = $('#dg_lead tbody input[type=checkbox]:checked');
        var selected_length = account_ele.size();
        var close = 0;
        if(0 == selected_length){
            showGritter('info','Alert!','Please select lead to assign');
            return false;
        } else{
            var lead_id = [];
            $.each(account_ele, function(i, ele){
                var rowIndex = dg_lead.fnGetPosition($(this).closest('tr')[0]);
                var rowData = dg_lead.fnGetData(rowIndex);
//                if(rowData.is_close == 1) {
//                    showGritter('info','Alert!','You can\'t assign close lead');
//                    close = 1;
//                    return false;
//                }
                lead_id.push($(ele).val());
            });
            if(0 == 0) {
                $('form#frm_change').find('div.control-group').removeClass("success error");
                $('#bd_lead_id').val(lead_id);
                $('#modal_change').modal('show');
            }

        }
    });

    $('#change_kc').click(function(){
        var account_ele = $('#dg_lead tbody input[type=checkbox]:checked');
        var selected_length = account_ele.size();
        var close = 0;
        if(0 == selected_length){
            showGritter('info','Alert!','Please select lead to assign');
            return false;
        } else{
            var lead_id = [];
            $.each(account_ele, function(i, ele){
                var rowIndex = dg_lead.fnGetPosition($(this).closest('tr')[0]);
                var rowData = dg_lead.fnGetData(rowIndex);
//                if(rowData.is_close == 1) {
//                    showGritter('info','Alert!','You can\'t assign close lead');
//                    close = 1;
//                    return false;
//                }
                lead_id.push($(ele).val());
            });
            if(0 == 0) {
                $('form#frm_change_kc').find('div.control-group').removeClass("success error");
                $('#kc_lead_id').val(lead_id);
                $('#modal_change_kc').modal('show');
            }

        }
    });


    $('#delete_lead').click(function(){

        var delete_ele = $('#dg_lead tbody input[type=checkbox]:checked');
        var selected_length = delete_ele.size();

        if(0 == selected_length){
            showGritter('info','Alert!','Please select record to delete.');
            return false;
        }else{
            bootbox.confirm("Are you sure to delete selected record(s)? It will delete all lead related data and can not be reverted", function(result) {
                if(result) {

                    var delete_id = [];
                    $.each(delete_ele, function(i, ele){
                        delete_id.push($(ele).val());
                    });

                    $.ajax({
                        url: 'control/lead.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_lead.fnStandingRedraw();
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

    $("#filter_lead").ajaxChosen({
        minTermLength:3,
        allow_single_deselect:true,
        type: 'post',
        url: 'control/lead.php?act=getleads',
        dataType: 'json'
    }, function (data) {
        var results = [];
        if(data.length > 0){
            $.each(data, function (i, val) {
                results.push({ value: val.value, text: val.text });
            });
        }
        return results;
    });

});

$(document).on("click",".comment",function(){
    var rowData = dg_lead.fnGetData($(this).parents('tr'));
    var lead_id = rowData.lead_id;
    $('form#frm_comment').find('div.control-group').removeClass("success error");
    $('form#frm_comment').find('div.control-group span').text('');
    $("#lead_id").val(lead_id);
    $('#modal_comment').modal('show');
});

$(document).on("click",".lead_check",function(){
    var lead_id = $(this).attr("id").split("_").pop();
    bootbox.confirm("Are you sure to complete selected lead(s)?", function(result) {
        if(result) {

            $.ajax({
                url: 'control/lead.php?act=completelead',
                type:'post',
                dataType:'json',
                data:{ lead_id : lead_id, },
                success: function(resp){
                    dg_lead.fnStandingRedraw();
                    if(resp.success){
                        showGritter('success',resp.title,resp.msg);
                    } else {
                        showGritter('error',resp.title,resp.msg);
                    }
                }
            });
        }
    });
})

function DeleteRecord(rid){

    $('#chk_'+rid).prop('checked', true);
    $('#delete_lead').click();
}


function ExportToExcel(ele){

    var query_string = decodeURIComponent($.param(server_params));
    $(ele).attr('href','export_lead_details.php?='+query_string);
    return true;
}
</script>

<!-- Bulk Upload Start -->
<div class="row-fluid hide">
    <div class="span12">
        <div class="span12 widget-container-span ui-sortable">
            <div class="widget-box">
                <div class="widget-header">
                    <h4>Bulk Lead Upload</h4>

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
                            The correct column order is <span class="text-info">(Lead Name, Email, Date of Birth, Address, State, City, Pincode,
                                 Mobile, Landline, Status, Source, Remarks) </span>& you must follow this.
                            <a class="btn btn-mini btn-primary" href="upload_format/leads.xlsx">
                                <i class="icon-download"></i>Download Sample File
                            </a>
                        </div>
                        <form class="form-horizontal" id="form_upload_file" enctype="multipart/form-data">

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
<!-- Bulk Upload End -->

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
                            <label class="control-label" for="type_id">Lead</label>
                            <div class="controls">
                                <select class="chzn-select-filter" id="filter_lead" data-placeholder="enter lead/code" name="filter[lead_name][]" multiple>
                                    <option></option>
                                    <?php echo $leadDd; ?>
                                </select>
                            </div>
                        </div>


                        <div class="control-group inline">
                            <label class="control-label" for="type_id">Lead Status</label>
                            <div class="controls">
                                <select class="chzn-select-filter" data-placeholder="Status" name="filter[status][]" multiple>
                                    <option></option>
                                    <?php echo $statusDd; ?>
                                </select>
                            </div>
                        </div>

                        <div class="control-group inline">
                            <label class="control-label" for="category">Loan/Product Type</label>
                            <div class="controls">
                                <select class="chzn-select-filter" data-placeholder="Select Loan/Product Type" multiple name="filter[category][]">
                                    <option></option>
                                    <?php echo $categoryDd; ?>
                                </select>
                            </div>
                        </div>



                        <div class="hide control-group">
                            <label class="control-label" for="locality">Locality</label>
                            <div class="controls">
                                <select class="chzn-select-filter" data-placeholder="Select locality" multiple name="filter[locality][]">
                                    <option></option>
                                    <?php echo $subLocalityDd; ?>
                                </select>
                            </div>
                        </div>


                        <div class="control-group inline">
                            <label class="control-label" for="partner">Partner</label>
                            <div class="controls">
                                <select class="chzn-select-filter" name="filter[partner][]" id="filter_partner" data-placeholder="type partner" multiple>
                                    <option></option>
                                    <?php echo $partnerDd; ?>
                                </select>
                            </div>

                        </div>

                        <div class="control-group inline">
                            <label class="control-label" for="customer">Customer</label>
                            <div class="controls">
                                <select class="chzn-select-filter" name="filter[customer][]" id="filter_customer" data-placeholder="type cutomer" multiple>
                                    <option></option>
                                    <?php echo $customerDd; ?>
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


                        <label class="control-label" for="filter_group_by"></label>
                        <div class="control-group hide">
                            <label class="control-label" for="filter_group_by">Group By</label>
                            <div class="controls">
                                <select class="chzn-select-filter" data-placeholder="group by" name="filter[group_by]" id="filter_group_by">
                                    <option value=""></option>
                                    <option value="zone">Zone</option>
                                    <option value="region">Region</option>
                                    <option value="branch">Branch</option>
                                    <option value="employee">Employee</option>
                                </select>
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
                    <?php echo $partnerName; ?> Leads
					<span class="widget-toolbar pull-right">

                         <?php
                         if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Change Lead BD')))   {
                         ?>
                        <a id='change_bd'  data-placement="top" data-rel="tooltip" data-original-title="Assign selected lead to other BD" class="pointer"><i class="icon-user icon-large white">Change BD</i></a>
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Change Lead KC')))   {
                        ?>
                        <a id='change_kc'  data-placement="top" data-rel="tooltip" data-original-title="Assign selected lead to other KC" class="pointer"><i class="icon-user icon-large white">Change KC</i></a>
                        <?php } ?>
                        <?php
                        /*
                         <a id='assign_to'  data-placement="top" data-rel="tooltip" data-original-title="Assign selected lead to other partner" class="pointer"><i class="icon-user icon-large white">Assign To</i></a>
                        if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Add Lead')))   {
                            ?>
                            <a id='add_lead' href="lead_addedit.php?token=<?php echo $token; ?>" class="" data-placement="top" data-rel="tooltip"  data-original-title="Add"><i class="icon-plus icon-large white"></i></a>&nbsp|
                        <?php }  ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Edit Lead')))   {
                            ?>
                            <a id='edit_lead' href="lead_addedit.php?token=<?php echo $token; ?>" class="" data-placement="top" data-rel="tooltip" data-original-title="Edit"><i class="icon-pencil icon-large white"></i></a>&nbsp|
                        <?php } */ ?>

                        <?php
                        /*
                        if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Delete Lead')))   {
                            ?>
                            <a id='delete_lead' href="javascript:void(0);" class="" data-placement="top" data-rel="tooltip" data-original-title="Delete"><i class="icon-trash icon-large white"></i></a>
                        <?php } */ ?>

<!--                        --><?php
//                         if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Export Lead')))   {
                           if(($acl->IsAllowed($login_id,'LEAD', 'Lead', 'Delete Lead'))){
                           ?>


                            <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="top" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white"></i></a>
                        <?php } ?>
                        <div class="info">

                        </div>
					</span>
                </div>

                <table id='dg_lead' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th></th>
                        <th data-class="expand">Code</th>
                        <th>Lead</th>
                        <th>Remarks</th>
                        <th>Activity Type</th>
                        <th>Status</th>
                        <th data-hide="phone">Email</th>
                        <th>Mobile</th>
                        <th data-hide="phone">Loan/Product Type</th>
                        <th data-hide="phone">CW Status</th>
                        <th data-hide="phone,tablet">Partner</th>
                        <th data-hide="phone,tablet">BD</th>
                        <th data-hide="phone,tablet">Kc</th>
                        <th data-hide="phone">Customer</th>
                        <th data-hide="phone">State</th>
                        <th data-hide="phone">City</th>
                        <th data-hide="phone,tablet">Created On</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- Lead Assign Model Box -->
<div id="modal_assign" class="modal hide" tabindex="-1">
    <form id='frm_assign' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Assign Lead to Partner</h4>
        </div>
        <div class="modal-body overflow-visible">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="partner_id">Partner</label>
                        <div class="controls">
                            <select id="partner_id" name="partner_id" class="chzn-select" data-placeholder="select partner">
                                <option></option>
                                <?php
                                echo $partnerDd;
                                ?>
                            </select>
                            <span for="partner_id" class="help-inline"></span>
                            <input type="hidden" id='assign_lead_id' name='assign_lead_id' value=""/>
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
<!-- Lead Assign Model Box End-->

<!-- Lead Comment model box Start-->
<div id="modal_comment" class="modal hide" tabindex="-1">
    <form id='frm_comment'>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Add Comment</h4>
        </div>
        <div class="modal-body overflow-visible">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label for="kyc_tracker_details" class="control-label">Comment</label>
                        <div class="controls">
                            <textarea name='lead_comment' id='lead_comment' class="span8"></textarea>
                            <input type="hidden" name="lead_id" id="lead_id"/>
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
<!-- Lead Comment model box End-->


<!-- Lead Details Model Box Start -->
<div id="modal_lead_detail" class="modal hide" tabindex="-1">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="blue bigger">Lead Details</h4>
    </div>
    <div class="modal-body overflow-scrollable">
        <div class="row-fluid">
            <div class="span12">
                <div class="control-group" id="lead_detail_div">

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
<!-- Lead Details Model Box End -->

<!-- Change user Model Box Start-->
<div id="modal_change" class="modal hide" tabindex="-1">
    <form id='frm_change' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Change Lead BD</h4>
        </div>
        <div class="modal-body overflow-visible">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="bd_user_id">Select BD</label>
                        <div class="controls">
                            <select id="bd_user_id" name="bd_user_id" class="chzn-select" data-placeholder="select BD">
                                <option></option>
                                <?php
                                echo $bdDd;
                                ?>
                            </select>
                            <span for="bd_user_id" class="help-inline"></span>
                            <input type="hidden" id='bd_lead_id' name='bd_lead_id' value=""/>
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
<!-- Change user Model Box End-->


<?php
include_once 'footer.php';
?>
