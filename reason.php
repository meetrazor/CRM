<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen',
    'css/bootstrap-timepicker',
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
    'js/chosen.jquery.min',
    'js/bootbox.min',
    'js/date-time/bootstrap-datepicker.min',
);
include_once 'header.php';
$loanDd = $db->CreateOptions("html", "loan_type_master", array("loan_type_id", "loan_type_name"), null, array("loan_type_name" => "asc"), "is_active = '1'");
$productTypeDd = $db->CreateOptions("html", "category_master", array("category_id", "category_name"), null, array("category_name" => "asc"), "is_active = '1'");
$reasonType = $db->GetEnumvalues("reason_master","reason_type");
?>

    <script type="text/javascript">
    $(function() {

        $('.modal').on('shown.bs.modal', function () {
            $('.chzn-select', this).chosen({
                allow_single_deselect:true
            });
            $(".chzn-select option[value='Lead']").prop("selected","selected");
            $(".chzn-select").trigger("liszt:updated");
        });

        $('[data-rel=tooltip]').tooltip();


        $('.date-picker').datepicker({
            orientation: 'top',
            autoclose: true,
        }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_reason = $('#dg_reason').dataTable({
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
                    sNext: '<i class="icon-angle-right"></i>',
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/reason.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'reason_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="reason_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"loan_type_name" },
                { "mData":"category_name" },
                { "mData":"reason_name" },
                {
                    "mData": "is_active",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Active" : "Inactive";
                    }
                },
                {
                    "mData": "is_default",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Reason', 'Edit Reason')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['reason_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Reason', 'Delete Reason')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['reason_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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

        // loan type change event
        $('#loan_type_id').change(function() {
            var loan_type_id = $(this).val();
            $.ajax({
                url: 'control/reason.php?act=get_product_type_dd',
                data: {id: loan_type_id},
                type: 'post',
                dataType: 'html',
                beforeSend: function () {
                    $('#product_type_loader').show();
                },
                complete: function () {
                    $('#product_type_loader').hide();
                },
                success: function (resp) {
                    $('#category_id').html(resp);
                    $("#category_id").trigger("liszt:updated");
                }
            });
        });

        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };
            // Company type validateion code  
            $("#frm_reason").validate({
                rules:{
                    loan_type_id:{required:true },
                    category_id:{required:true },
                    reason_name:{required:true },
                    reason_type:{required:true },
                    commission:{required:true,number:true },
                    effective_date:{required:true},
                    reason_code:{required:true},
                },
                messages:{
                    loan_type_id:{required:'Please select loan type'},
                    category_id:{required:'Please select product type'},
                    reason_name:{required:'Please enter reason name'},
                    reason_type:{required:'Please select reason type'},
                    commission:{required:'Please enter commission',number:'Please enter number only'},
                    effective_date:{required:'Please select effective date'},
                    reason_code:{required:"Please enter reason code"},
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
                        url: 'control/reason.php?act=addedit',/*i have changed here*/
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
                                $('#modal_add_reason').modal('hide');
                                dg_reason.fnDraw();
                                showGritter('success',resObj.title,resObj.msg);
                            }else{
                                if(resObj.hasOwnProperty("errors")){
                                    var message = '';
                                    $.each(resObj.errors,function(key,value){
                                        message += value + "<br>";
                                        showGritter('error',"Error",message);
                                    });
                                } else {
                                    showGritter('error',resObj.title,resObj.msg);
                                }
                            }
                        }
                    });
                }
            });
        }
        $('#add_reason').click(function(){
            $('form#frm_reason input,select#reason_type').val('');
            $('form#frm_reason').find('div.control-group').removeClass("success error");
            $('form#frm_reason').find('div.control-group span.help-inline').text('');
            $('#act_reason').text('Add');
            $('#action').val('add');
            $('#reason_code').prop('readonly', false);
            $('#modal_add_reason').modal('show');
        });

        $('#edit_reason').click( function (e) {

            var selected_list = $('#dg_reason tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select reason to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_reason.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_reason #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_reason #'+key).prop("checked",true);
                    } else {
                        $('form#frm_reason #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_reason #'+key).length){
                        $('form#frm_reason #'+key).val(val);
                    }
                }
            });

            $('#act_reason').text('Edit');
            $('form#frm_reason').find('div.control-group').removeClass("success error");
            $('form#frm_reason').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_reason').modal('show');
        });

        $("#modal_add_reason").on("hidden", function () {
            $('#dg_reason tbody input[type=checkbox]:checked').prop("checked",false);
            $('select').val('0');
        });

        $('#modal_add_reason').on('hidden.bs.modal', function() {
            $(this)
                .find("input,textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();
        });

        $('#delete_reason').click(function(){

            var delete_ele = $('#dg_reason tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select reason to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected reason(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/reason.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_reason.fnStandingRedraw();
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

    function DeleteRecord(reasonId){

        $('#ids_'+reasonId).prop('checked', true);
        $('#delete_reason').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_reason').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Reason List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Reason', 'Add Reason')))   {
                            ?>
                            <a id='add_reason' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Reason', 'Edit Reason')))   {
                            ?>
                            <a id='edit_reason' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Reason', 'Delete Reason')))   {
                            ?>
                            <a id='delete_reason' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_reason' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Loan Type</th>
                            <th>Product Type</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="modal_add_reason" class="modal hide" tabindex="-1">
        <form id='frm_reason' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_reason'>Add</span> Reason</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">

                        <div class="control-group">
                            <label for="loan" class="control-label">Loan Type </label>
                            <div class="controls">
                                <select id="loan_type_id" name="loan_type_id" data-placeholder="Select Loan Type"
                                        class="">
                                    <option></option>
                                    <?php echo $loanDd; ?>
                                </select>
                                <span for="loan_type_id" class="help-inline"></span>
                            </div>
                        </div>

                        <div class="control-group">
                            <label for="product_type" class="control-label">Product Type </label>
                            <div class="controls">
                                <select id="category_id" name="category_id" data-placeholder="Select Loan Type"
                                        class="">
                                    <option></option>
                                    <?php echo $productTypeDd; ?>
                                </select>
                                <i id='product_type_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                                <span for="category_id" class="help-inline"></span>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="reason_name">Reason Name</label>
                            <div class="controls">
                                <input type="text" name="reason_name" id="reason_name" placeholder="ex. Information"/>
                                <input type="hidden" name="reason_id" id="reason_id"/>
                                <input type="hidden" id='action' name='action' value="add"/>
                            </div>
                        </div>

                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" checked>
                                <span class="lbl"> Active</span>
                            </label>
                        </div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_default" id="is_default" checked>
                                <span class="lbl"> Is Default</span>
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
                <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                    wait...
                </div>
            </div>
        </form>
    </div>
<?php
include_once 'footer.php';
?>