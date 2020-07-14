<?php





/**
 * Copyright (c) 2014-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

// Configurations
$access_token = "EAADB5BFuoxkBAFhBTr10tPRJg5asXSpwe6vgeoDZAebxI4tdNrL05ZANi2ZCplFijXtkRojXjGeZCyK7qnbLLBL0baZAgLoKuGYxsWft3N4IYwPaNBsi5PfE40b1arygW2zBpp2xoflZC5q6PrfCwZCROtZCxVcBQnAZD";
//$access_token = "EAADB5BFuoxkBADZCTK7xyRLygZAxwf5U7ZCdxUqHYxoqCQXKg2G7VBd3KTZA2HPUdJEyNdZAlOMjswjH1UBrAwo2934G0oP92D2VpUB1C9wRyu3Kj3rtw8ZBvUhh9gbqtHm7kE6kN7LhxaxA8wVKeZAykTYP9fxLYtN3raZC0ZA1lhQ96WLDLjW97lSS5Ct8lb3AZD";
$app_id = "213185289167641";
$app_secret = "d97651b98a89457d6d8dfda7ac82d711";
// should begin with "act_" (eg: $account_id = 'act_1234567890';)
$account_id = "act_218445318209991";
define('SDK_DIR', __DIR__ . '/..'); // Path to the SDK directory
$loader = include SDK_DIR.'/vendor/autoload.php';
date_default_timezone_set('Asia/Calcutta');
// Configurations - End

include_once '../../core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once '../../core/Db.php';
$db = new Db();
$db->ConnectionOpen();



if(is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
    throw new \Exception(
        'You must set your access token, app id and app secret before executing'
    );
}

if (is_null($account_id)) {
    throw new \Exception(
        'You must set your account id before executing');
}

use FacebookAds\Api;

Api::init($app_id, $app_secret, $access_token);

use FacebookAds\Http;
use FacebookAds\Object\Page;
use FacebookAds\Object\LeadgenForm;
use FacebookAds\Cursor;

Cursor::setDefaultUseImplicitFetch(true);


$page = new Page("449622328579373");
$leadsCursor = $page->getLeadgenForms();
$leadsResponse = $leadsCursor->getResponse();
$leadsContent = $leadsResponse->getContent();
echo "<pre>";
print_r($leadsContent);
echo "</pre>";
$next = $leadsCursor->fetchAfter();
$leadsResponse = $leadsCursor->getResponse();
$leadsContent = $leadsResponse->getContent();
//print_r($leadsContent);
die();

//$leadsCursor = $form->getLeads();
//$leadsCursor->setUseImplicitFetch(true);
$leadsCursor->end();
while ($leadsCursor->valid()) {
    $leadsResponse = $leadsCursor->getResponse();
    $leadsContent = $leadsResponse->getContent();
//    echo "<pre>";
//    print_r($leadsContent);
//    echo "</pre>";

    $leadsCursor->next();
   // die();
}
//die();
//echo "<pre>";
//print_r($leadsContent);
//echo "</pre>";
//die();


//$form = new LeadgenForm("199582417212668");
//$test = $form->read();
//echo "<pre>";
//print_r($test);
//echo "</pre>";
//die();


$form = new LeadgenForm("269843830119513");
$leadsCursor = $form->getLeads();

$leadsCursor->setUseImplicitFetch(true);
$leadsCursor->end();

while ($leadsCursor->valid()) {
    $leadsResponse = $leadsCursor->getResponse();
    $leadsContent = $leadsResponse->getContent();
    echo "<pre>";
     print_r($leadsContent);
    echo "</pre>";
//    foreach($leadsContent as $data){
//        foreach($data as $key => $val){
//
//            foreach($val['field_data'] as $userData){
//               // print_r($userData);
//                $userValue[$userData['name']] =  $userData['values'][0];
//            }
//            //$db->Insert("temp_fb",$userValue);
////            echo "<pre>";
////             print_r($userValue);
////            echo "</pre>";
//        }
//       // die();
//    }
//    echo "<pre>";
//   print_r($leadsContent);
//    echo "</pre>";
    $leadsCursor->next();
   // die();
}
die();


///**
// * Read Lead
//*/
//use FacebookAds\Object\Ad;
//
//$ad = new Ad("23842546398260751");
//$leads = $ad->getLeads();
//core::PrintArray($leads);