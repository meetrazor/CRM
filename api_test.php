<?php

require(__DIR__ . '/Httpful/Bootstrap.php');
\Httpful\Bootstrap::init();
use \Httpful\Request;

define('SOURCE',"crm");
define('USER',"arrow");
define('PASSWORD',"arrow");
define('AGENT',"demo");
define('API_URL','http://capitaworld.zapto.org/agc/api.php');


function pauseCall($agent) {


    $callURL = API_URL."?source=".SOURCE."&user=".USER."&pass=".PASSWORD."&agent_user=".AGENT."&function=external_pause&value=RESUME";
echo $callURL;
    //echo $smsUrl."<br/>";
    try {
        $resp['msg'] = Request::get($callURL)
            ->send();
        $resp['success'] = true;

        return $resp['msg'];

    }catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
        $resp['msg'] = "error";
        $resp['success'] = false;
        return $resp;
    }

}

function dialCall($agent) {


    $callURL = API_URL."?source=".SOURCE."&user=".USER."&pass=".PASSWORD."&agent_user=".AGENT."&function=external_dial&value=02242461330&phone_code=1&search=YES&preview=NO&focus=NO";
    echo $callURL;
    //echo $smsUrl."<br/>";
    try {
        $message = Request::get($callURL)
            ->send();
        $resp['success'] = true;
        return $message;

    }catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
        $resp['msg'] = "error";
        $resp['success'] = false;
        return $resp;
    }

}

function hangCall($agent) {


    $callURL = API_URL."?source=".SOURCE."&user=".USER."&pass=".PASSWORD."&agent_user=".AGENT."&function=external_hangup&value=1";
    echo $callURL;
    //echo $smsUrl."<br/>";
    try {
        $resp['msg'] = Request::get($callURL)
            ->send();
        $resp['success'] = true;
        return $resp;

    }catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
        $resp['msg'] = "error";
        $resp['success'] = false;
        return $resp;
    }

}

function statusCall($agent) {


    $callURL = API_URL."?source=".SOURCE."&user=".USER."&pass=".PASSWORD."&agent_user=".AGENT."&function=external_status&value=A";
    echo $callURL;
    //echo $smsUrl."<br/>";
    try {
        $resp['msg'] = Request::get($callURL)
            ->send();
        $resp['success'] = true;
        return $resp;

    }catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
        $resp['msg'] = "error";
        $resp['success'] = false;
        return $resp;
    }

}

//print_r(pauseCall(1));
//print_r(dialCall(1));
//print_r(hangCall(1));
print_r(statusCall(1));

//agent_user is not paused