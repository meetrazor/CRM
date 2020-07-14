<?php
class Core {
	
	// returns boolean true or false by checking type of request is ajax or not
	public static function isAjax(){
		
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
	public static function StrLeft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2));
	}
	
	public static function LoginCheck($return = false){
		if(!isset($_SESSION['user_id']) || $_SESSION['user_id']==''){
		    if($return){
		        return true;
            } else {
                header('Location: login.php');
                exit;
            }

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
	
	public static function ArrayToHTMLOptions($option_array, $selected = null){
		$options = "";
		foreach ($option_array as $key => $val){
			
			//$options .= (!is_null($selected) && $val == $selected) ? "<option value='$key' selected='selected'>$val</option>" : "<option value='$key'>$val</option>";
			
			if(is_array($selected) && !empty($selected)){
				$options .= (in_array($key, $selected)) ? "<option value='$key' selected='selected'>$val</option>" : "<option value='$key'>$val</option>";
			}else{
				$options .= (!is_null($selected) && $key == $selected) ? "<option value='$key' selected='selected'>$val</option>" : "<option value='$key'>$val</option>";
			}
			
		}
		return $options;
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
	
	public static function PrintArray($data=array(),$status = 0){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		if($status == 1){
		    die();
        }
	}

	public static function searchInarray($value, $array) {
		$response = array();
		$i = 0;
		foreach ($array as $key => $val) {
			if (strpos($val['comment'], $value) !== false) {
				$response[$i] = $val['comment'];
				$i++;
			}
		}
		return $response;
	 }
	
	public static function PrependNullOption($option_list){
		return "<option value=''>--Select--</option>".$option_list;
	}
	
	public static function PrependEmptyOption($option_list){
		return "<option value=''></option>".$option_list;
	}
	
	public static function DisplayMessage($msg,$msg_type = 0, $autohide = 0) {
		$html= $class = $title = '';
		switch ($msg_type){
			case 1;
				$class="success_msg";
				$title="Success Message";
				break;
			case 2;
				$title="Notice Message";
				$class="notice_msg";
				break;
			case 3;
				$class='warning_msg';
				$title="Warning Message";
				break;
			case 0;
			default:
				$class='error_msg';
				$title="Error Message";
				break;
		}
		
		if($autohide != 0){
			$html =	"<script type='text/javascript'>$(function(){ $('#$class').delay(5000).slideUp('normal', function() { $(this).remove(); }); });</script>
					<div id='$class' class='msg_info'>".
						"<b>$title</b><br />$msg".
					"</div>";
		}else{
			$html =	"<div id='$class' class='msg_info'>".
						"<div class='dismiss'><a onclick=\"javascript:$('#$class').slideUp('normal', function() { $(this).remove(); });\" href='javascript:void(0)'></a></div>".
						"<b>$title</b><br />$msg".
					"</div>";
		}
		
		return $html;
	}
	
	public static function FilterNullValues($array = array(), $filter_zero = false){
		
		return ($filter_zero===true) ? array_filter($array) : array_filter($array,'strlen');
	}
	
	public static function CreateWhereEquals($search, $filter_null = true){
		
		$new_array_without_nulls = ($filter_null) ? Core::FilterNullValues($search) : $search;
		
		$condition = "";
		foreach ($new_array_without_nulls as $key => $val){
			
			$match_cond = (is_numeric($val)) ? "$key=$val" : "$key='$val'";
			$condition .= ($condition=='') ? " $match_cond" : " && $match_cond";	
		}
		return $condition;
	}
	
	public static function CreateWhereForSingleTable($search){
		
		$new_array_without_nulls = Core::FilterNullValues($search);
	//	echo "<pre>";
	//	print_r($new_array_without_nulls);
	//	echo "</pre>";
			
		$condition = "";
		foreach ($new_array_without_nulls as $key => $val){
			
			$match_cond = (is_numeric($val)) ? "$key=$val" : ((strtotime($val)) ? "$key='$val'" : "$key like '%$val%'");
			$condition .= ($condition=='') ? " $match_cond" : " && $match_cond";	
		}
		return $condition;
	}
	
	public static function YMDToDMY($ymd, $show_his = false){
		return ($show_his) ? date('d-m-Y h:i:s A',strtotime($ymd)) : date('d-m-Y',strtotime($ymd));
	}
	
	public static function DMYToYMD($dmy, $show_his = false){
        return ($show_his) ? date('Y-m-d h:i:s A',strtotime($dmy)) : date('Y-m-d',strtotime($dmy));
		//return date('Y-m-d',strtotime($dmy));
	}
	/**
	 * @author:
	 * @desc: clear browser cache 
	 */
	public function ClearBrowserCache() {
	    header("Pragma: no-cache");
	    header("Cache: no-cache");
	    header("Cache-Control: no-cache, must-revalidate");
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	}
	
	/**
	 * This function converts number to words in Indian rupee format
	 * If is useful where you need to show amount in words format  
	 * @param float $no <p>The Number to be converted into words</p>
	 */
	public function NumberToWords($no){
	
		//creating array  of word for each digit
		$words = array('0'=> 'Zero' ,'1'=> 'one' ,'2'=> 'two' ,'3' => 'three','4' => 'four','5' => 'five','6' => 'six','7' => 'seven','8' => 'eight','9' => 'nine','10' => 'ten','11' => 'eleven','12' => 'twelve','13' => 'thirteen','14' => 'fourteen','15' => 'fifteen','16' => 'sixteen','17' => 'seventeen','18' => 'eighteen','19' => 'nineteen','20' => 'twenty','30' => 'thirty','40' => 'forty','50' => 'fifty','60' => 'sixty','70' => 'seventy','80' => 'eighty','90' => 'ninty','100' => 'hundred','1000' => 'thousand','100000' => 'lakh','10000000' => 'crore');
	
	
		//for decimal number taking decimal part
	
		$cash=(int)$no;  //take number wihout decimal
		$decpart = $no - $cash; //get decimal part of number
	
		$decpart=sprintf("%01.2f",$decpart); //take only two digit after decimal
		
		$decpart=substr($decpart,2,2); //take fractional part
		//echo $decpart;exit;
		$decimalstr='';
	
		//if given no. is decimal than  preparing string for decimal digit's word
	
		if($decpart>0)
		{
			$decimalstr .= "point ".$this->NumberToWords($decpart);
		}
	
		if($no == 0)
		return ' ';
		else {
			$novalue='';
			$highno=$no;
			$remainno=0;
			$value=100;
			$value1=1000;
			while($no>=100)    {
				if(($value <= $no) &&($no  < $value1))    {
					$novalue=$words["$value"];
					$highno = (int)($no/$value);
					$remainno = $no % $value;
					break;
				}
				$value= $value1;
				$value1 = $value * 100;
			}
			if(array_key_exists("$highno",$words))  //check if $high value is in $words array
			return $words["$highno"]." ".$novalue." ".$this->NumberToWords($remainno).$decimalstr;  //recursion
			else {
				$unit=$highno%10;
				$ten =(int)($highno/10)*10;
				return $words["$ten"]." ".$words["$unit"]." ".$novalue." ".$this->NumberToWords($remainno
				).$decimalstr; //recursion
			}
		}
	}
	
	public static function GenRandomStr($length) {
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$string = '';    
		for ($p = 0; $p < $length; $p++) {
			$string.= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $string;
	}
	
	public static function GenRandomNumber($length) {
		$characters = "0123456789";
		$string = '';    
		for ($p = 0; $p < $length; $p++) {
			$string.= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $string;
	}

	public function UploadFile($fieldname, $maxsize, $uploadpath, $extensions=false, $ref_name=false) {
		$upload_field_name = $_FILES[$fieldname]['name'];
		if(empty($upload_field_name) || $upload_field_name == 'NULL' ) {			
			return array('file'=>$_FILES[$fieldname]["name"], 'status'=>false, 'msg'=>'Please upload a file');
		}
		//$file_extension = strtolower(end(explode(".",$upload_field_name)));
		$file_extension = strtolower(pathinfo($upload_field_name, PATHINFO_EXTENSION));
		
		if($extensions !== false && is_array($extensions) ) {
			if(!in_array($file_extension,$extensions) ) {
				return array('file'=>$_FILES[$fieldname]["name"], 'status'=>false, 'msg'=>'Please upload valid file');
			}			
		}
		$file_size = @filesize($_FILES[$fieldname]["tmp_name"]);
		if ($file_size > $maxsize) {
			return array('file'=>$_FILES[$fieldname]["name"], 'status'=>false, 'msg'=>'File Exceeds maximum limit');
		}
		if(isset($upload_field_name)) {
			if ($_FILES[$fieldname]["error"] > 0) {
				return array('file'=>$_FILES[$fieldname]["name"], 'status'=>false, 'msg'=>'Error: '.$_FILES[$fieldname]['error']);
			}
		}
		if($ref_name == false ) {
			//$file_name = time().'_'.str_replace(" ","_",$upload_field_name);
			
			$file_name_without_ext =  $this->FileNameWithoutExt($upload_field_name);
			$file_name = time().'_'.Core::RenameUploadFile($file_name_without_ext).".".$file_extension;
		} else {
			$file_name = str_replace(" ", "_",$ref_name).".".$file_extension;
		}
		if(move_uploaded_file($_FILES[$fieldname]["tmp_name"], $uploadpath.$file_name)) {			
			return array('file'=>$_FILES[$fieldname]["name"], 'status'=>true, 'msg'=>'File Uploaded Successfully!', 'filename'=>$file_name);
		} else {
			return array('file'=>$_FILES[$fieldname]["name"], 'status'=>false, 'msg'=>'Sorry unable to upload your file, Please try after some time.');			
		}
	}
	
	public function UploadMultipleFile($fieldname, $maxsize, $uploadpath, $index, $extensions=false, $ref_name=false) {
		$upload_field_name = $_FILES[$fieldname]['name'][$index];
		if(empty($upload_field_name) || $upload_field_name == 'NULL' ) {			
			return array('file'=>$_FILES[$fieldname]["name"][$index], 'status'=>false, 'msg'=>'Please upload a file');
		}
		
		//$file_extension = strtolower(end(explode(".",$upload_field_name)));
		$file_extension = strtolower(pathinfo($upload_field_name, PATHINFO_EXTENSION));
		
		if($extensions !== false && is_array($extensions) ) {
			if(!in_array($file_extension,$extensions) ) {
				return array('file'=>$_FILES[$fieldname]["name"][$index], 'status'=>false, 'msg'=>'Please upload valid file');
			}			
		}
		$file_size = @filesize($_FILES[$fieldname]["tmp_name"][$index]);
		if ($file_size > $maxsize) {
			return array('file'=>$_FILES[$fieldname]["name"][$index],'status'=>false, 'msg'=>'File Exceeds maximum limit');
		}
		if(isset($upload_field_name)) {
			if ($_FILES[$fieldname]["error"][$index] > 0) {
				return array('file'=>$_FILES[$fieldname]["name"][$index],'status'=>false, 'msg'=>'Error: '.$_FILES[$fieldname]['error']);
			}
		}
		$file_name = "";
		if($ref_name == false ) {
			
			$microtime = microtime();
			
			$search = array('.',' ');
			$microtime = str_replace($search, "_", $microtime);
			$file_name_without_ext =  $this->FileNameWithoutExt($upload_field_name);
			$file_name = $microtime .'_' . Core::RenameUploadFile($file_name_without_ext).".".$file_extension;
		} else {
			$file_name = Core::RenameUploadFile($ref_name).".".$file_extension;
		}
		if(move_uploaded_file($_FILES[$fieldname]["tmp_name"][$index], $uploadpath.$file_name)) {
			return array('file'=>$_FILES[$fieldname]["name"][$index], 'status'=>true, 'msg'=>'File Uploaded Successfully!', 'filename'=>$file_name);
		} else {
			return array('file'=>$_FILES[$fieldname]["name"][$index], 'status'=>false, 'msg'=>'Sorry unable to upload your file, Please try after some time.');			
		}
	}
	
	/**
	 * @author :
	 * @desc: This function filters the uploaded file name and properly rename it 
	 * @param: $data : data string
	 * changes : Other 4 characters are added 
	 */
	public static function RenameUploadFile($data) {
		$search = array("'"," ","(",")",".","&","-","\"","\\","?",":","/");
		$replace = array("","_","","","","","","","","","","");
		$new_data=str_replace($search, $replace, $data);
		return strtolower($new_data);
	}
	
	/**
	 * @author :
	 * @desc: This function same as RenameUploadFile function but given a proper name
	 * filters the string as it can be accepated by system 
	 * @param: $data : data string
	 * changes : Other 4 characters are added 
	 */
	public static function FilterString($data) {
		$search = array("'"," ","(",")",".","&","-","\"","\\","?",":","/");
		$replace = array("","_","","","","","","","","","","");
		$new_data=str_replace($search, $replace, $data);
		return strtolower($new_data);
	}
	
	/**
	 * @author :
	 * @desc: This function makes proper title from uploaded file 
	 * @param: $data : data string
	 * changes : Other 4 characters are added 
	 */
	public static function FileToTitle($data) {
		$search = array("'","(",")",".","&","\"","\\","?",":","/");
		$replace = array("","",""," ","","","","","","");
		$new_data=str_replace($search, $replace, $data);
		return ucwords($new_data);
	}
	
	public function FileNameWithoutExt($filename){
		return substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename,'.'))));
	}
	
	public static function PadString($number,$total_length, $prefix_text = '', $postfix_text = '',$padding_char = "0", $pad_side = 'left'){
		
		$string = '';
		switch ($pad_side){
			case 'right':
				$string = str_pad($number, $total_length, $padding_char, STR_PAD_RIGHT);
				break;
			default:
			case 'left':
				$string = str_pad($number, $total_length, $padding_char, STR_PAD_LEFT);
				break;
		}
		return $prefix_text.$string.$postfix_text;
	}
	
	public static function CheckSqlDateFormat($date,$sequence = 'dmy') {
		$date = substr($date, 0, 10);
		
		$date_arr = explode('-', $date);
		if(3 == sizeof($date_arr)){
			$day = $month = $year = 0;
			switch ($sequence){
				case 'ymd':
					list($year, $month, $day) = $date_arr;
					break;
				default:
				case 'dmy':
					list($day, $month, $year) = $date_arr;
					break;
			}
			
			if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
				return false;
			}
			return checkdate($month, $day, $year);		
		}else{
			return false;
		}
	}
	
	public static function LastMonthFirstLastDate($start_date = null){
		
		$lastmonth = array();
		
		if($start_date != null){
		if($start_date != null){
			$y = date('Y',strtotime($start_date));
			$m = date('n',strtotime($start_date));
			$lastmonth['start'] = date("$y-m-d",mktime(0,0,0,$m-1,1,date($y)));
			$lastmonth['end'] = date("$y-m-d",mktime(0,0,0,$m,0,date($y)));
			
		}else{
			$m = date('n');
			$lastmonth['start'] = date('Y-m-d',mktime(0,0,0,$m-1,1,date('Y')));
			$lastmonth['end'] = date('Y-m-d',mktime(0,0,0,$m,0,date('Y')));
		}
	}
		return $lastmonth;
	}
	
	public static function MonthsDropDown($selected = null){
		
		$months = array();
		for ($i = 0; $i < 12; $i++) {
		    $timestamp = mktime(0, 0, 0, date('n') - $i, 1);
		    //$months[date('n', $timestamp)] = date('F', $timestamp);
		    $months[date('m', $timestamp)] = date('F', $timestamp);
		}
		
		return Core::ArrayToHTMLOptions($months, $selected);
	}
	
	public static function YearDropDown($from,$to,$selected){
		$years = array();
		for($i=$to; $i>=$from; $i--){
			$years[$i] = $i;
		}
		return Core::ArrayToHTMLOptions($years, $selected);
	}
	
	public static function DaysDiffFromToday($date){
		
		$now = time(); // or your date as well
		$your_date = strtotime($date);
		$datediff = $your_date - $now;
		return floor($datediff/(60*60*24));
	}

    public static function StripAllSlashes($content) {

        return stripslashes(stripcslashes($content));
    }

    public static function UniqueFileName($id = null){

        $microtime = microtime();

        $search = array('.',' ');
        $microtime = str_replace($search, "_", $microtime);

        return ($id == null) ? $microtime : $id . '_' . $microtime;
    }

    public static function CheckEnvironment() {
        if(ENVIRONMENT == 'production'){
            return true;
        }
    }



    public static function numberFormat($number,$decimal = 2){
        if($number < 0){
            $number = abs($number);
            $result = "-".(number_format(round($number,$decimal),$decimal,'.', ','));
        }else {
            $result = number_format(round($number,$decimal),$decimal);
        }
        return $result;
    }

    	public static function multiSearchInarray($value,$value2, $array) {
		$response = array();
		$i = 0;
		foreach ($array as $key => $val) {
			if ((strpos($val['comment'], $value) !== false)||(strpos($val['comment'], $value2) !== false)) {
				$response[$i] = $val['comment'];
				$i++;
			}
		}
		return $response;
	 }


}
