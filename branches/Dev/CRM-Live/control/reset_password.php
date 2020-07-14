<?php
include_once 'header_an.php';

$act = $db->FilterParameters($_GET['act']);


if(Core::isAjax()){
	
	if($act == 'reset_password'){
		
		$_POST = $db->FilterParameters($_POST);


		
		$token = $_POST['token'];
		$token_res = $db->FetchRowWhere('password_reset', "*","token='$token' && is_used='0'");
		if($db->CountResultRows($token_res) > 0){
            $requestTime = DATE_TIME_DATABASE;
			$token_data = $db->MySqlFetchRow($token_res);
			
			$user_res = $db->FetchRow('admin_user', 'email', $token_data['email'], array('user_id','first_name','email'));
			$count = $db->CountResultRows($user_res);
			if($count > 0){
				
				$user_data = $db->MySqlFetchRow($user_res);
				
				$data['pass_str'] = md5($_POST['password']);
				$update = $db->Update('admin_user', $data, 'user_id', $user_data['user_id']);
				
				if($update){
					
					$reset_data['is_used'] = 1;
					$reset_update = $db->Update('password_reset', $reset_data, 'token', $token);
					
					// Sending Mail
					$name = $user_data['first_name'];
					$message = "Hello {$name}, <br/>You password has been updated successfully!<br/>";

                    $to      = array($user_data['first_name']=>$user_data['email']);
					$subject = "CRM Partner : Password successfully updated";

                    $status = sMail($to,"CRM Partner",$subject,$message,"CRM Partner",USER_NAME);

                    Utility::insertEMailLog($requestTime,$status,$user_data['user_id'],"",$message,$user_data['email']);
					
					
					// Sending Mail Ends
					echo json_encode(array('success' => true));
				}
			}
		}else{
			
			echo json_encode(array('success' => false));
		}
	}
}
	