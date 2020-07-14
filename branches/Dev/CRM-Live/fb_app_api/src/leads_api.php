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
$access_token = "EAADB5BFuoxkBADovFOCDWVLhJ0M5eBcMXMXIJiJj0xCAvI77udK8Va012NZCoWjyyMNeGBs7mY3ViNMqZAswznRJI11GyjrP12ZB9LmeJ2ucqTqxmCBZCbzwqghv9DX6RyZC3XTZC7Vp71mXRYSIegOwBGUeCCw8kZD";
$app_id = 213185289167641;
$app_secret = "d97651b98a89457d6d8dfda7ac82d711";
// should begin with "act_" (eg: $account_id = 'act_1234567890';)
$account_id = "act_218445318209991";
define('SDK_DIR', __DIR__ . '/fb_app_api/'); // Path to the SDK directory
echo SDK_DIR;
require(__DIR__ . '/FacebookAds/api.php');
require(__DIR__ . '/FacebookAds/Session.php');
require(__DIR__ . '/FacebookAds/ApiConfig.php');
require(__DIR__ . '/FacebookAds/ApiRequest.php');
require(__DIR__ . '/FacebookAds/Cursor.php');
require(__DIR__ . '/FacebookAds/Http/Client.php');
require(__DIR__ . '/FacebookAds/Enum/EnumInstanceInterface.php');
require(__DIR__ . '/FacebookAds/Enum/AbstractEnum.php');
require(__DIR__ . '/FacebookAds/Object/Traits/AdLabelAwareCrudObjectTrait.php');
require(__DIR__ . '/FacebookAds/Object/CanRedownloadInterface.php');
require(__DIR__ . '/FacebookAds/Object/AbstractObject.php');
require(__DIR__ . '/FacebookAds/Object/AbstractCrudObject.php');
require(__DIR__ . '/FacebookAds/Object/AbstractArchivableCrudObject.php');
require(__DIR__ . '/FacebookAds/Object/Ad.php');
require(__DIR__ . '/FacebookAds/Object/Fields/AdFields.php');
require(__DIR__ . '/FacebookAds/TypeChecker.php');
require(__DIR__ . '/FacebookAds/Object/Values/AdBidTypeValues.php');
require(__DIR__ . '/FacebookAds/Object/Values/AdConfiguredStatusValues.php');
include_once(__DIR__ . '\FacebookAds\Object\Values');


date_default_timezone_set('Asia/Calcutta');
// Configurations - End

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


/**
 * Read Lead
*/
use FacebookAds\Object\Ad;

$ad = new Ad(23842546398260751);
$leads = $ad->getLeads();
core::PrintArray($leads);