<?php 
$asset_css = array(
		'css/jquery.gritter',
		'css/chosen.create-option'
);

$asset_js = array(
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-validation/dist/jquery.validate.extension',
		'js/jquery-form/jquery.form',
		'js/jquery.gritter.min',
		'js/chosen.create-option.jquery'
);
$middle_breadcrumb = array('title' => 'User Permission', 'link' => 'user_permission_master.php');
include_once "header.php";
?>
<style type='text/css'>
.row-fluid [class*="span"]:first-child{ margin-left: 2.5641%; }
</style>
<script type="text/javascript">
$(document).on('click','.chk_select', function(){
	var id = $(this).val();
	var status = $(this).is(":checked");
	
	$("#div_"+id+" input:checkbox").prop("checked",status);
});


$(document).ready(function() {	

	$(".chzn-select").chosen();

	$("#role_id").change(function(){
		
		role_id = $("#role_id").val();
		
		$.ajax({
			url:'control/user_permission_master.php?act=getrole_data',
			data: {"role_id":role_id},
			type:'post',
			beforeSend: function(){
				$('#loader').show();
			},
			complete: function(){
				$('#loader').hide();
			},
			success: function(resp){
				$('#selrole_data').html(resp);
				$('.form-actions').show();
			}
		});
	});

	if (jQuery().validate) {

		$.validator.setDefaults({ ignore: ":hidden:not(select)" });
		
		var e = function(e) {
			$(e).closest(".control-group").removeClass("success");
		};
	
		$('#client_form').validate({		
			rules: { 
				"user_names[]" : { required: true},
				role_id : { required: true},
			},
			messages: { 
				"user_names[]" : { required: 'Please select atlease one user'},
				"role_id" : { required: 'Please select role' },
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
				$(e).closest(".control-group").removeClass("error").addClass("success");
			},
			submitHandler: function(form) {			
				
				$(form).ajaxSubmit({
					url: 'control/user_permission_master.php?act=add',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(form).find('button').hide();
                        $('#loader').show();
                    },
                    complete: function(){
                        $('#loader').hide();
                        $(form).find('button').show();
                    },
					type:'post',
					dataType: 'json',
					clearForm: false,
					success: function (resp, statusText){
                        $(form).find('button').show();
						showGritter(resp.msg_class, resp.title, resp.msg);
						if (resp.success) {
							$('#client_form').clearForm();
							setTimeout(function(){location.reload();},3000);
						}
					}
				});
			}
		});
	}
});
function clearFormAndError(form_id){

	$('form#'+form_id).find('div.control-group').removeClass("success error");
	$('form#'+form_id).find('div.control-group span').text('');
	$('form#'+form_id).clearForm();
}
</script>
<div class="page-header position-relative">
	<h4>Add User Permissions</h4>
</div>
<div class='row-fluid'>
	<div class="span12">
		<form class="form-horizontal" id="client_form">
			<div class="control-group">
				<label class="control-label" for="user_names">Select User</label>
				<div class="controls">
					<select name="user_names[]" id="user_names" multiple="multiple" class="chzn-select" data-placeholder='Select User'>
						<option value=""></option>        	
				        <?php //$rs = mysql_query("select admin_id, username from admin_user where is_active='1' and admin_id!=1 and admin_id not in(select usermaster_id from user_panel_permission) order by username");         
						$rs = mysql_query("select user_id, concat(first_name,' ',last_name) as user_name from admin_user where user_id not in(select usermaster_id from user_panel_permission) order by first_name");
						         
		        		if(mysql_num_rows($rs) > 0) {
						while($row_users = mysql_fetch_object($rs)) {
						?>
			        	<option value="<?php echo $row_users->user_id; ?>"><?php echo stripslashes($row_users->user_name); ?></option>
			        	<?php } } ?>
		       		</select>
		       		<span for="user_names" class="help-inline"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="role_id">Select Role</label>
				<div class="controls">
					<select name="role_id" id="role_id" class="chzn-select" data-placeholder='Select a role'>
						<option value=""></option>
		                <?php $rs_role = mysql_query("select role_id , role_name from role_master order by role_name");
							if(mysql_num_rows($rs_role) > 0){
								while($row_role = mysql_fetch_object($rs_role)){
						?>
		                <option value="<?php echo $row_role->role_id; ?>"><?php echo stripcslashes($row_role->role_name);?></option>
		                <?php }}?>
		            </select>
		            <span for="role_id" class="help-inline"></span>
		            <i id='loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
				</div>
			</div>
			<div id="selrole_data" class='row-fluid'></div>
			<div class='clearfix'></div>
      		<div class="form-actions hide">
				<button type="submit" class="btn btn-primary">
					<i class="icon-ok bigger-110"></i>Submit
				</button>
				<button id='btn_cancel' type="button" class="btn" onclick="clearFormAndError('client_form');">
					<i class="icon-undo bigger-110"></i>Reset
				</button>
                <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                    wait...
                </div>
			</div>
		</form>
	</div>
</div>
<?php
include_once "footer.php";
?>