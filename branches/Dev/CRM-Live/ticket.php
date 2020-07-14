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
    'data-tables/dataTables.colVis',
);

include_once 'header.php';

$showMerged = (isset($_GET['show_merged']) && !empty($_GET['show_merged'])) ? true : false;

$status = (isset($_GET['status']) && !empty($_GET['status'])) ? $db->FilterParameters($_GET['status']) : '';
$getUserId = (isset($_GET['get_user_id']) && !empty($_GET['get_user_id'])) ? $db->FilterParameters($_GET['get_user_id']) : '';
$getUserLevel = (isset($_GET['get_user_level']) && !empty($_GET['get_user_level'])) ? $_GET['get_user_level'] : '';


$customerDd = $db->CreateOptions("html","customer_master",array("customer_id","concat(customer_name,' ',mobile_no)"),null,array("customer_name" => "asc"),null,array(0,10));
$tellecallerDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = ".UT_TC."");
$disposition = $db->FetchToArray("disposition_master",array("disposition_id","disposition_name"),"disposition_type = 'support'",array("disposition_name"=>"asc"));
if($userType == UT_ST){
    $mainTable = array("reason_master as rm",array("rm.reason_id","rm.reason_name"));
    $joinTable = array(
            array("left","category_master as cm","cm.category_id = rm.category_id",array("cm.category_name"))
    );
    $reasonResult = $db->JoinFetch($mainTable,$joinTable,"rm.reason_id in (select reason_id from user_reason where user_id = '{$login_id}')",array("rm.reason_name"=>"asc"));
    $reason = $db->FetchToArrayFromResultset($reasonResult);
} else {
    $mainTable = array("reason_master as rm",array("rm.reason_id","rm.reason_name"));
    $joinTable = array(
        array("left","category_master as cm","cm.category_id = rm.category_id",array("cm.category_name"))
    );
    $reasonResult = $db->JoinFetch($mainTable,$joinTable,null,array("rm.reason_name"=>"asc"));
    $reason = $db->FetchToArrayFromResultset($reasonResult);
    //$reason = $db->FetchToArray("reason_master",array("reason_id","reason_name"),null,array("reason_name"=>"asc"));
}

$hideColumnRes = $db->FetchCellValue("user_table_state","column_hidden","user_id = '{$login_id}' and page = '$current_page'");
if($hideColumnRes) {
    $hideColumn  =  str_replace("\"","",$hideColumnRes);
} else {
    $hideColumn  = DEFAULT_TICKET;
}

