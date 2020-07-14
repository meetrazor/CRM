<?php
class Db extends Dbconfig {

	public function CharactersetUTF8(){
		
		$sql = "SET character_set_results = 'utf8', " 
			 . "character_set_client = 'utf8', "
			 . "character_set_connection = 'utf8', "
			 . "character_set_database = 'utf8', "
			 . "character_set_server = 'utf8'";
		$this->Query($sql);
	}
	
	public function Query($sql){
		
		$result = mysql_query($sql);
		return $result;
	}

    public function setSqlMode(){

		$result = mysql_query("SET @@global.sql_mode= ''");
		return $result;
	}
	
	public function CountResultRows($resouce){
		
		return mysql_num_rows($resouce);	
	}
	
	public function GetNextAutoIncreamentValue($table){
		
		if(isset($table) && $table != ''){
			
			$result = $this->Query("SHOW TABLE STATUS WHERE name='$table'");
			
			$row = $this->MySqlFetchRow($result);
			
// 			echo '<pre>';
// 			print_r($row);exit;
			$next_inc_value = $row["Auto_increment"];
			
			return $next_inc_value;
		}
	}
	
	public function FetchRowInAssocArray($result){
		return mysql_fetch_assoc($result);
	}
	
	public function FetchTableField($table, $neglect = null) { //Function That fetch the fields of table
		
		$field = array();
		$sql="SHOW COLUMNS FROM `$table`";
		//echo $sql;
		$res=mysql_query($sql);
		
		while($data=@mysql_fetch_assoc($res)) {
			$field[]=$data["Field"];
		}
		
		if(is_array($neglect) && count($neglect) >= 1){
			
			foreach($neglect as $val) {
				$index = array_search($val, $field); 
				if($index !== false){
					unset($field[$index]);
				}
			}
		}
		return $field;
	}
	
	public function FetchToArray($table, $columns, $condition = null, $sort_by = null, $limit = null){
		
		$res = $this->Fetch($table, $columns, $condition, $sort_by, $limit);
		$array_res = array();
		
		if(mysql_num_rows($res)>=1){
			
			if($result_fields_count = mysql_num_fields($res) <= 1){
				for($i=0;$i<sizeof($res),$row = mysql_fetch_assoc($res);$i++)
					$array_res[] = $row[key($row)];
			}else{
				for($i=0;$i<sizeof($res),$row = mysql_fetch_assoc($res);$i++)
					$array_res[$i] = $row;
			}
		}
		return $array_res;
	}
	
	public function FetchToArrayFromResultset($res){
		
		$array_res = array();
		$res_rows = $this->CountResultRows($res); 
		if($res_rows >= 1){
			
			if($result_fields_count = mysql_num_fields($res) <= 1){
				for($i=0;$i<$res_rows,$row = mysql_fetch_assoc($res);$i++)
					$array_res[] = $row[key($row)];
			}else{
				for($i=0;$i<$res_rows,$row = mysql_fetch_assoc($res);$i++)
					$array_res[$i] = $row;
			}
		}
		return $array_res;
	}
	
	public function FetchRowForForm($table, $columns, $condition = null){
		
		$table_fields = $this->FetchTableField($table);
		$res = $this->FetchRowWhere($table, $columns, $condition);
		if($this->CountResultRows($res)>=1){
			$array_res = $this->MySqlFetchRow($res);
		}else{
			
			foreach ($table_fields as $key => $value){
				$array_res[$value] = '';
			}
		}
		return $array_res;
	}
	
	public function FetchRow($table,$filed,$filed_value,$columns = '*'){
		
		if(is_array($columns)){
			$columns = implode(", ", $columns);
		}
		
		$sql= "select $columns from $table where $filed='$filed_value' limit 0,1";
// 		echo $sql;
// 		exit;
		$res=mysql_query($sql);
		return $res;
	}
	
	public function FetchRowWhere($table,$columns = '*', $condition){
		
		if(is_array($columns)){
			$columns = implode(", ", $columns);
		}
		
		$sql= "select $columns from $table where $condition limit 0,1";
 //		echo $sql;
// 		exit;
		$res=mysql_query($sql);
		return $res;
	}
	
	public function FetchCellValue($table,$column,$where){

		$data = '';
		$query = "select $column from $table where $where";
//		echo $query ;
//        exit;
		$res = mysql_query($query);
		if($this->CountResultRows($res)){
			$res_data = $this->MySqlFetchRow($res);
			$data = $res_data[$column];	
		}
		return $data;
	}
					
