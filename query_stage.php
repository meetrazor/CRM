<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/chosen',
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
    'js/chosen.jquery.min',
    'js/jquery.gritter.min',
    'js/bootbox.min'
);
include_once 'header.php';
$loanDd = $db->CreateOptions("html", "loan_type_master", array("loan_type_id", "loan_type_name"), null, array("loan_type_name" => "asc"), "is_active = '1'");
$productTypeDd = $db->CreateOptions("html", "category_master", array("category_id", "category_name"), null, array("category_name" => "asc"), "is_active = '1'");
$reasonDd = $db->CreateOptions("html", "reason_master", array("reason_id", "reason_name"), null, array("reason_name" => "asc"), "is_active = '1'");
?>
<style type="text/css">
    table#dg_query_stage tfoot {
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

    $(".chzn-select").chosen({
        allow_single_deselect: true,
    });

    var responsiveHelper2 = undefined;
    dg_query_stage = $('#dg_query_stage').dataTable({
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
        "sAjaxSource": "control/query_stage.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
        },
        "aaSorting": [[ 1, "asc" ]],
        "aoColumns": [
            {
                mData: "query_stage_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="query_stage_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            { "mData": "loan_type_name" },
            { "mData": "category_name" },
            { "mData": "reason_name" },
            { "mData": "query_stage_name" },
            {
                "mData": "is_active",
                mRender: function(v,t,o){
                    return (v == 1) ? "Active" : "Inactive";
                }
            },
            {
                "mData": "is_default",
                mRender: function(v,t,o){
                    return (v == 1) ? "Yes" : "No";
                }
            },
            {
                mData: null,
                bSortable:false,
                mRender: function(v,t,o){
                    var act_html = "";

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Edit Query Stage')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['query_stage_id'] +"')\" class='btn btn-minier btn-warning' data-placement='buttom' data-rel='tooltip' data-original-title='Edit' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                    <?php } ?>

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Delete Query Stage')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['query_stage_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            $("#query_url").val('');
            $("#query_stage_dd").hide();
        } else {
            $("#query_stage_dd").show();
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

    $('#category_id').change(function(){
        var category_id = $(this).val();
        $.ajax({
            url: 'control/ticket_addedit.php?act=get_reason_dd', data : { id : category_id },type:'post',dataType: 'html',
            beforeSend: function(){
                $('#reason_loader').show();
            },
            complete: function(){
                $('#reason_loader').hide();
            },
            success: function(resp){
                $('#reason_id').html(resp);
                $("#reason_id").trigger("liszt:updated");
            }
        });
    });


    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code  
        $("#frm_query_stage").validate({
            rules:{
                loan_type_id:{ required:true },
                category_id:{ required:true },
                query_stage_name:{
                    required:true,
                    maxlength: 100,
                },
                reason_id:{
                    required:true,
                },
            },
            messages:{
                query_stage_name:{
                    required:'Please enter query Stage name',
                    maxlength:'Max length is 100 character',
                },
                reason_id:{required:'Please select reason'},
                loan_type_id: { required : 'Please select loan type'},
                category_id: { required : 'Please select product type'},
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
                    url: 'control/query_stage.php?act=addedit',/*i have changed here*/
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
                            $('#modal_add_query_stage').modal('hide');
                            dg_query_stage.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            if(resObj.hasOwnProperty("errors")){
                                var message = '';
                                $.each(resObj.errors,function(key,value){
                                    message += value + "<br>";
                                });
                                showGritter('error',"Error",message);
                            }else{
                                showGritter('error', resObj.title, resObj.msg);
                            }
                        }
                    }
                });
            }
        });
    }
    $('#add_query_stage').click(function(){
        $('form#frm_query_stage input,select').val('');
        $('form#frm_query_stage').find('div.control-group').removeClass("success error");
        $('form#frm_query_stage').find('div.control-group span').text('');
        $('#act_query_stage').text('Add');
        $('#action').val('add');
        $('#modal_add_query_stage').modal('show');
    });

    $("#modal_add_query_stage").on("hidden", function () {
        $('#dg_query_stage tbody input[type=checkbox]:checked').prop("checked",false);
    });

    $('#modal_add_query_stage').on('hidden.bs.modal', function() {
        $(this)
            .find("input,textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
    });

    $('#edit_query_stage').click( function (e) {

        var selected_list = $('#dg_query_stage tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select query Stage to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        var selected_tr = selected_list[0];
        var ele = $(selected_tr).closest('tr').get(0);
        //console.log(ele);
        var aData = dg_query_stage.fnGetData( ele );

        $.each(aData, function(key,val){
            var inputType = $('form#frm_query_stage #'+key).prop("type");
            if(inputType == 'checkbox'){
                if(val == 1){
                    $('form#frm_query_stage #'+key).prop("checked",true);
                } else {
                    $('form#frm_query_stage #'+key).prop("checked",false);
                }
            }else {
                if($('form#frm_query_stage #'+key).length){
                    $('form#frm_query_stage #'+key).val(val);
                }
            }
        });
        $('#act_query_stage').text('Edit');
        $('form#frm_query_stage').find('div.control-group').removeClass("success error");
        $('form#frm_query_stage').find('div.control-group span').text('');

        $('#action').val('edit');
        $('#query_stage_code').prop('readonly', true);
        $('#modal_add_query_stage').modal('show');
    });


    $('#delete_query_stage').click(function(){

        var delete_ele = $('#dg_query_stage tbody input[type=checkbox]:checked');
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
                        url: 'control/query_stage.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_query_stage.fnStandingRedraw();
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

    $('#modal_add_query_stage').hide();


});

function EditRecord(id){
    $('#chk_'+id).prop('checked', true);
    $('#edit_query_stage').click();
}

function DeleteRecord(fid){
    $('#chk_'+fid).prop('checked', true);
    $('#delete_query_stage').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Query Stage List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Add Query Stage')))   {
                            ?>
                            <a id='add_query_stage' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Edit Query Stage')))   {
                            ?>
                            <a id='edit_query_stage' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Delete Query Stage')))   {
                            ?>
                            <a id='delete_query_stage' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_query_stage' class="table table-condensed table-bordered table-hover">
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
                        <th>Reason Name</th>
                        <th>Query Stage</th>
                        <th>Status</th>
                        <th>Default</th>
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
<div id="modal_add_query_stage" class="modal" tabindex="-1">
    <form id='frm_query_stage' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_query_stage'>Add</span> Query Stage</h4>
        </div>
        <div class="modal-body overflow-auto">
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
                        <label for="reason" class="control-label">Reason </label>
                        <div class="controls">
                            <select id="reason_id" name="reason_id" data-placeholder="Select Reason"
                                    class="">
                                <option></option>
                                <?php echo $reasonDd; ?>
                            </select>
                            <i id='reason_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                            <span for="reason_id" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="query_stage_name">Query Stage</label>
                        <div class="controls">
                            <input type="text" name="query_stage_name" id="query_stage_name" placeholder="ex. GST"/>
                            <input type="hidden" name="query_stage_id" id="query_stage_id"/>
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
                            <span class="lbl"> Default</span>
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
