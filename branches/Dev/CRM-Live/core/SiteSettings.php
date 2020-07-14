<?php
date_default_timezone_set('Asia/Calcutta');

define('BASEPATH','/sidbi_crm/');
define('SITE_ROOT',Core:: SelfURL().BASEPATH);

define('ASSETS', SITE_ROOT.'assets');
define('JS', SITE_ROOT.'js');
define('CSS', SITE_ROOT.'css');
define('IMAGES', SITE_ROOT.'images');
define('MAX_UPLOAD_SIZE', 10240000);
define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('DOC_ROOT', ABSOLUTE_PATH . BASEPATH);
//define('USER_NAME','care@capitaworld.com');
//define('PASSWORD','care#1234');
define('USER_NAME',"apikey");
define('PASSWORD',"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");
define('UPLOADS_PATH_REL', SITE_ROOT.'uploads/');
define('UPLOADS_PATH_ABS', ABSOLUTE_PATH . BASEPATH.'uploads/');
define("PARTNER_EDUCATION_IMAGE_PATH",SITE_ROOT."uploads/partner_education/");
define("PARTNER_EDUCATION_IMAGE_PATH_ABS",DOC_ROOT."uploads/partner_education/");
define("EMAIL_COMMANDS_ABS_PATH",DOC_ROOT."uploads/email_attachment/");
define("TICKET_IMAGE_PATH",SITE_ROOT."uploads/tickets/");
define("TICKET_IMAGE_PATH_ABS",DOC_ROOT."uploads/tickets/");
define("NEWS_IMAGE_PATH_REL",UPLOADS_PATH_REL."news/");
define("NEWS_IMAGE_PATH_ABS",UPLOADS_PATH_ABS."news/");

define('DEFAULT_TICKET',"[12,13,17,18]");

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

define("SMS_ROUTE",4);
define("SMS_SENDER","PSBLON");
define("SMS_AUTH","239062AsDW2pr55ba7669b");
define("SMS_ENCRYPT","CWORLD");

//mobile app constants
define('APP_USER_ID', -999);
define('PER_PAGE_LIMIT',10);
define('USER_NOT_FOUND', 101);
define('USER_INACTIVE', 102);
define('GOOGLE_API_KEY',"AIzaSyCoLnabbJn0Z7NFW1cJnvslW0z6HHIwxcE");

// user type

define('UT_ADMIN',1);
define('UT_BD',2);
define('UT_TC',3);
define('UT_IA',6);
define('UT_KC',4);
define('UT_ST',5);

define("SEARCH_CHARACTERS",3);
define("VENDOR_LAVAL",7);
define("CAMPAIGN_TYPE_SML",12);

/******Web Service Status Response Code*****/

define('SUCCESS',200);
define('AUTH_MISSING',400);
define('INVALID_AUTH',401);
define('AUTH_EXPIRE',402);
define('INVALID_REQUEST',403);
define('INVALID_PARAMETER',404);
define('INACTIVE_USER',405);
define('NO_DATE_FOUND',409);
define('MISCELLANEOUS',500);

/***** Email Type ******/
define('USER_REGISTRATION_MAIL',1);
define('PARTNER_REGISTRATION_MAIL',2);
define('NET_TICKET',3);

/** Vici Dial Details */

define('SOURCE',"crm");
define('USER',"arrow");
define('VICI_PASSWORD',"arrow");
define('AGENT',"demo");
define('API_URL','http://182.75.46.50/agc/api.php');
define('ONLINE_CAMPAIGN_ID',179);
define('SIDBI_CAMPAIGN_ID',192);


/******Disposition*****/

define('D_FOLLOW_UP',11);
define('D_LEAD',1);

define('R_BANK_SUPPORT',8);
/******Status*****/
define('S_OPEN',97);
