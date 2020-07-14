<?php 
$asset_css = array(
		'css/jquery.gritter',
);

$asset_js = array(
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-validation/dist/jquery.validate.extension',
		'js/jquery-form/jquery.form',
		'js/jquery.gritter.min'
);
$middle_breadcrumb = array('title' => 'User Permission', 'link' => 'user_permission_master.php');
include_once "header.php";
 	
// 	if( ! $db->checkuserpermission($_SESSION['user_id'], 'USERS', 'User Permission', 'Edit') ) {
// 		//header("Location: index.php");
// 	}

$id = $db->FilterParameters($_GET['id']);
if(isset($id) && !empty($id) ) {
	$table = 'admin_user';
	$table_id = 'user_id';		
	$default_sort_column = 'v';		
	$condition = $table_id.'='.$id;		
	$rs = $db->Fetch($table, '*', $condition);
	if($db->CountResultRows($rs)){
		
		$row = $db->MySqlFetchRow($rs);
	}else{
	
		echo Utility::ShowMessage('Error', 'Invalid Record');
		include_once 'footer.php';
		exit;
	}
}else{
	
	echo Utility::ShowMessage('Error', 'Invalid Record');
	include_once 'footer.php';
	exit;
}

$user_name = "{$row['first_name']}"." "."{$row['last_name']}";
$user_name = ucwords($user_name);
$sel_role = $db->FetchRow('user_panel_permission', 'usermaster_id', $id, array('role_id'));
$row_roleid = $db->MySqlFetchRow($sel_role);
$sel_rolename = $db->FetchRow('role_master', 'role_id', $_SESSION['role_id'], array('role_name'));
$row_rolename = $db->MySqlFetchRow($sel_rolename);
$role_name = ucwords($row_rolename['role_name']);
?>
<script type="text/javascript">
var vRules = {
		
};
var vMessages = { 		
		
};

	
$(document).ready(function() {	

	$(".chk_select").change(function(){
		var id = $(this).val();
		var status = $(this).is(":checked");
		
		$("#div_"+id+" input:checkbox").prop("checked",status);
	});

	if (jQuery().validate) {
		var e = function(e) {
			$(e).closest(".control-group").removeClass("success");
		};
		$('#client_form').validate({		
			rules: vRules,
			messages: vMessages,
			errorElement : "span",
			errorClass : "help-inline",
			focusInvalid : false,
			ignore : "",
			invalidHandler : function(e, t) {},
			highlight : function(e) {
				$(e).closest(".control-group").removeClass("success").addClass("error");
			},
			unhighlight : function(t) {
				$(t).closest(".control-group").removeClass("error");
					setTimeout(function() {
						e(t);
					}, 3e3);
			},
			success : function(e) {
				$(e).closest(".control-group").removeClass("error").addClass("success");
			},
			submitHandler: function(form) {			
				
				$(form).ajaxSubmit({
					url: 'control/user_permission_master.php?act=edit',
					type: 'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(form).find('button').hide();
                        $('#loader').show();
                    },
                    complete: function(){
                        $('#loader').hide();
                        $(form).find('button').show();
                    },
					dataType: 'json',
					clearForm: false,
					success: function (resp, statusText) {
                        $(form).find('button').show();
						showGritter(resp.msg_class, resp.title, resp.msg);
						if (resp.success) {
							setTimeout(function(){location.reload();},3000);
						}
					}
				});
			}
		});
	}
});
</script>

<div class="page-header position-relative">
	<h4>Edit Permissions : <?php echo $user_name; ?></h4>
</div>
<div class='row-fluid'>
	<div class="span12">
		<form class="form-horizontal" id="client_form">
			<input type="hidden" name="usermaster_id" id="usermaster_id" value="<?php echo $row['user_id']; ?>" />
			<input type="hidden" name="role_id" id="role_id" value="<?php echo $row_roleid['role_id']; ?>" />
        <?php 
        $section_type = $db->GetEnumvalues("user_perm_master","section_name");

        $i=0;
	  	foreach($section_type as $section_name) {				
		?>
        
        <div class="span5 widget-container-span ui-sortable">
			<div class="widget-box">
				<div class="widget-header">
					<h5><?php echo ucfirst(strtolower($section_name)); ?> Options</h5>
					<div class="widget-toolbar">
						<a data-action="collapse" href="#">
							<i class="icon-chevron-up"></i>
						</a>
					</div>
				</div>
				<div class="widget-body">
					<div class="widget-body-inner" style="display: block;">
						<div class="widget-main">
                    	<?php 
						$res_page = $db->Query("select * from user_perm_master where section_name='".$section_name."' group by page_name order by display_order");
						if(mysql_num_rows($res_page) > 0) {
						?>
							<label>
								<input type='checkbox' class='chk_select' value='<?php echo $i;?>' />
								<span class="lbl"> Select All </span>
							</label>
							<div id='div_<?php echo $i;?>'>
								<table class='table'>
						<?php
						$i++;
						while($row_page = mysql_fetch_object($res_page)) {
						?>
                        <tr class="alternate">
                        	<td width="30%"><?php echo $row_page->page_name; ?></td>
                            <td>
                            <?php 
								  $res_perm = $db->Query("select * from user_perm_master where page_name='".$row_page->page_name."' order by perm_id");
								  if(mysql_num_rows($res_perm) > 0) {
								  	while($row_perm = mysql_fetch_object($res_perm)) {		
								  	$sql = "select * from user_panel_permission where usermaster_id='".$row['user_id']."' and perm_id='".$row_perm->perm_id."' and auth='1' ";
								  		$res_check = $db->Query($sql);
								  		if(mysql_num_rows($res_check) > 0 ) {
								  			$chk = 'checked="checked"';
								  		} else {
								  			$chk = '';
								  		}
							?>
                            <label>
								<input type="checkbox" <?php echo $chk;?> name="tableid[<?php echo $row_perm->perm_id;  ?>]" id="checkbox_<?php echo $row_perm->perm_id; ?>" value="yes" title="<?php echo $row_perm->permission_desc ?>" />
								<span class="lbl"> <?php echo $row_perm->permission_label ; ?></span>
							</label>
                            <?php } } ?>
							</td>
						</tr>
						<?php } ?>
                        	</table>
						</div>
                        <?php
						  } else{
						 		echo "No Action has been created!";
						  }
						?>
												</div>
											</div>
										</div>
									</div>
								</div>
								
        <?php } ?>   
		<div class='clearfix'></div>
      	<div class="form-actions">
			<button type="submit" class="btn btn-primary">
				<i class="icon-ok bigger-110"></i>Submit
			</button>
			<button id='btn_cancel' type="button" class="btn" onclick="clearFormAndError('client_form');">
				<i class="icon-undo bigger-110"></i>Reset
			</button>
            <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                wait...
            </div>
		</div>      
    </form>
  </div>
</div>
<?php
include_once "footer.php";
?>