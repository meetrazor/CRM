<?php
class Permission extends Db{
	
// 	public function checkuserpermission($userid, $section_name, $page_name=false, $perm_lbl=false) {

	private $default_allowed_pages = array(
		'index.php','login.php','no_access.php','add_permfields.php','edit_profile.php','change_password.php'
	);
	
	public function AllowedPages(){
		
		return $this->default_allowed_pages;
	}
	
	public function IsAllowed($userid, $section_name, $page_name=false, $perm_lbl=false) {
		
		$pg_sql = $pm_sql = '';
		if($page_name) {
			$pg_sql = " and page_name='".$page_name."' ";
		}
		if($perm_lbl) {
			$pm_sql = " and permission_label='".$perm_lbl."' ";
		}
			
		//			$sql = "select * from user_panel_permission where usermaster_id='".$userid."' and perm_id in (select perm_id from user_perm_master where section_name='".$section_name."' ".$pg_sql.$pm_sql.$rp_sql." ) ";
		$sql = "select * from user_panel_permission where usermaster_id='".$userid."' and perm_id in (select perm_id from user_perm_master where section_name='".$section_name."' ".$pg_sql.$pm_sql." ) ";
		//echo "<br/>".$sql."<br/>";
		$result = mysql_query($sql);
		if(mysql_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
        //return true;
	}
		
	public function IsAllowedPage($userid, $realpage_name) {
		
		$rp_sql = '';
		if($realpage_name){
			$rp_sql = " realpage_name='".$realpage_name."' ";
		}
		$sql = "select * from user_panel_permission where usermaster_id='".$userid."' and perm_id in (select perm_id from user_perm_master where ".$rp_sql." ) ";
// 		echo "<br/>".$sql."<br/>";exit;
		$result = mysql_query($sql);
		if(mysql_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
}