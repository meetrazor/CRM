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
        dg_tutorial = $('#dg_tutorial').dataTable({
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
            "sAjaxSource": "control/tutorial.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'tutorial_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="tutorial_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"tutorial_title" },
                { "mData":"youtube_id" },
                { "mData":"message"},
                { "mData":"created_at"},
                { "mData":"created_by"},

                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if(($acl->IsAllowed($login_id,'TUTORIAL', 'tutorial', 'Edit tutorial')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['tutorial_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'TUTORIAL', 'tutorial', 'Delete tutorial')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['tutorial_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            $("#frm_tutorial").validate({
                rules:{
                    tutorial_title:{required:true },
                    message:{required:true },
                    youtube_id:{required:true }
                },
                messages:{
                    tutorial_title:{required:'Please enter tutorial title'},
                    message:{required:'Please enter message'},
                    youtube_id:{required:'Please enter youtube id'}
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
                        url: 'control/tutorial.php?act=addedit',/*i have changed here*/
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
                                $('#modal_add_tutorial').modal('hide');
                                dg_tutorial.fnDraw();
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
        $('#add_tutorial').click(function(){
            $('form#frm_tutorial input,select#tutorial_type').val('');
            $('form#frm_tutorial').find('div.control-group').removeClass("success error");
            $('form#frm_tutorial').find('div.control-group span.help-inline').text('');
            $('#act_tutorial').text('Add');
            $('#action').val('add');
            $('#tutorial_code').prop('readonly', false);
            $('#modal_add_tutorial').modal('show');
        });

        $('#edit_tutorial').click( function (e) {

            var selected_list = $('#dg_tutorial tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select tutorial to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_tutorial.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_tutorial #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_tutorial #'+key).prop("checked",true);
                    } else {
                        $('form#frm_tutorial #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_tutorial #'+key).length){
                        $('form#frm_tutorial #'+key).val(val);
                    }
                }
            });

            $('#act_tutorial').text('Edit');
            $('form#frm_tutorial').find('div.control-group').removeClass("success error");
            $('form#frm_tutorial').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_tutorial').modal('show');
        });

        $("#modal_add_tutorial").on("hidden", function () {
            $('#dg_tutorial tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_tutorial').click(function(){

            var delete_ele = $('#dg_tutorial tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select tutorial to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected tutorial(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/tutorial.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_tutorial.fnStandingRedraw();
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

    function DeleteRecord(tutorialId){

        $('#ids_'+tutorialId).prop('checked', true);
        $('#delete_tutorial').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_tutorial').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Tutorials
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'TUTORIAL', 'tutorial', 'Add tutorial')))   {
                            ?>
                            <a id='add_tutorial' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'TUTORIAL', 'tutorial', 'Edit tutorial')))   {
                            ?>
                            <a id='edit_tutorial' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'TUTORIAL', 'tutorial', 'Delete tutorial')))   {
                            ?>
                            <a id='delete_tutorial' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_tutorial' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Title</th>
                            <th>Youtube Id</th>
                            <th>Message</th>
                            <th>Created On</th>
                            <th>Created by</th>
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
    <div id="modal_add_tutorial" class="modal hide" tabindex="-1">
        <form id='frm_tutorial' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_tutorial'>Add</span> tutorial</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="tutorial_title">Title<small class="text-error"> *</small></label>
                            <div class="controls">
                                <input type="text" name="tutorial_title" id="tutorial_title" placeholder="ex. Tutorial App"/>
                                <input type="hidden" name="tutorial_id" id="tutorial_id"/>
                                <input type="hidden" id='action' name='action' value="add"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="youtube_id">Youtube Id<small class="text-error"> *</small></label>
                            <div class="controls">
                                <input type="text" name="youtube_id" id="youtube_id" placeholder="ex. yzXK2d3aoDc"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="message">Message<small class="text-error"> *</small></label>
                            <div class="controls">
                                <textarea class="input-xlarge"  name="message" id="message"></textarea>
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