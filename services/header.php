<?php
include_once '../core/Dbconfig.php';
include_once '../core/Db.php';
include_once '../core/Core.php';
include_once '../core/Utility.php';
include_once '../core/session.php';
include_once '../core/SiteSettings.php';
include_once '../core/sMail.php';


$db = new Db();
$utl = new Utility();
$core = new Core();
$ses = new Session();
$ses->init();
$db->ConnectionOpen();
