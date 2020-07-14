<?php
$asset_css = array(
		'css/jquery.gritter',
		'data-tables/responsive/css/datatables.responsive',
        'css/chosen'
);

$asset_js = array(
		'js/lodash/lodash.min',
		'data-tables/js/jquery.dataTables.min',
		'data-tables/js/DT_bootstrap',
		'data-tables/responsive/js/datatables.responsive',
        'js/jquery-validation/dist/jquery.validate.min',
        'js/jquery-validation/dist/jquery.validate.extension',
        'js/jquery-form/jquery.form',
        'js/jquery.gritter.min',
		'js/bootbox.min',
        'js/chosen.jquery.min',
);

include_once 'header.php';
$roleDd = $db->CreateOptions("html","role_master",array("role_id","role_name"),null,array("role_id"=>"ASC"));
?>
<script type="text/javascript">
$(document).ready(function() {

    $('#modal_change_parent').on('shown.bs.modal', function () {
        $('.chzn-select', this).chosen({
            allow_single_deselect: true,
        });
    });


    var breakpointDefinition = {
			pc: 1280,
	        tablet: 1024,
	        phone : 480
	};
	var responsiveHelper2 = undefined;
	dg_table = $('#dg_table').dataTable({
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
        "bScrollCollapse" : true,
        "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
	    "sAjaxSource": 'control/role_permission_master.php',
	    "fnServerParams": function ( aoData ) {
	    	aoData.push({ "name": "act", "value": "fetch" });
		},
		"aaSorting": [[ 1, "asc" ]],
		"aoColumns": [
			{ 
				mData: "role_id",
				bSortable : false,
				mRender: function (v, t, o) {
					return '<label><input type="checkbox" value="'+v+'"/><span class="lbl"></span></label>';
				},
				sClass: 'center',
			},
			{ "mData": "role_name" },

			{
				"mData": null,
                bSortable:false,
				mRender : function ( v, t, o){
					var p = '';
                    if(o['role_id'] != 47) {
                        <?php
                         if($acl->IsAllowed($login_id,'USERS', 'Role Permission', 'Edit Role Permission')){
                     ?>
                        p = '<a href="role_edit_permission.php?id='+o['role_id']+'&token=<?php echo $token; ?>"> Edit Permission</a> &nbsp;&nbsp;';
                        <?php } ?>

                            <?php
                             if($acl->IsAllowed($login_id,'USERS', 'Role Permission', 'Delete Role Permission')){
                         ?>
                        p += '<a href="javascript:void(0)" onclick="DeleteRow('+o['role_id']+')">Remove Permission</a>';
                        <?php } ?>
                    }
					return p;
				}
			 },
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
        	$('[data-rel=tooltip]').tooltip();
        }
	});

    if (jQuery().validate) {
        var e = function(e) {
            $(e).closest(".control-group").removeClass("success");
        };
        $("#frm_change_parent").validate({

            rules:{
                'role_id':{required:true},
                'parent_role_id':{required:true }
            },
            messages:{
                'role_id':{required:'Please select role'},
                'parent_role_id':{required:'Please select role'}
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
                    url: 'control/role_permission_master.php?act=transferchild',
                    type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                    },
                    dataType: 'json',
                    clearForm: false,
                    success: function (resObj, statusText) {
                        $(e).find('button').show();
                        if(resObj.success){
                            $('#modal_change_parent').modal('hide');
                            showGritter('success',resObj.title,resObj.msg);
                        }else{
                            showGritter('error', resObj.title, resObj.msg);
                        }
                    }
                });
            }
        });
    }

	$('#delete_record').click(function(){
		
		var delete_ele = $('#dg_table tbody input[type=checkbox]:checked');
		var selected_length = delete_ele.size();
		
		if(0 == selected_length){
			showGritter('info','Alert!','Please select a role to delete.');
			return false;
		}else if(selected_length > 1){
            showGritter('info','Alert!','Only single record can be deleted at a time.');
            return false;
        }else{
			bootbox.confirm("Are you sure to delete selected role(s) and its permissions?", function(result) {
				if(result) {
					
					var delete_id = delete_ele.val();

					$.ajax({
						url: 'control/role_permission_master.php?act=delete',
						type:'post',
						dataType:'json',
						data:{ id : delete_id, },
						success: function(resp){
                            if(resp.success){
                                dg_table.fnDraw();
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
    $('#transfer_child').click(function(){
        $('form#frm_change_parent').find('div.control-group').removeClass("success error");
        $('form#frm_change_parent').find('div.control-group span.help-inline').text('');
        $('#modal_change_parent').modal('show');
    });
    $("#role_id").change(function(){
        $('#parent_role_id').html(''); //Clear
        $('#role_id option:not(:selected)').clone().appendTo('#parent_role_id');
        $("#parent_role_id").trigger("liszt:updated");
    });
});

function DeleteRow(val){

	$('input[type=checkbox][value='+val+']').prop('checked', true);
	$('#delete_record').click();
}
</script>

<div class="row-fluid">
    <div class="span12">
    <div class="row-fluid">
        <div class='span12'>
        <div class="table-header">
            Role List
            <span class="widget-toolbar pull-right">

                <?php
                if($acl->IsAllowed($login_id,'USERS', 'Role Permission', 'Add Role Permission')){
                ?>
                <a id='add_record' href="role_add_permission.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>
                <?php }  ?>
                <?php
                    if($acl->IsAllowed($login_id,'USERS', 'Role Permission', 'Edit Role Permission')){
                ?>
                <a  id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                 <?php } ?>


                <?php
                if($acl->IsAllowed($login_id,'USERS', 'Role Permission', 'Delete Role dPermission')){
                    ?>
                <a id='transfer_child' style="cursor: pointer" data-placement="top" data-rel="tooltip" data-original-title="Transfer Child"><i class="icon-large white">Transfer Child</i></a>
                    <?php } ?>
            </span>
        </div>
        <table id='dg_table' class="table table-condensed table-bordered table-hover">
            <thead>
                <tr>
                    <th class="center" width="5%">
                        <label>
                            <input type="checkbox"/>
                            <span class="lbl"></span>
                        </label>
                    </th>
                    <th width="25%">Role Name</th>
                    <th width="25%">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

    <div id="modal_change_parent" class="modal hide" tabindex="-1">
        <form id='frm_change_parent'>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Child Transfer</h4>
            </div>
            <div class="modal-body overflow-auto">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="segment_name">Role Name</label>
                            <div class="controls">
                                <select id="role_id" name="role_id" class="chzn-select" data-placeholder="select role">
                                    <option></option>
                                    <?php echo $roleDd; ?>
                                </select>
                                <span for="role_id" class="help-inline"></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="segment_name">Transfer To</label>
                            <div class="controls">
                                <select id="parent_role_id" name="parent_role_id" class="chzn-select" data-placeholder="select role">
                                    <option></option>
                                </select>
                                <span for="parent_role_id" class="help-inline"></span>
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
<?php
include_once "footer.php";
?>