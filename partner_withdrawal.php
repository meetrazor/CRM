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
$partnerDd = $db->CreateOptions("html","partner_master",array("partner_id","concat(first_name,' ',last_name) as partner_name"),null,array("concat(first_name,' ',last_name)"=>"asc"),"is_active = 1");
$paymentTypeDd = $db->CreateOptions("html","payment_type_master",array("payment_type_id","payment_type_name"),null,array("payment_type_name"=>"asc"),"is_active = 1");
$partnerId = (isset($_GET['partner_id']) && !empty($_GET['partner_id'])) ? intval($db->FilterParameters($_GET['partner_id'])) : '';
$partnerName = "Partners";
if($partnerId != ''){
    $partnerName = $db->FetchCellValue("partner_master","concat(first_name,' ',last_name)","partner_id = '{$partnerId}'");
}
?>
<style type="text/css">
    table#dg_partner_withdrawal tfoot {
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

    $('.modal').on('shown.bs.modal', function () {
        $('.chzn-select', this).chosen({
            allow_single_deselect:true
        });

        $(".chzn-select").trigger("liszt:updated");
    });


    $("#partner_id").change(function(){
        var partnerId = $(this).val();
        $.ajax({
            url: 'control/partner_payout.php?act=partnerpay',
            type:'post',
            dataType:'html',
            data:{ partner_id : partnerId },
            success: function(resp){
                $("#max_payout").val(resp);
                $("#max_payout_text").html("Remaining balance is "+resp+"");
            }
        });
    })



    var responsiveHelper2 = undefined;
    dg_partner_withdrawal = $('#dg_partner_withdrawal').dataTable({
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
        "sAjaxSource": "control/partner_withdrawal.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" },{ "name": "partner_id", "value": "<?php echo $partnerId; ?>" });
            server_params = aoData;
        },
        "aaSorting": [[ 3, "desc" ]],
        "aoColumns": [
            {
                mData: "partner_withdrawal_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="partner_withdrawal_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },

            { "mData": "partner_name" },
            {"mData": "amount"},
            {"mData": "created_at"},
            {
                mData: null,
                bSortable:false,
                mRender: function(v,t,o){
                    var act_html = "";

                    <?php
                    if(($acl->IsAllowed($login_id,'PARTNER', 'Payout', 'Add Partner Payout')))   {
                    ?>
                    if(o['is_process'] != 1) {
                        act_html = act_html + "<a href='javascript:void(0);' onclick=\"AddPayout('"+ o['partner_withdrawal_id'] +"','"+o['amount']+"','"+o['partner_id']+"')\" class='btn btn-minier btn-primary' data-placement='buttom' data-rel='tooltip' data-original-title='Process Request' title='Process Request'><i class='icon-lightbulb bigger-120'></i>Process</a>&nbsp";
                    } else {
                        act_html = act_html + "<a href='javascript:void(0);'  class='btn btn-minier btn-success' data-placement='buttom' data-rel='tooltip' data-original-title='Request Processed' title='Request Process'><i class='icon-lightbulb bigger-120'></i>Processed</a>&nbsp";
                    }

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


    if (jQuery().validate) {

        $.validator.addMethod('lessThanEqual', function(value, element, param) {
            return this.optional(element) || parseInt(value) <= parseInt($(param).val());
        }, "The value must be less than remaining balance");


        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code
        $("#frm_partner_payout").validate({
            rules:{
                partner_id:{required:true },
                payment_type_id:{required:true },
                amount:{required:true,number:true,lessThanEqual:"#max_payout" },
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
                    url: 'control/partner_payout.php?act=addedit',/*i have changed here*/
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                        $('#loader').show();
                    },
                    complete: function(){
                        $('#loader').hide();
                        $(e).find('button').show();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $(e).clearForm();
                            $('#modal_add_partner_payout').modal('hide');
                            dg_partner_withdrawal.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }



});




function AddPayout(id,amount,partner_id){
    $('form#frm_partner_payout input,select.chzn-select').val('');
    $('form#frm_partner_payout').find('div.control-group').removeClass("success error");
    $('form#frm_partner_payout').find('div.control-group span.help-inline').text('');
    $('#act_partner_payout').text('Add');
    $('#partner_withdrawal_id').val(id);
    $('#partner_id option[value='+partner_id+']').prop("selected","selected");
    $("#partner_id option:not(:selected)").attr('disabled','disabled');
    $('#amount').val(amount);
    $('#action').val('add');
    $("#partner_id").change();
    $('#modal_add_partner_payout').modal('show');
}

</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    <?php echo $partnerName; ?> Withdrawal List
                </div>
                <table id='dg_partner_withdrawal' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Partner</th>
                        <th>Amount</th>
                        <th>Requested On</th>
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

<div id="modal_add_partner_payout" class="modal hide" tabindex="-1">
    <form id='frm_partner_payout' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_partner_payout'>Add</span> Partner Payout</h4>
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
                            <input type="hidden" name="partner_payout_id" id="partner_payout_id"/>
                            <input type="hidden" name="partner_withdrawal_id" id="partner_withdrawal_id"/>
                            <input type="hidden" name="max_payout" id="max_payout"/>
                            <input type="hidden" id='action' name='action' value="add"/>
                            <span class="help-block" id="max_payout_text"></span>
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
            <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                wait...
            </div>
        </div>
    </form>
</div>
<?php
include_once 'footer.php';
?>
