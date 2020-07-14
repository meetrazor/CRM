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
$emergencyType = $db->GetEnumvalues("emergency_master","emergency_type");
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
        dg_emergency = $('#dg_emergency').dataTable({
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
            "sAjaxSource": "control/emergency.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'emergency_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="emergency_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"emergency_name" },
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
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Emergency', 'Edit Emergency')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['emergency_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Emergency', 'Delete Emergency')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['emergency_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            $("#frm_emergency").validate({
                rules:{
                    emergency_name:{required:true },
                    emergency_type:{required:true },
                    commission:{required:true,number:true },
                    effective_date:{required:true},
                    emergency_code:{required:true},
                },
                messages:{
                    emergency_name:{required:'Please enter emergency name'},
                    emergency_type:{required:'Please select emergency type'},
                    commission:{required:'Please enter commission',number:'Please enter number only'},
                    effective_date:{required:'Please select effective date'},
                    emergency_code:{required:"Please enter emergency code"},
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
                        url: 'control/emergency.php?act=addedit',/*i have changed here*/
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
                                $('#modal_add_emergency').modal('hide');
                                dg_emergency.fnDraw();
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
        $('#add_emergency').click(function(){
            $('form#frm_emergency input,select#emergency_type').val('');
            $('form#frm_emergency').find('div.control-group').removeClass("success error");
            $('form#frm_emergency').find('div.control-group span.help-inline').text('');
            $('#act_emergency').text('Add');
            $('#action').val('add');
            $('#emergency_code').prop('readonly', false);
            $('#modal_add_emergency').modal('show');
        });

        $('#edit_emergency').click( function (e) {

            var selected_list = $('#dg_emergency tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select emergency to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_emergency.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_emergency #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_emergency #'+key).prop("checked",true);
                    } else {
                        $('form#frm_emergency #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_emergency #'+key).length){
                        $('form#frm_emergency #'+key).val(val);
                    }
                }
            });

            $('#act_emergency').text('Edit');
            $('form#frm_emergency').find('div.control-group').removeClass("success error");
            $('form#frm_emergency').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_emergency').modal('show');
        });

        $("#modal_add_emergency").on("hidden", function () {
            $('#dg_emergency tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_emergency').click(function(){

            var delete_ele = $('#dg_emergency tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select emergency to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected emergency(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/emergency.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_emergency.fnStandingRedraw();
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

    function DeleteRecord(emergencyId){

        $('#ids_'+emergencyId).prop('checked', true);
        $('#delete_emergency').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_emergency').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Emergency List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Emergency', 'Add Emergency')))   {
                            ?>
                            <a id='add_emergency' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Emergency', 'Edit Emergency')))   {
                            ?>
                            <a id='edit_emergency' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Emergency', 'Delete Emergency')))   {
                            ?>
                            <a id='delete_emergency' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_emergency' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Emergency</th>
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
    <div id="modal_add_emergency" class="modal hide" tabindex="-1">
        <form id='frm_emergency' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_emergency'>Add</span> Emergency</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="emergency_name">Emergency Name</label>
                            <div class="controls">
                                <input type="text" name="emergency_name" id="emergency_name" placeholder="ex. In House"/>
                                <input type="hidden" name="emergency_id" id="emergency_id"/>
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