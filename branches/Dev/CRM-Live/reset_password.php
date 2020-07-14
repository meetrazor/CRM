<?php 

include_once 'core/Dbconfig.php';
// Creating Db Object and Opening Connection
include_once 'core/Db.php';
$db = new Db();
$db->ConnectionOpen();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();

include_once 'core/SiteSettings.php';

$token = (isset($_GET['token']) && $_GET['token'] != '') ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Reset Password - CRM Admin</title>

		<meta name="description" content="User login page" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<!--basic styles-->

		<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
		<link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="assets/css/font-awesome.min.css" />

		<!--[if IE 7]>
		  <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
		<![endif]-->

		<!--page specific plugin styles-->
		
		<link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.custom.min.css" />
		<link rel="stylesheet" href="assets/css/jquery.gritter.css" />
		<!--fonts-->
		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" />

		<!--ace styles-->

		<link rel="stylesheet" href="assets/css/ace.min.css" />
		<link rel="stylesheet" href="assets/css/ace-responsive.min.css" />
		<link rel="stylesheet" href="assets/css/ace-skins.min.css" />
		<style type="text/css">
		label.error {
			color: red;
		}
		</style>
		<!--[if lte IE 8]>
		  <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
		<![endif]-->

	<script src="assets/js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="assets/js/jquery-validation/dist/jquery.validate.min.js"></script>
	<script type="text/javascript" src="assets/js/jquery-form/jquery.form.js"></script>

	<script src="assets/js/jquery.gritter.min.js"></script>
	
	<script type="text/javascript">
	$(document).ready(function(){

		$('#form-reset').validate({
        	rules:{
            	password: { required: true, minlength: 5 },
            	confirm_password: { equalTo: "#password" },
            },
			messages:{
				
				password: {required:' Please enter new password.'},
				confirm_password: { equalTo: "Confirm password does not match" },
			},
			submitHandler: function(form) {
				
				$(form).find('input[type=text],input[type=password]').removeClass('error');
				$(form).find('label.error').remove();
				
				$(form).ajaxSubmit({
					url: 'control/reset_password.php?act=reset_password',
					type:"post",
					data : { token : '<?php echo $token;?>'},
                    beforeSubmit: function (formData, jqForm, options) {
                        $(form).find('input[type="submit"], button').hide();
                        $('#loader').show();
                    },
                    complete:function(){
                        $(form).find('input[type="submit"], button').show();
                        $('#loader').hide();
                    },
					dataType: 'json',
					clearForm: false,
					success: function (resObj, statusText) {
						if(resObj.success){
							
							showGritter('Successful', 'Your password has been changed successfully', 'success');
							setTimeout(function(){ 
        						$('div[id^="reset"]').hide();
        						window.location = 'login.php';
        					}, 3000);
    					}else{
    						showGritter('Error', 'Invalid Request', 'error');
        				}
					}
				});
			}
		});
	});

	function showGritter(title, msg, gclass){
		$.gritter.add({
			title: title,
			text: msg,
			class_name: 'gritter-' + gclass + ' gritter-center  gritter-light'
		});
	}
	
	</script>	
</head>
<?php 	
if($token == ''){
?>
<body class="login-layout">
<div class="row-fluid">
	<div class="login-container">
		<div>
			<div class="alert alert-error">
			    <strong>Error:</strong> Invalid request
			</div>
		</div>
	</div>
</div>
</body>
</html>
<?php 
	exit;
}

$token_data = array();
$token_res = $db->FetchRowWhere('password_reset', array('password_reset_id'),"token='$token' && is_used='0'");

if($db->CountResultRows($token_res) == 0){
?>
<body class="login-layout">
<div class="row-fluid">
	<div class="login-container">
		<div>
			<div class="alert alert-error">
			    <strong>Error:</strong> Invalid reset request
			</div>
		</div>
	</div>
</div>
</body>
</html>

<?php 	
	exit;
}
?>
	<body class="login-layout">
		<div class="main-container container-fluid">
			<div class="main-content">
				<div class="row-fluid">
					<div class="span12">
						<div class="login-container">
							<div class="row-fluid">
								<div class="center">
                                    <h1>
                                        <span class="" style="color: #0FAC37">CRM Admin</span>
                                    </h1>
								</div>
							</div>

							<div class="space-6"></div>

							<div class="row-fluid">
								<div class="position-relative">
									<div id="login-box" class="login-box visible widget-box no-border">
										<div class="widget-body">
											<div class="widget-main">
												<h4 class="header blue lighter bigger">
													<i class="icon-key green"></i>
													Reset Password
												</h4>

												<div class="space-6"></div>

												<form id="form-reset">
													<fieldset>
														<label>
															<span class="block input-icon input-icon-right">
																<input type="password" name="password" id='password'  class="span12" placeholder="New Password"/>
																<i class="icon-lock"></i>
															</span>
														</label>

														<label>
															<span class="block input-icon input-icon-right">
																<input type="password" name="confirm_password" class="span12" placeholder="Confirm New Password"/>
																<i class="icon-lock"></i>
															</span>
														</label>

														<div class="space"></div>

														<div class="clearfix">
															<button type="submit" class="width-35 pull-right btn btn-small btn-primary">
																<i class="icon-refresh"></i>
																Reset
															</button>
                                                            <span id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                                                                wait...
                                                            </span>
														</div>

														<div class="space-4"></div>
													</fieldset>
												</form>
											</div><!--/widget-main-->
										</div><!--/widget-body-->
									</div><!--/login-box-->
								</div><!--/position-relative-->
							</div>
						</div>
					</div><!--/.span-->
				</div><!--/.row-fluid-->
			</div>
		</div><!--/.main-container-->

		<!--basic scripts-->
		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>

		<!--page specific plugin scripts-->

		<!--ace scripts-->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!--inline scripts related to this page-->

		<script type="text/javascript">
			function show_box(id) {
			 $('.widget-box.visible').removeClass('visible');
			 $('#'+id).addClass('visible');
			}
		</script>
	</body>
</html>
