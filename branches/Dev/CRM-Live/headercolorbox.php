<?php
include_once 'core/session.php';
$ses = new Session();
$ses->init();

include_once 'core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once 'core/Db.php';
include_once 'core/sMail.php';
$db = new Db();
$db->ConnectionOpen();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();




// Creating Permission Object
include_once 'core/Permission.php';
$acl = new Permission();

include_once 'core/SiteSettings.php';
//Core::LoginCheck();
//Core::tokenCheck();

$current_page = Core::CurrentPage();
$page_display_name = Utility::PageDisplayName($current_page);
$token = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";

// Core::PrintArray($acl->AllowedPages());exit;
$login_id = $ses->Get('user_id');
$partnerCount = $db->FunctionFetch("partner_master","count","partner_id","is_new = 0");
$customerCount = $db->FunctionFetch("customer_master","count","customer_id","is_new = 0");
$leadCount = $db->FunctionFetch("lead_master","count","lead_id","is_new = 0");
if(!in_array($current_page, $acl->AllowedPages()) && !$acl->IsAllowedPage($login_id, $current_page)){

//	header('Location: no_access.php?token='.$token.'');
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
        <title>Alliance Partner Admin - Dashboard</title>

		<meta name="description" content="overview &amp; stats" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!--basic styles-->

		<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
		<link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="assets/css/font-awesome.min.css" />

		<!--[if IE 7]>
		  <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
		<![endif]-->

		<!--page specific plugin styles-->
		<?php 
		if(isset($asset_css) && !empty($asset_css)){ 
			foreach ($asset_css as $css_file){
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo ASSETS.'/'.$css_file.'.css';?>">
		<?php 
			}
		}
		?>
		<!--fonts-->

<!-- 		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" /> -->

		<!--ace styles-->

		<link rel="stylesheet" href="assets/css/ace.min.css" />
		<link rel="stylesheet" href="assets/css/ace-responsive.min.css" />
		<link rel="stylesheet" href="assets/css/ace-skins.min.css" />

		<!--[if lte IE 8]>
		  <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
		<![endif]-->

		<!--inline styles related to this page-->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--basic scripts-->

		<!--[if !IE]>-->

		<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!--<![endif]-->

		<!--[if IE]>
		<script type="text/javascript">
		 window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
		</script>
		<![endif]-->

		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<?php 
		if(isset($asset_js) && !empty($asset_js)){ 
			foreach ($asset_js as $js_file){
		?>
		<script type="text/javascript" src="<?php echo ASSETS.'/'.$js_file.'.js';?>"></script>
		<?php 
			}
		}
		?>
		<!--ace scripts-->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>
		
		<script type="text/javascript">
		function showGritter(gclass,gtitle,gmessage){
			$.gritter.add({
				time: '1000',
				title: gtitle,
				text: gmessage,
				class_name: 'gritter-'+ gclass +' gritter-center  gritter-light'
			});
		}
		
		$(document).ready(function(){
			$('table th input:checkbox').on('click' , function(){
				var that = this;
				$(this).closest('table').find('tr > td:first-child input:checkbox')
				.each(function(){
					this.checked = that.checked;
					$(this).closest('tr').toggleClass('selected');
				});
					
			});
		});
		</script>
		<style type="text/css">
			.main-content { margin-left: 0px; }
		</style>
	</head>
	<body>
		<div class="main-container container-fluid">
			<div class="main-content">
				<div class='page-content'>