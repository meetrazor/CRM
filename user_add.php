<?php
$asset_css = array(
		'css/jquery.gritter',
        'css/datepicker',
        'css/chosen',
        'css/bootstrap-timepicker',
);

$asset_js = array(
		'js/jquery.gritter.min',
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-validation/dist/jquery.validate.extension',
		'js/jquery-form/jquery.form',
		'js/chosen.jquery.min',
		'js/jquery.autosize-min',
        'js/date-time/bootstrap-datepicker.min',
        'js/date-time/bootstrap-timepicker.min',
        'js/jquery.maskedinput.min',
);

$middle_breadcrumb = array('title' => 'Users', 'link' => 'users.php');
include_once 'header.php';
$table = 'admin_user';
$table_id = 'user_id';
$action = 'add';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field){
    $data[$field] = '';
}

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';

// If edit type then reassign data array
if(isset($id) && $id!=''){
    $result = $db->FetchRow($table, $table_id, $id);
    $count = $db->CountResultRows($result);
    if($count > 0){
        $action = 'edit';
        $row_data = $db->MySqlFetchRow($result);
        foreach ($data_fields as $field){
            $data[$field] = $row_data[$field];
        }
    }else{
        $error = 'Invalid Record Or Record Not Found';
    }
} else {
    $error = 'Invalid Record Or Record Not Found';
}
if(isset($id) && $id!=''){
    if($error != ''){
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),$data['state_id'],array("state_name"=>"asc"));
$userDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name) as user_name"),$login_id,array("first_name"=>"ASC"));
if($userType != UT_ADMIN and $isAdmin == 1){
    $userTypeRole = $db->FetchToArray("role_user_type","role_id","user_type_id = '{$userType}'");
    $userTypeRoleIds = (count($userTypeRole) > 0 ) ? implode(",",$userTypeRole) : "-1";
    $roleCondition = "role_id in ($userTypeRoleIds)";
} else {
    $roleCondition = "1=1";
}
$roleDd = $db->CreateOptions("html","role_master",array("role_id","role_name"),$data['role_id'],array("role_name"=>"ASC"),$roleCondition);
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$data['city_id'],array("city_name"=>"asc"));
$selectedCity = $db->FetchToArray("user_city","city_id","user_id = '{$id}'");
$prospectCityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$selectedCity,array("city_name"=>"asc"));
$selectedCategory = $db->FetchToArray("user_category","category_id","user_id = '{$id}'");
$prospectCategoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$selectedCategory,array("category_name"=>"asc"));
$selectedStage = $db->FetchToArray("user_query_stage","query_stage_id","user_id = '{$id}'");
$queryStageDd = $db->CreateOptions("html","query_stage_master",array("query_stage_id","query_stage_name"),$selectedStage,array("query_stage_name"=>"asc"));
$selectedReason = $db->FetchToArray("user_reason","reason_id","user_id = '{$id}'");
$reasonDd = $db->CreateOptions("html","reason_master",array("reason_id","reason_name"),$selectedReason,array("reason_name"=>"asc"));
$userCondition = ($userType != UT_ADMIN) ? "user_type_id = ".$userType."" : "";
$userTypeDd = $db->CreateOptions("html","user_type_master",array("user_type_id","user_type_name"),$data['user_type'],array("user_type_name"=>"asc"),$userCondition);
$userDd = $db->CreateOptions("ht/ml","admin_user",array("user_id","concat(first_name,' ',last_name)"),$data['reporting_to'],array("concat(first_name,' ',last_name)"=>"asc"),"user_type in(".UT_TC.",".UT_BD.")");
$userLevel = $db->GetEnumvalues("admin_user","user_level");
?>
<script type="text/javascript" xmlns="http://www.w3.org/1999/html">
$(function() {

    $(".chzn-select").chosen({
        allow_single_deselect:true,
    });

    $('.date-picker').datepicker({
        orientation: 'top',
        autoclose: true
    }).next().on(ace.click_event, function () {
        $(this).prev().focus();
    });



    $('#state_id').change(function(){
        var state_id = $(this).val();
        $.ajax({
            url: 'control/users.php?act=getcities', data : { state_id : state_id },type:'post',dataType: 'html',
            beforeSend: function(){
                $('#city_loader').show();
            },
            complete: function(){
                $('#city_loader').hide();
            },
            success: function(resp){
                $('#city_id').html(resp);
                $("#city_id").trigger("liszt:updated");
            }
        });
    });

	$('#btn_cancel').click(function(){
		form = $(this).closest('form');
		form.find('div.control-group').removeClass("success error");
		form.find('span.help-inline').text("");
		var email = $('#email').val();
		form.clearForm();
		$('#email').val(email);
        $('select.chzn-select').trigger("liszt:updated");
        $('select.chzn-select').trigger("chosen:updated");
		
	});

    $("#pincode").focusout(function(){
        var pincode = $(this).val();
        $.getJSON( "http://maps.googleapis.com/maps/api/geocode/json?address="+pincode+"&sensor=false&components=country:IN", function( data ) {
            if(data.status == 'OK'){
                var items = [];
                var result = data.results[0].formatted_address;
                var value = result.split(",");
                if((result.match(/,/g) || []).length == 2){
                    count = value.length;
                    state = value[count - 2];
                    stateonly = $.trim(state.replace(/[0-9$.]/g, ""));
                    city = value[count - 3];
                    $("#state_id option").filter(function() {
                        return this.text == stateonly;
                    }).attr('selected', true);
                    $("#state_id").trigger("liszt:updated");
                    $("#city_id option").filter(function() {
                        return this.text == city;
                    }).attr('selected', true);
                    $("#city_id").trigger("liszt:updated");
                } else {

                    $("#state_id option").filter(function() {
                        return this.value == '';
                    }).attr('selected', true);
                    $("#state_id").trigger("liszt:updated");
                    $("#city_id option").filter(function() {
                        return this.value == '';
                    }).attr('selected', true);
                    $("#city_id").trigger("liszt:updated");

                }
            }

        });
    });

    $("#user_type").change(function(){
        var userType = $(this).val();
        if(userType == <?php echo UT_BD; ?> || userType == <?php echo UT_KC; ?>){
            $("#prospect_city").show();
        }else {
            $("#prospect_city").hide();
        }

        if(userType == <?php echo UT_TC; ?>){
            $("#agent_code_dd").show();
        }else {
            $("#agent_code_dd").hide();
        }

        if(userType == <?php echo UT_BD; ?> || userType == <?php echo UT_KC; ?> || userType == <?php echo UT_ST; ?>){
            $("#reporting_to_dd").show();
            getReportingUsers();
        } else {
            $("#reporting_to_dd").hide();
        }

        if(userType == <?php echo UT_KC; ?>){
            $("#user_category_dd").show();
        } else {
            $("#user_category_dd").hide();
        }

        if(userType == <?php echo UT_ST; ?>){
           // $("#query_stage_dd").show();
            $("#user_level_dd").show();
            $("#reason_dd").show();
        } else {
           // $("#query_stage_dd").hide();
            $("#user_level_dd").hide();
            $("#reason_dd").hide();
        }
    });

	
	if (jQuery().validate) {
		var e = function(e) {
			$(e).closest(".control-group").removeClass("success");
		};
        jQuery.validator.addMethod("zipcode", function(value, element) {
            return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
        }, "Please provide a valid pin code.");

        jQuery.validator.addMethod("pan_card", function(value, element)
        {
            return this.optional(element) || /^[A-Za-z]{5}\d{4}[A-Za-z]{1}$/.test(value);
        }, "Invalid Pan Number");

		$("#form_add").validate({
			rules: { 
				email: {
                    required : true,
                    email: true,
                    "remote" :
                    {
                        url: 'control/users.php?act=checkemail',
                        data:{"user_id":"<?php echo $id;?>"},
                        async:false,
                        type: "post"
                    }
                },
				state_id: { required : true },
				city_id: { required : true },
				'user_city[]': { required : "#prospect_city:visible" },
				'query_stage[]': { required : "#query_stage:visible" },
				'reason_id[]': { required : "#reason_id:visible" },
				'user_category[]': { required : "#user_category:visible" },
				//reporting_to: { required : "#reporting_to:visible" },
				role_id: { required : true },
				user_type: { required : true },
				first_name: { required : true, regex: ['name','Name']},
				last_name: { required : true, regex: ['name','Name']},
				mobile_no: { required : true, regex: ['mobile','mobile']},
				//landline_no: { regex: ['landline','landline']},
                pincode:{zipcode:true},
                agent_code:{required:"#agent_code:visible"},
                filepath:{extension: "png|jpg|jpeg|gif" },
                password:{required: true, minlength: 6 },
                confirm_password:{required: true, minlength: 6, equalTo: "#password"},
                user_level: { required : "#user_level:visible" },
			},

			messages: {
				email: {
                    required : 'Please enter email address',
                    email: 'Please enter valid email address',
                    remote:"email is duplicate"
                },
				first_name: { required : 'Please enter first name'},
				last_name: { required : 'Please enter last name'},
				mobile_no: { required : 'Please enter mobile no'},
				state_id: { required : 'Please select state'},
				city_id: { required : 'Please select city'},
                'user_city[]': { required : "Please select prospect city" },
                'reason_id[]': { required : "Please select prospect reason" },
				role_id: { required : 'Please select role'},
				user_type: { required : 'Please select user type'},
                user_level: { required : 'Please select user level'},
                agent_code:{required:"Please enter agent code"},
                password:{required: 'New password is required', minlength: 'Password must be great or equal to 6 character long' },
                confirm_password:{required: 'Confirm password is required', minlength: 'Password must be great or equal to 6 character long', equalTo: 'Confirm password does not match' },

			},
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
				e.closest(".control-group").removeClass("error").addClass("success");
			},
			submitHandler : function(e) {
				
				$(e).ajaxSubmit({
					url: 'control/users.php?act=addedit',
					type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                        $('#loader').show();
                    },
                    complete: function(){
                        $('#loader').hide();
                        $(e).find('button').show();
                    },
					dataType: 'json',
					clearForm: false,
					success: function (resObj, statusText) {
						$(e).find('button').attr('disabled', false);
						
						if(resObj.success){
							showGritter('success',resObj.title,resObj.msg);
							setTimeout(function(){location.reload(true);},3000);
						}else{
                            if(resObj.hasOwnProperty("errors")){
                                var message = '';
                                $.each(resObj.errors,function(key,value){
                                    message += value + "<br>";
                                    showGritter('error',"Error",message);
                                });
                            } else {
                                showGritter('error',resObj.title,resObj.msg);
                            }
						}
					}
				});
			}
		});
	}

    $('.date-picker-bday').datepicker({
        autoclose: true,
        endDate:'<?php echo date("d-m-Y",strtotime("-16 years")); ?>',
    }).next().on(ace.click_event, function () {
        $(this).prev().focus();
    });

    $('#mobile_no').mask('9999999999');
    $('#landline_no').mask('(999)9999999?9');
    $('#pincode').mask('999999');
    $('#user_type').change();


    <?php
    if($action == 'add') {
    ?>
    setTimeout(function(){
        $('input').val("");
    }, 100);
    <?php } ?>
});

