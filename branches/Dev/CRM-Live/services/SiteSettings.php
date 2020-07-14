<?php
date_default_timezone_set('Asia/Calcutta');

define('BASEPATH','/alliance_partner/admin/');
define('SITE_ROOT',Core:: SelfURL().BASEPATH);

define('ASSETS', SITE_ROOT.'assets');
define('JS', SITE_ROOT.'js');
define('CSS', SITE_ROOT.'css');
define('IMAGES', SITE_ROOT.'images');
define('MAX_UPLOAD_SIZE', 10240000);
define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('DOC_ROOT', ABSOLUTE_PATH . BASEPATH);
define('USER_NAME','updates@sachinenterprises.co.in');
define('PASSWORD','updates@123');
define("PARTNER_EDUCATION_IMAGE_PATH",SITE_ROOT."uploads/partner_education/");
define("PARTNER_EDUCATION_IMAGE_PATH_ABS",DOC_ROOT."uploads/partner_education/");

/********* Date Constant *********/

define('STR_TO_TIME',strtotime(date("Y-m-d H:i:s")));

define('ONLY_DATE',date("d-m-Y"));

define('ONLY_DATE_YMD',date("Y-m-d"));

define('DATE_TIME_INDIAN',date("m-d-Y H:i:s"));

define('DATE_TIME_DATABASE',date("Y-m-d H:i:s"));

define('DATE_TIME_FORMAT',date("l dS F Y, H:i:s A", STR_TO_TIME));

define('DATETIMEFORMAT',date("l-dS-F-Y-H-i-s-A", STR_TO_TIME));

define('NO_RESULT', "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
            <div class='col-xs-12' align='center'>
            <h3><span style='color:#438EB9;'>No Result Found...</span></h3>
            </div>
            </div>");

define('NO_DATA', "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
            <div class='col-xs-12' align='center'>
            <h3><span style='color:#438EB9;'>No Data Found...</span></h3>
            </div>
            </div>");

define('PLEASE_WAIT', "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 record-list'>
            <div class='col-xs-12' align='center'>
            <h3><span style='color:#438EB9;'>Please Wait...</span></h3>
            </div>
            </div>");

define("SMS_PASS","92870664");
define("SMS_USER","capitaworld_com");
define("SMS_ID","CWORLD");

define('APP_USER_ID', -999);
define("PER_PAGE_LIMIT",10);