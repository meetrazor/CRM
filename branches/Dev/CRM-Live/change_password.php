<?php
$asset_css = array(
		'css/jquery.gritter'
);

$asset_js = array(
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-form/jquery.form',
		'js/jquery.gritter.min'
);

include_once 'header.php';

$page_display_name = 'Change Password';
?>
<!-- BEGIN Main Content -->
<div class="row-fluid">
	<div class="span12">
		<div class="box">
			<div class="box-title">
				<h3>
					<i class="icon-key"></i> <?php echo $page_display_name;?>
				</h3>
			</div>
			<div class="box-content">
				<form class="form-horizontal" id="form_changepass">

					<div class="control-group">
						<label class="control-label" for="old_password">Old Password:</label>
						<div class="controls">
							<div class="span12">
								<input type="password" name="old_password" id="old_password" class="input-xlarge" />
							</div>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="password">Password:</label>
						<div class="controls">
							<div class="span12">
								<input type="password" name="password" id="password" class="input-xlarge" />
							</div>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="confirm_password">Confirm
							Password:</label>
						<div class="controls">
							<div class="span12">
								<input type="password" name="confirm_password" id="confirm_password" class="input-xlarge"/>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn btn-primary">
							<i class="icon-ok bigger-110"></i>Submit
						</button>
						<button id='btn_cancel' type="button" class="btn">
							<i class="icon-undo bigger-110"></i>Reset
						</button>
                        <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                            wait...
                        </div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
            $(function() {

				$('#btn_cancel').click(function(){
					form = $(this).closest('form');
					form.find('div.control-group').removeClass("success error");
					form.find('span.help-inline').text("");
					form.clearForm();
				});
				$('#form_changepass input[type="password"]').val('');
            	
				if (jQuery().validate) {
					var e = function(e) {
            			$(e).closest(".control-group").removeClass("success");
            		};
	            	$("#form_changepass").validate({
	                	rules:{ 
		                	old_password:{required: true, minlength: 6 },
		                	password:{required: true, minlength: 6 },
		                	confirm_password:{required: true, minlength: 6, equalTo: "#password"}
                		},
	                	messages:{
	                		old_password:{required: 'Old password is required', minlength: 'Password must be great or equal to 6 character long' },
		                	password:{required: 'New password is required', minlength: 'Password must be great or equal to 6 character long' },
		                	confirm_password:{required: 'Confirm password is required', minlength: 'Password must be great or equal to 6 character long', equalTo: 'Confirm password does not match' }
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
								url: 'control/change_pass.php?act=change_pass',
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
									$('#msg-title').text(resObj.title);
									$('#msg-txt').text(resObj.msg);
									if(resObj.success){
										$(e).clearForm();
										showGritter('success',resObj.title,resObj.msg);
									}else{
										showGritter('error',resObj.title,resObj.msg);
									}
								}
							});
						}
					});
				}
            });
</script>
<!-- END Main Content -->
<?php
include_once 'footer.php';
?>