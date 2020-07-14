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
$db->setSqlMode();
$db->CharactersetUTF8();
// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

include_once 'core/SiteSettings.php';

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();

// Creating Permission Object
include_once 'core/Permission.php';
$acl = new Permission();

// Creating New Notification Object
include_once 'core/notification.php';
$nf = new Notifications();


Core::LoginCheck();
//Core::tokenCheck();

$current_page = Core::CurrentPage();
$page_display_name = Utility::PageDisplayName($current_page);
$token = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";

// Core::PrintArray($acl->AllowedPages());exit;
$login_id = $ses->Get('user_id');
$partnerCount = $db->FunctionFetch("partner_master","count","partner_id","is_new = 0");
$customerCount = $db->FunctionFetch("customer_master","count","customer_id","is_new = 0");
$leadCount = $db->FunctionFetch("lead_master","count","lead_id","is_new = 0");
$userType = $ses->Get("user_type");
$userLevel = $ses->Get("user_level");
$isAdmin = $ses->Get("is_admin");
$agentCode = $ses->Get("agent_code");
$activityType = $db->GetEnumvalues("activity_master","activity_type");
if(!in_array($current_page, $acl->AllowedPages()) && !$acl->IsAllowedPage($login_id, $current_page)){

	//header('Location: no_access.php?token='.$token.'');
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>CRM Admin - Dashboard</title>

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
				time: '3000',
				title: gtitle,
				text: gmessage,
				class_name: 'gritter-'+ gclass +' gritter-center  gritter-light'
			});
		}

        var wait = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>" +
            "<div class='col-xs-12' align='center'>" +
            "<h3><span style='color:#438EB9;'>Please wait...</span></h3>" +
            "</div>" +
            "</div>";
        var noResult = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>" +
            "<div class='col-xs-12' align='center'>" +
            "<h3><span style='color:#438EB9;'>No Result Found...</span></h3>" +
            "</div>" +
            "</div>";

		$(document).ready(function(){

           // var updateProspect = window.setInterval('updateProspect()', 25000); // 25 seconds
           // var notificationDisplayInterval = window.setInterval('notificationDisplay()', 10000); // 10 seconds

			$('table th input:checkbox').on('click' , function(){
				var that = this;
				$(this).closest('table').find('tr > td:first-child input:checkbox')
				.each(function(){
					this.checked = that.checked;
					$(this).closest('tr').toggleClass('selected');
				});
			});

            $.ajaxPrefilter(function(options) {

                if(options.url.indexOf("?") < 0){
                    options.url += "?" + "etoken=<?php echo $token; ?>";
                } else {
                    options.url += "&" + "etoken=<?php echo $token; ?>";
                }

            });

            $(document).ajaxSuccess(function(event, request, settings) {
                try {
                    var obj = jQuery.parseJSON( request.responseText );
                    if (obj.aut == '1') {
                        console.log(obj.aut);
                        window.location = '/login.php?error=Invalid token session.Please login again';
                    }
                } catch(e) {
                    //JSON parse error, this is not json (or JSON isn't in your browser)
                }
            });

			if (jQuery().dataTable) {

				$.extend( $.fn.dataTable.defaults, {
					iDisplayLength : 200,
					aLengthMenu : [[100, 200, 300, -1], [100, 200, 300, "All"]],
			    });
			}
		});

        var notificationDisplay = function() {
            $.ajax({
                url: 'control/lead.php?act=getundisplaynotification',
                type:'post',
                dataType: 'json',
                success: function(resp) {
                    if(resp.message != null){
                        $.gritter.add({
                            // (string | mandatory) the heading of the notification
                            title: 'New Notification!',
                            sticky: true,
                            // (string | mandatory) the text inside the notification
                            text: resp.message,
                            class_name: 'gritter-info gritter-right',
                            before_open: function(){
                                $.ajax({
                                    url: 'control/lead.php?act=notificationupdate',
                                    type:'post',
                                    async:true,
                                    dataType:'json',
                                    data:{"notification_id" : resp.notification_id},
                                    success: function(resp){
                                    }
                                });
                            },
                            before_close: function(e, manual_close){
                                $.ajax({
                                    url: 'control/lead.php?act=notificationupdate',
                                    type:'post',
                                    dataType:'json',
                                    async:true,
                                    data:{"notification_id" : resp.notification_id},
                                    success: function(resp){
                                    }
                                });
                            },
                        });
                    }
                }
            });
        };


        function updateProspect() {
            $.ajax({
                url: 'control/prospect.php?act=updateprospect',
                type:'post',
                dataType:'json'
            });
        }

		</script>


	</head>
	<body>
		<div class="navbar">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a href="index.php?token=<?php echo $token; ?>" class="brand">
						<small>
							<i class="icon-desktop"></i>
							CRM Admin
						</small>
					</a><!--/.brand-->
					<!--  -->
					<ul class="nav ace-nav pull-right">
						<li class="light-blue">
							<a data-toggle="dropdown" href="#" class="dropdown-toggle">
								<i class="icon-user"></i>
								<span class="user-info" style="padding-top: 8px;">
								<?php echo ucfirst($ses->Get('first_name')).' '.ucfirst($ses->Get('last_name'));?>
								</span>

								<i class="icon-caret-down"></i>
							</a>

							<ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
								<li>
									<a href="change_password.php?token=<?php echo $token; ?>">
										<i class="icon-key"></i>
										Change password
									</a>
								</li>
                                <!--
								<li>
									<a href="edit_profile.php">
										<i class="icon-pencil"></i>
										Edit Profile
									</a>
								</li>
								-->

								<li class="divider"></li>

								<li>
									<a href="logout.php">
										<i class="icon-off"></i>
										Logout
									</a>
								</li>
							</ul>
						</li>
					</ul><!--/.ace-nav-->
				</div><!--/.container-fluid-->
			</div><!--/.navbar-inner-->
		</div>

		<div class="main-container container-fluid">
            <?php if($userType == UT_TC) { ?>
                <?php if(1 == 1) { ?>
                <a class="menu-toggler" id="menu-toggler" href="#">
                    <span class="menu-text"></span>
                </a>

                <div class="sidebar menu-min" id="sidebar">
                    <div id="sidebar-shortcuts" class="sidebar-shortcuts">

                        <div id="sidebar-shortcuts-mini" class="sidebar-shortcuts-mini">
                            <span class="btn btn-success"></span>
                            <span class="btn btn-info"></span>
                            <span class="btn btn-warning"></span>
                            <span class="btn btn-danger"></span>
                        </div>
                    </div>

                    <?php include_once 'navigation.php';?>

                    <div class="sidebar-collapse" id="sidebar-collapse">
                        <i class="icon-double-angle-left icon-double-angle-right"></i>
                    </div>


                </div>
                <?php } ?>
            <?php } else { ?>
                <a class="menu-toggler" id="menu-toggler" href="#">
                    <span class="menu-text"></span>
                </a>

                <div class="sidebar menu-min" id="sidebar">
                    <div id="sidebar-shortcuts" class="sidebar-shortcuts">

                        <div id="sidebar-shortcuts-mini" class="sidebar-shortcuts-mini">
                            <span class="btn btn-success"></span>
                            <span class="btn btn-info"></span>
                            <span class="btn btn-warning"></span>
                            <span class="btn btn-danger"></span>
                        </div>
                    </div>

                    <?php include_once 'navigation.php';?>

                    <div class="sidebar-collapse" id="sidebar-collapse">
                        <i class="icon-double-angle-left"></i>
                    </div>


                </div>
            <?php } ?>
			<div class=<?php echo ($userType == UT_TC) ? (1 == 1) ? "main-content" : "" : "main-content" ?>>
                <?php if(1 != 1) { ?>
                    <?php if($isAdmin == 1) { ?>
                        <div class="breadcrumbs" id="breadcrumbs">
                            <ul class="breadcrumb">
                                <li>
                                    <i class="icon-home home-icon"></i>
                                    <a href="index.php?token=<?php echo $token; ?>">Home</a>

                                    <span class="divider">
                                        <i class="icon-angle-right arrow-icon"></i>
                                    </span>
                                </li>
                                <?php if(isset($middle_breadcrumb)) echo Utility::MiddleBreadCrumb($middle_breadcrumb['title'],$middle_breadcrumb['link']);?>
                                <li class="active"><?php echo $page_display_name;?></li>

                            </ul><!--.breadcrumb-->
                            <?php
                            if(isset($middle_breadcrumb))
                            echo '<span class="pull-right"><a href="'.$middle_breadcrumb['link']."?token=".$token.'">Back</a></span>';
                            ?>

                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="breadcrumbs" id="breadcrumbs">
                        <ul class="breadcrumb">
                            <li>
                                <i class="icon-home home-icon"></i>
                                <a href="index.php?token=<?php echo $token; ?>">Home</a>

                                    <span class="divider">
                                        <i class="icon-angle-right arrow-icon"></i>
                                    </span>
                            </li>
                            <?php if(isset($middle_breadcrumb)) echo Utility::MiddleBreadCrumb($middle_breadcrumb['title'],$middle_breadcrumb['link']);?>
                            <li class="active"><?php echo $page_display_name;?></li>

                        </ul><!--.breadcrumb-->
                        <?php
                        if(isset($middle_breadcrumb))
                            echo '<span class="pull-right"><a href="'.$middle_breadcrumb['link']."?token=".$token.'">Back</a></span>';
                        ?>

                    </div>
                <?php } ?>

				<div class='page-content'>
