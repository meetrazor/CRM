<?php 
$asset_css = array(
		'css/jquery.gritter',
        'css/chosen',
);

$asset_js = array(
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-validation/dist/jquery.validate.extension',
		'js/jquery-form/jquery.form',
		'js/jquery.gritter.min',
        'js/chosen.jquery.min',
);
$middle_breadcrumb = array('title' => 'Role Permission', 'link' => 'role_permission_master.php');
include_once "header.php";
$userCondition = ($userType == UT_BD || $userType == UT_TC) ? "user_type_id = ".$userType."" : "";
$userTypeDd = $db->CreateOptions("html","user_type_master",array("user_type_id","user_type_name"),null,array("user_type_name"=>"asc"),$userCondition);
?>
<script type="text/javascript">
var vRules = {
	role_name:{required:true},
	'user_type[]':{required:true}
};
var vMessages = { 		
	role_name:{required:"Please enter role name."}	
};

$(document).ready(function() {

    $(".chzn-select").chosen({
        allow_single_deselect:true,
    });
	
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
					url: 'control/role_permission_master.php?act=add',
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

function clearFormAndError(form_id){

	$('form#'+form_id).find('div.control-group').removeClass("success error");
	$('form#'+form_id).find('div.control-group span').text('');
	$('form#'+form_id).clearForm();
}
</script>
<div class="page-header position-relative">
	<h4>Add New Role And Permissions</h4>
</div>
<div class='row-fluid'>
	<div class="span12">
		<form class="form-horizontal" id="client_form">
			<div class="control-group">
				<label class="control-label" for="role_name">Role Name</label>
				<div class="controls">
					<input type="text" placeholder="Role Name" name="role_name" id="role_name">
				</div>
			</div>
            <div class="control-group">
                <label class="control-label" for="user_type">Related To</label>
                <div class="controls">
                    <select id="user_type" name="user_type[]" data-placeholder="select user type" multiple class="chzn-select">
                        <option></option>
                        <?php   echo $userTypeDd; ?>
                    </select>
                    <span for="user_type" class="help-inline"></span>
                </div>
            </div>
			<?php $section_type = $db->GetEnumvalues("user_perm_master","section_name"); 
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
					                            <?php //if($result_perm = $db->get_results("select * from user_perm_master where page_name='".$row_page->page_name."' order by perm_id")){ 
														///foreach($result_perm as $row_perm){
													  $res_perm = $db->Query("select * from user_perm_master where page_name='".$row_page->page_name."' order by perm_id");
													  if(mysql_num_rows($res_perm) > 0) {
													  	
													  	while($row_perm = mysql_fetch_object($res_perm)) {		
					                            
												?>
												<label>
													<input type="checkbox" name="tableid[<?php echo $row_perm->perm_id;  ?>]" id="checkbox_<?php echo $row_perm->perm_id; ?>" value="yes" title="<?php echo $row_perm->permission_desc ?>" />
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