	public function Fetch($table, $columns, $condition = null, $sort_by = null, $limit = null, $group_by = null){
		
		if(is_array($columns)){
			$columns = implode(", ", $columns);
		}
		
		if(is_null($condition) || $condition==""){
			$condition = "1=1";
		}
		
		$sort_order = "";
		if(is_array($sort_by) && $sort_by != null){
			
			foreach ($sort_by as $key => $val){
				
				$sort_order .= ($sort_order == "") ? "order by $key $val" : ", $key $val";
			}
		}
		
		if($group_by != null){
			$group_by = "group by ".$group_by;			
		}
		
		if(is_array($limit) && $limit != null){
			$limit = "limit ".$limit[0].", ".$limit[1];			
		}
		
		$sql= trim("select $columns from $table where $condition $group_by $sort_order $limit");
// 		echo $sql.'<br/>';
// 		exit;
		$res=mysql_query($sql);
		
		return $res;
	}
	
	public function JoinFetch($main_table = array(), $join_tables = array(), $condition = null, $sort_by = null, $limit = null, $group_by = null){
		
		$columns = isset($main_table[1]) ? $main_table[1] : array();
		$main_table = $main_table[0];
		
		$join_str = "";
		foreach ($join_tables as $join_table){
			
			$join_str .= $join_table[0]." join ".$join_table[1]." on (".$join_table[2].") ";
			
			if(isset($join_table[3])){
				$columns = array_merge($columns,$join_table[3]);
			}
		}
		
		$columns = (sizeof($columns) > 0) ? implode(", ", $columns) : "*";
		
		if(is_null($condition) || $condition==""){
			$condition = "1=1";
		}
		
		$sort_order = "";
		if(is_array($sort_by) && $sort_by != null){
			
			foreach ($sort_by as $key => $val){
				
				$sort_order .= ($sort_order == "") ? "order by $key $val" : ", $key $val";
			}
		}
		
		if($group_by != null){
			$group_by = "group by ".$group_by;			
		}
		
		if(is_array($limit) && $limit != null){
			$limit = "limit ".$limit[0].", ".$limit[1];			
		}
		
		$sql= trim("select $columns from $main_table $join_str where $condition $group_by $sort_order $limit");
 		//echo $sql.'<br/><br/><br/>';
// 		exit;
		$res=mysql_query($sql);
		
		return $res;
	}
	
	public function FunctionFetch($table, $function, $column, $condition = null, $limit = null, $group_by = null){
		
		if(is_array($column)){
			$column = implode(", ", $column);
		}
		
		if(is_null($condition) || $condition == ""){
			$condition = "1=1";
		}
		
		if($group_by != null){
			$group_by = "group by ".$group_by;			
		}
		
		if(is_array($limit) && $limit != null){
			$limit = "limit ".$limit[0].", ".$limit[1];			
		}
		
		$sql = "SELECT ".$function."(".$column.") as ret_val FROM $table where $condition $group_by $limit";
// 		echo $sql;
// 		exit;
		$res=mysql_query($sql);
		$ret_val = mysql_fetch_assoc($res);
		$ret_data = $ret_val['ret_val'];
		return $ret_data;
	}
	
