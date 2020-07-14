<?php
include_once 'header_an.php';
include_once '../core/Validator.php';

$act = $db->FilterParameters($_GET['act']);

function CheckIfRegisteredUser($email){

    global $db;
    $count = $db->FunctionFetch('admin_user', 'count', 'user_id', "email='{$email}'");

    return ('0' == $count) ? false : true;
}

if(Core::isAjax()){

    if($act == 'forgot_password'){


        $validator = array(

            'rules' => array(
                'email' => array('required' => true, 'email' => true, 'registered_user' => true),
            ),
            'messages' => array(
                'email' => array('required' => 'Please enter your email id', 'email' => 'Please enter a valid email id', 'registered_user' => 'Invalid user'),
            )
        );

        $validator_obj = new Validator($validator);
        $requestTime = DATE_TIME_DATABASE;
        $validator_obj->addMethod('registered_user', 'CheckIfRegisteredUser');
        $errors = $validator_obj->validate($_POST);

        if(count($errors) > 0){

            echo json_encode(array('success' => false, 'errors' => $errors));
        }else{

            $_POST['email'] = $db->FilterParameters($_POST['email']);

            $userInfo = $db->FetchToArray("admin_user",array("user_id","concat(first_name,' ',last_name) as user_name"),"email = '{$_POST['email']}'");
            $token = Core::GenRandomStr(30);
            $data['token'] = $token;
            $data['email'] = $_POST['email'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['is_used'] = 0;
            $data['user_type'] = "admin";
            $data['type_id'] = $userInfo[0]['user_id'];

            $insert = $db->Insert('password_reset', $data);

            if($insert){


                $redirect_url = "".SITE_ROOT."crm/reset_password.php?token=$token";
                // Sending Mail
                $message = "Your Password Reset Link : <br/>"
                    . "<a href='$redirect_url'>Click Here To Reset<a><br/><br/>"
                    . "Or<br/><br/>"
                    . "Go to URL : $redirect_url";
                $to      = array($userInfo[0]['user_name']=>$_POST['email']);
                $subject = "CRM Partner : Your password reset link";

                $status = sMail($to,"CRM Partner",$subject,$message,"CRM Partner",USER_NAME);

                Utility::insertEMailLog($requestTime,$status,$userInfo[0]['user_id'],"",$message,$_POST['email']);
                //mail($to, $subject, $message, $headers);
                // Sending Mail Ends
                echo json_encode(array('success' => true,'email' => $_POST['email']));
            }
        }
    }
}
	