$dispositionDd = $db->CreateOptions("html","disposition_master",array("disposition_id","disposition_name"),null,array("disposition_name"=>"asc"),"disposition_type = 'support'");
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),null,array("status_name"=>"asc"),"status_type = 'support'");
$queryStageDd = $db->CreateOptions("html","query_stage_master",array("query_stage_id","query_stage_name"),null,array("query_stage_name"=>"asc"));
$subQueryStageDd = $db->CreateOptions("html","sub_query_stage_master",array("sub_query_stage_id","sub_query_stage_name"),null,array("sub_query_stage_name"=>"asc"));
$queryTypeDd = $db->CreateOptions("html","query_type_master",array("query_type_id","query_type_name"),null,array("query_type_name"=>"asc"));
$loanDd = $db->CreateOptions("html","loan_type_master",array("loan_type_id","loan_type_name"),null,array("loan_type_name"=>"asc"),"is_active = '1'");
$reasonDd = $db->CreateOptions("html","reason_master",array("reason_id","reason_name"),null,array("reason_name"=>"asc"),"is_active = '1'");
$productTypeDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"),"is_active = '1'");
$createdByDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"));
$userId = (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? intval($db->FilterParameters($_GET['user_id'])) : '';
$queryTypeColor =  $db->FetchToArray("query_type_master",array("query_type_name","query_color"));
$callFrom = $db->GetEnumvalues("tickets","call_from");
$supportUsers = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"),"user_type in (".UT_ST.")");
$bankDd = $db->CreateOptions("html","bank_master",array("bank_id","bank_name"),null,array("bank_name"=>"asc"));
if($userLevel == 'level1'){
    $userCondition = 'user_level = "level2"';
} elseif($userLevel == 'level2'){
    $userCondition = 'user_level = "level3"';
} else {
    if($userType == UT_ADMIN ){
        $userCondition = '1 = 1';
    } else {
        $userCondition = '1 != 1';
    }
}
$userLevel = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),null,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = '".UT_ST."' and ".$userCondition);
?>
    <style type="text/css">
        table#dg_ticket tfoot {
            display: table-header-group;
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

        function getCurentFileName(){
            var pagePathName= window.location.pathname;
            return pagePathName.substring(pagePathName.lastIndexOf("/") + 1);
        }


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


    $(document).on('click','#dg_ticket tbody tr td:not(.not-click)', function(){
        var tr = $(this).parent('tr').get(0);
        data = dg_ticket.fnGetData( tr );

        ticket_id = data.ticket_id;

        if(ticket_id){
            $.ajax({
                url: 'control/ticket.php?act=ticketinfo',
                type:'post',
                dataType:'html',
                beforeSend: function(){
                    $("#modal_ticket_detail").modal('show');
                    $("#ticket_detail_div").html(wait);
                },
                data:{ticket_id:ticket_id },
                success: function(resp){
                    $("#ticket_detail_div").html(resp);
                    $("#modal_ticket_detail").modal('show');
                }
            });
        }
    });

    $(document).on('click','.customer-info', function(){
        var tr = $(this).closest('tr').get(0);
        data = dg_ticket.fnGetData( tr );
        var customerId = data.customer_id;

        if(customerId){
            $.ajax({
                url: 'control/customer.php?act=customerinfo',
                type:'post',
                dataType:'html',
                beforeSend: function(){
                    $("#modal_customer_detail").modal('show');
                    $("#customer_detail_div").html(wait);
                },
                data:{customer_id : customerId },
                success: function(resp){
                    $("#customer_detail_div").html(resp);
                    $("#modal_customer_detail").modal('show');
                }
            });
        }
    });

    $(document).ready(function(){
        $(".chzn-select-filter").chosen({
            allow_single_deselect: true
        });

        $.fn.getColumnUnShown = function(dTable){
            vCols = new Array();

            $.each(dTable.fnSettings().aoColumns, function(c){
                if(dTable.fnSettings().aoColumns[c].bVisible != true){
                    vCols = vCols.concat(dTable.fnSettings().aoColumns[c].idx)
                }
            });

            return vCols;
        }

        $.fn.getColumnShown = function(dTable){
            vCols = new Array();

            $.each(dTable.fnSettings().aoColumns, function(c){
                if(dTable.fnSettings().aoColumns[c].bVisible == true && dTable.fnSettings().aoColumns[c].idx != 0 && dTable.fnSettings().aoColumns[c].idx != 21){
                    vCols = vCols.concat(dTable.fnSettings().aoColumns[c].idx)
                }
            });

            return vCols;
        }
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
            url: 'control/upload_call.php',
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
                dg_ticket.fnStandingRedraw();
            },
            complete: function(){
                $('#form_upload_file button').show();
                $('#loader').hide();
            }
        });
        $(".icon-chevron-up").click();

        // bulk prospect upload end
        //$('.chzn-select', this).chosen({
          //  allow_single_deselect:true
        //});

        //filter button
        $("#filter_btn").click(function(){
            showMessage();
            dg_ticket.fnStandingRedraw();
        });

        $("#clear").click(function(){
            form = $(this).closest('form');
            form.clearForm();
            $(".chzn-select-filter").trigger("liszt:updated");
            dg_ticket.fnStandingRedraw();

        });
        //date Picker
        if (jQuery().daterangepicker) {
            $(".created-date").daterangepicker(
            {
                ranges : {
                    Today : [ "today", "today" ],
                    Yesterday : [ "yesterday", "yesterday" ],
                    "Last 7 Days" : [ Date.today().add({days : -6}), "today" ],
                    "Last 30 Days" : [ Date.today().add({days : -29}), "today" ],
                    "This Month" : [
                        Date.today().moveToFirstDayOfMonth(),
                        Date.today().moveToLastDayOfMonth() ],
                    "Last Month" : [
                        Date.today().moveToFirstDayOfMonth().add({months : -1}),
                        Date.today().moveToFirstDayOfMonth().add({days : -1})]
                }, 
                opens : "right",
                format : "dd-MM-yyyy",
                separator : " to ",
                locale : {
                    applyLabel : "Submit",
                    fromLabel : "From",
                    toLabel : "To",
                    customRangeLabel : "Custom Range",
                    daysOfWeek : [ "Su", "Mo", "Tu", "We", "Th","Fr", "Sa" ],
                    monthNames : [ "January", "February", "March","April", "May", "June", "July","August", "September", "October","November", "December" ],
                    firstDay : 1
                },
                buttonClasses : [ "btn-danger" ]
            },
            function(e, t) {
                $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
            });

            $(".updated-date").daterangepicker(
            {
                ranges : {
                    Today : [ "today", "today" ],
                    Yesterday : [ "yesterday", "yesterday" ],
                    "Last 7 Days" : [ Date.today().add({days : -6}), "today"],
                    "Last 30 Days" : [ Date.today().add({days : -29}), "today"],
                    "This Month" : [
                        Date.today().moveToFirstDayOfMonth(),
                        Date.today().moveToLastDayOfMonth() 
                    ],
                    "Last Month" : [
                        Date.today().moveToFirstDayOfMonth().add({ months : -1}),
                        Date.today().moveToFirstDayOfMonth().add({ days : -1}) 
                    ]
                }, 
                opens : "right",
                format : "dd-MM-yyyy",
                separator : " to ",
                locale : {
                    applyLabel : "Submit",
                    fromLabel : "From",
                    toLabel : "To",
                    customRangeLabel : "Custom Range",
                    daysOfWeek : [ "Su", "Mo", "Tu", "We", "Th","Fr", "Sa" ],
                    monthNames : [ "January", "February", "March","April", "May", "June", "July","August", "September", "October","November", "December" ],
                    firstDay : 1
                },
                buttonClasses : [ "btn-danger" ]
            },
            function(e, t) {
                $(this).val(e.toString("dd-MM-yyyy") + " : " + t.toString("dd-MM-yyyy"));
            });
        }

        $('.modal').on('shown.bs.modal', function () {
            $('.chzn-select-modal', this).chosen({
                allow_single_deselect:true
            });
        });

        $('[data-rel=tooltip]').tooltip();
        $(".ticket-count").html(0);

        var breakpointDefinition = {
            pc: 1280,
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_ticket = $('#dg_ticket').dataTable({
            "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
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
                    sNext: '<i class="icon-angle-right"></i>',
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "bScrollCollapse" : true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/ticket.php",
            "fnServerParams": function ( aoData ) {
                var form_data = $('#frm_filter').serializeArray();
                $.each(form_data, function(i, val){
                    aoData.push(val);
                });
                aoData.push({ "name": "act", "value": "fetch" },
                    //{ "name": "disposition_id", "value": ""+$("#myTab li.active").attr('id')+"" },
                    { "name": "reason_id", "value": ""+$("#myTab li.active").attr('id')+"" },
                    { "name": "user_id", "value": "<?php echo $userId; ?>" },
                    { "name": "get_user_id", "value": "<?php echo $getUserId; ?>" },
                    { "name": "get_user_level", "value": "<?php echo $getUserLevel; ?>" },
                    { "name": "show_merged", "value": "<?php echo $showMerged; ?>" },
                    { "name": "status", "value": "<?php echo $status; ?>" });
                server_params = aoData;
            },
            "aaSorting": [[ 25, "desc" ],[ 24, "desc" ]], 
            "aoColumns": [
                {
                    mData: "ticket_id",
                    "visible": true,
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center not-click'
                },
                {
                    "class":          'details-control not-click',
                    "orderable":      false,
                    "data":           null,
                    "defaultContent": '',
                },
                {
                    "mData": "ticket_number",
                    "class": 'not-click'
                },
                {
                    "mData": "merged_ticket_number",
                    "class": 'not-click'
                },
                {
                    "mData": "customer_name",
                    sClass: 'not-click',
                    mRender: function (v,t,o){
                        return (v != null) ? "<a href='javascript:void(0)' class='customer-info' title='View Details'>"+v+"</a>" : "";
                    }
                },
                {"mData": "bank_name" },
                {"mData": "email" },
                {"mData": "loan_type_name" },
                {"mData": "category_name" },
                {"mData": "mobile_no" },
                {"mData": "personal_mobile_no" },
                {"mData": "reason_name" },
                {"mData": "call_from" },
                {
                    "mData": "query_stage_name",
                    sClass:""
                },
                {
                    "mData": "query_type_name",
                    sClass:""
                },
                {
                    "mData": "sub_query_stage_name",
                    sClass:""
                },
                {
                    "mData": "short_comment",
                    "class": 'get-comment not-click',
                },
                {"mData": "resolve_date_time" },
                {"mData": "disposition_name" },
                {"mData": "status_name" },
                {"mData": "created_by" },
                {"mData": "assign_to" },
                {"mData": "escalate_2" },
                {"mData": "escalate_3" },
                {"mData": "created_at" },
                {"mData": "updated_at" },
                {"mData": "updated_by" },
                {
                    "mData": "resolve_time",
                },
                {
                    bSortable :false,
                    mData: null,
                    <?php if($showMerged === true ) { ?>
                    "visible": false,
                    <?php } ?>
                    sClass: 'not-click',
                    mRender: function(v,t,o){
                        var act_html = '';
                        // if(o['is_close'] != 1) {
                            // call button 
                            // if (o['mobile_no']!=='') {
                            //      act_html = act_html + "<a href='javascript:void(0);' onclick=\"CallCustomer('"+ o['mobile_no'] +"')\" class='btn btn-minier btn-pink' data-placement='bottom' data-rel='tooltip' data-original-title='Call "+o['mobile_no']+"'><i class='icon-mobile-phone bigger-300'></i></a>&nbsp";
                            // }
                            if(o['is_close'] != 1) {
                                <?php
                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'View Single Ticket')){
                                ?>
                                act_html = act_html + "<a href='view_single_ticket.php?id="+ o['ticket_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-pink' data-placement='bottom' data-rel='tooltip' data-original-title='View "+o['customer_name']+"'><i class='icon-eye-open bigger-120'></i></a>&nbsp";
                                <?php } ?>
                            // uncomment this if only open tickets call function
                            //         if (o['mobile_no']!=='') {
                            //      act_html = act_html + "<a href='javascript:void(0);' onclick=\"CallCustomer('"+ o['mobile_no'] +"')\" class='btn btn-minier btn-pink' data-placement='bottom' data-rel='tooltip' data-original-title='Call "+o['mobile_no']+"'><i class='icon-mobile-phone bigger-300'></i></a>&nbsp";
                            // }
                                <?php
                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Edit Ticket')){
                                ?>
                                act_html = act_html + "<a href='ticket_addedit.php?id="+ o['ticket_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['customer_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Update Ticket')){
                                ?>
                                if(o['escalate_to_2'] == null && o['escalate_to_3'] == null){
                                    act_html = act_html+"<a href='javascript:void(0);' onclick=\"updateRecord('"+ o['ticket_id'] +"')\" class='btn btn-primary btn-minier' title='Update'><i class='icon-comment bigger-120'></i> </a>&nbsp";
                                } else if(o['escalate_to_2'] != null && o['escalate_to_3'] == null){
                                    act_html = act_html+"<a href='javascript:void(0);' onclick=\"updateRecord('"+ o['ticket_id'] +"')\" class='btn btn-primary btn-minier' title='Update'><i class='icon-comment bigger-120'></i> </a>&nbsp";
                                } else if(o['escalate_to_3'] != null){
                                    act_html = act_html+"<a href='javascript:void(0);' onclick=\"updateRecord('"+ o['ticket_id'] +"')\" class='btn btn-primary btn-minier' title='Update'><i class='icon-comment bigger-120'></i> </a>&nbsp";
                                } else {
                                    <?php
                                     if($userType == UT_ADMIN){ ?>
                                    act_html = act_html+"<a href='javascript:void(0);' onclick=\"updateRecord('"+ o['ticket_id'] +"')\" class='btn btn-primary btn-minier' title='Update'><i class='icon-comment bigger-120'></i> </a>&nbsp";
                                    <?php } ?>
                                }
                                <?php } ?>

                                <?php
                                if ($acl->IsAllowed($login_id, 'Ticket', 'Ticket', 'Merge Ticket')) {
                                ?>
                                act_html = act_html + "<a href='ticket_merge.php?id="+ o['ticket_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-info' data-placement='bottom' data-rel='tooltip' data-original-title='Merge ticket'><i class='icon-exchange bigger-120'></i></a>&nbsp";
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Delete Ticket')){
                                ?>
                                act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['ticket_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                                <?php } ?>

                            } else {

                                <?php
                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'View Single Ticket')){
                                ?>
                                act_html = act_html + "<a href='view_single_ticket.php?id="+ o['ticket_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-pink' data-placement='bottom' data-rel='tooltip' data-original-title='View "+o['customer_name']+"'><i class='icon-eye-open bigger-120'></i></a>&nbsp";
                                <?php } ?>

                                <?php
                                if($userType == UT_TC || $userType == UT_ADMIN){
                                    if($acl->IsAllowed($login_id,'ticket', 'Ticket', 'Update Ticket After Close')){
                                    ?>
                                    act_html = act_html+"<a href='javascript:void(0);' onclick=\"updateRecord('"+ o['ticket_id'] +"')\" class='btn btn-primary btn-minier' title='Update'><i class='icon-comment bigger-120'></i> </a>&nbsp";
                                    <?php } ?>
                                <?php } ?>

                            }


                        //}
                        return act_html;
                    }
                }
            ],
            fnPreDrawCallback: function () {
                if (!responsiveHelper2) {
                    responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
                }
            },
            fnRowCallback  : function (nRow, aData, iDisplayIndex) {

                $('td', nRow).css('background-color', aData.query_color);
                responsiveHelper2.createExpandIcon(nRow);
                return nRow;
            },
            fnDrawCallback : function (oSettings) {
                responsiveHelper2.respond();
                $(this).removeAttr('style');
                $('[data-rel=tooltip]').tooltip();
            }
        });

        var colvis = new $.fn.dataTable.ColVis( dg_ticket,{
            restore: "Restore",
            showAll: "Show all",
            showNone: "Show none",
            exclude: [0,1,21],
            "fnStateChange": function ( iColumn, bVisible ) {
                var columnsUnShown = $('#dg_ticket').getColumnUnShown(dg_ticket);
                $.ajax({
                    url: 'control/ticket.php?act=changetablestate',
                    type:'post',
                    dataType:'json',
                    async:false,
                    data:{ column_shown : columnsUnShown,module:"ticket",page:getCurentFileName()}
                });
            }

        } );

        $( colvis.button() ).insertAfter('div.info');

        $("tfoot inpdut").on("keyup", function(e) {
            // If the length is 3 or more characters, or the user pressed ENTER, search

            if ($(this).val().length > <?php echo SEARCH_CHARACTERS; ?> || e.keyCode == 13) {
                // Call the API search function
                dg_ticket.fnFilter( this.value, $(this).attr("colPos") );
            }
            if($(this).val() == "") {
                dg_ticket.fnFilter( this.value, $(this).attr("colPos") );
            }

        });

        //
        var Otable = $("#dg_ticket").DataTable();
        // show more details or rows

        $('#dg_ticket tbody').on('click', '.get-comment', function () {
//            $(this).closest('tr').find('td.details-control').click();

            var tr = $(this).closest('tr').get(0);
            data = dg_ticket.fnGetData( tr );
            var getComment = data.comment;
            var messageNo = data.message_no;
           // if(messageNo == ''){
             //   $('#view_comment').text(getComment);
              //  $('#modal_view_comment').modal('show');
           // } else {
                var ticketId = data.ticket_id;
                $.ajax({
                    url: 'control/ticket.php?act=getcomment',
                    data : { ticket_id : ticketId },
                    type:'post',
                    dataType: 'html',
                    success: function(resp){
                        $('#view_comment').html(resp);
                        $('#modal_view_comment').modal('show');
                    }
                });
           // }
        });
        $('#dg_ticket tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = Otable.row( tr );
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                var ticketId = row.data().ticket_id;
                $.ajax({
                    url: 'control/ticket.php?act=tickethistory',
                    type:'post',
                    dataType:'html',
                    beforeSend: function(){
                        row.child( wait ).show();
                        tr.addClass('shown');
                    },
                    data:{ ticket_id : ticketId },
                    success: function(resp){
                        row.child( resp ).show();
                        tr.addClass('shown');
                    }
                });
            }
        } );

        $("tfoot input").keyup( function () {
            dg_ticket.fnFilter( this.value, $(this).attr("colPos") );
        });


        $('#edit_record').click( function (e) {
            var selected_list = $('#dg_ticket tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select a ticket to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var rowIndex = dg_ticket.fnGetPosition( $("#chk_"+selected_list.val()+"").closest('tr')[0] );
            var aData = dg_ticket.fnGetData( rowIndex  );
            var isClose = aData.is_close;
            <?php
              if(!$acl->IsAllowed($login_id,'ticket', 'Ticket', 'Update Ticket After Close')){ ?>
            if(1 == isClose){
                showGritter('error','Alert!','You can\'t Update this record');
                return false;
            }
            <?php } ?>
            href = $('#edit_record').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_record').attr('href',href);
            return true;
        });


        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };

            $("#frm_ticket").validate({

                rules:{
                    //disposition_id:{required:true },
                    status_id:{required:true },
                    comment:{required:true }
                },
                messages:{
                    comment:{required:'Please enter comment'}
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
                        url: 'control/ticket_addedit.php?act=addedit',
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
                                dg_ticket.fnStandingRedraw();
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
                    'support_user_id':{required:true },
                    'support_ticket_id':{required:true }
                },
                messages:{
                    'support_user_id':{required:'Please select Bd'}

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
                        url: 'control/ticket.php?act=changesupport',/*i have changed here*/
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
                                dg_ticket.fnStandingRedraw();
                                showGritter('success',resObj.title,resObj.msg);
                            }else{
                                showGritter('error', resObj.title, resObj.msg);
                            }
                        }
                    });
                }
            });
        }

        $('#update_record').click( function (e) {

            var selected_list = $('#dg_ticket tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select disposition to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_ticket.fnGetData( ele );
            var isClose = aData.is_close;
            <?php
              if(!$acl->IsAllowed($login_id,'ticket', 'Ticket', 'Update Ticket After Close')){ ?>
            if(1 == isClose){
                showGritter('error','Alert!','You can\'t Update this record');
                return false;
            }
            <?php } ?>


            $.each(aData, function(key,val){
                var inputType = $('form#frm_ticket #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_ticket #'+key).prop("checked",true);
                    } else {
                        $('form#frm_ticket #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_ticket #'+key).length){
                        $('form#frm_ticket #'+key).val(val);
                    }
                }
            });

            $('#comment').val('');
            $('form#frm_ticket').find('div.control-group').removeClass("success error");
            $('form#frm_ticket').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('.chzn-select-modal').trigger('liszt:updated');
            $('.chzn-select-modal').trigger('chosen:updated');
            $('#modal_comment').modal('show');
        });

        $('#modal_comment').on('hidden.bs.modal', function () {
            $('#dg_ticket tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_record').click(function(){

            var delete_ele = $('#dg_ticket tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select ticket to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected ticket(s)? It will delete all ticket related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/ticket.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_ticket.fnDraw();
                                showGritter('success',resp.title,resp.msg);
                            }
                        });
                    }
                });
            }
        });

        $('#assign_to').click(function(){
            var account_ele = $('#dg_ticket tbody input[type=checkbox]:checked');
            var selected_length = account_ele.size();
            var close = 0;
            if(0 == selected_length){
                showGritter('info','Alert!','Please select ticket to assign');
                return false;
            } else{
                var ticket_id = [];
                $.each(account_ele, function(i, ele){
                    var rowIndex = dg_ticket.fnGetPosition($(this).closest('tr')[0]);
                    var rowData = dg_ticket.fnGetData(rowIndex);
                    if(rowData.is_close == 1) {
                        showGritter('info','Alert!','You can\'t assign close ticket');
                        close = 1;
                        return false;
                    }
                    ticket_id.push($(ele).val());
                });
                if(close == 0) {
                    $('form#frm_change').find('div.control-group').removeClass("success error");
                    $('#support_ticket_id').val(ticket_id);
                    $('#modal_change').modal('show');
                }

            }
        });

        $("#syn_mail").click(function(){
            $.ajax({
                url: 'control/ticket.php?act=synMail',
                type:'post',
                dataType:'json',
                beforeSend: function (formData, jqForm, options) {
                    bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being saved');
                },
                success: function(resp){
                    bootbox.hideAll();
                    dg_ticket.fnDraw();
                    if(resp.success){
                        showGritter('success',resp.title,resp.msg);
                    } else {
                        showGritter('error',resp.title,resp.msg);
                    }
                }
            });
        });

        $("#myTab li").click(function(){
            $(this).addClass("active").siblings().removeClass("active");
            dg_ticket.fnDraw();
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

    });

    function ExportToExcel(ele){

        var query_string = decodeURIComponent($.param(server_params));
        $(ele).attr('href','export_ticket.php?='+query_string);
        return true;
    }

    function ExportToExcelAll(ele){

        var query_string = decodeURIComponent($.param(server_params));
        $(ele).attr('href','export_ticket_all.php?='+query_string);
        return true;
    }


    function DeleteRecord(rid){

        $('#chk_'+rid).prop('checked', true);
        $('#delete_record').click();
    }

    function CallCustomer(mobile_no){

    	bootbox.confirm("Are you sure to call this number "+ mobile_no , function(result) {
            if(result) {
                $.ajax({
                    url:'control/ticket.php?act=call',
                    type:'post',
                    dataType: 'json',
                    data:{ id : mobile_no},
                    success: function(resObj){
            		//after success 
                    }
                });
            }
        });

    }

    function updateRecord(sid){

        $('#chk_'+sid).prop('checked', true);
        $('#update_record').click();
    }

    function DeleteImage(id){
        bootbox.confirm("Are you sure to delete image", function(result) {
            if(result) {
                $.ajax({
                    url:'control/ticket.php?act=deleteimage',
                    type:'post',
                    dataType: 'json',
                    data:{ id : id},
                    success: function(resObj){
                        var element = $('#new_image_'+id+'');
                        element.fadeOut(500, function() { element.remove(); });
                        showGritter('success',resObj.title,resObj.msg);
                    }
                });
            }
        });
    }

    </script>
    <!-- Query Color Display Start -->
        <?php
        if(count($queryTypeColor) > 0){ ?>
            <div class="row">
                <?php
                foreach($queryTypeColor as $queryTypeData){
                ?>
                <div class="span2 center">
                    <div class="" style="background-color: <?php echo $queryTypeData['query_color']; ?>">
                        <?php echo $queryTypeData['query_type_name']; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php }
        ?>
    <!-- Query Color Display End -->
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
                                <label class="control-label" for="prospect_id">Customer</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" id="filter_customer" data-placeholder="Select Customer" name="filter[customer][]" multiple>
                                        <option></option>
                                        <?php echo $customerDd; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="control-group inline">
                                <label class="control-label" for="">Loan Type</label>
                                <div class="controls">
                                    <select class="chzn-select-filter select-loan-type" data-placeholder="Select Loan Type" name="filter[loan_type][]" multiple>
                                        <option></option>
                                        <?php echo $loanDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="">Product Type</label>
                                <div class="controls">
                                    <select class="chzn-select-filter select-product-type" data-placeholder="Select Product Type" name="filter[product_type][]" multiple>
                                        <option></option>
                                        <?php echo $productTypeDd; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="control-group inline">
                                <label class="control-label" for="" >Reason</label>
                                <div class="controls">
                                    <select class="chzn-select-filter select-reason-type" data-placeholder="Select Reason" name="filter[reason][]"  multiple>
                                        <option></option>
                                        <?php echo $reasonDd; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="control-group inline">
                                <label class="control-label" for="">Query Stage</label>
                                <div class="controls">
                                    <select class="chzn-select-filter select-query-stage" data-placeholder="Select Query Stage" name="filter[query_stage][]" multiple>
                                        <option></option>
                                        <?php echo $queryStageDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="">Sub Query Stage</label>
                                <div class="controls">
                                    <select class="chzn-select-filter select-sub-query-stage" data-placeholder="Select Sub Query Stage" name="filter[sub_query_stage][]" multiple>
                                        <option></option>
                                        <?php echo $subQueryStageDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="">Query Type</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select Query Type" name="filter[query_type][]" multiple>
                                        <option></option>
                                        <?php echo $queryTypeDd; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="">Bank</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select Bank" name="filter[bank_id][]" multiple>
                                        <option></option>
                                        <?php echo $bankDd; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="control-group inline">
                                <label class="control-label" for="disposition_id">Disposition </label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select Disposition" name="filter[disposition][]" multiple>
                                        <option></option>
                                        <?php echo $dispositionDd; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group inline">
                                <label class="control-label" for="">Status</label>
                                <div class="controls">
                                    <select class="chzn-select-filter" data-placeholder="Select status" name="filter[status][]" multiple>
                                        <option></option>
                                        <?php echo $statusDd; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="control-group inline">
                                <label class="control-label" for="user">Created By/Telecaller</label>
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
                                        <input class='input-large created-date' data-placement='top' type='text' placeholder='Created Date'
                                               name='filter[created_on]' id="date_range_1" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[created_on]' class='help-inline'></span>
                                    </div>

                                </div>
                            </div>

                            <div class="control-group inline">
                                <label class="control-label" for="date_range_1">Updated Date</label>
                                <div class="controls">
                                    <div class='row-fluid input-append'>
                                        <input class='input-large updated-date' data-placement='top' type='text' placeholder='Updated Date'
                                               name='filter[updated_at]' id="updated_date" data-date-format='dd-mm-yyyy'
                                               readonly='readonly'>
                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                        <span for='filter[updated_at]' class='help-inline'></span>
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

    <div class="alert alert-danger hide" style="font-size: 16px">

        Column filter working when either <b>ENTER</b> has been pressed or there are <b>AT LEAST 3</b> characters in the search
        <br>
    </div>
    <div class="tabbable">
        <ul class="nav nav-tabs" id="myTab">
            <li class="active" id="0" ><a>All</a></li>
            <?php
            if(count($reason) > 0){
                foreach($reason as $reasonData){ ?>
                    <li class="" id="<?php echo $reasonData['reason_id']; ?>" ><a><?php echo $reasonData['category_name']; ?>-<?php echo $reasonData['reason_name']; ?></a></li>
                <?php
                }
            }
            ?>


        </ul>
        <div class="tab-content">
            <div class="tab-pane in active">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="row-fluid">
                            <div class='span12'>
                                <div class="table-header">
                                    Ticket List
                                <span class="widget-toolbar pull-right">
                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Add Ticket')){
                                        ?>
                                        <a id='add_record' href="ticket_addedit.php?type=call&token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                                    <?php } ?>

                                    <?php if($showMerged === false) { ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Edit Ticket')){
                                        ?>
                                        <a id='edit_record' href="ticket_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Update Ticket')){
                                        ?>
                                        <a id='update_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Update" class="white"><i class="icon-comment icon-large white"></i>Update</a>&nbsp|
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Delete Ticket')){
                                        ?>
                                        <a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                                    <?php } ?>

                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Export Ticket')){
                                        ?>
                                        <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="bottom" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                                    <?php } ?>


                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Export Ticket')){
                                        ?>
                                        <a target="_blank" id="" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="bottom" data-rel="tooltip" data-original-title="Export All Ticket Data" onclick="return ExportToExcelAll(this);"><i class="icon-save icon-large white">Export All</i></a>
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Assign Ticket')){
                                        ?>
                                        <a id='assign_to'  data-placement="top" data-rel="tooltip" data-original-title="Assign selected ticket to other user" class="pointer"><i class="icon-user icon-large white">Assign To</i></a>
                                    <?php } ?>

                                    <?php
                                    if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Assign Ticket')){
                                        ?>
                                        <a id='syn_mail'  data-placement="top" data-rel="tooltip" data-original-title="Synchronize Mail" class="syn_mail"><i class="icon-envelope icon-large white">Syn Mail</i></a>
                                    <?php } ?>

                                </span>
                                </div>
                                <div class="info"></div>
                                <table id='dg_ticket' class="table table-condensed table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="center" width="5%">
                                            <label>
                                                <input type="checkbox"/>
                                                <span class="lbl"></span>
                                            </label>
                                        </th>
                                        <th></th>
                                        <th>No.</th>
                                        <th>Merged With</th>
                                        <th>Customer</th>
                                        <th>Bank Name</th>
                                        <th>Email</th>
                                        <th>Loan Type</th>
                                        <th>Product Type</th>
                                        <th>Mobile</th>
                                        <th>Personal Mobile No</th>
                                        <th>Reason</th>
                                        <th>Query Received From</th>
                                        <th>Stage</th>
                                        <th>Query Type</th>
                                        <th>Sub Query Stage</th>
                                        <th>Comment</th>
                                        <th>Expected Time</th>
                                        <th>Disposition</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Assign To</th>
                                        <th>Escalate 2</th>
                                        <th>Escalate 3</th>
                                        <th>Created On</th>
                                        <th>Updated On</th>
                                        <th>Updated By</th>
                                        <th>Resolve Time</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th class="center">
                                        </th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="number" name="filter_number" class="span7" colPos="1">
                                        </th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="customer" name="filter_customer" class="span7" colPos="2">
                                        </th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="email" name="filter_email" class="span7" colPos="11">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="loan_type_name" name="filter_loan_type_name" class="span7" colPos="13">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="category_name" name="filter_category_name" class="span7" colPos="14">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="mobile" name="filter_mobile" class="span7" colPos="6">
                                        </th>

                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="call from" name="filter_call_from" class="span7" colPos="7">
                                        </th>

                                        <th>
                                            <input type="text"  placeholder="stage" name="filter_stage" class="span7" colPos="8">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="Query Type" name="filter_query_type" class="span7" colPos="9">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="Sub Query Stage" name="filter_sub_query_stage" class="span7" colPos="12">
                                        </th>
                                        <th>
                                            <input type="text"  placeholder="comment" name="filter_comment" class="span7" colPos="10">
                                        </th>
                                        <th></th>
                                        <th>
                                            <input type="text"  placeholder="disposition" name="filter_disposition" class="span7" colPos="3">
                                        </th>

                                        <th>
                                            <input type="text"  placeholder="status" name="filter_status" class="span7" colPos="4">
                                        </th>
                                        <th></th>
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
            </div>
        </div>
    </div>


    <!-- Ticket Update model box Start-->
    <div id="modal_comment" class="modal hide" tabindex="-1">
        <form id='frm_ticket'>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Add Comment</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="disposition_id">Disposition</label>
                            <div class="controls">
                                <select name="disposition_id" id="disposition_id" class="chzn-select-modal">
                                    <option></option>
                                    <?php echo $dispositionDd; ?>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="status_id">Status</label>
                            <div class="controls">
                                <select name="status_id" id="status_id" class="chzn-select-modal">
                                    <option></option>
                                    <?php echo $statusDd; ?>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label for="comment" class="control-label">Comment</label>
                            <div class="controls">
                                <textarea name='comment' id='comment' class="span8"></textarea>
                                <input type="hidden" name="ticket_id" id="ticket_id"/>
                                <input type="hidden" name="customer_id" id="customer_id"/>
                                <input type="hidden" name="ticket_number" id="ticket_number"/>
                            </div>
                        </div>


                        <div class="control-group">
                            <label class="control-label" for="parent_disposition_id">Escalate to</label>
                            <div class="controls">
                                <select name="escalate_to" id="escalate_to" class="chzn-select-modal">
                                    <option></option>
                                    <?php echo $userLevel; ?>
                                </select>
                            </div>
                        </div>

                        <div class='control-group'>
                            <label for='filename' class='control-label'>Upload Document</label>
                            <div class='controls'>
                                <input type='file' id="filename" name='filename[]' placeholder='File' class='upload' multiple/>
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
    <!-- Ticket Update model box End-->

    <!-- Prospect Details Model Box Start -->
    <div id="modal_customer_detail" class="modal hide" tabindex="-1">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Customer Details</h4>
        </div>
        <div class="modal-body overflow-scrollable">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group" id="customer_detail_div">

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



    <!-- Change user Model Box Start-->
    <div id="modal_change" class="modal hide" tabindex="-1">
        <form id='frm_change' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Change Ticket Support</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="support_user_id">Select Support</label>
                            <div class="controls">
                                <select id="support_user_id" name="support_user_id" class="chzn-select-modal" data-placeholder="select Support User">
                                    <option></option>
                                    <?php
                                    echo $supportUsers;
                                    ?>
                                </select>
                                <span for="support_user_id" class="help-inline"></span>
                                <input type="hidden" id='support_ticket_id' name='support_ticket_id' value=""/>
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

    <!-- Comment Details Model Box Start -->
    <div id="modal_view_comment" class="modal hide" tabindex="-1">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Comment</h4>
        </div>
        <div class="modal-body overflow-scrollable">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group" id="comment_view_details">
                        <table class='table table-bordered'>
                        <tr>
                            <th>Comment</th>
                            <td id="view_comment"></td>
                        </tr>
                        </table>
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
    <!-- Comment Details Model Box End -->


    <!-- Ticket Details Model Box Start -->
    <div id="modal_ticket_detail" class="modal hide" tabindex="-1">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Ticket Details</h4>
        </div>
        <div class="modal-body overflow-scrollable">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group" id="ticket_detail_div">

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
    <!-- Ticket Details Model Box End -->


<?php
include_once 'footer.php';
?>
