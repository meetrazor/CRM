<?php
class Core {
	
	// returns boolean true or false by checking type of request is ajax or not
	public static function isAjax(){
		
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
	public static function StrLeft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2));
	}
	
	public static function LoginCheck(){
		if(!isset($_SESSION['user_id']) || $_SESSION['user_id']==''){
			header('Location: login.php');
			exit;
		}
	}

    public static function tokenCheck($token=''){
        global $db;
        if($token != ''){
            $etoken = $token;
        } else {
            $etoken = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";;
        }
        if(!isset($_SESSION['token']) || !isset($etoken) |!isset($etoken) || $etoken=='' || ($etoken != $_SESSION['token'])){
            if(Core::isAjax()){
                $response['aut'] = 1;
                echo json_encode($response);
                exit;
            } else {
                header('Location: login.php?error=Invalid token session.Please login again');
            }
        }
    }
	
	public static function SelfURL(){
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = Core::StrLeft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port;
	}
	

	
	public static function CurrentPage(){
		
		$script_name = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'],'/')); 
		return substr($script_name, 1);
	}
	
	public static function isActiveLink($page){
		
		$current_page = Core::CurrentPage();

        $response = false;
        if(is_array($page)){

            if(in_array($current_page, $page)){

                $response = true;
            }
        }else{
            $response = ($page === $current_page);
        }
		return $response;
	}
	
	public static function PrintArray($data=array()){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
	
	public static function PrependNullOption($option_list){
		return "<option value=''>--Select--</option>".$option_list;
	}
	
	public static function PrependEmptyOption($option_list){
		return "<option value=''></option>".$option_list;
	}
	

	
	public static function YMDToDMY($ymd, $show_his = false){
		return ($show_his) ? date('d-m-Y h:i:s A',strtotime($ymd)) : date('d-m-Y',strtotime($ymd));
	}
	
	public static function DMYToYMD($dmy, $show_his = false){
        return ($show_his) ? date('Y-m-d h:i:s A',strtotime($dmy)) : date('Y-m-d',strtotime($dmy));
		//return date('Y-m-d',strtotime($dmy));
	}

	
	public static function GenRandomNumber($length) {
		$characters = "0123456789";
		$string = '';    
		for ($p = 0; $p < $length; $p++) {
			$string.= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $string;
	}


	


    public static function StripAllSlashes($content) {

        return stripslashes(stripcslashes($content));
    }

}