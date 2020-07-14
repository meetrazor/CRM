<?php
include_once '../core/Dbconfig.php';
include_once '../core/Db.php';
include_once '../core/Core.php';
include_once '../core/Utility.php';
include_once '../core/session.php';
include_once '../core/SiteSettings.php';
include_once '../core/sMail.php';
include_once '../core/notification.php';
include_once '../core/Miscellaneous.php';


$db = new Db();
$utl = new Utility();
$core = new Core();
$ses = new Session();
$nf = new Notifications();
$misc = new Eventife_Model_Custom_Miscellaneous();
$ses->init();
$db->ConnectionOpen();
$db->CharactersetUTF8();
$userType = $ses->Get("user_type");
$isAdmin = $ses->Get("is_admin");
$agentCode = $ses->Get("agent_code");
$token = $ses->Get("token");
$userLevel = $ses->Get("user_level");
//Core::tokenCheck();