	public function Insert($table,$data,$status = 0) {
		
		$data = $this->FilterParameters($data);
		$fields = array_keys($data);
		$table_fields = $this->FetchTableField($table);

		$parameters = array();
		for($i=0;$i<count($fields);$i++) {
			
			if(in_array($fields[$i], $table_fields)){

				if(is_array($data[$fields[$i]]) && count($data[$fields[$i]]) >1) {
					$data_arr=implode(',',$data[$fields[$i]]);
				} else {
					$data_arr=$data[$fields[$i]];
				}
				$str[]="'".$data_arr."'";
				$parameters[]=$fields[$i];
			}
		}
		
		$sql="insert into `$table` (`".implode('`,`',$parameters)."`) values (".implode(',',$str).")";
 		//echo $sql;
// 		exit;
		$res=mysql_query($sql);
		$id=mysql_insert_id();


		if($res) {
            if($table != 'activity_log'){
                $this->CreateActivityLog($table,$fields[0],$data,'Add',$id);
            }
			if($status == '1') {
				return $id;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function Update($table,$d,$id_field,$id_field_value) {
		
		$d = $this->FilterParameters($d);
		$f = array_keys($d);
		$table_fields = $this->FetchTableField($table);
		
		for($i=0;$i<count($f);$i++) {
			
			if(in_array($f[$i], $table_fields)){
				if(is_array($d[$f[$i]])) {
					if(sizeof($d[$f[$i]]) > 1){
						$data_arr=implode(',',$d[$f[$i]]);
					}else{
						$data_arr = $d[$f[$i]];
						$data_arr = $data_arr[0];
					}
				} else {
						
					if($d[$f[$i]]==""){
						$data_arr="";
					}
					else{
						$format_string = (string)$d[$f[$i]];
						$data_arr=$format_string;
					}
				}
				$parameters[]=$f[$i]."="."'".$data_arr."'";
			}
		}

		$sql="update $table set ".implode(',',$parameters)." where $id_field='".$id_field_value."'";
//		echo $sql;
//		exit;
		$res=mysql_query($sql);
		if($res) {
            $this->CreateActivityLog($table,$id_field,$d,'Update',$id_field_value);
			return true;
		} else {
			return false;
		}
	}
	
	public function UpdateWhere($table,$d,$condition) {

		$d = $this->FilterParameters($d);
		$f = array_keys($d);
		$table_fields = $this->FetchTableField($table);

		for($i=0;$i<count($f);$i++) {

			if(in_array($f[$i], $table_fields)){
				if(is_array($d[$f[$i]])) {
					if(sizeof($d[$f[$i]]) > 1){
						$data_arr=implode(',',$d[$f[$i]]);
					}else{
						$data_arr = $d[$f[$i]];
						$data_arr = $data_arr[0];
					}
				} else {

					if($d[$f[$i]]==""){
						$data_arr="";
					}
					else{
						$format_string = (string)$d[$f[$i]];
						$data_arr=$format_string;
					}
				}
				$parameters[]=$f[$i]."="."'".$data_arr."'";
			}
		}

		$sql="update $table set ".implode(',',$parameters)." where $condition";
// 		echo $sql;
// 		exit;
		$res=mysql_query($sql);
		if($res) {
            if($table != 'prospect_queue'){
                list($columnName,$etc,$data) = explode(" ",$condition);
                $this->CreateActivityLog($table,$columnName,$data,'Update');
            }
			return true;
		} else {
			return false;
		}
	}
	
	public function Delete($table, $id, $para){
		
		$para = $this->FilterParameters($para);
		if(is_array($para) && count($para)>0){
			$sql="delete from ".$table." where ".$id." in (".implode(',',$para).")";
		}else{
			$sql="delete from ".$table." where $id='$para'";
		}
//		echo $sql;
//		exit;
		$res=mysql_query($sql);
		
		if($res){
			return true;
		} else {
			return false;
		}
	}
	
	public function DeleteWhere($table,$condition){

		$sql="delete from ".$table." where $condition";

// 		echo $sql;
// 		exit;
		$res=mysql_query($sql);

		if($res){
            list($columnName,$etc,$data) = explode(" ",$condition);
            $this->CreateActivityLog($table,$columnName,$data,'Delete');
			return true;
		} else {
			return false;
		}
	}
		
	public function FilterParameters($array) {
		
		if(is_array($array)) {
			
			foreach($array as $key => $value) {
				
				if(is_array($array[$key])){
					$array[$key] = $this->FilterParameters($array[$key]);
				}
				if(is_string($array[$key])){
					$array[$key] = mysql_real_escape_string(trim($array[$key]));
				}
			}
		}
		
		if(is_string($array)){
			$array = mysql_real_escape_string(trim($array));
		}
		return $array;
	}
	
	public function CreateOptionsFromResutlset($option_result, $format = 'html', $selected = null){
		
		$count = 0;
		if(is_resource($option_result)){
			$count = $this->CountResultRows($option_result);
		}
		
		$options = ('array' == $format) ? array() : '';
		if( 0 < $count){
			
			// if something wrorng happens then remove this $column logic
			$columns = array();
			for($i = 0; $i < mysql_num_fields($option_result); $i++) {
			    $field_info = mysql_fetch_field($option_result, $i);
			    $columns[$i] = $field_info->name;
			}
			// Up to here
			
			while ($option_data = $this->MySqlFetchRow($option_result)){
				
				$option_data[$columns[1]] = stripslashes($option_data[$columns[1]]);
				
				switch ($format){
					case "array":
						$options[$option_data[$columns[0]]] = $option_data[$columns[1]];
						break;
					case "json":
						$options[] = array(
							"$columns[0]" => $option_data[$columns[0]],
							"$columns[1]" => $option_data[$columns[1]],
						);
						break;
					default:
					case "html":
						if(!is_null($selected)){
							
							if(is_array($selected)){
								$options[] = (in_array($option_data[$columns[0]], $selected)) ? "<option value='{$option_data[$columns[0]]}' selected='selected'>{$option_data[$columns[1]]}</option>" : "<option value='{$option_data[$columns[0]]}'>{$option_data[$columns[1]]}</option>";
							}else{
								$options[] = ($option_data[$columns[0]] == $selected) ? "<option value='{$option_data[$columns[0]]}' selected='selected'>{$option_data[$columns[1]]}</option>" : "<option value='{$option_data[$columns[0]]}'>{$option_data[$columns[1]]}</option>";
							}
						}else{
							$options[] = "<option value='{$option_data[$columns[0]]}'>{$option_data[$columns[1]]}</option>";
						}
						break;
				}
			}
				
			switch ($format){
				case "array":
					$options = $options;
					break;
				case "json":
					$options = json_encode($options);
					break;
				default:
				case "html":
					$options = implode("", $options);
					break;
			}
		}
		return $options;
	}
	public function CreateOptions($format,$table, $columns, $selected = null, $sort_by = null, $condition = null, $limit = null, $group_by = null){
		
		$option_result = $this->Fetch($table, $columns, $condition, $sort_by, $limit, $group_by);
		
		$options = $this->CreateOptionsFromResutlset($option_result, $format, $selected);
		return $options;
	}
	
	public function LikeSearchCondition($search_term, $search_columns = array()){
		
		$condition = "";
		
		foreach ($search_columns as $key => $val){
			$condition .= ($key == count($search_columns) - 1 ) ? "$val like '%$search_term%' " : "$val like '%$search_term%' OR ";
		}
		return $condition;
	}
	
	public function MySqlFetchRow($result, $type = 'assoc'){
		
		$row = false;
		if($result != false){
				
			switch ($type){
				case 'array' :
					$row = mysql_fetch_array($result,MYSQL_NUM);
					break;
				case 'object':
					$row = mysql_fetch_object($result);
					break;
				default:
				case 'assoc':
					$row = mysql_fetch_assoc($result);
					break;
			}	
		}
		return $row;
	}
	
	public function GetEnumvalues($table, $column) {
		$query = "SHOW COLUMNS FROM `".$table."` LIKE '".$column."'";
		//$this->query($query);
			
		$res = mysql_query($query);
		$row = mysql_fetch_array($res);
		$enum = $row['Type'];
		$off  = strpos($enum,"(");
		$enum = substr($enum, $off+1, strlen($enum)-$off-2);
		$values = explode(",",$enum);
		// For each value in the array, remove the leading and trailing
		// single quotes, convert two single quotes to one. Put the result
		// back in the array in the same form as CodeCharge needs.
		for( $n = 0; $n < count($values); $n++) {
			$val = substr( $values[$n], 1,strlen($values[$n])-2);
			$val = str_replace("''","'",$val);
			$values[$n] = stripslashes($val);//array( $val, $val );
		}
		return $values;
		//$values;
		//preg_match_all("/'([\w]*)'/", $this->last_result[0]->Type, $values);
		//return $values[1];
	}

    public function TimeStampAtCreate($user_id){

        $date = date('Y-m-d H:i:s');

        $create_log['created_at'] = $date;
        $create_log['created_by'] = $user_id;
        $create_log['updated_at'] = $date;
        $create_log['updated_by'] = $user_id;

        return $create_log;
    }

    public function TimeStampAtCreate_social_login(){

        $date = date('Y-m-d H:i:s');
        $create_log['created_at'] = $date;

        return $create_log;
    }

    public function TimeStampAtUpdate($user_id){

        $date = date('Y-m-d H:i:s');

        $update_log['updated_at'] = $date;
        $update_log['updated_by'] = $user_id;

        return $update_log;
    }

    /**
     * @author:
     * @desc: This function provides the list of existing tables in the database
     */
    public function ShowTables(){

        $table_res = mysql_query("SHOW TABLES FROM ". $this->GetDb());

        $tables = array();

        if(mysql_num_rows($table_res) > 0){

            while ($table_name = mysql_fetch_row($table_res)){

                $tables[] = $table_name[0];
            }
        }

        return $tables;
    }


    public  function CreateActivityLog($table,$columnName,$data,$act,$id = ""){
        global $ses;
        if(isset($columnName) && $columnName != null && $columnName != ''){
            if($act == 'Delete'){
                $dataArrayForActivityLog = $columnName." = ".$data;
            }
            else{
                if(is_array($data)){
                    $dataArrayForActivityLog = http_build_query($data,'',', ');
                }
                else{
                    $dataArrayForActivityLog = $columnName." = ".$data;
                }
            }
            $logId = ($id == '') ? '' : $id;
            $logColumn = $columnName;

            $userId = $ses->Get("user_id");
            $logModule = (isset($table) && $table != '') ? ucwords(str_replace("_"," ",$table)) : "";
            $this->LogActivity("".$logModule." $act [$logColumn: $logId, Data: [ $dataArrayForActivityLog ] ]",$userId,$logModule,$act);
        }
    }

    public function LogActivity($description, $userId, $logModule, $act)
    {
        global $misc;
        $userAgentData = $misc->detectUserAgent();;
        $log = array(
            'module' => $logModule,
            'action_name' => $act,
            'description' => $description,
            'log_date' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'user_browser' => $userAgentData['browser'],
            'user_platform'=> $userAgentData['platform'],
            'device_type'=> $userAgentData['device_type'],
            'user_ip'=> $userAgentData['ip'],
        );
        $this->insert('activity_log', $log);
    }

}
?>