function getReportingUsers(){
    var userType = $("#user_type").val();
    $.ajax({
        url: 'control/users.php?act=getusers', data : { user_type : userType },type:'post',dataType: 'html',
        beforeSend: function(){
            $('#user_type_loader').show();
        },
        complete: function(){
            $('#user_type_loader').hide();
        },
        success: function(resp){
            $('#reporting_to').html(resp);
            $("#reporting_to").trigger("liszt:updated");
        }
    });
}
</script>

<div class='row-fluid'>
	<div class="span12">
		<form class="form-horizontal" id="form_add">
			
			<div class="control-group">
				<label class="control-label" for="email">Email<small class="text-error"> *</small></label>
				<div class="controls">
					<div class="span12">
						<input value="<?php echo $data['email']; ?>" type="email" name="email" id="email" class="" placeholder="name@company.com" />
					</div>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="first_name">First Name<small class="text-error"> *</small></label>
				<div class="controls">
					<div class="span12">
						<input value="<?php echo $data['first_name']; ?>" type="text" name="first_name" id="first_name" placeholder="ex. Naitik" />
						<input value="<?php echo $id; ?>" type="hidden" name="user_id" id="user_id" />
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="last_name">Last Name<small class="text-error"> *</small></label>
				<div class="controls">
					<div class="span12">
						<input value="<?php echo $data['last_name']; ?>" type="text" name="last_name" id="last_name" class="" placeholder="ex. Shah" />
					</div>
				</div>
			</div>



            <?php
            if($action == 'add') {
            ?>
            <div class="control-group">
                <label class="control-label" for="password">Password<small class="text-error"> *</small></label>
                <div class="controls">
                    <div class="span12">
                        <input type="password" name="password" id="password" autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="confirm_password">Confirm Password<small class="text-error"> *</small></label>
                <div class="controls">
                    <div class="span12">
                        <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" />
                    </div>
                </div>
            </div>
            <?php } ?>


            <div class="control-group">
                <label class="control-label" for="user_type">User Type<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="user_type" name="user_type" data-placeholder="select user type" class="chzn-select">
                        <option></option>
                        <?php   echo $userTypeDd; ?>
                    </select>
                    <i id='user_type_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                    <span for="user_type" class="help-inline"></span>
                </div>
            </div>

            <div class="control-group" id="reporting_to_dd">
                <label class="control-label" for="reporting_to">Reporting to</label>
                <div class="controls">
                    <select id="reporting_to" name="reporting_to" class="chzn-select" data-placeholder="select reporting to">
                        <option></option>
                        <?php echo $userDd; ?>
                    </select>
                    <span for="reporting_to" class="help-inline"></span>
                </div>
            </div>


            <div class="control-group" id="agent_code_dd">
                <label class="control-label" for="agent_code">Agent Code<small class="text-error"> *</small></label>
                <div class="controls">
                    <div class="span12">
                        <input value="<?php echo $data['agent_code']; ?>" type="text" name="agent_code" id="agent_code" class="" placeholder="ex. 0001" />
                    </div>
                </div>
            </div>

            <div class="control-group" id="query_stage_dd" style="display:none" >
                <label for="query_stage" class="control-label">Support Stage<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="query_stage" name="query_stage[]" data-placeholder="Select Prospect Stage" class="chzn-select" multiple>
                        <option></option>
                        <?php echo $queryStageDd; ?>
                    </select>
                    <i id='city_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                    <span for="query_stage" class="help-inline"></span>
                </div>
            </div>



            <div class="control-group" id="user_level_dd">
                <label class="control-label" for="call_from">User Level<small class="text-error"> *</small></label>
                <div class="controls">
                    <select class="chzn-select" data-placeholder="Select User Level" id="user_level" name="user_level">
                        <option></option>
                        <?php
                        if(count($userLevel) > 0){
                            foreach($userLevel as $userLevelData) {
                                $selected = ($userLevelData == $data['user_level']) ? "selected" : "";
                                ?>

                                <option value="<?php echo $userLevelData; ?>" <?php echo $selected; ?>><?php echo ucwords($userLevelData); ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                    <span for="user_level" class="help-inline"></span>
                </div>
            </div>


            <div class="control-group" id="reason_dd">
                <label for="reason_id" class="control-label">Reason<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="reason_id" name="reason_id[]" data-placeholder="Select Reason Stage" class="chzn-select" multiple>
                        <option></option>
                        <?php echo $reasonDd; ?>
                    </select>
                    <span for="reason_id" class="help-inline"></span>
                </div>
            </div>

            <div class="control-group" id="prospect_city">
                <label for="city_id" class="control-label">Prospect City<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="user_city" name="user_city[]" data-placeholder="Select Prospect City" class="chzn-select" multiple>
                        <option></option>
                        <?php echo $prospectCityDd; ?>
                    </select>
                    <i id='city_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                    <span for="user_city" class="help-inline"></span>
                </div>
            </div>

            <div class="control-group" id="user_category_dd">
                <label for="city_id" class="control-label">Select Product<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="user_category" name="user_category[]" data-placeholder="Select Product" class="chzn-select" multiple>
                        <option></option>
                        <?php echo $prospectCategoryDd; ?>
                    </select>
                    <span for="user_category" class="help-inline"></span>
                </div>
            </div>


            <div class="control-group">
                <label class="control-label" for="role_id">Role<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="role_id" name="role_id" data-placeholder="select role" class="chzn-select">
                        <option></option>
                        <?php   echo $roleDd; ?>
                    </select>
                    <span for="role_id" class="help-inline"></span>
                </div>
            </div>



            <div class='control-group'>
                <label class='control-label' for='birth_date'>
                    Birth Date:
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='input-small date-picker-bday' data-placement='top' type='text' placeholder='birth date'
                               name='birth_date' id="birth_date" data-date-format='dd-mm-yyyy'
                               readonly='readonly'
                               value="<?php echo ($data['birth_date'] != '0000-00-00' && $data['birth_date'] != '') ? core::YMDToDMY($data['birth_date']) : ""; ?>">
                        <span class='add-on'><i class='icon-calendar'></i></span>
                        <span for='start_date' class='help-inline'></span>
                    </div>
                </div>
            </div>


			<div class="control-group">
				<label class="control-label" for="address">Address</label>
				<div class="controls">
					<div class="span12">
						<textarea name="address" id="address" rows="3" ><?php echo $data['address']; ?></textarea>
					</div>
				</div>
			</div>


            <div class="control-group">
                <label for="pincode" class="control-label">Pincode</label>
                <div class="controls">
                    <input type="tel" value="<?php echo $data['pincode']; ?>" name='pincode' id='pincode' placeholder="ex.395001" />
                </div>
            </div>

            <div class="control-group">
                <label for="state_id" class="control-label">State<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="state_id" name="state_id" data-placeholder="Select State" class="chzn-select">
                        <option></option>
                        <?php echo $stateDd; ?>
                    </select>
                    <span for="state_id" class="help-inline"></span>
                </div>
            </div>

            <div class="control-group">
                <label for="city_id" class="control-label">City<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="city_id" name="city_id" data-placeholder="Select City" class="chzn-select">
                        <option></option>
                        <?php echo $cityDd; ?>
                    </select>
                    <i id='city_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                    <span for="city_id" class="help-inline"></span>
                </div>
            </div>


			
			<div class="control-group">
				<label class="control-label" for="mobile_no">Mobile<small class="text-error"> *</small></label>
				<div class="controls">
					<div class="span12">
						<input value="<?php echo $data['mobile_no']; ?>" type="tel" name="mobile_no" id="mobile_no"  placeholder="ex 8976677XXX" />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="landline_no">Landline</label>
				<div class="controls">
                    <input value="<?php echo $data['landline_no']; ?>" type="tel" name="landline_no" id="landline_no" class=""  placeholder="ex 261 2474270"/>
				</div>
			</div>
            <div class="controls">
                <label>
                    <input type="checkbox" name="is_active" id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : ""; ?>>
                    <span class="lbl">Is Active</span>
                </label>
            </div>

            <div class="controls">
                <label>
                    <input type="checkbox" name="is_admin" id="is_admin" <?php echo ($data['is_admin'] == 1) ? "checked" : ""; ?>>
                    <span class="lbl">Is Admin</span>
                </label>
            </div>

			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">
					<i class="icon-ok bigger-110"></i>Submit
				</button>
				<button id='btn_cancel' type="button" class="btn">
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
include_once 'footer.php';
?>