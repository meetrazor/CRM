<?php
include_once 'header_an.php';
$act = $db->FilterParameters($_GET['act']);
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	if(isset($_GET["act"]) && $_GET["act"] == 'loginvalidate'){
		$_POST = $db->FilterParameters($_POST);
		
		$response = array(); 
		if(isset($_POST['csrf_token']) && $ses->Get('csrf_hash') == $_POST['csrf_token']){
			$password = md5($_POST['user_password']);
			
			$condition = "email='{$_POST['email']}' && pass_str='{$password}'";
			$res = $db->Fetch('admin_user', array('user_level','user_id','first_name','last_name','email','role_id','user_type','is_admin','agent_code'), $condition, null, array(0,1));
			$counter = mysql_num_rows($res);
			if(1 == $counter) {
				$data = mysql_fetch_assoc($res);
                $ses->Set("user_id",$data["user_id"]);
                $ses->Set("first_name",$data["first_name"]);
                $ses->Set("last_name",$data["last_name"]);
                $ses->Set("email",$data["email"]);
                $ses->Set("role_id",$data["role_id"]);
                $ses->Set("user_type",$data["user_type"]);
                $ses->Set("is_admin",$data["is_admin"]);
                $ses->Set("login_form",date('H:i'));
                $ses->Set("agent_code",$data['agent_code']);
                $ses->Set("user_level",$data['user_level']);
                $ses->Set("token",core::GenRandomStr(20));
				//unset($_SESSION['csrf_hash']);
				$response = array('success' => true,'title' => 'Login Successful', 'msg' => 'You have logged in success!!!','token'=>$ses->Get("token"),"user_type"=>$data['user_type'],'is_admin'=>$data['is_admin']);
			} else {
				$response = array('success' => false,'title' => 'Login Fail', 'msg' => 'Invalid Username/Password!!!'); 
			}
		}else{
			$response = array('success' => false,'title' => 'Error', 'msg' => 'Session Time out Or Invalid Source!!!');
		}
		echo json_encode($response);
		exit;
	}
}
include_once 'footer.php';
