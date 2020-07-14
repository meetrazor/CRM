<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
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
    'js/bootbox.min'
);
include_once 'header.php';
$partnerId = (isset($_GET['partner_id']) && !empty($_GET['partner_id'])) ? intval($db->FilterParameters($_GET['partner_id'])) : '';
$partnerName = "Partners";
if($partnerId != ''){
    $partnerName = $db->FetchCellValue("partner_master","concat(first_name,' ',last_name)","partner_id = '{$partnerId}'");
}
?>
<style type="text/css">
    table#dg_partner_commission tfoot {
        display: table-header-group;
    }
</style>
<script type="text/javascript">
$(function() {
    $('[data-rel=tooltip]').tooltip();
    var breakpointDefinition = {
        tablet: 1024,
        phone : 480
    };



    var responsiveHelper2 = undefined;
    dg_partner_commission = $('#dg_partner_commission').dataTable({
        "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
        "bPaginate":true,
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
        "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
        "sAjaxSource": "control/partner_commission.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
        },
        "aaSorting": [[ 4, "desc" ]],
        "aoColumns": [
            {
                mData: "partner_commission_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="partner_commission_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            { "mData": "customer_name" },
            { "mData": "partner_name" },
            {
                "mData": "lead_name",
                mRender: function(v,t,o){
                    var act_html =  (v != null) ? "<a id='leadinfo_"+o['lead_id']+"' class='client_status lead-info pointer'>"+v+"</a>" : "";
                    return act_html;
                },
                "sClass":"not-click"
            },
            {"mData": "amount"},
            {"mData": "created_at"},
            {
                mData: null,
                bSortable:false,
                'sClass':"hide",
                mRender: function(v,t,o){
                    var act_html = "";

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Partner', 'Edit Partner Commission')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['partner_commission_id'] +"')\" class='btn btn-minier btn-warning' data-placement='buttom' data-rel='tooltip' data-original-title='Edit' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                    <?php } ?>

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Partner', 'Delete Partner Commission')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['partner_commission_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
        fnRowCallback  : function (nRow) {
            responsiveHelper2.createExpandIcon(nRow);
        },
        fnDrawCallback : function (oSettings) {
            responsiveHelper2.respond();
            $(this).removeAttr('style');
        }
    });


    $("#is_file_upload").click(function(){
        if($(this).prop('checked')) {
            $("#payment_url").val('');
            $("#partner_commission_dd").hide();
        } else {
            $("#partner_commission_dd").show();
        }
    })






    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code  
        $("#frm_partner_commission").validate({
            rules:{
                partner_commission_name:{required:true },
                payment_url:{url:true }
            },
            messages:{
                partner_commission_name:{required:'Please enter Partner Commission'}
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
                    url: 'control/partner_commission.php?act=addedit',/*i have changed here*/
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
                            $('#modal_add_partner_commission').modal('hide');
                            dg_partner_commission.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }
    $('#add_partner_commission').click(function(){
        $('form#frm_partner_commission input,select').val('');
        $('form#frm_partner_commission').find('div.control-group').removeClass("success error");
        $('form#frm_partner_commission').find('div.control-group span').text('');
        $('#act_partner_commission').text('Add');
        $('#action').val('add');
        $('#modal_add_partner_commission').modal('show');
    });

    $('#edit_partner_commission').click( function (e) {

        var selected_list = $('#dg_partner_commission tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select Partner Commission to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        var selected_tr = selected_list[0];
        var ele = $(selected_tr).closest('tr').get(0);
        //console.log(ele);
        var aData = dg_partner_commission.fnGetData( ele );

        $.each(aData, function(key,val){
            var inputType = $('form#frm_partner_commission #'+key).prop("type");
            if(inputType == 'checkbox'){
                if(val == 1){
                    $('form#frm_partner_commission #'+key).prop("checked",true);
                } else {
                    $('form#frm_partner_commission #'+key).prop("checked",false);
                }
            }else {
                if($('form#frm_partner_commission #'+key).length){
                    $('form#frm_partner_commission #'+key).val(val);
                }
            }
        });
        $('#act_partner_commission').text('Edit');
        $('form#frm_partner_commission').find('div.control-group').removeClass("success error");
        $('form#frm_partner_commission').find('div.control-group span').text('');

        $('#action').val('edit');
        $('#partner_commission_code').prop('readonly', true);
        $('#modal_add_partner_commission').modal('show');
    });


    $('#delete_partner_commission').click(function(){

        var delete_ele = $('#dg_partner_commission tbody input[type=checkbox]:checked');
        var selected_length = delete_ele.size();

        if(0 == selected_length){
            showGritter('info','Alert!','Please select record to delete.');
            return false;
        }else{
            bootbox.confirm("Are you sure to delete selected record(s)?", function(result) {
                if(result) {

                    var delete_id = [];
                    $.each(delete_ele, function(i, ele){
                        delete_id.push($(ele).val());
                    });

                    $.ajax({
                        url: 'control/partner_commission.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_partner_commission.fnStandingRedraw();
                            if(resp.success) {
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

});


$(document).on("click",".lead-info",function(){
    var leadId = $(this).attr("id").split("_").pop();
    $.ajax({
        url: 'control/lead.php?act=leadinfo',
        type:'post',
        dataType:'html',
        data:{ lead_id : leadId },
        beforeSend: function(){
            $('#lead_info').html(wait);
            $("#model_title").text("Lead Summary");
            $('#modal_add_lead_info').modal('show');
        },
        success: function(resp){
            $("#lead_info").html(resp);
            if($.isNumeric(leadId)){
                $("#more_lead_details").attr("href","lead_view.php?lead_id="+leadId+"");
            }
        }
    });
});

function EditRecord(id){
    $('#chk_'+id).prop('checked', true);
    $('#edit_partner_commission').click();
}

function DeleteRecord(fid){
    $('#chk_'+fid).prop('checked', true);
    $('#delete_partner_commission').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    <?php echo $partnerName; ?> Commission List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Partner', 'Add Partner Commission')))   {
                            ?>
                            <a id='add_partner_commission' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Partner', 'Edit Partner Commission')))   {
                            ?>
                            <a id='edit_partner_commission' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Partner', 'Delete Partner Commission')))   {
                            ?>
                            <a id='delete_partner_commission' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_partner_commission' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Partner</th>
                        <th>Customer</th>
                        <th>Lead</th>
                        <th>Amount</th>
                        <th>Created On</th>
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
<div id="modal_add_partner_commission" class="modal hide" tabindex="-1">
    <form id='frm_partner_commission' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_partner_commission'>Add</span> Partner Commission</h4>
        </div>
        <div class="modal-body overflow-auto">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="partner_commission_name">Partner Commission</label>
                        <div class="controls">
                            <input type="text" name="partner_commission_name" id="partner_commission_name" placeholder="ex. 2 Wheeler"/>
                            <input type="hidden" name="partner_commission_id" id="partner_commission_id"/>
                            <input type="hidden" id='action' name='action' value="add"/>
                        </div>
                    </div>
                    <div class="control-group hide" id="partner_commission_dd">
                        <label class="control-label" for="partner_commission_name">Payment Url</label>
                        <div class="controls">
                            <input type="text" name="payment_url" id="payment_url" placeholder=""/>
                        </div>
                    </div>
                    <div class="controls" id="file_upload_dd">
                        <label>
                            <input type="checkbox" name="is_file_upload" id="is_file_upload">
                            <span class="lbl"> File Upload Required</span>
                        </label>
                    </div>

                    <div class="controls">
                        <label>
                            <input type="checkbox" name="is_active" id="is_active" checked>
                            <span class="lbl"> Active</span>
                        </label>
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

<!-- lead info model box start -->
<div id="modal_add_lead_info" class="modal hide" tabindex="-1">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="blue bigger" id="model_title">Sales Summary of this Client</h4>
    </div>
    <div class="modal-body overflow-auto">
        <div class="row-fluid" id="lead_info">
        </div>
    </div>
    <div class="modal-footer">
        <!--            <a class="btn btn-info btn-minier" id="more_lead_details">More details & history</a>-->
        <button class="btn btn-minier" data-dismiss="modal">
            <i class="icon-remove"></i>
            Cancel
        </button>
    </div>
</div>
<!-- lead info model box end -->
<?php
include_once 'footer.php';
?>
