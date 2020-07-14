<?php
include_once 'header.php';
$act = $db->FilterParameters($_GET['act']);
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	if(isset($_GET["act"]) && $_GET["act"] == 'change_pass'){
		
		$_POST = $db->FilterParameters($_POST);
		
		$response = array(); 
		
		$password = md5($_POST['old_password']);
		$new_pass = md5($_POST['password']);
		$condition = "email='{$_SESSION['email']}' && pass_str='{$password}'";
		$res = $db->Fetch('admin_user', array('user_id'), $condition, null, array(0,1));
		$counter = $db->CountResultRows($res);
		if(1 == $counter) {
			//update password
			$data['pass_str'] = $new_pass;
			$update = $db->UpdateWhere('admin_user', $data, $condition);
			if($update){
				$response = array('success' => true,'title' => 'Success! ', 'msg' => 'Your password has been changed successfully!!!');
			}else{
				$response = array('success' => false,'title' => 'Error! ', 'msg' => 'Some Error Occured!!!');
			}
		} else {
			$response = array('success' => false,'title' => 'Error! ', 'msg' => 'Invalid Old Password!!!'); 
		}
		
		echo json_encode($response);
		exit;
	}
}
include_once 'footer.php';
?>