<?php
include_once 'core/session.php';
$ses = new Session();
$ses->init();
include_once 'core/Core.php';
$core = new Core();
include_once 'core/SiteSettings.php';
$csrf_token = Core::GenRandomStr(10);
$ses->Set('csrf_hash',$csrf_token);
$error = isset($_GET['error']) ? mysql_real_escape_string($_GET['error']) : "";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Login Page - CRM Admin</title>

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
    <script src='https://www.google.com/recaptcha/api.js'></script>

	<script src="assets/js/jquery.gritter.min.js"></script>

	<script type="text/javascript">
	$(document).ready(function(){

		$('#form-login').validate({
        	rules:{email: {required: true, email: true}, user_password: { required: true}, csrf_token:{ required: true }},
			errorPlacement: function(error,element) { return true; },
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					url: 'control/loginvalidate.php?act=loginvalidate',
					type:"post",
					beforeSubmit: function (formData, jqForm, options) {
						$(form).find('input[type="submit"]').show();
						$('#msg_wait').show();
					},
					dataType: 'json',
					clearForm: false,
					success: function (resObj, statusText) {
						if(true === resObj.success){
                            if(resObj.user_type == <?php echo UT_TC; ?> && resObj.is_admin != 1){
                                window.location = "index.php?token="+resObj.token+"";
                            } else {
                                window.location = "index.php?token="+resObj.token+"";
                            }

    					}else{
    						showError();
        				}
					},
					error: function(){

						showError();

						$(form).find('input[type="submit"]').show();

					}
				});
			}
		});

		$('#form-forgot').validate({
			rules:{email: {required: true, email: true}},
			messages: { email : {required: 'Please enter your email address', email: 'Invalid email address'}},
			submitHandler: function(form) {

				$(form).find('input[type=text]').removeClass('error');
				$(form).find('label.error').remove();

				$(form).ajaxSubmit({
					url: 'control/forgot_password.php?act=forgot_password',
					type:"post",
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

						$(form).find('input[type="submit"], button').show();

						if(resObj.success){
							$(form).clearForm();
							showGritter('Mail Sent', 'Password reset link has been mailed to ' + resObj.email, 'success');
							show_box('login-box');

    					}else{
							$.each(resObj.errors,function(key, value){

								$(form).find('input[name='+ key +']').addClass('error');

								if(!$(form).find('label[for="'+key+'"].error').length == 0){
									$(form).find('label[for="'+key+'"].error').text(value);
								}else{
									$(form).find('input[name='+ key +']').parent().append('<label for="'+key+'" class="error" style="display: block;">'+ value +'</label>');
								}
							});
        				}
					}
				});
			}
		});
	});

	function showError(){
		$.gritter.add({
			title: 'ERROR:',
			text: 'Invalid User Login',
			class_name: 'gritter-error gritter-center  gritter-light'
		});
	}

	function showGritter(title, msg, gclass){
		$.gritter.add({
			title: title,
			text: msg,
			class_name: 'gritter-' + gclass + ' gritter-center  gritter-light'
		});
	}

	</script>
</head>

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
                                                <h6 class="alert-danger lighter bigger">
                                                    <?php echo $error; ?>
                                                </h6>
												<h4 class="header blue lighter bigger">
													<i class="icon-key green"></i>
													Log In
												</h4>

												<div class="space-6"></div>

												<form id="form-login">
													<fieldset>
														<label>
															<span class="block input-icon input-icon-right">
																<input type='hidden' value='<?php echo $csrf_token;?>' name='csrf_token'/>
																<input type="text" name="email" class="span12" placeholder="Username" />
																<i class="icon-user"></i>
															</span>
														</label>

														<label>
															<span class="block input-icon input-icon-right">
																<input type="password" name="user_password" class="span12" placeholder="Password" />
																<i class="icon-lock"></i>
															</span>
														</label>

														<div class="space"></div>

														<div class="clearfix">
															<button type="submit" class="width-35 pull-right btn btn-small btn-primary">
																<i class="icon-key"></i>
																Login
															</button>
														</div>

														<div class="space-4"></div>
													</fieldset>
												</form>
											</div><!--/widget-main-->

											<div class="toolbar clearfix" style="background: #0FAC37!important;">
												<div>
													<a href="#" onclick="show_box('forgot-box'); return false;" class="forgot-password-link">
														<i class="icon-arrow-left"></i>
														I forgot my password
													</a>
												</div>
											</div>
										</div><!--/widget-body-->
									</div><!--/login-box-->

									<div id="forgot-box" class="forgot-box widget-box no-border">
										<div class="widget-body">
											<div class="widget-main">
												<h4 class="header red lighter bigger">
													<i class="icon-key"></i>
													Retrieve Password
												</h4>

												<div class="space-6"></div>
												<p>
													Enter your email and to receive instructions
												</p>

												<form  id="form-forgot" >
													<fieldset>
														<label>
															<span class="block input-icon input-icon-right">
																<input type="text" name='email' id='fotgot_pass_email' class="span12" placeholder="Email" />
																<i class="icon-envelope"></i>
															</span>
														</label>
														<div class="clearfix">
															<button type="submit" class="width-35 pull-right btn btn-small btn-danger">
																<i class="icon-lightbulb"></i>
																Send Me!
															</button>
                                                             <span id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                                                                wait...
                                                            </span>
														</div>
													</fieldset>
												</form>
											</div><!--/widget-main-->

											<div class="toolbar center">
												<a href="#" onclick="show_box('login-box'); return false;" class="back-to-login-link">
													Back to login
													<i class="icon-arrow-right"></i>
												</a>
											</div>
										</div><!--/widget-body-->
									</div><!--/forgot-box-->
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
