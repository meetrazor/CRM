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
$dispositionDd = $db->CreateOptions("html","disposition_master",array("disposition_id","disposition_name"),null,array("disposition_name"=>"ASC"));
$dispositionType = $db->GetEnumvalues("disposition_master","disposition_type");
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
        dg_disposition = $('#dg_disposition').dataTable({
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
            "sAjaxSource": "control/disposition.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'disposition_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="disposition_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"disposition_type" },
                { "mData":"disposition_name" },
                { "mData":"disposition_code" },
                { "mData":"parent_disposition_name" },
                {
                    "mData": "is_close",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
                {
                    "mData": "is_callback",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
                {
                    "mData": "is_meeting",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
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
                {"mData": "prospect_count"},
                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Disposition', 'Edit Disposition')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['disposition_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Disposition', 'Delete Disposition')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['disposition_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            $("#frm_disposition").validate({
                rules:{
                    disposition_name:{required:true },
                    disposition_code:{required:true },
                    disposition_type:{required:true },
                    commission:{required:true,number:true },
                    effective_date:{required:true},
                },
                messages:{
                    disposition_name:{required:'Please enter disposition name'},
                    disposition_type:{required:'Please select disposition type'},
                    commission:{required:'Please enter commission',number:'Please enter number only'},
                    effective_date:{required:'Please select effective date'},
                    disposition_code:{required:"Please enter disposition code"},
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
                        url: 'control/disposition.php?act=addedit',/*i have changed here*/
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
                                $('#modal_add_disposition').modal('hide');
                                dg_disposition.fnDraw();
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
        $('#add_disposition').click(function(){
            $('form#frm_disposition input,select#disposition_type').val('');
            $('form#frm_disposition').find('div.control-group').removeClass("success error");
            $('form#frm_disposition').find('div.control-group span.help-inline').text('');
            $('#act_disposition').text('Add');
            $('#action').val('add');
            $('#disposition_code').prop('readonly', false);
            $('#modal_add_disposition').modal('show');
        });

        $('#edit_disposition').click( function (e) {

            var selected_list = $('#dg_disposition tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select disposition to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_disposition.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_disposition #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_disposition #'+key).prop("checked",true);
                    } else {
                        $('form#frm_disposition #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_disposition #'+key).length){
                        $('form#frm_disposition #'+key).val(val);
                    }
                }
            });

            $('#act_disposition').text('Edit');
            $('form#frm_disposition').find('div.control-group').removeClass("success error");
            $('form#frm_disposition').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_disposition').modal('show');
        });

        $("#modal_add_disposition").on("hidden", function () {
            $('#dg_disposition tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_disposition').click(function(){

            var delete_ele = $('#dg_disposition tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select disposition to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected disposition(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/disposition.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_disposition.fnStandingRedraw();
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

    function DeleteRecord(dispositionId){

        $('#ids_'+dispositionId).prop('checked', true);
        $('#delete_disposition').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_disposition').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Disposition List
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Disposition', 'Add Disposition')))   {
                            ?>
                            <a id='add_disposition' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Disposition', 'Edit Disposition')))   {
                            ?>
                            <a id='edit_disposition' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Disposition', 'Delete Disposition')))   {
                            ?>
                            <a id='delete_disposition' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_disposition' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Type</th>
                            <th>Disposition</th>
                            <th>Code</th>
                            <th>Parent Disposition</th>
                            <th>Is Close</th>
                            <th>Callback</th>
                            <th>Meeting</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Total Prospect</th>
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
    <div id="modal_add_disposition" class="modal hide" tabindex="-1">
        <form id='frm_disposition' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_disposition'>Add</span> Disposition</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">

                        <div class="control-group">
                            <label class="control-label" for="disposition_type">Disposition Type</label>
                            <div class="controls">

                                <select  name="disposition_type" id="disposition_type" class="chzn-select" data-placeholder="Select Disposition Type">
                                    <option></option>
                                    <?php
                                    if(count($dispositionType) > 0){
                                        foreach($dispositionType as $dispositionData) { ?>
                                            <option value="<?php echo $dispositionData; ?>"><?php echo ucwords($dispositionData); ?></option>
                                        <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <span for="disposition_type" class="help-inline"></span>
                                <input type="hidden" name='action' value="<?php echo $action; ?>"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="disposition_name">Disposition Name</label>
                            <div class="controls">
                                <input type="text" name="disposition_name" id="disposition_name" placeholder="ex. In House"/>
                                <input type="hidden" name="disposition_id" id="disposition_id"/>
                                <input type="hidden" id='action' name='action' value="add"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="disposition_code">Disposition Code</label>
                            <div class="controls">
                                <input type="text" name="disposition_code" id="disposition_code" placeholder="ex. A"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="parent_disposition_id">Parent Disposition</label>
                            <div class="controls">
                                <select name="parent_disposition_id" id="parent_disposition_id">
                                    <option></option>
                                    <?php echo $dispositionDd; ?>
                                </select>
                            </div>
                        </div>

                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_close" id="is_close">
                                <span class="lbl">Is Close</span>
                            </label>
                        </div>

                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_callback" id="is_callback">
                                <span class="lbl"> Callback</span>
                            </label>
                        </div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_meeting" id="is_meeting">
                                <span class="lbl"> Meeting</span>
                            </label>
                        </div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" checked>
                                <span class="lbl"> Active</span>
                            </label>
                        </div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_default" id="is_default">
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