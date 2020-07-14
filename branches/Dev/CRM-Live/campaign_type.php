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
?>
<style type="text/css">
    table#dg_campaign_type tfoot {
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
    dg_campaign_type = $('#dg_campaign_type').dataTable({
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
        "sAjaxSource": "control/campaign_type.php",//change here

        "fnServerParams": function ( aoData ) { //send other data to server side
            aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
        },
        "aaSorting": [[ 1, "asc" ]],
        "aoColumns": [
            {
                mData: "campaign_type_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="campaign_type_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            {"mData": "campaign_type_name"},
            {"mData": "sort_code"},
            {
                "mData": "is_active",
                bSortable:false,
                mRender: function(v,t,o){
                    return (v == 1) ? "Active" : "Inactive";
                }
            },
            {
                mData: null,
                bSortable:false,
                mRender: function(v,t,o){
                    var act_html = "";

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Campaign Type', 'Edit Campaign Type')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['campaign_type_id'] +"')\" class='btn btn-minier btn-warning' data-placement='buttom' data-rel='tooltip' data-original-title='Edit' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                    <?php } ?>

                    <?php
                    if(($acl->IsAllowed($login_id,'MASTERS', 'Campaign Type', 'Delete Campaign Type')))   {
                    ?>
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['campaign_type_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        // Company type validateion code  
        $("#frm_campaign_type").validate({
            rules:{
                campaign_type_name:{required:true },
                sort_code:{required:true}
            },
            messages:{
                campaign_type_name:{required:'Please enter campaign type'}
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
                    url: 'control/campaign_type.php?act=addedit',/*i have changed here*/
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
                            $('#modal_add_campaign_type').modal('hide');
                            dg_campaign_type.fnDraw();
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }
    $('#add_campaign_type').click(function(){
        $('form#frm_campaign_type input,select').val('');
        $('form#frm_campaign_type').find('div.control-group').removeClass("success error");
        $('form#frm_campaign_type').find('div.control-group span').text('');
        $('#act_campaign_type').text('Add');
        $('#action').val('add');
        $('#modal_add_campaign_type').modal('show');
    });

    $('#edit_campaign_type').click( function (e) {

        var selected_list = $('#dg_campaign_type tbody input[type=checkbox]:checked');
        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select campaign type to edit.');
            return false;
        }else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be edited at a time.');
            return false;
        }

        var selected_tr = selected_list[0];
        var ele = $(selected_tr).closest('tr').get(0);
        //console.log(ele);
        var aData = dg_campaign_type.fnGetData( ele );

        $.each(aData, function(key,val){
            var inputType = $('form#frm_campaign_type #'+key).prop("type");
            if(inputType == 'checkbox'){
                if(val == 1){
                    $('form#frm_campaign_type #'+key).prop("checked",true);
                } else {
                    $('form#frm_campaign_type #'+key).prop("checked",false);
                }
            }else {
                if($('form#frm_campaign_type #'+key).length){
                    $('form#frm_campaign_type #'+key).val(val);
                }
            }
        });
        $('#act_campaign_type').text('Edit');
        $('form#frm_campaign_type').find('div.control-group').removeClass("success error");
        $('form#frm_campaign_type').find('div.control-group span').text('');

        $('#action').val('edit');
        $('#campaign_type_code').prop('readonly', true);
        $('#modal_add_campaign_type').modal('show');
    });


    $('#delete_campaign_type').click(function(){

        var delete_ele = $('#dg_campaign_type tbody input[type=checkbox]:checked');
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
                        url: 'control/campaign_type.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_campaign_type.fnStandingRedraw();
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

function EditRecord(id){
    $('#chk_'+id).prop('checked', true);
    $('#edit_campaign_type').click();
}

function DeleteRecord(fid){
    $('#chk_'+fid).prop('checked', true);
    $('#delete_campaign_type').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Campaign Type List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Campaign Type', 'Add Campaign Type')))   {
                            ?>
                            <a id='add_campaign_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Campaign Type', 'Edit Campaign Type')))   {
                            ?>
                            <a id='edit_campaign_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Campaign Type', 'Delete Campaign Type')))   {
                            ?>
                            <a id='delete_campaign_type' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_campaign_type' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Campaign Type</th>
                        <th>Sort Code</th>
                        <th>Status</th>
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
<div id="modal_add_campaign_type" class="modal hide" tabindex="-1">
    <form id='frm_campaign_type' class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger"><span id='act_campaign_type'>Add</span> Campaign Type</h4>
        </div>
        <div class="modal-body overflow-auto">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <label class="control-label" for="campaign_type_name">Campaign Type</label>
                        <div class="controls">
                            <input type="text" name="campaign_type_name" id="campaign_type_name" placeholder="ex. 2 Wheeler"/>
                            <input type="hidden" name="campaign_type_id" id="campaign_type_id"/>
                            <input type="hidden" id='action' name='action' value="add"/>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="sort_code">Sort Code</label>
                        <div class="controls">
                            <input type="text" name="sort_code" id="sort_code" placeholder="ex. sms"/>
                        </div>
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
            <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                wait...
            </div>
        </div>
    </form>
</div>
<?php
include_once 'footer.php';
?>
