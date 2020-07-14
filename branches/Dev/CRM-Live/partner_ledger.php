<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen',
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
    'js/chosen.jquery.min',
);
include_once 'header.php';
?>
<style type="text/css">
    table#dg_partner_ledger tfoot {
        display: table-header-group;
    }
</style>
<script type="text/javascript">
$(function() {

    $('.modal').on('shown.bs.modal', function () {
        $('.chzn-select', this).chosen({
            allow_single_deselect:true
        });

        $(".chzn-select").trigger("liszt:updated");
    });

    $('[data-rel=tooltip]').tooltip();
    var breakpointDefinition = {
        tablet: 1024,
        phone : 480
    };
    var responsiveHelper2 = undefined;
    dg_partner_ledger = $('#dg_partner_ledger').dataTable({
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
        "aLengthMenu": [[25,50,100], [25,50,100]],
        "sAjaxSource": "control/partner_ledger.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
        },
        "aaSorting": [[ 2, "asc" ]],
        "aoColumns": [
            {
                mData: "partner_ledger_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="partner_ledger_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            { "mData": "partner_name" },
            {"mData": "created_at"},
            {
                "mData": "particular",
                mRender: function(v,t,o){
                    var act_html =  (o['ledger_from'] == 'lead') ? "<a id='leadinfo_"+o['lead_id']+"' class='client_status lead-info pointer'>"+v+"</a>" : v;
                    return act_html;
                },
                "sClass":"not-click"
            },
            {"mData": "debit_amount"},
            {"mData": "credit_amount"},
            {"mData": "ledger_balance"},

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
            $("#partner_ledger_dd").hide();
        } else {
            $("#partner_ledger_dd").show();
        }
    })






    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code  
        $("#frm_partner_ledger").validate({
            rules:{
                partner_id:{required:true },
                payment_type_id:{required:true },
                amount:{required:true,number:true },
                remarks:{required:true}
            },
            messages:{
                partner_id:{required:'Please select partner'},
                payment_type_id:{required:'Please select payment type'},
                amount:{required:'Please enter amount',number:'Please enter only number'},
                remarks:{required:"Please enter remarks"}
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
                    url: 'control/partner_ledger.php?act=addedit',/*i have changed here*/
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
                            $('#modal_add_partner_ledger').modal('hide');
                            dg_partner_ledger.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }
    $('#add_partner_ledger').click(function(){
        $('form#frm_partner_ledger input,select.chzn-select').val('');
        $('form#frm_partner_ledger').find('div.control-group').removeClass("success error");
        $('form#frm_partner_ledger').find('div.control-group span.help-inline').text('');
        $('#act_partner_ledger').text('Add');
        $('#action').val('add');
        $('#modal_add_partner_ledger').modal('show');
    });

    $('#edit_partner_ledger').click( function (e) {

        var selected_list = $('#dg_partner_ledger tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select Partner Payout to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        var selected_tr = selected_list[0];
        var ele = $(selected_tr).closest('tr').get(0);
        //console.log(ele);
        var aData = dg_partner_ledger.fnGetData( ele );

        $.each(aData, function(key,val){
            var inputType = $('form#frm_partner_ledger #'+key).prop("type");
            if(inputType == 'checkbox'){
                if(val == 1){
                    $('form#frm_partner_ledger #'+key).prop("checked",true);
                } else {
                    $('form#frm_partner_ledger #'+key).prop("checked",false);
                }
            }else {
                if($('form#frm_partner_ledger #'+key).length){
                    $('form#frm_partner_ledger #'+key).val(val);
                }
            }
        });
        $('#act_partner_ledger').text('Edit');
        $('form#frm_partner_ledger').find('div.control-group').removeClass("success error");
        $('form#frm_partner_ledger').find('div.control-group span').text('');

        $('#action').val('edit');
        $('#partner_ledger_code').prop('readonly', true);
        $('#modal_add_partner_ledger').modal('show');
    });


    $('#delete_partner_ledger').click(function(){

        var delete_ele = $('#dg_partner_ledger tbody input[type=checkbox]:checked');
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
                        url: 'control/partner_ledger.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_partner_ledger.fnStandingRedraw();
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
    $('#edit_partner_ledger').click();
}

function DeleteRecord(fid){
    $('#chk_'+fid).prop('checked', true);
    $('#delete_partner_ledger').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Partner Ledger
                </div>
                <table id='dg_partner_ledger' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Partner</th>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>

                    </tr>
                    </thead>
                    <tbody>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<div id="modal_add_partner_ledger" class="modal hide" tabindex="-1">
    <form id='frm_partner_ledger' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_partner_ledger'>Add</span> Partner Payout</h4>
        </div>
        <div class="modal-body overflow-auto">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="partner_id">Partner<small class="text-error"> *</small></label>
                        <div class="controls">
                            <select id="partner_id" name="partner_id" data-placeholder="select partner" class="chzn-select">
                                <option></option>
                                <?php echo $partnerDd; ?>
                            </select>
                            <span for="partner_id" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="payment_type_id">Payment Type<small class="text-error"> *</small></label>
                        <div class="controls">
                            <select id="payment_type_id" name="payment_type_id" data-placeholder="select payment type" class="chzn-select">
                                <option></option>
                                <?php echo $paymentTypeDd; ?>
                            </select>
                            <span for="payment_type_id" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="amount">Amount<small class="text-error"> *</small></label>
                        <div class="controls">
                            <input type="tel" name="amount" id="amount" placeholder="ex. 1000"/>
                            <input type="hidden" name="partner_ledger_id" id="partner_ledger_id"/>
                            <input type="hidden" id='action' name='action' value="add"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="remarks">Remarks<small class="text-error"> *</small></label>
                        <div class="controls">
                            <textarea type="text" name="remarks" id="remarks"></textarea>
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
