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
		'js/jquery.gritter.min',
		'js/bootbox.min',
        'js/jquery-validation/dist/jquery.validate.min',
        'js/jquery-validation/dist/jquery.validate.extension',
        'js/jquery-form/jquery.form'
);

include_once 'header.php';
?>
    <style type="text/css">
        table#dg_user tfoot {
            display: table-header-group;
        }
    </style>
<script type="text/javascript">
$(document).ready(function(){

$('[data-rel=tooltip]').tooltip();
	
	var breakpointDefinition = {
			pc: 1280,
	        tablet: 1024,
	        phone : 480
	};
	var responsiveHelper2 = undefined;
	dg_user = $('#dg_user').dataTable({
        "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
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
        "sAjaxSource": "control/users.php",
	    "fnServerParams": function ( aoData ) {
	    	aoData.push({ "name": "act", "value": "fetch" });
            server_params = aoData;
		},
		"aaSorting": [[ 1, "asc" ]],
        "aoColumns": [
            {
                mData: "user_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center'
            },
            {
                "mData": "full_name",
                mRender: function (v, t, o) {
                    return v;
                }
            },
            { "mData": "user_type_name" },
            { "mData": "role_name" },
            { "mData": "reporting_to" },
            { "mData": "email" },
            { "mData": "mobile_no" },

            {
                "mData": "updated_at"
            },
            {
                "mData": "rcd_updated_by"
            },
            {
                "mData": "is_active",
                mRender: function (v,t,o){
                    return (v == '1') ? '<i class="icon-user green"></i>' : '<i class="icon-user red"></i>';
                }
            },
            {
                "mData":"activity_count",
                bSortable: false,
                mRender: function(v,t,o){
                    var act_html = '';
                    act_html = act_html + "<a href='activity.php?token=<?php echo $token; ?>&user_id="+o['user_id']+"'  data-rel='tooltip' title='Total Activity'>"+o['activity_count']+" </a>&nbsp";
                    return act_html;
                }
            },
            {
                bSortable :false,
                mData: null,
                mRender: function(v,t,o){
                    var act_html = '';
                    act_html = act_html + "<a href='user_add.php?id="+ o['user_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['full_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                    act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['user_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                    return act_html;
                }
            }
		],
		fnPreDrawCallback: function () {
			if (!responsiveHelper2) {
				responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
			}
		},
        "fnRowCallback" : function(nRow, aData, iDisplayIndex){
            responsiveHelper2.createExpandIcon(nRow);
            return nRow;
        },
        fnDrawCallback : function (oSettings) {
        	responsiveHelper2.respond();
        	$(this).removeAttr('style');
        	$('[data-rel=tooltip]').tooltip();
        }
	});

    $("tfoot input").keyup( function () {
        dg_user.fnFilter( this.value, $(this).attr("colPos") );
    });



    if (jQuery().validate) {

        var e = function(e) {

            $(e).closest(".control-group").removeClass("has-success");

        };

        $("#frm_change").validate({

            rules: {
                password:{required: true, minlength: 6 },
                confirm_password:{required: true, minlength: 6, equalTo: "#password"},
            },

            messages: {
                password:{required: 'new password is required', minlength: 'password must be great or equal to 6 character long' },
                confirm_password:{required: 'confirm password is required', minlength: 'password must be great or equal to 6 character long', equalTo: 'Confirm password does not match' }

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

                    url: 'control/users.php?act=resetpassword',

                    type:'post',

                    beforeSubmit: function (formData, jqForm, options) {

                        $(e).find('button').attr('disabled', true);

                    },

                    dataType: 'json',

                    clearForm: false,

                    success: function (resObj, statusText) {

                        $(e).find('button').attr('disabled', false);


                        if(resObj.success){

                            $(e).clearForm();

                            $('#modal_reset_password').modal('hide');

                            dg_user.fnDraw();

                            showGritter('success',resObj.title,resObj.msg);

                        }else{

                            showGritter('error',resObj.title,resObj.msg);

                        }

                    }

                });

            }

        });

    }


    $('#edit_record').click( function (e) {
		var selected_list = $('#dg_user tbody input[type=checkbox]:checked');
		var selected_length = selected_list.size();
		
		if(0 == selected_length){
			
			showGritter('info','Alert!','Please select a user to edit.');
			return false;
		}else if(selected_length > 1){
			showGritter('info','Alert!','Only single record can be edited at a time.');
			return false;
		}

		href = $('#edit_record').attr('href');
		href += '&id=' + selected_list.val();
		$('#edit_record').attr('href',href);
		return true;
	});

    $('#reset_password').click( function (e) {

        var selected_list = $('#dg_user tbody input[type=checkbox]:checked');

        var selected_length = selected_list.size();

        if(0 == selected_length){

            showGritter('info','Alert!','Please select a user to reset password.');

            return false;

        }else if(selected_length > 1){

            showGritter('info','Alert!','Only single user password can be reset at a time.');

            return false;

        }

        $('form#frm_reset_password').find('div.control-group').removeClass("has-success has-error");

        $('form#frm_reset_password').find('div.control-group span').text('');

        $('#user_id').val(selected_list.val());

        $('#modal_reset_password').modal('show');

        return true;

    });
	
	$('#delete_record').click(function(){
		
		var delete_ele = $('#dg_user tbody input[type=checkbox]:checked');
		var selected_length = delete_ele.size();
		
		if(0 == selected_length){
			showGritter('info','Alert!','Please select user to delete.');
			return false;
		}else{
			bootbox.confirm("Are you sure to delete selected user(s)? It will delete all user related data and can not be reverted", function(result) {
				if(result) {
					
					var delete_id = [];
					$.each(delete_ele, function(i, ele){
						delete_id.push($(ele).val());
					});
					
					$.ajax({
						url: 'control/users.php?act=delete',
						type:'post',
						dataType:'json',
						data:{ id : delete_id, },
						success: function(resp){
							dg_user.fnDraw();
							showGritter('success',resp.title,resp.msg);
						}
					});
				}
			});	
		}
	});
});

