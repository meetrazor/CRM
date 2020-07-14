<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/colorpicker'
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
    'js/bootstrap-colorpicker.min',
);
include_once 'header.php';
?>
<style type="text/css">
    table#dg_query_type tfoot {
        display: table-header-group;
    }
    .colorpicker { z-index: 9999; }
</style>
<script type="text/javascript">
$(function() {
    $('[data-rel=tooltip]').tooltip();
    var breakpointDefinition = {
        tablet: 1024,
        phone : 480
    };
    var responsiveHelper2 = undefined;
    dg_query_type = $('#dg_query_type').dataTable({
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
        "sAjaxSource": "control/query_type.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
        },
        "aaSorting": [[ 1, "asc" ]],
        "aoColumns": [
            {
                mData: "query_type_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="query_type_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            { "mData": "query_type_name" },
            { "mData": "query_color" },
            {
                "mData": "is_active",
                bSortable:false,
                mRender: function(v,t,o){
                    return (v == 1) ? "Active" : "Inactive";
                }
            },
            {
                "mData": "is_default",
                bSortable:false,
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
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Query Type', 'Edit Query Type')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['query_type_id'] +"')\" class='btn btn-minier btn-warning' data-placement='buttom' data-rel='tooltip' data-original-title='Edit' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                    <?php } ?>

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Query Type', 'Delete Query Type')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['query_type_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
        fnRowCallback  : function (nRow, aData, iDisplayIndex) {

            $('td', nRow).css('background-color', aData.query_color);
            responsiveHelper2.createExpandIcon(nRow);
            return nRow;
        },
        fnDrawCallback : function (oSettings) {
            responsiveHelper2.respond();
            $(this).removeAttr('style');
        }
    });


    $("#is_file_upload").click(function(){
        if($(this).prop('checked')) {
            $("#query_url").val('');
            $("#query_type_dd").hide();
        } else {
            $("#query_type_dd").show();
        }
    })






    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code  
        $("#frm_query_type").validate({
            rules:{
                query_type_name:{required:true },
                query_url:{url:true }
            },
            messages:{
                query_type_name:{required:'Please enter query type'}
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
                    url: 'control/query_type.php?act=addedit',/*i have changed here*/
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
                            $('#modal_add_query_type').modal('hide');
                            dg_query_type.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }
    $('#add_query_type').click(function(){
        $('form#frm_query_type input,select').val('');
        $('form#frm_query_type').find('div.control-group').removeClass("success error");
        $('form#frm_query_type').find('div.control-group span').text('');
        $('#act_query_type').text('Add');
        $('#action').val('add');
        $('#modal_add_query_type').modal('show');
    });

    $('#edit_query_type').click( function (e) {

        var selected_list = $('#dg_query_type tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select query type to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        var selected_tr = selected_list[0];
        var ele = $(selected_tr).closest('tr').get(0);
        //console.log(ele);
        var aData = dg_query_type.fnGetData( ele );

        $.each(aData, function(key,val){
            var inputType = $('form#frm_query_type #'+key).prop("type");
            if(inputType == 'checkbox'){
                if(val == 1){
                    $('form#frm_query_type #'+key).prop("checked",true);
                } else {
                    $('form#frm_query_type #'+key).prop("checked",false);
                }
            }else {
                if($('form#frm_query_type #'+key).length){
                    $('form#frm_query_type #'+key).val(val);
                }
            }
        });
        $('#act_query_type').text('Edit');
        $('form#frm_query_type').find('div.control-group').removeClass("success error");
        $('form#frm_query_type').find('div.control-group span').text('');

        $('#action').val('edit');
        $('#query_type_code').prop('readonly', true);
        //$('#query_color').colorpicker();
        $('#modal_add_query_type').modal('show');
    });


    $('#delete_query_type').click(function(){

        var delete_ele = $('#dg_query_type tbody input[type=checkbox]:checked');
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
                        url: 'control/query_type.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_query_type.fnStandingRedraw();
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

    $('#query_color').colorpicker();

});

function EditRecord(id){
    $('#chk_'+id).prop('checked', true);
    $('#edit_query_type').click();
}

function DeleteRecord(fid){
    $('#chk_'+fid).prop('checked', true);
    $('#delete_query_type').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Query Type List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Type', 'Add Query Type')))   {
                            ?>
                            <a id='add_query_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Type', 'Edit Query Type')))   {
                            ?>
                            <a id='edit_query_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Type', 'Delete Query Type')))   {
                            ?>
                            <a id='delete_query_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_query_type' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Query Type</th>
                        <th>Query Color</th>
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
<div id="modal_add_query_type" class="modal hide" tabindex="-1">
    <form id='frm_query_type' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_query_type'>Add</span> Query Type</h4>
        </div>
        <div class="modal-body overflow-auto">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="query_type_name">Query Type</label>
                        <div class="controls">
                            <input type="text" name="query_type_name" id="query_type_name" placeholder="ex. Priority"/>
                            <input type="hidden" name="query_type_id" id="query_type_id"/>
                            <input type="hidden" id='action' name='action' value="add"/>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="query_color">Color</label>
                        <div class="controls">
                            <div class="bootstrap-colorpicker">
                                <input id="query_color" name="query_color" type="text" class="input-mini" id />
                            </div>
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
