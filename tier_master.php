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
    'js/bootbox.min',
);
include_once 'header.php';
?>

    <script type="text/javascript">
    $(function() {

        $('[data-rel=tooltip]').tooltip();

        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_tier = $('#dg_tier').dataTable({
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
            "lengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/tier_master.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'tier_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="tier_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"tier_name" },
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
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier', 'Edit Tier')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['tier_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier', 'Delete Tier')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['tier_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            $("#frm_tier").validate({
                rules:{
                    tier_name:{required:true },
                },
                messages:{
                    tier_name:{required:'Please enter tier name'},
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
                        url: 'control/tier_master.php?act=addedit',/*i have changed here*/
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
                                $('#modal_add_tier').modal('hide');
                                dg_tier.fnDraw();
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
        $('#add_tier').click(function(){
            $('form#frm_tier input,select#tier_type').val('');
            $('form#frm_tier').find('div.control-group').removeClass("success error");
            $('form#frm_tier').find('div.control-group span.help-inline').text('');
            $('#act_tier').text('Add');
            $('#action').val('add');
            $('#tier_code').prop('readonly', false);
            $('#modal_add_tier').modal('show');
        });

        $('#edit_tier').click( function (e) {

            var selected_list = $('#dg_tier tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select tier to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_tier.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_tier #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_tier #'+key).prop("checked",true);
                    } else {
                        $('form#frm_tier #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_tier #'+key).length){
                        $('form#frm_tier #'+key).val(val);
                    }
                }
            });

            $('#act_tier').text('Edit');
            $('form#frm_tier').find('div.control-group').removeClass("success error");
            $('form#frm_tier').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_tier').modal('show');
        });

        $("#modal_add_tier").on("hidden", function () {
            $('#dg_tier tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_tier').click(function(){

            var delete_ele = $('#dg_tier tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select tier to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected tier(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/tier_master.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_tier.fnStandingRedraw();
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

    function DeleteRecord(tierId){

        $('#ids_'+tierId).prop('checked', true);
        $('#delete_tier').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_tier').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Tiers
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier', 'Add Tier')))   {
                            ?>
                            <a id='add_tier' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier', 'Edit Tier')))   {
                            ?>
                            <a id='edit_tier' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier', 'Delete Tier')))   {
                            ?>
                            <a id='delete_tier' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_tier' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Tier</th>
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
    <div id="modal_add_tier" class="modal hide" tabindex="-1">
        <form id='frm_tier' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_tier'>Add</span> Tier</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="tier_name">Tier Name</label>
                            <div class="controls">
                                <input type="text" name="tier_name" id="tier_name" placeholder="ex. In House"/>
                                <input type="hidden" name="tier_id" id="tier_id"/>
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