$(document).on("click","#reset_password5",function(){
    var rowData = dg_user.fnGetData($(this).parents('tr'));
    var user_id = rowData.user_id;
    $('form#frm_comment').find('div.control-group').removeClass("success error");
    $('form#frm_comment').find('div.control-group span').text('');
    $("#user_id").val(user_id);
    $('#modal_reset_password').modal('show');
});

function ExportToExcel(ele){

    var query_string = decodeURIComponent($.param(server_params));
    $(ele).attr('href','export_users.php?='+query_string);
    return true;
}


function DeleteRecord(rid){

    $('#chk_'+rid).prop('checked', true);
    $('#delete_record').click();
}

</script>
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class='span12'>
				<div class="table-header">
					<?php echo Utility::userTypeLabel("user",$ses->Get("user_type")) ?> List
					<span class="widget-toolbar pull-right">
                        <?php
                        if($acl->IsAllowed($login_id,'USERS', 'Users', 'Add User')){
                        ?>
						<a id='add_record' href="user_add.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'USERS', 'Users', 'Edit User')){
                        ?>
						<a id='edit_record' href="user_add.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'USERS', 'Users', 'Delete User')){
                        ?>
						<a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'USERS', 'Users', 'dExport User Details')){
                        ?>
                        <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="top" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                        <?php } ?>

                        <a id='reset_password' href="javascript:void(0)" data-placement="top" data-rel="tooltip" data-original-title="Reset Password" class="white"><i class="icon-key icon-large white"></i> Reset Password</a>

					</span>	
				</div>
				<table id='dg_user' class="table table-condensed table-bordered table-hover">
					<thead>
						<tr>
							<th class="center" width="5%">
								<label>
									<input type="checkbox"/>
									<span class="lbl"></span>
								</label>
							</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Role</th>
                            <th>Reporting To</th>
                            <th>Email</th>
                            <th>Mobile No.</th>
                            <th>Updated On</th>
                            <th>Updated By</th>
                            <th>Is Active</th>
                            <th>Total Activity</th>
                            <th>Action</th>
						</tr>
					</thead>
                    <tfoot>
                    <tr>
                        <th class="center">
                        </th>
                        <th>
                            <input type="text"  placeholder="name" name="name" class="span12" colPos="1">
                        </th>
                        <th>
                            <input type="text"  placeholder="user type" name="user_type" class="span10" colPos="7">
                        </th>
                        <th>
                            <input type="text"  placeholder="role" name="role" class="span10" colPos="6">
                        </th>
                        <th></th>
                        <th>
                            <input type="text"  placeholder="email" name="email" class="span7" colPos="4">
                        </th>
                        <th>
                            <input type="text"  placeholder="mobile" name="mobile" class="span12" colPos="5">
                        </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </tfoot>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

    <!-- Change user Model Box Start-->
    <div id="modal_reset_password" class="modal hide" tabindex="-1">
        <form id='frm_change' class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Reset Password</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label for="password" class="control-label">Password</label>
                            <div class="controls">
                                <input type="password" name='password' id='password'>
                                <input type="hidden" name="user_id" id="user_id"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="confirm_password" class="control-label">Confirm Password</label>
                            <div class="controls">
                                <input type="password" name='confirm_password' id='confirm_password'>
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
    <!-- Change user Model Box End-->


<?php 
include_once 'footer.php';
?>